<?php

namespace Modules\ClubPoint\Services;

use Modules\ClubPoint\Repositories\ClubPointRepository;

class ProductService
{
   
    public $clubPointRepository;

    public function __construct(ClubPointRepository  $clubPointRepository)
    {
        $this->clubPointRepository = $clubPointRepository;
    }
    public function create($data){

    }
    public function totalSalesList()
    {
        return $this->ordermanageRepository->totalSalesList();
    }
}