<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\FolderType;
use App\Models\DataUpdateLog;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Customer\Entities\CustomerAddress;
use Modules\Customer\Entities\CustomerFinancialProfile;
use Modules\Customer\Entities\CustomerProfile;
use Modules\DigitalFolder\Entities\Folder;
use Modules\DigitalFolder\Repositories\DigitalFolderRepository;

class KycUpdateService
{
    public function __construct(
        private readonly DigitalFolderRepository $digitalFolderRepository
    ) {}

    public function processUpdate(User $user, array $validatedData, string $ip, string $userAgent): void
    {
        DB::transaction(function () use ($user, $validatedData, $ip, $userAgent) {
            $address = $user->customerAddress;

            $validatedData = $this->sanitizeInput($validatedData);
            $validatedData = $this->normalizeAliases($validatedData);
            $validatedData = $this->sanitizeDecimalFields($validatedData);
            $validatedData = $this->normalizeBooleanFields($validatedData);
            $validatedData = $this->handleFileUploads($user, $validatedData);

            if (!empty($validatedData['password'])) {
                $validatedData['password'] = Hash::make((string) $validatedData['password']);
            } else {
                unset($validatedData['password']);
            }
            unset($validatedData['password_confirmation']);

            // Agrupamos las actualizaciones para evitar el listado excesivo de parámetros (SonarQube)
            $updatesGroup = [
                'user'      => $this->extractUserUpdates($validatedData),
                'profile'   => $this->extractModelUpdates(new CustomerProfile(), $validatedData),
                'financial' => $this->extractModelUpdates(new CustomerFinancialProfile(), $validatedData),
                'address'   => $this->extractAddressUpdates($validatedData)
            ];

            [$oldData, $payloadAfterLog] = $this->buildDeltaLog($user, $updatesGroup);

            DataUpdateLog::create([
                'user_id' => $user->id,
                'payload_before' => $oldData,
                'payload_after' => $payloadAfterLog,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
            ]);

            if (!empty($updatesGroup['user'])) {
                $user->update($updatesGroup['user']);
            }

            if (!empty($updatesGroup['profile'])) {
                $user->customerProfile()->updateOrCreate(['user_id' => $user->id], $updatesGroup['profile']);
            }

            if (!empty($updatesGroup['financial'])) {
                $user->customerFinancialProfile()->updateOrCreate(['user_id' => $user->id], $updatesGroup['financial']);
            }

            if (!empty($updatesGroup['address'])) {
                $this->persistAddress($user, $address, $updatesGroup['address'], $validatedData, $updatesGroup['user']);
            }

            $user->forceFill(['data_verified_at' => now()])->save();
            Cache::forget("user_{$user->id}_kyc_valid");
        });
    }

    private function sanitizeInput(array $data): array
    {
        $helperKeys = [
            'birth_city_name',
            'city_name',
            'country_name',
            'declaration_city_name',
            'declaration_country_name',
            'foreign_city_name',
            'issue_city_name',
            'nationality_name',
            'product_name',
            'representative_name',
            'state_name',
        ];

        foreach ($helperKeys as $key) {
            unset($data[$key]);
        }

        return $data;
    }

    private function normalizeAliases(array $data): array
    {
        if (array_key_exists('bank', $data) && !array_key_exists('bank_id', $data)) {
            $data['bank_id'] = $data['bank'];
        }
        unset($data['bank']);

        if (array_key_exists('account_type', $data) && !array_key_exists('bank_account_type_id', $data)) {
            $data['bank_account_type_id'] = $data['account_type'];
        }
        unset($data['account_type']);

        return $data;
    }

    private function sanitizeDecimalFields(array $data): array
    {
        $decimalFields = ['monthly_income', 'monthly_expenses', 'other_income', 'total_assets', 'total_liabilities', 'total_equity', 'ica_rate'];

        foreach ($decimalFields as $field) {
            if (array_key_exists($field, $data)) {
                $parsed = $this->parseDecimalValue($data[$field]);
                if (!is_null($parsed)) {
                    $data[$field] = $parsed;
                }
            }
        }

        return $data;
    }

