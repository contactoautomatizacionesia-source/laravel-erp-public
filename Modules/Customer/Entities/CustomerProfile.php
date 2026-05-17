<?php

namespace Modules\Customer\Entities;

use App\Models\TypeDocument;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
// Importamos los catálogos (Asegúrate que los namespaces sean correctos según tu estructura)
use Modules\GeneralSetting\Entities\Catalogs\CivilStatus;
use Modules\GeneralSetting\Entities\Catalogs\EconomicActivity;
use Modules\GeneralSetting\Entities\Catalogs\Profession;
use Modules\GeneralSetting\Entities\Catalogs\LeadSource;
use Modules\GeneralSetting\Entities\Catalogs\ContractType;
use Modules\GeneralSetting\Entities\Catalogs\CountryPhoneCode;
use Modules\GeneralSetting\Entities\Catalogs\Gender;
use Modules\Plans\Entities\PlanChild;
use Modules\Product\Entities\Product;
use Modules\Setup\Entities\City;
use Modules\Setup\Entities\Country;

class CustomerProfile extends Model
{
    protected $table = 'customer_profiles';

    protected $fillable = [
        'user_id',
        // Paso 1
        'document_type_id', 'document_number', 'date_of_birth', 'birth_city_id',
        'issue_date', 'issue_city_id', 'expiration_date', 'nationality_id',
        // Paso 2
        'whatsapp', 'whatsapp_country_code_id',
        'phone_calls', 'phone_calls_code_id',
        'phone_office', 'phone_office_code_id',
        'secondary_email',

        'civil_status_id', 'economic_activity_id', 'profession_id',
        'product_id', 'lead_source_id', 'gender_id',
        // Paso 3
        'front_id_image', 'back_id_image',
        // Paso 9
        'code', 'registration_date', 'contract_type_id', 'representative_id', 'plan_child_id',
        'is_manual_assignment',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'issue_date' => 'date',
        'expiration_date' => 'date',
        'registration_date' => 'date',
    ];

    // --- RELACIONES ---

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // "Padrino"
    public function representative()
    {
        return $this->belongsTo(User::class, 'representative_id');
    }

    //Tipo de doucmento
    public function documentType() { return $this->belongsTo(TypeDocument::class, 'document_type_id'); }

    // Catálogos
    public function civilStatus() { return $this->belongsTo(CivilStatus::class); }
    public function economicActivity() { return $this->belongsTo(EconomicActivity::class); }
    public function profession() { return $this->belongsTo(Profession::class); }
    public function product() { return $this->belongsTo(Product::class); }
    public function leadSource() { return $this->belongsTo(LeadSource::class); }
    public function contractType() { return $this->belongsTo(ContractType::class); }
    public function gender() { return $this->belongsTo(Gender::class, 'gender_id'); }
    
    // Geografía
    public function birthCity() { return $this->belongsTo(City::class, 'birth_city_id'); }
    public function issueCity() { return $this->belongsTo(City::class, 'issue_city_id'); }
    public function nationalityCountry() { return $this->belongsTo(Country::class, 'nationality_id'); }
    
    // Contacto
    public function whatsappCode() { return $this->belongsTo(CountryPhoneCode::class, 'whatsapp_country_code_id');}
    public function phoneCallsCode() { return $this->belongsTo(CountryPhoneCode::class, 'phone_calls_code_id');}
    public function phoneOfficeCode() { return $this->belongsTo(CountryPhoneCode::class, 'phone_office_code_id');}

    // Plan activo
    public function planChild()
    {
        return $this->belongsTo(PlanChild::class, 'plan_child_id');
    }

    // Historial de planes
    public function planHistory()
    {
        return $this->hasMany(EntrepreneurPlanHistory::class, 'user_id', 'user_id')
            ->orderBy('started_at', 'desc');
    }

    public function activePlanHistory()
    {
        return $this->hasOne(EntrepreneurPlanHistory::class, 'user_id', 'user_id')
            ->whereNull('ended_at');
    }
}
