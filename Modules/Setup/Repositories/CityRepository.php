<?php

namespace Modules\Setup\Repositories;

use Modules\Setup\Entities\City;
use Modules\Setup\Entities\Country;
use Modules\Setup\Entities\State;

class CityRepository{

    public function getAll()
    {
        return City::query()
            ->select('cities.*')
            ->with(['state', 'state.country'])
            ->leftJoin('states', 'states.id', '=', 'cities.state_id')
            ->leftJoin('countries', 'countries.id', '=', 'states.country_id')
            ->orderBy('cities.name');
    }

    public function getCountries(){
        return Country::where('status', 1)->orderBy('name')->get();
    }

    public function getStates(){
        return State::where('status', 1)->orderBy('name')->get();
    }


    public function getByStateId($state_id)
    {
        return City::where('state_id', $state_id)->where('status', 1)->orderBy('name')->get();
    }


    public function getStateByCountry($id){

        $country = Country::findOrFail($id);
        return $country->states;
    }

    public function store($data){

        return City::create([
            'name' => $data['name'],
            'state_id' => $data['state'],
            'status' => $data['status']
        ]);

    }

    public function getById($id){
        return City::findOrFail($id);
    }

    public function update($data){

        $city = City::findOrFail($data['id']);
        $city->state_id = $data['state'];
        $city->status = $data['status'];
        $city->setTranslation('name', app()->getLocale(), $data['name']);
        $city->save();
        return $city;

    }

    public function status($data){
        return City::where('id',$data['id'])->update([
            'status' => $data['status']
        ]);
    }

    public function searchForSelect($stateId, $search = null, $limit = 5)
    {
        $query = City::where('status', 1);

        if ($stateId) {
            $query->where('state_id', $stateId);
        }

        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $cities = $query->orderBy('name')->limit($limit)->get(['id', 'name']);

        $locale = app()->getLocale();
        return $cities->map(function($city) use ($locale) {
            $name = $city->getTranslation('name', $locale, false);
            if (!$name) {
                $translations = $city->getTranslations('name');
                $name = reset($translations);
            }
            return [
                'id' => $city->id,
                'name' => $name,
            ];
        });
    }

    public function searchCityFull($search = null, $limit = 10)
    {
        $locale = app()->getLocale();

        $cities = City::with(['state.country'])
            ->where('status', 1)
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->when(!$search, function ($query) {
                $query->whereHas('state', function ($state) {
                    $state->where("id", 799);
                });
            })
            ->orderBy('name', 'ASC') 
            ->limit($limit)
            ->get();

        return $cities->map(function($city) use ($locale) {
            $cityName = $city->getTranslation('name', $locale, false);
            
            if (!$cityName) {
                 $translations = $city->getTranslations('name');
                 $cityName = reset($translations);
            }
            
            if (!$cityName) {
                $cityName = $city->name; 
            }

            $stateName = $city->state ? $city->state->name : '';
            $countryName = $city->state && $city->state->country ? $city->state->country->name : '';

            return [
                'id' => $city->id,
                'name' => implode(' - ', array_filter([$cityName, $stateName, $countryName])),
            ];
        });
    }

}

