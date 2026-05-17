<?php

namespace Modules\Affiliate\Http\Controllers;

use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Affiliate\Entities\AffiliateCategoryCommission;
use Modules\Affiliate\Http\Requests\AffiliateConfigurationRequest;
use Modules\Affiliate\Http\Requests\AffiliateLinkRequest;
use Modules\Affiliate\Repositories\AffiliateRepository;
use Modules\Affiliate\Repositories\AffiliateTransactionRepository;
use Modules\Product\Entities\Category;
use Modules\Product\Repositories\CategoryRepository;


class AffiliateController extends Controller
{
    protected $affiliateRepo;

    public function __construct(AffiliateRepository $affiliateRepo)
    {
        $this->affiliateRepo = $affiliateRepo;
//        $this->middleware(['affiliate']);
    }

    public function index(Request $request)
    {
        try{
            $data['start_date'] = isset($request->startDate)?$request->startDate:'';
            $data['end_date'] = isset($request->endDate)?$request->endDate:'';
            $data['user'] = Auth::user();
            $affiliate_wallet = $data['user']->affiliateWallet;
            if($affiliate_wallet && $affiliate_wallet->paypal_account){
                $data['paypal_account'] =$affiliate_wallet->paypal_account;
            }
            $data['data'] = $this->affiliateRepo->all();
            $affiliateTransactionRepo =  new AffiliateTransactionRepository();
            $data['user_transaction_data'] = $affiliateTransactionRepo->userWiseWithdraw($data['start_date'],$data['end_date']);
            $data['user_income_data'] = $affiliateTransactionRepo->userWiseIncome($data['start_date'],$data['end_date']);
            return view('affiliate::affiliate.index',$data);
        }catch(Exception $e){
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json(['error' => $e->getMessage()],503);
        }
    }
    public function store(AffiliateLinkRequest $request)
    {
        try{
            $this->affiliateRepo->create($request->validated());
            Toastr::success('Affiliate Link Generated Successfully !');
            return back();
        }catch(Exception $e){
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json(['error' => $e->getMessage()],503);
        }
    }

    public function configurationIndex()
    {
        try{
            $categoryRepo = new CategoryRepository(new Category());
            $data['categories'] = $categoryRepo->getAll();
            $data['calculation_methods'] = ['Percentage','Fixed'];
            $data['category_commissions'] = AffiliateCategoryCommission::all();
            return view('affiliate::affiliate.configuration',$data);
        }catch(Exception $e){
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json(['error' => $e->getMessage()],503);
        }
    }

    public function configurationUpdate(AffiliateConfigurationRequest $request)
    {
        try{
            DB::beginTransaction();
            $this->affiliateRepo->configuration($request->except(['_token']));
            DB::commit();
            Toastr::success('Affiliate Configuration Updated Successfully !');
            return back();
        }catch(Exception $e){
            DB::rollBack();
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json(['error' => $e->getMessage()],503);
        }
    }

    public function addOrUpdatePaypalAccount(Request $request)
    {
        $request->validate([
            'paypal_account' =>'required',
        ]);
        try{
            $this->affiliateRepo->addOrUpdatePaypalAccount($request->all());
            Toastr::success('Operation Successful!');
            return back();
        }catch(Exception $e){
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json(['error' => $e->getMessage()],503);
        }
    }

    public function deleteLink($id){
        $result = $this->affiliateRepo->deleteLink($id);
        if($result == 'success'){
            Toastr::success(__('common.deleted_successfully'), __('common.success'));
            return redirect()->back();
        }else{
            Toastr::error('Link not found.',__('common.error'));
            return redirect()->back();
        }
    }

    public function customerJoinAffiliate()
    {
        try{
            $user = User::find(auth()->user()->id)->first();
            if($user){
                User::where('id',auth()->user()->id)->update([
                    'affiliate_request' => 1,
                    'accept_affiliate_request' => affiliateConfig('admin_approval_need') == 1 ? 0 : 1,
                ]);
                Toastr::success('common.join_request_success','Success');
                return redirect()->back();
            }else{
                Toastr::error(__('common.Something Went Wrong'), __('common.success'));
                return redirect()->back();
            }
        }catch(Exception $e){
            Toastr::error(__('common.Something Went Wrong'), __('common.success'));
            return redirect()->back();
        }

    }
}
