<?php

namespace Modules\Affiliate\Http\Controllers;


use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Affiliate\Http\Requests\AffiliateWithdrawRequest;
use Modules\Affiliate\Http\Requests\BalanceTransferRequest;
use Modules\Affiliate\Repositories\AffiliateTransactionRepository;
use Exception;
use Yajra\DataTables\Facades\DataTables;

class AffiliateTransactionController extends Controller
{
    protected $affiliateTransactionRepo;

    public function __construct(AffiliateTransactionRepository $affiliateTransactionRepo)
    {
        $this->affiliateTransactionRepo = $affiliateTransactionRepo;
    }

    public function store(AffiliateWithdrawRequest $request)
    {
        try{
            DB::beginTransaction();
            $this->affiliateTransactionRepo->withdrawRequest($request->validated());
            DB::commit();
            return response()->json(['status' => 200]);
        }catch(Exception $e){
            DB::rollBack();
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json(['error' => $e->getMessage()],503);
        }
    }
    public function balanceTransferToWallet(BalanceTransferRequest $request)
    {
        try{
            DB::beginTransaction();
            $this->affiliateTransactionRepo->balanceTransferToWallet($request->validated());
            DB::commit();
            return response()->json(['status' => 200]);
        }catch(Exception $e){

            DB::rollBack();
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json(['error' => $e->getMessage()],503);
        }
    }
    public function edit($id)
    {
        try{
            $data['user'] = Auth::user();
            $affiliate_wallet = $data['user']->affiliateWallet;
            if($affiliate_wallet && $affiliate_wallet->paypal_account){
                $data['paypal_account'] =$affiliate_wallet->paypal_account;
            }
            $data['transaction'] = $this->affiliateTransactionRepo->find($id);
            return view('affiliate::affiliate.components._edit_withdraw_request_modal',$data);
        }catch(Exception $e){
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json(['error' => $e->getMessage()],503);
        }
    }

    public function update(Request $request,$id)
    {

        if(isset($request->balance_transfer_request)){
            $validate_rules = [
                'user_id'=>'required',
                'payment_type'=>'required',
                'transfer_amount'=>'required',
            ];
        }else{
            $validate_rules = [
                'user_id'=>'required',
                'withdraw_amount'=>'required',
                'payment_type'=>'required',
            ];
        }
        $request->validate($validate_rules, validationMessage($validate_rules));

        try{

            DB::beginTransaction();
            $this->affiliateTransactionRepo->update($request->all(),$id);
            DB::commit();
            return response()->json(['status' => 200]);
        }catch(Exception $e){
            DB::rollBack();
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json(['error' => $e->getMessage()],503);
        }
    }

    public function destroy(Request $request)
    {
        try{
            DB::beginTransaction();
            $this->affiliateTransactionRepo->delete($request->id);
            DB::commit();
            return response()->json(['status' => 200]);
        }catch(Exception $e){
            DB::rollBack();
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json(['error' => $e->getMessage()],503);
        }
    }

    public function pendingWithdraw()
    {
        try{
            $data['data'] = $this->affiliateTransactionRepo->pendingWithdraw();
            return view('affiliate::withdraw.pending_withdraw',$data);
        }catch(Exception $e){
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json(['error' => $e->getMessage()],503);
        }
    }

    public function pendingWithdrawDatatable(Request $request)
    {
        try{
            $data['start_date'] = '';
            $data['end_date'] = '';
            if($request->filter_date)
            {
                $date = explode('-',$request->filter_date);
                $data['start_date'] = date('Y-m-d',strtotime($date[0]));
                $data['end_date'] = date('Y-m-d',strtotime($date[1]));
            }
            $data= $this->affiliateTransactionRepo->pendingWithdrawQuery($data['start_date'],$data['end_date']);
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('date',function($row){
                    return showDate($row->request_date);
                })
                ->addColumn('amount',function($row){
                    return single_price($row->withdraw_amount);
                })
                ->addColumn('payment_type',function($row){
                    if($row->payment_type == 1){
                        $payment_type = 'Offline';
                    } elseif($row->payment_type == 2){
                        $payment_type = 'Paypal';
                    }else{
                        $payment_type = 'Add User Wallet';
                    }
                    return $payment_type;
                })
                ->addColumn('action',function($row){
                    return view('affiliate::withdraw.components._action',['row' => $row]);
                })
                ->rawColumns(['action'])
                ->toJson();
        }catch(Exception $e){
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json(['error' => $e->getMessage()],503);
        }
    }

    public function confirmWithdraw($id)
    {
        try{
            DB::beginTransaction();
            $this->affiliateTransactionRepo->withdrawConfirm($id);
            DB::commit();
            return response()->json(['status' => 200]);
        }catch(Exception $e){
            DB::rollBack();
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json(['error' => $e->getMessage()],503);
        }
    }

    public function completeWithdraw()
    {
        try{
            return view('affiliate::withdraw.complete_withdraw');
        }catch(Exception $e){
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json(['error' => $e->getMessage()],503);
        }
    }

    public function completeWithdrawDatatable(Request $request)
    {
        try{
            $data['start_date'] = '';
            $data['end_date'] = '';
            if($request->filter_date)
            {
                $date = explode('-',$request->filter_date);
                $data['start_date'] = date('Y-m-d',strtotime($date[0]));
                $data['end_date'] = date('Y-m-d',strtotime($date[1]));
            }
            $data= $this->affiliateTransactionRepo->confirmWithdrawQuery($data['start_date'],$data['end_date']);

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('request_date',function($row){
                    return showDate($row->request_date);
                })
                ->editColumn('confirm_date',function($row){
                    return showDate($row->confirm_date);
                })
                ->addColumn('confirmedUser',function($row){
                    return $row->confirmedUser?$row->confirmedUser->name:'NA';
                })
                ->addColumn('amount',function($row){
                    return single_price($row->withdraw_amount);
                })
                ->addColumn('payment_type',function($row){
                    if($row->payment_type == 1){
                        $payment_type = 'Offline';
                    } elseif($row->payment_type == 2){
                        $payment_type = 'Paypal';
                    }else{
                        $payment_type = 'Add User Wallet';
                    }
                    return $payment_type;
                })
                ->rawColumns(['action'])
                ->toJson();
        }catch(Exception $e){
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json(['error' => $e->getMessage()],503);
        }
    }

    public function pendingCommissionApproved()
    {
        try{
            DB::beginTransaction();
            Artisan::call('affiliate:commission');
            DB::commit();
            Toastr::success('Pending Commissions Approved Successfully');
            return back();
        }catch(Exception $e){
            DB::rollBack();
            Toastr::error($e->getMessage(), 'Error!!');
            return $e->getMessage();
        }
    }


}
