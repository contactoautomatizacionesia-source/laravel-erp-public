<?php

namespace Modules\GeneralSetting\Http\Controllers;

use App\Enums\HttpStatusCode;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\GeneralSetting\Services\ParameterSettingService;
use Brian2694\Toastr\Facades\Toastr;
use Modules\UserActivityLog\Traits\LogActivity;
use App\Repositories\UserRepositoryInterface;

class ParameterSettingController extends Controller
{
    protected $service;

    public function __construct(ParameterSettingService $service, UserRepositoryInterface $userRepository) {
        $this->service = $service;
        $this->userRepository = $userRepository;
    }

    /**
     * Muestra la lista de parámetros.
     */
    public function index()
    {
        try {
            // 1. Asignamos directamente a variables locales
            $parameters = $this->service->getAll();

            // 2. Cargamos solo los staffs activos para el selector
            $staffCollection = $this->userRepository->all(['user.role', 'department']);

            $staffs = $staffCollection->filter(function ($staff) {
                return $staff->user && $staff->user->is_active == 1;
            })->map(function ($staff) {
                $user = $staff->user;
                $user->staff_id = $staff->id;
                return $user;
            })->values();

            // Definimos la estructura jerárquica de secciones y sus campos
            $sectionsMap = [
                'section_personal_information' => [
                    'label' => 'Bloque: Info. Personal',
                    'fields' => ['first_name', 'last_name', 'middle_name', 'document_type_id', 'document_number', 'date_of_birth']
                ],
                'section_document_information' => [
                    'label' => 'Bloque: Info. del Documento',
                    'fields' => ['birth_city_id', 'issue_date', 'issue_city_id', 'expiration_date', 'gender_id', 'nationality_id']
                ],
                'section_ubication_contact' => [
                    'label' => 'Bloque: Ubicación y Contacto',
                    'fields' => ['country', 'state', 'city', 'address', 'whatsapp', 'phone_calls', 'phone_office', 'email', 'secondary_email', 'civil_status_id']
                ],
                'section_other_information' => [
                    'label' => 'Bloque: Otra Información',
                    'fields' => ['economic_activity_id', 'profession_id', 'product_id', 'lead_source_id']
                ],
                'section_represent_information' => [
                    'label' => 'Bloque: Representante',
                    'fields' => ['representative', 'referral_code', 'registration_date', 'contract_type_id']
                ],
                'section_labor_information' => [
                    'label' => 'Bloque: Info. Laboral',
                    'fields' => ['company_name', 'job_title', 'work_address', 'public_resources', 'marital_society', 'is_pep', 'pep_family']
                ],
                'section_bank_information' => [
                    'label' => 'Bloque: Datos Bancarios',
                    'fields' => ['bank', 'account_number', 'account_type']
                ],
                'section_financial_information' => [
                    'label' => 'Bloque: Info. Financiera',
                    'fields' => ['monthly_income', 'monthly_expenses', 'other_income', 'other_income_desc', 'total_assets', 'total_liabilities', 'total_equity']
                ],
                'section_tax_information' => [
                    'label' => 'Bloque: Info. Tributaria',
                    'fields' => ['iva_responsibility', 'rent_retention_agent', 'ica_retention_agent', 'sales_tax_responsible', 'grand_contributor', 'self_withholder', 'source_retention', 'retention_reason', 'ica_tax', 'ica_rate', 'declaration_city_id', 'has_rut', 'rut_file']
                ],
                'section_foreign_currency' => [
                    'label' => 'Bloque: Moneda Extranjera',
                    'fields' => ['ops_foreign_currency', 'ops_foreign_desc', 'has_foreign_accounts', 'foreign_bank', 'foreign_account_number', 'foreign_currency', 'foreign_country_id', 'foreign_city_id']
                ],
                'section_documents' => [
                    'label' => 'Bloque: Documentos Adjuntos',
                    'fields' => ['front_id_image', 'back_id_image']
                ],
                'section_security' => [
                    'label' => 'Bloque: Seguridad (Contraseña)',
                    'fields' => ['password', 'password_confirmation']
                ],
            ];

            // 5. Construimos el arreglo final para la vista
            $availableKycFields = [];
            foreach ($sectionsMap as $sectionId => $data) {
                $fieldDetails = [];
                foreach ($data['fields'] as $field) {
                    // Ya no validamos contra el Request, simplemente pasamos los campos definidos en el mapa
                    $fieldDetails[$field] = ucwords(str_replace('_', ' ', $field));
                }
                $availableKycFields[$sectionId] = [
                    'label' => $data['label'],
                    'fields' => $fieldDetails
                ];
            }

            // 6. Enviamos a la vista de la forma más segura (compact)
            return view('generalsetting::parameter_settings.index', compact('parameters', 'staffs', 'availableKycFields'));
        } catch (\Exception $e) {
            $this->handleError($e, 'create');
            return back();
        }
    }