    private function parseDecimalValue(mixed $value): ?string
    {
        $result = null;

        if (is_int($value) || is_float($value)) {
            $result = (string) $value;
        } elseif (is_string($value)) {
            $raw = trim($value);
            if (is_numeric($raw)) {
                $result = $raw;
            } elseif (preg_match('/^\\d{1,3}(\\.\\d{3})+(,\\d+)?$/', $raw)) {
                $raw = str_replace(['.', ','], ['', '.'], $raw);
                $result = is_numeric($raw) ? $raw : null;
            } elseif (preg_match('/^\\d{1,3}(,\\d{3})+(\\.\\d+)?$/', $raw)) {
                $raw = str_replace(',', '', $raw);
                $result = is_numeric($raw) ? $raw : null;
            }
        }

        return $result;
    }

    private function handleFileUploads(User $user, array $data): array
    {
        $fileKeys = ['front_id_image', 'back_id_image', 'rut_file'];
        $filesToUpload = [];

        foreach ($fileKeys as $key) {
            if (array_key_exists($key, $data) && $data[$key] instanceof UploadedFile) {
                $filesToUpload[$key] = $data[$key];
            } else {
                unset($data[$key]);
            }
        }

        if (!empty($filesToUpload)) {
            $userFolder = $this->digitalFolderRepository->ensureUserFolder($user);
            $yearFolder = $this->digitalFolderRepository->ensureYearFolder($userFolder);
            $registerFolder = $this->digitalFolderRepository->ensureStandardSubfolder($yearFolder, FolderType::Register);
            $kycUpdateFolder = $this->createKycUpdateFolder($registerFolder, $user);

            foreach ($filesToUpload as $key => $file) {
                $fileRecord = $this->digitalFolderRepository->uploadFile($file, $kycUpdateFolder->id, $user->id);
                $data[$key] = $fileRecord->path;
            }
        }

        return $data;
    }

    private function createKycUpdateFolder(Folder $parentFolder, User $user): Folder
    {
        $folderName = 'update-kyc-' . now()->format('Y-m-d-His');

        $folder = Folder::create([
            'name' => $folderName,
            'parent_id' => $parentFolder->id,
            'owner_id' => $user->id,
            'type' => 'regular',
            'description' => 'KYC update folder ' . $folderName,
            'can_be_deleted' => true,
            'can_be_modified' => true,
            'is_active' => true,
        ]);

        $folder->users()->attach($user->id, ['permission' => 'read']);

        return $folder;
    }

    private function extractUserUpdates(array $data): array
    {
        $allowed = ['first_name', 'last_name', 'middle_name', 'email', 'password'];
        return array_intersect_key($data, array_flip($allowed));
    }

    private function extractModelUpdates(object $model, array $data): array
    {
        if (!method_exists($model, 'getFillable')) {
            return [];
        }

        $fillable = $model->getFillable();
        if (empty($fillable)) {
            return [];
        }

        $updates = array_intersect_key($data, array_flip($fillable));
        unset($updates['user_id']);

        return $updates;
    }

    private function extractAddressUpdates(array $data): array
    {
        $allowed = ['country', 'state', 'city', 'address'];
        return array_intersect_key($data, array_flip($allowed));
    }

    private function buildDeltaLog(User $user, array $updatesGroup): array
    {
        $before = [];
        $after = [];

        $candidates = array_merge(
            $updatesGroup['user'],
            $updatesGroup['profile'],
            $updatesGroup['financial'],
            $updatesGroup['address']
        );

        foreach ($candidates as $key => $newValue) {
            $oldValue = null;

            if (array_key_exists($key, $updatesGroup['user'])) {
                $oldValue = $user->getAttribute($key);
            } elseif (array_key_exists($key, $updatesGroup['profile'])) {
                $oldValue = $user->customerProfile?->getAttribute($key);
            } elseif (array_key_exists($key, $updatesGroup['financial'])) {
                $oldValue = $user->customerFinancialProfile?->getAttribute($key);
            } elseif (array_key_exists($key, $updatesGroup['address'])) {
                $oldValue = $user->customerAddress?->getAttribute($key);
            }

            $normalizedOld = $this->normalizeComparable($key, $oldValue);
            $normalizedNew = $this->normalizeComparable($key, $newValue);

            if ($normalizedOld !== $normalizedNew) {
                if ($key === 'password') {
                    $before[$key] = '********';
                    $after[$key]  = '********';
                } else {
                    $before[$key] = $this->normalizeForPayload($key, $oldValue);
                    $after[$key]  = $this->normalizeForPayload($key, $newValue);
                }
            }
        }

        return [$before, $after];
    }

