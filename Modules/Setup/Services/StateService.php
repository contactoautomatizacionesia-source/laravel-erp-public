<?php
namespace Modules\Setup\Services;

use Illuminate\Support\Facades\Validator;
use \Modules\Setup\Repositories\StateRepository;

class StateService
{
    protected $stateRepository;

    public function __construct(StateRepository  $stateRepository)
    {
        $this->stateRepository = $stateRepository;
    }

    public function getAll(){
        return $this->stateRepository->getAll();
    }

    public function getCountries(){
        return $this->stateRepository->getCountries();
    }

    public function store($data){
        return $this->stateRepository->store($data);
    }

    public function getById($id){
        return $this->stateRepository->getById($id);
    }

    public function update($data){
        return $this->stateRepository->update($data);
    }
    
    public function status($data){
        return $this->stateRepository->status($data);
    }

    public function searchForSelect($countryId, $search = null, $limit = 5)
    {
        return $this->stateRepository->searchForSelect($countryId, $search, $limit);
    }
    
    public function destroy($id)
    {
        return $this->stateRepository->destroy($id);
    }
}
