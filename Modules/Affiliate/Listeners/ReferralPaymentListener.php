<?php

namespace Modules\Affiliate\Listeners;



use App\Models\User;
use Carbon\Carbon;
use Modules\Affiliate\Entities\AffiliateCategoryCommission;
use Modules\Affiliate\Entities\AffiliateLinkVisitTrackUser;
use Modules\Affiliate\Entities\AffiliateReferralPayment;
use Modules\Affiliate\Events\ReferralPayment;
use Modules\Product\Entities\Product;
use Request;
use Browser;


class ReferralPaymentListener
{

    public function __construct()
    {
        //
    }


    public function handle(ReferralPayment $event)
    {
        $itemPrice = $event->item_price;
        $itemId = $event->item_id;

        if($event->user_id == 0){
            if($this->checkVisitRecord()){
                $paymentTo = $this->checkVisitRecord()->affiliateLink->owner_id;
                $affiliateLinkId = $this->checkVisitRecord()->affiliate_link_id;
                $validityStartDate = $this->checkVisitRecord()->date;
            }else{
                return false;
            }

        }else{
            $user = User::with(['isReferralUser','isReferralUser.affiliateLink'])->find($event->user_id);
            $paymentTo = $user->isReferralUser->affiliateLink->owner_id;
            $affiliateLinkId = $user->isReferralUser->affiliate_link_id;
            $validityStartDate = $user->isReferralUser->validity_start_date;
        }


        $commission_amount = $this->commissionAmountCalculation($itemId,$itemPrice);

        $data = [
            'payment_to'=>$paymentTo,
            'amount'=>number_format($commission_amount,2),
            'affiliate_link_id'=>$affiliateLinkId,
            'payment_from'=>$event->user_id,
            'item_id'=>$itemId,
            'date'=>date('Y-m-d'),
        ];

        if(affiliateConfig('referral_duration_type') == 'Fixed'){
            $validity_end_date = Carbon::parse($validityStartDate)->addDays(affiliateConfig('referral_duration'));
            $today = Carbon::now();
            if($today->lte($validity_end_date)){
                AffiliateReferralPayment::create($data);
                return true;
            }
        }elseif(affiliateConfig('referral_duration_type') == 'Onetime'){
             $onetime_flag = $this->checkAffiliateLinkPaymentAvailable($data);
             if($onetime_flag){
                 AffiliateReferralPayment::create($data);
                 return true;
             }
        }else{
             AffiliateReferralPayment::create($data);
            return true;
        }
    }
    private function checkVisitRecord()
    {
        $ip = Request::ip();
        $agent = Browser::browserFamily().'-'.Browser::browserVersion().'-'.Browser::browserEngine().'-'.Browser::platformName().'-'.Browser::deviceModel();
//        $agent = Browser::browserFamily().'-'.Browser::platformName().'-'.Browser::deviceModel();
        $row = AffiliateLinkVisitTrackUser::with(['affiliateLink'])->where('ip',$ip)->where('agent',$agent)->orderBy('id','DESC')->first();
        if($row){
            return $row;
        }else{
            return false;
        }
    }

    private function commissionAmountCalculation($itemId,$itemPrice)
    {
        $amount = 0;
        if(affiliateConfig('commission_type') == 'Product'){
            if(affiliateConfig('amount_type') == 'Flat'){
                $amount = affiliateConfig('commission_amount');
            }else{
                $amount = (affiliateConfig('commission_amount') / 100) * $itemPrice;
            }
        }else{
            $item =  Product::with(['categories'])->find($itemId);
            $product_category_ids = $item->categories->pluck('id')->toArray();
            $category_wise_commissions = AffiliateCategoryCommission::whereIn('category_id',$product_category_ids)->get();

            if(count($category_wise_commissions) > 0){
                if(count($category_wise_commissions) > 1){
                    if(affiliateConfig('multi_category_commission_calculate') == 'Minimum'){
                        $row = $category_wise_commissions->where('amount',$category_wise_commissions->min('amount'))->first();
                        if($row->calculation_method == 'Percentage'){
                            $amount = ($row->amount / 100) * $itemPrice;
                        }else{
                            $amount = $row->amount;
                        }
                    }elseif (affiliateConfig('multi_category_commission_calculate') == 'Maximum'){
                        $row = $category_wise_commissions->where('amount',$category_wise_commissions->max('amount'))->first();
                        if($row->calculation_method == 'Percentage'){
                            $amount = ($row->amount / 100) * $itemPrice;
                        }else{
                            $amount = $row->amount;
                        }
                    }else{
                        $avg_amount = $category_wise_commissions->avg('amount');
                        $amount = ($avg_amount/ 100) * $itemPrice;
                    }

                }else{
                    if($category_wise_commissions[0]->calculation_method == 'Percentage'){
                        $amount = ($category_wise_commissions[0]->amount / 100) * $itemPrice;
                    }else{
                        $amount = $category_wise_commissions[0]->amount;
                    }
                }

            }else{
                if(affiliateConfig('common_calculation_method') == 'Percentage'){
                    $amount = (affiliateConfig('common_amount') / 100) * $itemPrice;
                }else{
                    $amount = affiliateConfig('common_amount');
                }
            }
        }
        return $amount;

    }


    private function checkAffiliateLinkPaymentAvailable($data)
    {
        $payment = AffiliateReferralPayment::where('affiliate_link_id',$data['affiliate_link_id'])->first();
        if($payment){
            return false;
        }else{
            return true;
        }

    }
}
