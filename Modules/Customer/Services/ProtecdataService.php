<?php

namespace Modules\Customer\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Customer\Entities\SignatureBatch;
use Modules\Customer\Entities\SignatureDocument;

class ProtecdataService
{
    private bool   $enabled;
    private string $baseUrl;
    private string $username;
    private string $password;
    private string $callbackUrl;
    private string $companyName;
    private string $notification;

    public function __construct()
    {
        $cfg = config('customer.protecdata');

        // enabled se lee desde general_settings (BD) para que se pueda activar/desactivar
        // desde el panel admin sin necesidad de tocar el .env ni hacer deploy.
        $this->enabled      = (bool) (app('general_setting')->protecdata_enabled ?? false);
        $this->baseUrl      = rtrim($cfg['url'] ?? '', '/');
        $this->username     = $cfg['username']     ?? '';
        $this->password     = $cfg['password']     ?? '';
        $this->callbackUrl  = $cfg['callback_url'] ?? '';
        $this->companyName  = $cfg['company_name'] ?? '';
        $this->notification = $cfg['notification'] ?? '4';
    }

    /**
     * Indica si el servicio está habilitado.
     * Consultarlo antes de cualquier operación que consuma la API.
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    // -------------------------------------------------------------------------
    //  API — Primitivos
    // -------------------------------------------------------------------------

    /**
     * Obtiene un JWT fresco de ProtecData.
     * El token tiene vida útil limitada; obtener uno nuevo antes de cada lote.
     */
    public function authenticate(): string
    {
        $response = Http::post("{$this->baseUrl}/api/users/authenticate", [
            'username' => $this->username,
            'password' => $this->password,
        ]);

        return $response->json('token');
    }

    /**
     * Sube un PDF a ProtecData y devuelve el documentoid.
     * El archivo debe existir en storage antes de llamar este método.
     *
     * @param  string $token     JWT obtenido de authenticate()
     * @param  string $storagePath  Ruta relativa en storage (p.ej. "fileExplorer/1/3/7/12/contrato.pdf")
     */
    public function uploadDocument(string $token, string $storagePath): string
    {
        $content = Storage::get($storagePath);

        $response = Http::withToken($token)
            ->attach('file', $content, basename($storagePath))
            ->post("{$this->baseUrl}/api/Transaccion/upload/doc");

        return $response->json('id');
    }

