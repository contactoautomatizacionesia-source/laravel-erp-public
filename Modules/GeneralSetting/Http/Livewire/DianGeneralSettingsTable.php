<?php

namespace Modules\GeneralSetting\Http\Livewire;

use Livewire\Component;
use Modules\Product\Entities\Brand;
use Modules\GeneralSetting\Entities\DianSetting;
use Modules\UserActivityLog\Traits\LogActivity;
use Carbon\Carbon;

class DianGeneralSettingsTable extends Component
{
    // Propiedades para el formulario (Data Binding)
    public $editingSettingsId = null;

    // Propiedades
    public $brand_id;
    public $brand_name;
    public $resolution_number;
    public $resolution_date;
    public $invoice_number_from;
    public $invoice_number_to;

    // Reglas de validación dinámicas
    protected function rules()
    {
        return [
            'resolution_number'   => 'required|string',
            'resolution_date'     => 'required|date',
            'invoice_number_from' => 'required|integer|min:1',
            'invoice_number_to'   => 'required|integer|min:1|gt:invoice_number_from',
        ];
    }

    protected function validationAttributes()
    {
        return [
            'invoice_number_from' => __('general_settings.invoice_number_from'),
            'invoice_number_to'   => __('general_settings.invoice_number_to'),
        ];
    }

    public function render()
    {
        $brandsData = Brand::leftJoin('dian_settings', 'brands.id', '=', 'dian_settings.brand_id')
            ->select(
                'brands.id as brand_id',
                'brands.name as brand_name',
                'brands.logo',
                'dian_settings.id as setting_id',
                'dian_settings.resolution_number as resolution_number',
                'dian_settings.resolution_date as resolution_date',
                'dian_settings.invoice_number_from as invoice_number_from',
                'dian_settings.invoice_number_to as invoice_number_to',
            )
            ->orderBy('brands.id', 'DESC')
            ->get();

        $brands = $brandsData->map(function ($brand) {
            $decodedBrandName = json_decode($brand->brand_name, true);

            $brandName = (json_last_error() === JSON_ERROR_NONE && is_array($decodedBrandName))
                ? ($decodedBrandName[app()->getLocale()] ?? $decodedBrandName['en'] ?? reset($decodedBrandName))
                : $brand->brand_name;

            return [
                'brand_id'            => $brand->brand_id,
                'brand_name'          => $brandName,
                'logo'                => $brand->logo,
                'resolution_number'   => $brand->resolution_number,
                'resolution_date'     => $brand->resolution_date
                    ? Carbon::parse($brand->resolution_date)->format('m/d/Y')
                    : null,
                'invoice_number_from' => $brand->invoice_number_from,
                'invoice_number_to'   => $brand->invoice_number_to
            ];
        });

        return view('generalsetting::livewire.dian.dian-general-settings-table', [
            'brands' => $brands->toArray()
        ]);
    }

    // Método para alternar pestañas/vistas
    public function setView()
    {
        $this->resetValidation();
    }

    public function edit($brandId)
    {
        $this->resetValidation();
        $this->resetForm();

        $this->brand_id = $brandId;

        $brand = Brand::find($brandId);

        $decodedBrandName = json_decode($brand->name, true);
        $this->brand_name = (json_last_error() === JSON_ERROR_NONE && is_array($decodedBrandName))
            ? ($decodedBrandName[app()->getLocale()] ?? reset($decodedBrandName))
            : $brand->name;

        $dianSetting = DianSetting::where('brand_id', $brandId)->first();

        if ($dianSetting) {
            $this->editingSettingsId   = $dianSetting->id;
            $this->resolution_number   = $dianSetting->resolution_number;
            $this->resolution_date     = $dianSetting->resolution_date
                ? \Carbon\Carbon::parse($dianSetting->resolution_date)->format('m/d/Y')
                : '';
            $this->invoice_number_from = $dianSetting->invoice_number_from;
            $this->invoice_number_to   = $dianSetting->invoice_number_to;
        }

        $this->dispatchBrowserEvent('open-edit-general-settings-modal');
    }

    public function save()
    {
        $this->validate();

        $formattedDate = Carbon::parse($this->resolution_date)->format('Y-m-d');

        $dataToSave = [
            'resolution_number'   => $this->resolution_number,
            'resolution_date'     => $formattedDate,
            'invoice_number_from' => $this->invoice_number_from,
            'invoice_number_to'   => $this->invoice_number_to,
        ];

        DianSetting::updateOrCreate(
            ['brand_id' => $this->brand_id],
            $dataToSave
        );

        $this->dispatchBrowserEvent('close-edit-modal');
        $this->dispatchBrowserEvent('toastr:success', ['message' => __('common.dian_settings_saved_successfully')]);
        LogActivity::successLog(__('common.log_dian_settings_saved'));

        $this->resetForm();
    }

    public function resetForm()
    {
        $this->brand_id = null;
        $this->editingSettingsId = null;
        $this->resolution_number   = '';
        $this->resolution_date     = '';
        $this->invoice_number_from = '';
        $this->invoice_number_to   = '';
    }
}
