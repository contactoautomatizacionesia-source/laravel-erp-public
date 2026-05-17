<?php

namespace Modules\Customer\Http\Controllers;

use App\Enums\FolderType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Customer\Entities\SignatureDocument;
use Modules\Customer\Exceptions\ProtecdataCallbackException;
use Modules\DigitalFolder\Entities\FolderFile;
use Modules\DigitalFolder\Repositories\DigitalFolderRepository;

class ProtecdataCallbackController extends Controller
{
    public function __construct(private DigitalFolderRepository $folderRepo) {}

    /**
     * Endpoint receptor del callback de ProtecData.
     *
     * ProtecData hace POST aquí cuando el usuario firma (o rechaza) un documento.
     * Payload esperado: { "id": "uuid-proceso", "estado": "True", "url": "https://..." }
     *
     * Si PROTECDATA_ENABLED=false, se responde 200 inmediatamente sin procesar.
     * Esto evita efectos secundarios en entornos de QA donde el servicio está apagado.
     */
    public function handle(Request $request): JsonResponse
    {
        Log::info('ProtecData callback recibido [RAW]', [
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        if (! (bool) (app('general_setting')->protecdata_enabled ?? false)) {
            Log::info('ProtecData callback recibido pero el servicio está desactivado (PROTECDATA_ENABLED=false).');
            return response()->json(['ok' => true]);
        }

        return $this->procesarCallback($request);
    }

    private function procesarCallback(Request $request): JsonResponse
    {
        $processId = $request->input('id');
        $estado    = $request->input('estado');
        $pdfUrl    = $request->input('url');

        Log::info('ProtecData callback recibido', [
            'process_id' => $processId,
            'estado'     => $estado,
        ]);

        try {
            $document = $this->resolverDocumento($processId);
        } catch (ProtecdataCallbackException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }

        // ProtecData envía estado como booleano true o string "True" según la versión
        if (filter_var($estado, FILTER_VALIDATE_BOOLEAN) && $pdfUrl) {
            try {
                $this->procesarFirmaCompletada($document, $pdfUrl);
            } catch (ProtecdataCallbackException $e) {
                Log::error('ProtecData: error al procesar firma', [
                    'process_id' => $processId,
                    'error'      => $e->getMessage(),
                ]);
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        return response()->json(['ok' => true]);
    }

    private function resolverDocumento(?string $processId): SignatureDocument
    {
        if (! $processId) {
            throw ProtecdataCallbackException::invalidPayload();
        }

        $document = SignatureDocument::with('batch.user')
            ->where('protecdata_process_id', $processId)
            ->first();

        if (! $document) {
            Log::warning('ProtecData callback: proceso no encontrado en BD', ['process_id' => $processId]);
            throw ProtecdataCallbackException::processNotFound($processId);
        }

        return $document;
    }

    // -------------------------------------------------------------------------
    //  Lógica interna
    // -------------------------------------------------------------------------
    private function procesarFirmaCompletada(SignatureDocument $document, string $pdfUrl): void
    {
        $user = $document->batch->user;

        Log::info('ProtecData: iniciando procesamiento de firma', [
            'document_id' => $document->id,
            'user_id'     => $user->id,
            'pdf_url'     => $pdfUrl,
        ]);

        // 1. Resolver carpeta Contratos/ del usuario en el File Explorer
        $userFolder      = $this->folderRepo->ensureUserFolder($user);
        $yearFolder      = $this->folderRepo->ensureYearFolder($userFolder);
        $contratosFolder = $this->folderRepo->ensureStandardSubfolder($yearFolder, FolderType::Contracts);

        Log::info('ProtecData: carpeta destino resuelta', [
            'document_id' => $document->id,
            'folder_id'   => $contratosFolder->id,
            'folder_path' => $contratosFolder->getPhysicalPath(),
        ]);

        // 2. Validar dominio antes de hacer la petición (prevenir SSRF)
        $allowedHost = 'validart2storage.blob.core.windows.net';
        $parsedHost  = parse_url($pdfUrl, PHP_URL_HOST);

        if ($parsedHost !== $allowedHost) {
            Log::error('ProtecData: URL del PDF no pertenece al dominio permitido (posible SSRF)', [
                'document_id'  => $document->id,
                'pdf_url'      => $pdfUrl,
                'parsed_host'  => $parsedHost,
                'allowed_host' => $allowedHost,
            ]);
            throw ProtecdataCallbackException::domainNotAllowed($parsedHost);
        }

        // 3. Descargar PDF firmado desde Azure Storage
        $response = Http::timeout(30)->get($pdfUrl);

        if (! $response->successful()) {
            Log::error('ProtecData: fallo al descargar PDF desde Azure', [
                'document_id' => $document->id,
                'pdf_url'     => $pdfUrl,
                'http_status' => $response->status(),
            ]);
            throw ProtecdataCallbackException::downloadFailed($response->status());
        }

        $pdfContent = $response->body();
        $pdfSize    = strlen($pdfContent);

        Log::info('ProtecData: PDF descargado correctamente', [
            'document_id' => $document->id,
            'pdf_url'     => $pdfUrl,
            'size_bytes'  => $pdfSize,
        ]);

        // 3. Guardar en disco
        $filename = $document->signed_filename;
        $path     = $contratosFolder->getPhysicalPath() . '/' . $filename;
        $stored   = Storage::put($path, $pdfContent);

        if (! $stored) {
            Log::error('ProtecData: fallo al guardar PDF en disco', [
                'document_id' => $document->id,
                'path'        => $path,
            ]);
            throw ProtecdataCallbackException::storageFailed($path);
        }

        Log::info('ProtecData: PDF guardado en disco', [
            'document_id' => $document->id,
            'path'        => $path,
            'size_bytes'  => $pdfSize,
        ]);

        // 4. Registrar en folder_files → visible en File Explorer del usuario
        FolderFile::create([
            'folder_id'     => $contratosFolder->id,
            'uploaded_by'   => $user->id,
            'name'          => $filename,
            'original_name' => $filename,
            'path'          => $path,
            'mime_type'     => 'application/pdf',
            'extension'     => 'pdf',
            'size'          => $pdfSize,
        ]);

        // 5. Marcar documento individual como firmado
        $document->update([
            'status'         => SignatureDocument::STATUS_SIGNED,
            'signed_at'      => now(),
            'pdf_local_path' => $path,
        ]);

        // 6. Recalcular status del lote padre
        $document->batch->recalculateStatus();

        Log::info('ProtecData: documento firmado y guardado exitosamente', [
            'user_id'      => $user->id,
            'batch_id'     => $document->signature_batch_id,
            'document_id'  => $document->id,
            'path'         => $path,
            'size_bytes'   => $pdfSize,
            'batch_status' => $document->batch->fresh()->status,
        ]);
    }
}
