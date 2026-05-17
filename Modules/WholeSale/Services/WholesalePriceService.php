<?php
namespace Modules\WholeSale\Services;

use Modules\WholeSale\Repositories\WholesalePriceRepository;

class WholesalePriceService
{
    protected $wholesalePriceRepository;

    public function __construct(WholesalePriceRepository  $wholesalePriceRepository)
    {
        $this->wholesalePriceRepository = $wholesalePriceRepository;
    }

    public function getAllWholesalePrice($id)
    {
        return $this->wholesalePriceRepository->getAllWholesalePrice($id);
    }
}
