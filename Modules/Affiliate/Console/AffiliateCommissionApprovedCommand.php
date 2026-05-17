<?php

namespace Modules\Affiliate\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Affiliate\Entities\AffiliateReferralPayment;
use Modules\Affiliate\Entities\AffiliateUserWallet;


class AffiliateCommissionApprovedCommand extends Command
{

    protected $name = 'affiliate:commission';


    protected $description = 'Affiliate Commissions Approval.';

    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        $today  = Carbon::now();
        $pending_commissions = AffiliateReferralPayment::where('status',0)->get();

        foreach ($pending_commissions as $commission){
            $approval_date = Carbon::parse($commission->date)->addDays(affiliateConfig('balance_add_account_after_days'));
            if($approval_date->lte($today)){
                $row = $commission->update(['status' => 1]);
                if($row){
                    $this->userWallet($commission);
                }
            }
        }

        return true;
    }

    private function userWallet($data)
    {
        $row = AffiliateUserWallet::where('user_id',$data->payment_to)->first();
        if($row){
            $row->update(['amount'=>$row->amount + $data->amount]);
        }else{
            AffiliateUserWallet::create([
                'user_id'   => $data->payment_to,
                'amount'    => number_format($data->amount,2),
            ]);
        }
    }
}