    private function normalizeComparable(string $key, mixed $value): string
    {
        $normalized = (string) $value;

        if (is_null($value) || $value === '') {
            $normalized = '';
        } elseif (in_array($key, ['date_of_birth', 'issue_date', 'expiration_date', 'registration_date'], true)) {
            $normalized = $this->toDateStringOrNull($value) ?? '';
        } elseif (in_array($key, ['monthly_income', 'monthly_expenses', 'other_income', 'total_assets', 'total_liabilities', 'total_equity', 'ica_rate'], true)) {
            $parsed = $this->parseDecimalValue($value);
            if (!is_null($parsed) && is_numeric($parsed)) {
                $normalized = number_format((float) $parsed, 2, '.', '');
            }
        } elseif ((str_ends_with($key, '_id') || in_array($key, ['country', 'state', 'city'], true)) && is_numeric($value)) {
            $normalized = (string) (int) $value;
        } elseif (is_bool($value)) {
            $normalized = $value ? '1' : '0';
        }

        return $normalized;
    }

    private function normalizeForPayload(string $key, mixed $value): mixed
    {
        $normalized = $value;

        if (is_null($value) || $value === '') {
            $normalized = $value;
        } elseif (in_array($key, ['date_of_birth', 'issue_date', 'expiration_date', 'registration_date'], true)) {
            $normalized = $this->toDateStringOrNull($value) ?? $value;
        } elseif (in_array($key, ['monthly_income', 'monthly_expenses', 'other_income', 'total_assets', 'total_liabilities', 'total_equity', 'ica_rate'], true)) {
            $parsed = $this->parseDecimalValue($value);
            if (!is_null($parsed) && is_numeric($parsed)) {
                $normalized = number_format((float) $parsed, 2, '.', '');
            }
        } elseif ((str_ends_with($key, '_id') || in_array($key, ['country', 'state', 'city'], true)) && is_numeric($value)) {
            $normalized = (int) $value;
        } elseif (is_bool($value)) {
            $normalized = $value ? 1 : 0;
        }

        return $normalized;
    }

    private function toDateStringOrNull(mixed $value): ?string
    {
        $result = null;

        if ($value instanceof \Carbon\CarbonInterface) {
            $result = $value->toDateString();
        } elseif (is_string($value) && preg_match('/^\\d{4}-\\d{2}-\\d{2}/', $value)) {
            try {
                $result = Carbon::parse($value)->toDateString();
            } catch (\Throwable) {
                $result = null;
            }
        }

        return $result;
    }

    private function persistAddress(
        User $user,
        ?CustomerAddress $address,
        array $addressUpdates,
        array $validatedData,
        array $userUpdates
    ): void {
        if ($address) {
            $address->update($addressUpdates);
        } else {
            $user->customerAddress()->create(array_merge([
                'name' => $user->name ?? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                'email' => $userUpdates['email'] ?? $user->email,
                'phone' => $validatedData['whatsapp'] ?? ($user->phone ?? ''),
                'postal_code' => '',
                'is_shipping_default' => 1,
                'is_billing_default' => 1,
            ], $addressUpdates));
        }
    }

    private function normalizeBooleanFields(array $data): array
    {
        $booleanFields = ['public_resources', 'marital_society', 'is_pep', 'pep_family', 'iva_responsibility', 'rent_retention_agent', 'ica_retention_agent', 'sales_tax_responsible', 'grand_contributor', 'self_withholder', 'source_retention', 'ica_tax', 'has_rut', 'ops_foreign_currency', 'has_foreign_accounts'];

        foreach ($booleanFields as $field) {
            if (array_key_exists($field, $data)) {
                $value = $data[$field];
                if ($value === 'SI' || $value === 'YES') {
                    $data[$field] = 1;
                } elseif ($value === 'NO') {
                    $data[$field] = 0;
                }
            }
        }

        return $data;
    }
}
