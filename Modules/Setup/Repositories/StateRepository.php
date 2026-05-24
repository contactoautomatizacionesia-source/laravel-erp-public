<?php

namespace Modules\Setup\Repositories;

use Modules\Setup\Entities\Country;
use Modules\Setup\Entities\State;

class StateRepository{

    public function getAll(){
        return State::query()
            ->select('states.*')
            ->with('country')
            ->orderBy('states.name');
    }

    public function getCountries(){
        return Country::where('status', 1)->orderBy('name')->get();
    }

    public function getByCountryId($countryId)
    {
        return State::where('country_id', $countryId)->where('status', 1)->orderBy('name')->get();
    }

    public function store($data){

        return State::create([
            'name' => $data['name'],
            'country_id' => $data['country'],
            'status' => $data['status']
        ]);
    }

    public function getById($id){
        return State::findOrFail($id);
    }

    public function update($data){

        $state = State::findOrFail($data['id']);

        return $state->update([
            'name' => $data['name'],
            'country_id' => $data['country'],
            'status' => $data['status']
        ]);

    }

    public function status($data){
        return State::where('id',$data['id'])->update([
            'status' => $data['status']
        ]);
    }

    public function searchForSelect($countryId, $search = null, $limit = 5)
    {        
        $query = State::where('status', 1);

        if ($countryId) {
            $query->where('country_id', $countryId);
        }

        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $states = $query->orderBy('name')->limit($limit)->get(['id', 'name']);

        $locale = app()->getLocale();
        return $states->map(function($state) use ($locale) {
            $name = $state->getTranslation('name', $locale, false);
            if (!$name) {
                $translations = $state->getTranslations('name');
                $name = reset($translations);
            }
            return [
                'id' => $state->id,
                'name' => $name,
            ];
        });
    }

    public function destroy($id)
    {
        $state = State::findOrFail($id);

        // Cascada Lógica para Cities
        \Modules\Setup\Entities\City::where('state_id', $state->id)->delete();

        $state->delete();
        
        return true;
    }
}