    /**
     * Crea un proceso de firma en ProtecData y devuelve el process_id.
     *
     * @param  string $token       JWT
     * @param  string $documentoId Retornado por uploadDocument()
     * @param  array  $firmantes   Array de firmantes, cada uno con: Email, Indicativo, Telefono, Nombre, Pais
     * @return string $processId
     */
    public function createProcess(string $token, string $documentoId, array $firmantes): string
    {
        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/api/Transaccion/create", [
                'documentoid'   => $documentoId,
                'Firmantes'     => $firmantes,
                'NombreCreador' => $this->companyName,
                'Modificar'     => 'false',
                'Callback'      => $this->callbackUrl,
                'Notificacion'  => $this->notification,
            ]);

        $body = $response->json();

        Log::info('ProtecData createProcess response', ['body' => $body]);

        return (string) ($body['id'] ?? '');
    }

    // -------------------------------------------------------------------------
    //  Flujo de alto nivel
    // -------------------------------------------------------------------------

    /**
     * Crea un lote de firma para el usuario y envía cada contrato a ProtecData.
     *
     * Si el servicio está desactivado (PROTECDATA_ENABLED=false), registra el
     * lote en BD con status=pending pero NO llama a la API externa.
     * Esto permite que QA trabaje sin incurrir en costos reales.
     *
     * @param  User   $user
     * @param  array  $contratos  Lista de contratos a firmar. Cada elemento:
     *                            [
     *                              'company_name'      => 'Empresa A',
     *                              'contract_type'     => 'sagrilaft',
     *                              'storage_path'      => 'fileExplorer/.../contrato.pdf',  // PDF sin firmar
     *                              'original_filename' => 'empresa_a_contrato_42.pdf',
     *                              'signed_filename'   => 'empresa_a_contrato_42_firmado.pdf',
     *                            ]
     * @param  string $trigger    SignatureBatch::TRIGGER_*
     * @return SignatureBatch
     */
    public function iniciarLote(User $user, array $contratos, string $trigger = SignatureBatch::TRIGGER_REGISTRATION): SignatureBatch
    {
        $batch = SignatureBatch::create([
            'user_id'     => $user->id,
            'trigger'     => $trigger,
            'status'      => SignatureBatch::STATUS_PENDING,
            'total_docs'  => count($contratos),
            'signed_docs' => 0,
        ]);

        if (! $this->enabled) {
            // Servicio desactivado: registrar documentos sin protecdata_process_id.
            // El lote queda en pending y no se cobra nada a ProtecData.
            Log::info('ProtecData desactivado (PROTECDATA_ENABLED=false). Lote creado sin enviar a la API.', [
                'user_id'  => $user->id,
                'batch_id' => $batch->id,
            ]);

            foreach ($contratos as $contrato) {
                SignatureDocument::create([
                    'signature_batch_id'    => $batch->id,
                    'company_name'          => $contrato['company_name'],
                    'contract_type'         => $contrato['contract_type'],
                    'original_filename'     => $contrato['original_filename'],
                    'signed_filename'       => $contrato['signed_filename'],
                    'protecdata_process_id' => null, // sin enviar
                    'status'                => SignatureDocument::STATUS_PENDING,
                ]);
            }

            return $batch;
        }

        // Servicio activo: autenticar una sola vez y subir cada PDF
        $firmantes = $this->buildFirmantes($user);
        $token     = $this->authenticate();

        foreach ($contratos as $contrato) {
            try {
                $documentoId           = $this->uploadDocument($token, $contrato['storage_path']);
                $processId = $this->createProcess($token, $documentoId, $firmantes);

                SignatureDocument::create([
                    'signature_batch_id'    => $batch->id,
                    'company_name'          => $contrato['company_name'],
                    'contract_type'         => $contrato['contract_type'],
                    'original_filename'     => $contrato['original_filename'],
                    'signed_filename'       => $contrato['signed_filename'],
                    'protecdata_process_id' => $processId,
                    'status'                => SignatureDocument::STATUS_PENDING,
                ]);
            } catch (\Throwable $e) {
                // Si falla un documento, se registra con process_id null para reintento posterior.
                // No se aborta el lote completo para no perder los que sí se enviaron.
                Log::error('ProtecData: fallo al enviar documento', [
                    'user_id'      => $user->id,
                    'batch_id'     => $batch->id,
                    'company_name' => $contrato['company_name'],
                    'error'        => $e->getMessage(),
                ]);

                SignatureDocument::create([
                    'signature_batch_id'    => $batch->id,
                    'company_name'          => $contrato['company_name'],
                    'contract_type'         => $contrato['contract_type'],
                    'original_filename'     => $contrato['original_filename'],
                    'signed_filename'       => $contrato['signed_filename'],
                    'protecdata_process_id' => null,
                    'status'                => SignatureDocument::STATUS_PENDING,
                ]);
            }
        }

        return $batch;
    }

    // -------------------------------------------------------------------------
    //  Helpers privados
    // -------------------------------------------------------------------------

    private function buildFirmantes(User $user): array
    {
        $profile = $user->customerProfile;
        return [
            [
                'Email'      => $user->email,
                'Indicativo' => $profile?->whatsappCode?->code ?? 57,
                'Telefono'   => $profile?->whatsapp ?? $user->phone,
                'Nombre'     => $user->full_name,
                'Pais'       => 'Colombia',
            ],
        ];
    }
}
