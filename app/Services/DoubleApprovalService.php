<?php

namespace App\Services;

use App\Repositories\DoubleApprovalRepository;
use Modules\ClubPoint\Repositories\ClubPointRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Traits\Notification;
use Modules\GeneralSetting\Entities\EmailTemplateType;
use Modules\GeneralSetting\Entities\NotificationSetting;
use Modules\UserActivityLog\Traits\LogActivity;
use App\Exceptions\CustomHandledException;
use App\Models\Staff;
use App\Models\User;
use Exception;
use Illuminate\Support\Collection;

class DoubleApprovalService
{
    use Notification;

    protected $repository;

    public function __construct(DoubleApprovalRepository $repository)
    {
        $this->repository = $repository;
    }

    public function createPendingApproval(array $params)
    {
        // Validación: Si ya existe, devolvemos FALSE para que el controlador maneje el mensaje
        if ($this->repository->hasPending($params['module'], $params['action_type'])) {
            return false;
        }

        DB::beginTransaction();
        try {
            do {
                $hash = Str::random(40);
            } while ($this->repository->findByHash($hash));

            $this->repository->create([
                'hash'                  => $hash,
                'module'                => $params['module'],
                'action_type'           => $params['action_type'],
                'new_data'              => $params['new_data'],
                'original_data'         => isset($params['original_data']) ? $params['original_data'] : null,
                'requester_id'          => auth()->id(),
                'assigned_approver_id'  => $params['staff_id'],
                'status'                => 0,
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);

            $staff = Staff::findOrFail($params['staff_id']);
            $userToNotify = $staff->user;

            $notification = NotificationSetting::where('slug','double-approval-request')->first();

            if ($notification) {
                $messageTranslations = $notification->getTranslations('message');
                $adminMsgTranslations = $notification->getTranslations('admin_msg');

                // Definir las variables para el reemplazo
                $search = ['[sender_name]'];
                $replace = [auth()->user()->full_name];
                // Reemplazar en cada idioma del array
                foreach ($messageTranslations as $locale => $value) {
                    $messageTranslations[$locale] = str_replace($search, $replace, $value);
                }

                foreach ($adminMsgTranslations as $locale => $value) {
                    $adminMsgTranslations[$locale] = str_replace($search, $replace, $value);
                }

                // Asignar las traducciones procesadas de vuelta al modelo
                $notification->setTranslations('message', $messageTranslations);
                $notification->setTranslations('admin_msg', $adminMsgTranslations);

                $this->typeId = EmailTemplateType::where('type', 'double_approval_template')->first()->id;
                // La URL de revisión para el panel de notificaciones
                $this->notificationUrl = route($params['notification_url'], ['hash' => $hash]);

                $this->notificationSend($notification, $userToNotify->id);
            }

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Procesa la aprobación final ejecutando la lógica del repositorio correspondiente
     */
    public function processApproval(int $id, int $status, int $assignedApproverId, ?string $rejectionReason = null)
    {
        $pending = $this->repository->findById($id);

        if (!$pending || $pending->status != 0) {
            throw new Exception(__('common.not_approved', ['attribute' => 'pending approval', 'id' => $id]));
        }

        DB::beginTransaction();
        try {
            if ($status == 1) { // Aprobado
                $data = is_array($pending->new_data)
                    ? $pending->new_data
                    : json_decode($pending->new_data, true);

                // Ejecutar la acción real según el tipo
                switch ($pending->action_type) {
                    case 'set_massive_points':
                        app(ClubPointRepository::class)->storeSetPoint($data['set']);
                        break;

                    case 'convert_point_to_wallet':
                        // El repositorio espera el array completo que contiene 'wallet_point'
                        app(ClubPointRepository::class)->create($data);
                        break;
                    // -------------------
                }
            }

            // Actualizar estado en la tabla de pendientes
            $this->repository->updateStatus($id, $status, $assignedApproverId, $rejectionReason);

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function findByAssignedApproverId(int $assignedApproverId): Collection
    {
        return $this->repository->findByAssignedApproverId($assignedApproverId);
    }

    public function findByHash(string $hash)
    {
        return $this->repository->findByHash($hash);
    }

    public function sendStockAlertNotification(string $slug, array $notificationData = [])
    {
        try {
            $notification = NotificationSetting::where('slug', $slug)->first();

            if ($notification) {
                // NotificationSetting tiene habilitado el Trait de Spatie Translatable, por lo que podemos usar getTranslations para obtener un array de traducciones
                // Cuando un modelo usa este Trait, al acceder a $notification->message, Laravel no te devuelve el JSON completo, sino que automáticamente traduce el campo al idioma actual de la aplicación (por defecto en)
                $messageTranslations = $notification->getTranslations('message');
                $adminMsgTranslations = $notification->getTranslations('admin_msg');

                // Definir las variables para el reemplazo
                $search = ['{PRODUCT_NAME}', '{STOCK}'];
                $replace = [
                    $notificationData['product_name'], $notificationData['current_stock']
                ];
                // Reemplazar en cada idioma del array
                foreach ($messageTranslations as $locale => $value) {
                    $messageTranslations[$locale] = str_replace($search, $replace, $value);
                }

                foreach ($adminMsgTranslations as $locale => $value) {
                    $adminMsgTranslations[$locale] = str_replace($search, $replace, $value);
                }

                // Asignar las traducciones procesadas de vuelta al modelo
                $notification->setTranslations('message', $messageTranslations);
                $notification->setTranslations('admin_msg', $adminMsgTranslations);

                $templateType = match ($slug) {
                    'overstock_alert' => 'overstock_alert_template',
                    'empty_stock_alert' => 'empty_stock_alert_template',
                    default => 'low_stock_template',
                };

                $this->typeId = EmailTemplateType::where('type', $templateType)->first()->id;
                $this->notificationUrl = 'products/inventory-alerts';

                // Obtenemos los administradores
                $admins = User::where('is_active', 1)->whereHas('role', function($q){
                    $q->whereIn('type', ['superadmin', 'admin']);
                })->get();

                foreach ($admins as $admin) {
                    // Enviamos a cada administrador
                    $this->notificationSend($notification, $admin->id, 0, $notificationData, $slug);
                }

                $context = $messageTranslations[app()->getLocale()] ?? reset($messageTranslations);
                LogActivity::successLog($context);
            }
        } catch (\Exception $e) {
            // Obtenemos el mensaje en el idioma actual o el primero disponible si falla
            $context = isset($adminMsgTranslations) ? ($adminMsgTranslations[app()->getLocale()] ?? reset($adminMsgTranslations)) : $e->getMessage();
            throw new CustomHandledException($e, (string) $context);
        }
    }
}
