<?php

namespace Modules\OrderManage\Services;

use Modules\OrderManage\Repositories\OrderManageRepository;
use App\Models\DigitalFileDownload;
use Modules\GeneralSetting\Entities\GeneralSetting;
use App\Traits\SendMail;

class OrderManageService
{
    use SendMail; // Añadimos el trait de correos aquí para el envío de archivos digitales

    protected $ordermanageRepository;

    public function __construct(OrderManageRepository $ordermanageRepository)
    {
        $this->ordermanageRepository = $ordermanageRepository;
    }

    public function myConfirmedSalesList()
    {
        return $this->ordermanageRepository->myConfirmedSalesList();
    }
    public function myCompletedSalesList()
    {
        return $this->ordermanageRepository->myCompletedSalesList();
    }
    public function myPendingPaymentSalesList()
    {
        return $this->ordermanageRepository->myPendingPaymentSalesList();
    }
    public function myCancelledPaymentSalesList()
    {
        return $this->ordermanageRepository->myCancelledPaymentSalesList();
    }
    public function totalSalesList()
    {
        return $this->ordermanageRepository->totalSalesList();
    }
    public function findOrderByID($id)
    {
        return $this->ordermanageRepository->findOrderByID($id);
    }
    public function findOrderPackageByID($id)
    {
        return $this->ordermanageRepository->findOrderPackageByID($id);
    }
    public function orderInfoUpdate($data, $id)
    {
        return $this->ordermanageRepository->orderInfoUpdate($data, $id);
    }
    public function updateDeliveryStatus($data, $id)
    {
        return $this->ordermanageRepository->updateDeliveryStatus($data, $id);
    }
    public function updateDeliveryStatusRecieve($data)
    {
        return $this->ordermanageRepository->updateDeliveryStatusRecieve($data);
    }
    public function orderConfirm($id)
    {
        return $this->ordermanageRepository->orderConfirm($id);
    }
    public function getPackageInfo($id)
    {
        return $this->ordermanageRepository->getPackageInfo($id);
    }

    // --- MÉTODOS MOVIDOS DESDE EL REPOSITORIO ---

    public function getTrackOrderConfiguration()
    {
        return GeneralSetting::first();
    }

    public function trackOrderConfigurationUpdate($request)
    {
        $generatlSetting = GeneralSetting::first();
        $generatlSetting->track_order_by_secret_id = $request->track_order_by_secret_id;
        $generatlSetting->save();
    }

    public function sendDigitalFileAccess($data)
    {
        $exists = DigitalFileDownload::where('package_id', $data['package_id'])
            ->where('product_sku_id', $data['product_sku_id'])
            ->where('seller_product_sku_id', $data['seller_product_sku_id'])
            ->first();

        if (!$exists) {
            $digital_download = DigitalFileDownload::create([
                'customer_id' => (!empty($data['customer_id'])) ? $data['customer_id'] : null,
                'seller_id' => $data['seller_id'],
                'order_id' => $data['order_id'],
                'package_id' => $data['package_id'],
                'seller_product_sku_id' => $data['seller_product_sku_id'],
                'product_sku_id' => $data['product_sku_id'],
                'download_limit' => $data['qty'] * 3,
            ]);
        } else {
            $digital_download = $exists;
        }

        try {
            $this->sendDigitalFileMail($data['mail'], route('digital_file_download', encrypt($digital_download->id)), $data);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function DigitalFileDownload($slug)
    {
        $file = DigitalFileDownload::findOrFail(decrypt($slug));
        $part = explode('/', $file->file->file_source);
        if ($part[0] == '') {
            $filePath = 'public' . $file->file->file_source;
        } else {
            $filePath = 'public/' . $file->file->file_source;
        }
        $file->update([
            'downloaded_count' => $file->downloaded_count + 1,
        ]);
        if ($file->downloaded_count <= $file->download_limit) {
            return $filePath;
        } else {
            return false;
        }
    }
}
