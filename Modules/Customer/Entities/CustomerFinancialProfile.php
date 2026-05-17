<?php

namespace Modules\Customer\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Modules\GeneralSetting\Entities\Catalogs\Bank;
use Modules\GeneralSetting\Entities\Catalogs\BankAccountType;
use Modules\Setup\Entities\Country;
use Modules\Setup\Entities\City;

class CustomerFinancialProfile extends Model
{
    protected $table = 'customer_financial_profiles';

    protected $fillable = [
        'user_id',
        // Paso 4
        'bank_id', 'bank_account_type_id', 'account_number',
        // Paso 5
        'company_name', 'job_title', 'work_address',
        'public_resources', 'marital_society', 'is_pep', 'pep_family',
        // Paso 6
        'monthly_income', 'monthly_expenses', 'other_income', 'other_income_desc',
        'total_assets', 'total_liabilities', 'total_equity',
        // Paso 7
        'ops_foreign_currency', 'ops_foreign_desc', 'has_foreign_accounts',
        'foreign_bank', 'foreign_account_number', 'foreign_currency',
        'foreign_country_id', 'foreign_city_id',
        // Paso 8
        'iva_responsibility', 'rent_retention_agent', 'ica_retention_agent',
        'sales_tax_responsible', 'grand_contributor', 'self_withholder',
        'source_retention', 'retention_reason', 'ica_tax', 'ica_rate',
        'declaration_city_id', 'declaration_pdffile', 'has_rut', 'rut_file'
    ];

    protected $casts = [
        // Booleanos
        'public_resources' => 'boolean',
        'marital_society' => 'boolean',
        'is_pep' => 'boolean',
        'pep_family' => 'boolean',
        'ops_foreign_currency' => 'boolean',
        'has_foreign_accounts' => 'boolean',
        'iva_responsibility' => 'boolean',
        'rent_retention_agent' => 'boolean',
        'ica_retention_agent' => 'boolean',
        'sales_tax_responsible' => 'boolean',
        'grand_contributor' => 'boolean',
        'self_withholder' => 'boolean',
        'source_retention' => 'boolean',
        'ica_tax' => 'boolean',
        'has_rut' => 'boolean',
        
        // Montos
        'monthly_income' => 'decimal:2',
        'monthly_expenses' => 'decimal:2',
        'other_income' => 'decimal:2',
        'total_assets' => 'decimal:2',
        'total_liabilities' => 'decimal:2',
        'total_equity' => 'decimal:2',
        'ica_rate' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function bankAccountType()
    {
        return $this->belongsTo(BankAccountType::class);
    }
    
    public function foreignCountry()
    {
        return $this->belongsTo(Country::class, 'foreign_country_id');
    }

    public function foreignCity()
    {
        return $this->belongsTo(City::class, 'foreign_city_id');
    }
    
    public function declarationCity()
    {
        return $this->belongsTo(City::class, 'declaration_city_id');
    }
}