    /**
     * Crea un nuevo parámetro en el sistema.
     */
    public function store(Request $request)
    {
        try {
            $this->service->create($request->all());

            // Registro de éxito en logs
            LogActivity::successLog(__('general_settings.parameter_settings.created', [
                'name' => $request->parameter_name
            ]));

            Toastr::success(__('common.created_successfully'), __('common.success'));

            return redirect()->route('general_setting.parameter_settings.index');

        } catch (\Exception $e) {
            $this->handleError($e, 'create');
            Toastr::error(__('common.error_message')); // Notificación al usuario
            return back()->withInput(); // Redirección segura con datos previos
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $parameter = $this->service->findById($id);

            $request->validate(
                match ($parameter->slug) {
                    'product-stock' => [
                        'min_value' => ['required', 'integer', 'min:0'],
                        'max_value' => ['required', 'integer', 'gte:min_value'],
                    ],

                    'daily-count-failures' => [
                        'value_limit' => ['required', 'integer', 'min:1'],
                    ],

                    'cash-opening' => [
                        'monetary_value' => ['required', 'numeric', 'min:0'],
                    ],

                    'double-approval' => [
                        'staff_id' => ['required', 'exists:staff,id'],
                    ],

                    default => [],
                },
                match ($parameter->slug) {
                    'product-stock' => [
                        'min_value.required' => __('general_settings.parameter_settings.validation_messages.min_value.required'),
                        'min_value.integer' => __('general_settings.parameter_settings.validation_messages.min_value.integer'),
                        'min_value.min' => __('general_settings.parameter_settings.validation_messages.min_value.min'),
                        'max_value.required' => __('general_settings.parameter_settings.validation_messages.max_value.required'),
                        'max_value.integer' => __('general_settings.parameter_settings.validation_messages.max_value.integer'),
                        'max_value.gte' => __('general_settings.parameter_settings.validation_messages.max_value.gte'),
                    ],

                    'daily-count-failures' => [
                        'value_limit.required' => __('general_settings.parameter_settings.validation_messages.value_limit.required'),
                        'value_limit.integer' => __('general_settings.parameter_settings.validation_messages.value_limit.integer'),
                        'value_limit.min' => __('general_settings.parameter_settings.validation_messages.value_limit.min'),
                    ],

                    'cash-opening' => [
                        'monetary_value.required' => __('general_settings.parameter_settings.validation_messages.monetary_value.required'),
                        'monetary_value.numeric' => __('general_settings.parameter_settings.validation_messages.monetary_value.numeric'),
                        'monetary_value.min' => __('general_settings.parameter_settings.validation_messages.monetary_value.min'),
                    ],

                    'double-approval' => [
                        'staff_id.required' => __('general_settings.parameter_settings.validation_messages.staff_id.required'),
                        'staff_id.exists' => __('general_settings.parameter_settings.validation_messages.staff_id.exists'),
                    ],

                    default => [],
                }
            );

            $this->service->update($request->all(), $id);

            LogActivity::successLog(__('general_settings.parameter_settings.updated', [
                'name' => __('general_settings.' . $parameter->parameter_name)
            ]));
            
            // Respuesta de éxito consistente en JSON
            return response()->json([
                'status'  => 'success',
                'message' => __('common.updated_successfully')
            ], HttpStatusCode::OK->value);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Captura de errores de validación (Estado 422)
            return response()->json([
                'status' => 'error',
                'message' => __('common.validation_failed'),
                'errors'  => $e->errors() // Envía los mensajes específicos de cada campo
            ], HttpStatusCode::UNPROCESSABLE_ENTITY->value);

        } catch (\Exception $e) {
            // Otros errores (Estado 500)
            $this->handleError($e, 'active_status_update', $request->id);
            
            return response()->json([
                'status'  => 'error',
                'message' => __('common.error_message'),
                'details' => $e->getMessage()
            ], HttpStatusCode::INTERNAL_SERVER_ERROR->value);
        }
    }

