<?php
namespace Modules\Setup\Services;

use Illuminate\Support\Facades\Validator;
use \Modules\Setup\Repositories\CityRepository;

class CityService
{
    protected $cityRepository;

    public function __construct(CityRepository  $cityRepository)
    {
        $this->cityRepository = $cityRepository;
    }

    public function getAll(){
        return $this->cityRepository->getAll();
    }

    public function getCountries(){
        return $this->cityRepository->getCountries();
    }

    public function getStates(){
        return $this->cityRepository->getStates();
    }

    public function store($data){
        return $this->cityRepository->store($data);
    }

    public function getById($id){
        return $this->cityRepository->getById($id);
    }

    public function update($data){
        return $this->cityRepository->update($data);
    }
    
    public function status($data){
        return $this->cityRepository->status($data);
    }

    public function getStateByCountry($id){
        return $this->cityRepository->getStateByCountry($id);
    }

    public function searchForSelect($stateId, $search = null, $limit = 5)
    {
        return $this->cityRepository->searchForSelect($stateId, $search, $limit);
    }

    public function searchCityFull($search = null, $limit = 10)
    {
        return $this->cityRepository->searchCityFull($search, $limit);
    }
    
    public function destroy($id)
    {
        return $this->cityRepository->destroy($id);
    }
}
