<?php

namespace Modules\Setup\Repositories;

use Modules\Setup\Entities\Country;
use App\Traits\ImageStore;
use Modules\Setup\Entities\City;
use Modules\Setup\Entities\State;
use Illuminate\Support\Facades\DB;

class CountryRepository{
    use ImageStore;

    public function getAll(){
        return Country::orderBy('name')->get();
    }
    public function getContinent(){
        return DB::table('continents')->orderBy('id')->get();
    }

    public function getActiveAll(){
        return Country::orderBy('name')->where('status', 1)->get();
    }

    public function store($data){

        if(isset($data['flag'])){
            $imagename = ImageStore::saveFlag($data['flag'],$data['name'],61,36);
            $data['flag'] = $imagename;
        }
        Country::create([
            'name' => $data['name'],
            'code' => $data['code'],
            'phonecode' => $data['phonecode'],
            'flag' => isset($data['flag'])?$data['flag']:null,
            'status' => $data['status']
        ]);
        return true;
    }

    public function getById($id){
        return Country::findOrFail($id);
    }

    public function update($data){

        $country = Country::findOrFail($data['id']);
        
        // BUG 5: Verificar que hay al menos otro país con is_default=true
        if (isset($data['is_default']) && $data['is_default'] == false) {
            $otherDefault = Country::where('is_default', true)
                                   ->where('id', '!=', $data['id'])
                                   ->exists();
            if (!$otherDefault) {
                throw new \DomainException(
                    'Debe existir al menos un país marcado como predeterminado.'
                );
            }
        }
        
        if (isset($data['remove_flag']) && $data['remove_flag'] == 1) {
            ImageStore::deleteImage($country->flag);
            $data['flag'] = null;
        } elseif(isset($data['flag'])){
            ImageStore::deleteImage($country->flag);

            $imagename = ImageStore::saveFlag($data['flag'],$data['name'],61,36);
            $data['flag'] = $imagename;
        }else{
            $data['flag'] = $country->flag;
        }

        $country->update([
            'name' => $data['name'],
            'code' => $data['code'],
            'phonecode' => $data['phonecode'],
            'flag' => $data['flag'],
            'status' => $data['status'],
            'is_default' => $data['is_default'] ?? false,
        ]);
        return true;
    }

    public function status($data){
        return Country::where('id',$data['id'])->update([
            'status' => $data['status']
        ]);
    }

    public function getStateByCountry($id){
        return State::with('country')->where('status', 1)->where('country_id', $id)->orderBy('name')->get();
    }

    public function getCityByState($id){
        return City::with('state')->where('status', 1)->where('state_id', $id)->orderBy('name')->get();
    }

    public function searchForSelect($search = null, $limit = 5)
    {
        $query = Country::where('status', 1);

        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $countries = $query
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name']);
        
        $locale = app()->getLocale();
        return $countries->map(function($country) use ($locale) {
            $name = $country->getTranslation('name', $locale, false);
            if (!$name) {
                $translations = $country->getTranslations('name');
                $name = reset($translations);
            }
            return [
                'id' => $country->id,
                'name' => $name,
            ];
        });
    }

    public function destroy($id)
    {
        $country = Country::findOrFail($id);
        
        // Evitar eliminar si es el país por defecto
        if ($country->is_default) {
            throw new \DomainException('No se puede eliminar el país predeterminado.');
        }

        // Cascada Lógica para States y Cities
        $states = State::where('country_id', $country->id)->get();
        foreach ($states as $state) {
            City::where('state_id', $state->id)->delete();
            $state->delete();
        }

        $country->delete();
        
        return true;
    }
}

