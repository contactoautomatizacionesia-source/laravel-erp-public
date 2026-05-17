<?php

namespace Modules\Customer\Services;

use App\Enums\FolderType;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Appearance\Entities\AdminColor;
use Modules\Customer\Entities\ContractTemplate;
use Modules\Customer\Exceptions\NoActiveContractTemplateException;
use Modules\DigitalFolder\Entities\FolderFile;
use Modules\DigitalFolder\Repositories\DigitalFolderRepository;
use Spatie\Browsershot\Browsershot;

/**
 * Genera los PDFs de contrato para un usuario a partir de las plantillas activas
 * y los deposita en la carpeta digital del usuario.
 */
class ContractBuilderService
{
    public function __construct(
        private DigitalFolderRepository $digitalFolderRepository
    ) {}

    public function buildContratos(User $user): array
    {
        $templates = ContractTemplate::active()->with('brand')->get();

        if ($templates->isEmpty()) {
            return [];
        }

        $contratosFolder = $this->resolveContratosFolder($user);
        $basePath         = $contratosFolder->getPhysicalPath();

        $contratos = [];

        foreach ($templates as $template) {
            try {
                $originalFilename = "{$template->filename_prefix}_{$user->id}.pdf";
                $signedFilename   = "{$template->filename_prefix}_{$user->id}_firmado.pdf";
                $storagePath      = "{$basePath}/{$originalFilename}";

                $this->generateAndStore($user, $template, $storagePath);

                $this->registerInFileExplorer(
                    $storagePath,
                    $originalFilename,
                    $contratosFolder->id,
                    $user->id
                );

                $contratos[] = [
                    'company_name'      => $template->brand->name ?? $template->brand->getTranslation('name', 'es'),
                    'contract_type'     => $template->contract_type,
                    'storage_path'      => $storagePath,
                    'original_filename' => $originalFilename,
                    'signed_filename'   => $signedFilename,
                ];
            } catch (\Throwable $e) {
                Log::error('ContractBuilderService: fallo al generar contrato', [
                    'user_id'       => $user->id,
                    'template_id'   => $template->id,
                    'error'         => $e->getMessage(),
                ]);
            }
        }

        return $contratos;
    }

    public function previewLastContract(User $user): string
    {
        $template = ContractTemplate::active()->with('brand')->first();
        if (! $template) {
            throw new NoActiveContractTemplateException();
        }

        $data = $this->prepareViewData($user, $template);
        $renderedHtml = view($template->blade_view, $data)->render();

        return $this->getBrowsershotInstance($renderedHtml)->pdf();
    }

    // -------------------------------------------------------------------------
    //  Helpers privados
    // -------------------------------------------------------------------------

    /**
     * Centraliza la configuración de Browsershot para evitar discrepancias
     */
    private function getBrowsershotInstance(string $html): Browsershot
    {
        $instance = Browsershot::html($html);

        $userDataDir = storage_path('puppeteer_profile');
        if (!file_exists($userDataDir)) {
            mkdir($userDataDir, 0777, true);
        }

        $instance
            ->setChromePath(config('app.chrome_path', '/usr/bin/google-chrome'))
            ->setNodeBinary('/usr/bin/node')
            ->setNpmBinary('/usr/bin/npm')
            ->setNodeEnv([
                'HOME' => '/home/www-data',
                'PATH' => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
                'NODE_PATH' => base_path('node_modules'),
                'CHROME_CRASH_REPORTER_DISABLED' => '1',
            ])
            ->addChromiumArguments([
                'no-sandbox',
                'disable-setuid-sandbox',
                'disable-dev-shm-usage',
                'disable-gpu',
                'disable-breakpad',
                'no-zygote',
                'user-data-dir' => $userDataDir,
            ]);

        $instance
            ->paperSize(215.9, 279.4)
            ->margins(10, 12, 12, 12)
            ->showBackground();

        return $instance;
    }

    private function resolveContratosFolder(User $user)
    {
        $userFolder = $this->digitalFolderRepository->ensureUserFolder($user);
        $yearFolder = $this->digitalFolderRepository->ensureYearFolder($userFolder);
        return $this->digitalFolderRepository->ensureStandardSubfolder($yearFolder, FolderType::Contracts);
    }

