<?php

namespace Modules\Customer\Services;

use Exception;
use App\Traits\ImageStore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Enums\FolderType;
use App\Models\User;
use App\Traits\SendMail;
use \Modules\Customer\Repositories\CustomerRepository;
use Modules\Customer\Entities\SignatureBatch;
use Modules\Customer\Services\ContractBuilderService;
use Modules\Customer\Services\ProtecdataService;
use Modules\DigitalFolder\Repositories\DigitalFolderRepository;

class CustomerService
{
    use ImageStore, SendMail;

    protected $customerRepository;
    protected $digitalFolderRepository;
    protected $protecdataService;
    protected $contractBuilderService;

    public function __construct(
        CustomerRepository      $customerRepository,
        DigitalFolderRepository $digitalFolderRepository,
        ProtecdataService       $protecdataService,
        ContractBuilderService  $contractBuilderService
    ) {
        $this->customerRepository      = $customerRepository;
        $this->digitalFolderRepository = $digitalFolderRepository;
        $this->protecdataService       = $protecdataService;
        $this->contractBuilderService  = $contractBuilderService;
    }

    public function getAll()
    {
        return $this->customerRepository->getAll();
    }

    public function find($id)
    {
        return $this->customerRepository->find($id);
    }

    public function store(array $data)
    {
        DB::beginTransaction();
        $paths = [];

        try {

            // Normalizar datos (SI -> 1)
            $data = $this->normalizeBooleanFields($data);

            // Crear Cliente (Sin archivos aún, null implícitamente)
            $user = $this->customerRepository->store($data);

            // Gestionar Archivos (Folder + Upload + Update Profile)
            $this->handleFilesUpload($user, $data);

            DB::commit();

            // Iniciar proceso de firma fuera de la transacción (igual que en registerCustomer).
            $this->iniciarProcesoFirma($user);

            return $user;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($data, $id){
        DB::beginTransaction();
        try {

            $data = $this->normalizeBooleanFields($data);

            $result = $this->customerRepository->update($data, $id);
            $user = $result['user'];

            $this->handleFilesUpload($user, $data);

            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy($id){
        return $this->customerRepository->destroy($id);
    }
    public function imageDelete($data){
        return $this->customerRepository->imageDelete($data);
    }
    public function BulkUploadStore($data){
        return $this->customerRepository->BulkUploadStore($data);
    }

    public function posCustomer()
    {
        return $this->customerRepository->posCustomer();
    }

    public function getCustomersByAjax($search){
        return $this->customerRepository->getCustomersByAjax($search);
    }

    public function getTrashedOnly()
    {
        return $this->customerRepository->getTrashedOnly();
    }

    public function restore($id)
    {
        return $this->customerRepository->restore($id);
    }

    public function getActiveCustomersForSelect($search)
    {
        return $this->customerRepository->searchActiveCustomers($search);
    }

    public function updateApprovalStatus($id, $toStatus, $reason = null, $changedBy = null)
    {
        DB::beginTransaction();
        try {
            $user = $this->customerRepository->updateApprovalStatus($id, $toStatus, $reason, $changedBy);
            DB::commit();
            if ($toStatus == User::APPROVAL_STATUS_APPROVED) {
                $this->userActivationMailSend('user_activation_template', $user);
            }
            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function registerCustomer(array $data, ?int $changedBy = null)
    {
        DB::beginTransaction();
        try {

            // Crear Cliente
            $user = $this->customerRepository->createCompleteCustomer($data, $changedBy);

            // Gestionar Archivos (carpeta digital + documentos de registro)
            $this->handleFilesUpload($user, $data);

            DB::commit();

            // Iniciar proceso de firma electrónica fuera de la transacción:
            // si ProtecData falla, el usuario ya quedó creado correctamente.
            // Con PROTECDATA_ENABLED=false esto crea el lote en BD sin llamar a la API.
            $this->iniciarProcesoFirma($user);

            return $user;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Genera los PDFs de contrato y los envía a ProtecData para firma electrónica.
     *
     * ContractBuilderService itera las plantillas activas en contract_templates,
     * genera cada PDF con mPDF, lo guarda en la carpeta Contratos/ del usuario y
     * devuelve el array que consume ProtecdataService::iniciarLote().
     *
     * Con PROTECDATA_ENABLED=false el lote se crea en BD pero no se
     * consume la API, evitando cargos en entornos de QA.
     */
    private function iniciarProcesoFirma(User $user): void
    {
        try {
            $contratos = $this->contractBuilderService->buildContratos($user);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('ContractBuilder: fallo al construir contratos', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
            return;
        }

        if (empty($contratos)) {
            return;
        }

        try {
            $this->protecdataService->iniciarLote($user, $contratos, SignatureBatch::TRIGGER_REGISTRATION);
        } catch (\Exception $e) {
            // No propagar: el usuario ya fue creado. Registrar para revisión.
            \Illuminate\Support\Facades\Log::error('ProtecData: fallo al iniciar lote de firma', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function updateProfileFromPortal(array $data, $id)
    {
        DB::beginTransaction();
        try {
            $user = $this->customerRepository->find($id);

            if (isset($data['avatar']) && $data['avatar'] instanceof \Illuminate\Http\UploadedFile) {

                if ($user->avatar) {
                    $this->deleteImage($user->avatar);
                }

                $data['avatar'] = $this->saveImage($data['avatar'], 200, 200);

                $user->avatar = $data['avatar'];
                $user->save();
            }

            $data = $this->normalizeBooleanFields($data);

            $this->customerRepository->update($data, $id);

            $this->handleFilesUpload($user, $data);

            DB::commit();

            return $user->refresh();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // -----------------------------------------------------------------------
    //                         MÉTODOS PRIVADOS
    // -----------------------------------------------------------------------

    /**
     * Coordina la creación de la jerarquía de carpetas y la subida de los documentos clave.
     *
     * Estructura creada:
     *   Master → Empresarios → {Usuario} → {Año} → Registro → archivos
     */
    private function handleFilesUpload($user, array $data)
    {
        // A. Master → Empresarios → carpeta del usuario
        $userFolder = $this->digitalFolderRepository->ensureUserFolder($user);

        // B. Subcarpeta del año en curso (p.ej. "2026")
        $yearFolder = $this->digitalFolderRepository->ensureYearFolder($userFolder);

        // C1. Subcarpeta estándar "Registro" (FolderType::Register)
        $registroFolder = $this->digitalFolderRepository->ensureStandardSubfolder($yearFolder, FolderType::Register);
        // C2 - Subcarpeta estandar "Contratos" (FolderType::Contracts)
        $contratosFolder = $this->digitalFolderRepository->ensureStandardSubfolder($yearFolder, FolderType::Contracts);

        $uploadedPaths = [];
        $fileMappings  = [
            'front_id_image' => 'front',
            'back_id_image'  => 'back',
            'rut_file'       => 'rut',
        ];

        try {
            // D. Subir archivos a la carpeta Registro
            foreach ($fileMappings as $dataKey => $pathKey) {
                if (isset($data[$dataKey])) {
                    $fileRecord = $this->digitalFolderRepository->uploadFile(
                        $data[$dataKey],
                        $registroFolder->id,
                        $user->id
                    );
                    $uploadedPaths[$pathKey] = $fileRecord->path;
                }
            }

            // E. Actualizar el perfil del cliente con las rutas generadas
            if (!empty($uploadedPaths)) {
                $this->customerRepository->updateCustomerFilePaths($user, $uploadedPaths);
            }
        } catch (Exception $e) {
            foreach ($uploadedPaths as $path) {
                if (Storage::exists($path)) {
                    Storage::delete($path);
                }
            }
            throw $e;
        }
    }

    /**
     * Helper para convertir SI/NO a 1/0
     */
    private function normalizeBooleanFields(array $data)
    {
        $booleanFields = [
            'public_resources', 'marital_society', 'is_pep', 'pep_family',
            'iva_responsibility', 'rent_retention_agent', 'ica_retention_agent',
            'sales_tax_responsible', 'grand_contributor', 'self_withholder',
            'source_retention', 'ica_tax', 'has_rut', 'ops_foreign_currency',
            'has_foreign_accounts'
        ];

        foreach ($booleanFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = ($data[$field] === 'SI') ? 1 : 0;
            } else {
                $data[$field] = 0;
            }
        }
        return $data;
    }
}