    /**
     * Actualiza el estado activo de un parámetro.
     */
    public function update_active_status(Request $request)
    {
        try {
            // Buscar el parámetro actual para validar su estado y tipo
            $parameter = $this->service->findById($request->id);
           
            // Validación específica para la Doble Aprobación
            // Si se intenta activar (status = 1) y el slug es double-approval
            if ($request->is_active == 1 && $parameter->slug == 'double-approval') {
                if (empty($parameter->staff_id)) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => __('general_settings.parameter_settings.double_approval_staff_required')
                    ], 422); // Código 422 para errores de validación de negocio
                }
            }

            // Actualiza el is_active
            $this->service->update(['is_active' => $request->is_active], $request->id);

            // Registro de éxito en logs
            LogActivity::successLog(__('general_settings.parameter_settings.active_status_updated', [
                'name' => __('general_settings.' . $parameter->parameter_name)
            ]));

            return response()->json([
                'message' => __('common.updated_successfully')
            ], HttpStatusCode::OK->value);

        } catch (\Exception $e) {
            // Delegación del error al manejador centralizado
            // Nota: Al ser una petición AJAX, se registra el log pero el retorno es JSON
            $this->handleError($e, 'update_active_status', $request->id);

            return response()->json([
                'error' => __('common.error_message'),
                'details' => $e->getMessage()
            ], HttpStatusCode::INTERNAL_SERVER_ERROR->value);
        }
    }

    /**
     * Elimina un parámetro y registra la acción.
     */
    public function destroy(int $id)
    {
        try {
            $this->service->destroy($id);

            // Log de éxito usando lenguaje del módulo
            LogActivity::successLog(__('general_settings.parameter_settings.deleted', ['id' => $id]));
            Toastr::success(__('common.deleted_successfully'), __('common.success'));

            return redirect()->route('parameter_settings.index');
        } catch (\Exception $e) {
            $this->handleError($e, 'delete', $id);
            Toastr::error(__('common.error_message'));
            return back();
        }
    }

    /**
     * Handles errors in the controller.
     * @param \Exception $e
     * @param string $operation
     * @param int|null $id
     * @return RedirectResponse
     */
    private function handleError(\Exception $e, string $operation, ?int $id = null)
    {
        $idParam = $id ? ['id' => $id] : [];

        if ($e instanceof \Illuminate\Database\QueryException) {
        LogActivity::errorLog(__("generalsetting::general_settings.parameter_settings.errors.{$operation}_db", $idParam));
        } elseif ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            LogActivity::errorLog(__('generalsetting::general_settings.parameter_settings.errors.not_found', $idParam));
        } elseif ($e instanceof \Illuminate\Validation\ValidationException) {
            // No registrar en log de actividad para evitar saturación por errores de usuario
        } else {
            LogActivity::errorLog(__("generalsetting::general_settings.parameter_settings.errors.{$operation}_unknown", array_merge($idParam, ['error' => $e->getMessage()])));
        }
    }
}