    private function generateAndStore(User $user, ContractTemplate $template, string $storagePath): void
    {
        $data = $this->prepareViewData($user, $template);

        try {
            $renderedHtml = view($template->blade_view, $data)->render();
            $pdfOutput = $this->getBrowsershotInstance($renderedHtml)->pdf();
            Storage::put($storagePath, $pdfOutput);
        } catch (\Throwable $e) {
            Log::error('ContractBuilder FAIL', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function prepareViewData(User $user, ContractTemplate $template): array
    {
        $user->load([
            'customerProfile.documentType',
            'customerProfile.civilStatus',
            'customerProfile.profession',
            'customerProfile.economicActivity',
            'customerProfile.gender',
            'customerProfile.birthCity',
            'customerProfile.issueCity',
            'customerProfile.nationalityCountry',
            'customerProfile.whatsappCode',
            'customerProfile.representative.referralCode',
            'customerFinancialProfile.bank',
            'customerFinancialProfile.bankAccountType',
            'customerFinancialProfile.foreignCountry',
            'customerFinancialProfile.foreignCity',
            'customerFinancialProfile.declarationCity',
            'customerAddress.getState',
        ]);

        $profile = $user->customerProfile;
        $fp      = $user->customerFinancialProfile;
        $addr    = $user->customerAddress;
        $chk     = fn(bool $v) => $v ? 'X' : '';

        $adminColor = AdminColor::where('is_active', 1)->first();

        $resolvedColor = null;
        if ($adminColor) {
            $resolvedColor = $adminColor->color_mode === 'solid'
                ? $adminColor->solid_color
                : $adminColor->gradient_color_one;
        }

        $primaryColor = $resolvedColor ?? '#28a745';

        return [
            'user'             => $user,
            'profile'          => $profile,
            'primaryColor'     => $primaryColor,
            'financialProfile' => $fp,
            'fp'               => $fp,
            'address'          => $addr,
            'addr'             => $addr,
            'state'            => $addr?->getState?->name ?? '',
            'brand'            => $template->brand,
            'date'             => now()->locale('es_CO')->isoFormat('D [de] MMMM [de] YYYY'),
            'docCC'    => ($profile?->documentType?->name ?? '') === 'Cédula de Ciudadanía' ? 'X' : '',
            'docCE'    => ($profile?->documentType?->name ?? '') === 'Cédula de Extranjería' ? 'X' : '',
            'docOtro'  => (!in_array($profile?->documentType?->name ?? '', ['Cédula de Ciudadanía', 'Cédula de Extranjería']) && $profile?->document_number) ? 'X' : '',
            'pepSi'    => $chk((bool) $fp?->is_pep),
            'pepNo'    => $chk(! (bool) $fp?->is_pep),
            'pepFamSi' => $chk((bool) $fp?->pep_family),
            'pepFamNo' => $chk(! (bool) $fp?->pep_family),
            'pubSi'    => $chk((bool) $fp?->public_resources),
            'pubNo'    => $chk(! (bool) $fp?->public_resources),
            'socSi'    => $chk((bool) $fp?->marital_society),
            'socNo'    => $chk(! (bool) $fp?->marital_society),
            'savingsChk' => ($fp?->bankAccountType?->name ?? '') === 'Ahorros'   ? 'X' : '',
            'currentChk' => ($fp?->bankAccountType?->name ?? '') === 'Corriente' ? 'X' : '',
            'opsFxSi'  => $chk((bool) $fp?->ops_foreign_currency),
            'opsFxNo'  => $chk(! (bool) $fp?->ops_foreign_currency),
            'hasFxSi'  => $chk((bool) $fp?->has_foreign_accounts),
            'hasFxNo'  => $chk(! (bool) $fp?->has_foreign_accounts),
            'ivaSi'         => $chk((bool) $fp?->iva_responsibility),
            'ivaNo'         => $chk(! (bool) $fp?->iva_responsibility),
            'agRteSi'       => $chk((bool) $fp?->rent_retention_agent),
            'agRteNo'       => $chk(! (bool) $fp?->rent_retention_agent),
            'agIcaSi'       => $chk((bool) $fp?->ica_retention_agent),
            'agIcaNo'       => $chk(! (bool) $fp?->ica_retention_agent),
            'salesTaxSi'   => $chk((bool) $fp?->sales_tax_responsible),
            'salesTaxNo'   => $chk(! (bool) $fp?->sales_tax_responsible),
            'grandSi'       => $chk((bool) $fp?->grand_contributor),
            'grandNo'       => $chk(! (bool) $fp?->grand_contributor),
            'autRteSi'     => $chk((bool) $fp?->self_withholder),
            'autRteNo'     => $chk(! (bool) $fp?->self_withholder),
            'rvaSi'         => $chk((bool) $fp?->source_retention),
            'rvaNo'         => $chk(! (bool) $fp?->source_retention),
            'retentionReason' => $fp?->retention_reason ?? '',
            'icaTaxSi'     => $chk((bool) $fp?->ica_tax),
            'icaTaxNo'     => $chk(! (bool) $fp?->ica_tax),
            'icaRate'      => $fp?->ica_rate ?? '',
            'declarationCity' => $fp?->declarationCity?->name ?? '',
            'fmt'          => fn($v) => $v ? number_format($v, 2, ',', '.') : '',
        ];
    }

    /**
     * Crea el registro en folder_files para que el PDF sea visible
     * en el File Explorer del usuario.
     *
     * Se usa directamente FolderFile::create() porque el archivo ya está en
     * storage (no es un UploadedFile), por lo que no aplica uploadFile().
     */
    private function registerInFileExplorer(
        string $storagePath,
        string $originalFilename,
        int    $folderId,
        int    $uploadedBy
    ): void {
        $size = Storage::size($storagePath);
        FolderFile::create([
            'folder_id'     => $folderId,
            'uploaded_by'   => $uploadedBy,
            'name'          => basename($storagePath),
            'original_name' => $originalFilename,
            'path'          => $storagePath,
            'mime_type'     => 'application/pdf',
            'extension'     => 'pdf',
            'size'          => $size,
        ]);
    }
}
