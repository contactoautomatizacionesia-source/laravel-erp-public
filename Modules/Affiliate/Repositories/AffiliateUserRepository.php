<?php


namespace Modules\Affiliate\Repositories;



use App\Models\User;
use Modules\Affiliate\Entities\AffiliateLink;
use Modules\Affiliate\Entities\AffiliateReferralPayment;
use Modules\Affiliate\Entities\AffiliateWithdraw;

class AffiliateUserRepository
{
   public function all()
   {
      return User::where('affiliate_request',1)->get();
   }

   public function query()
   {
       return User::where('affiliate_request',1);
   }

   public function find($id)
   {
       return User::find($id);
   }

   public function approved($id)
   {
       $row = $this->find($id);
       if($row) {
          return $row->update(['accept_affiliate_request'=>1]);
       }
       return false;
   }

   public function disableEnable($id)
   {
       $row = $this->find($id);
       if($row) {
           if($row->accept_affiliate_request == 1){
               return $row->update(['accept_affiliate_request'=>2]);
           }else{
                return $row->update(['accept_affiliate_request'=>1]);
           }
       }
       return false;
   }

   public function getUserById($id){
       return User::find($id);
   }

   public function getLinksByUserId($id){
       $links = AffiliateLink::with('payment')->where('owner_id', $id)->get();
       return $links;
   }
   public function userIncomeDataByUserId($id){
        return AffiliateReferralPayment::with(['item','incomeFrom'])->where('payment_to',$id)->orderBy('id','DESC')->get();
   }
   public function userTransectionDataByUserId($id){
        return AffiliateWithdraw::with('user')->where('user_id',$id)->orderBy('id','DESC')->get();
   }

}
