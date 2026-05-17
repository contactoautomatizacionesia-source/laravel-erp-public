<?php
namespace App\Services;

use App\Repositories\StaffRepository;


class StaffService {
    protected $staffRepository;

    public function __construct(StaffRepository $staffRepository){
        $this->staffRepository = $staffRepository;
    }

    public function findByUserId(int $userId) {
        return $this->staffRepository->findByUserId($userId);
    }
}
