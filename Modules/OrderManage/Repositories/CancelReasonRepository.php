<?php
namespace Modules\OrderManage\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\OrderManage\Entities\CancelReason;
use Modules\OrderManage\Exceptions\CancelReasonInUseException;

class CancelReasonRepository
{
    public function getAll()
    {
        return CancelReason::all();
    }

    public function save($data)
    {
        $cancelReason = new CancelReason();
        $cancelReason->fill($data)->save();
    }

    public function update($data, $id)
    {
        $cancelReason = CancelReason::findOrFail($id);

        $cancelReason->fixLegacyTranslations();

        $cancelReason->update([
            'name' => $data['name'],
            'description' => $data['description']
        ]);
    }

    public function delete($id)
    {
        $inUse = DB::table('orders')->where('cancel_reason_id', $id)->exists()
            || DB::table('order_package_details')->where('cancel_reason_id', $id)->exists();

        if ($inUse) {
            throw new CancelReasonInUseException();
        }

        return CancelReason::findOrFail($id)->delete();
    }

    public function getById($id){
        return CancelReason::find($id);
    }
}
