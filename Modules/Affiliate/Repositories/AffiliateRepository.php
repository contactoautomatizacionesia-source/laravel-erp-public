<?php


namespace Modules\Affiliate\Repositories;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Affiliate\Entities\AffiliateCategoryCommission;
use Modules\Affiliate\Entities\AffiliateConfiguration;
use Modules\Affiliate\Entities\AffiliateLink;
use Modules\Affiliate\Entities\AffiliateLinkVisitTrackUser;
use Modules\Affiliate\Entities\AffiliateReferralUser;
use Modules\Affiliate\Entities\AffiliateUserWallet;
use Request;
use Browser;

class AffiliateRepository
{
    public function all()
    {
        return AffiliateLink::with(['owner','registerUser','payment'])->where('owner_id',Auth::id())->get();
    }
    public function create(array $data)
    {
        return AffiliateLink::create([
            'affiliate_link'=>$data['affiliate_link'],
            'owner_id'=>Auth::id(),
        ]);
    }

    public function affiliateUser($userId)
    {
        if($this->checkVisitRecord()){
            return AffiliateReferralUser::create([
                'user_id'=>$userId,
                'affiliate_link_id'=>$this->checkVisitRecord()->affiliate_link_id,
                'validity_start_date'=>$this->checkVisitRecord()->date,
                'reffered_by' => @$this->checkVisitRecord()->affiliateLink->owner_id
            ]);
        }else{
            return false;
        }

    }

    private function checkVisitRecord()
    {
        $ip = Request::ip();
        $agent = Browser::browserFamily().'-'.Browser::browserVersion().'-'.Browser::browserEngine().'-'.Browser::platformName().'-'.Browser::deviceModel();
//        $agent = Browser::browserFamily().'-'.Browser::platformName().'-'.Browser::deviceModel();
        $row = AffiliateLinkVisitTrackUser::where('ip',$ip)->where('agent',$agent)->orderBy('id','DESC')->first();
        if($row){
            return $row;
        }else{
            return false;
        }
    }

    public function configuration(array $data)
    {
        foreach ($data as $key => $value) {
            if($key == 'categories' || $key == 'calculation_method'){
                if($data['commission_type'] == 'Category' && $key == 'categories'){
                    AffiliateCategoryCommission::truncate();
                    $cat_commission_data = [];
                    foreach ($data['categories'] as $category){
                        $method = $data['calculation_method'.$category];
                        $amount = $data['commission_amount'.$category];
                        $cat_commission_data[] =
                            [
                                'category_id'=>$category,
                                'calculation_method'=>$method,
                                'amount'=>$amount
                            ] ;
                    }
                    DB::table('affiliate_category_commissions')->insert($cat_commission_data);
                }
            }else{
                $row = AffiliateConfiguration::where('key',$key)->first();
                if ($row) {
                    $row->update(['value' => $value]);
                }else {
                    AffiliateConfiguration::create([
                        'key' => $key,
                        'value' => $value,
                    ]);
                }
            }

        }

        Cache::forget('affiliate_config');

        $datas = [];
        foreach (AffiliateConfiguration::get() as  $setting) {
            $datas[$setting->key] = $setting->value;
        }
        Cache::rememberForever('affiliate_config', function () use($datas) {
            return $datas;
        });

       return true;
    }

    public function addOrUpdatePaypalAccount(array $data)
    {
        return AffiliateUserWallet::updateOrCreate(
            [
                'user_id'   => Auth::id(),
            ],
            [
                'paypal_account'     => $data['paypal_account'],
            ]
        );
    }

    public function deleteLink($id){
        if(auth()->user()->role->type == 'superadmin' || auth()->user()->role->type == 'admin' || auth()->user()->role->type == 'staff'){
            $link = AffiliateLink::where('id', $id)->first();
        }else{
            $link = AffiliateLink::where('id', $id)->where('owner_id', auth()->id())->first();
        }
        if($link){
            $link->delete();
            return 'success';
        }
        return 'failed';
    }

}
