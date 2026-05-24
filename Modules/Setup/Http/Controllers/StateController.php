<?php

namespace Modules\Setup\Http\Controllers;

use DomainException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Setup\Services\CoverageCascadeService;
use Modules\Setup\Services\StateService;
use Yajra\DataTables\Facades\DataTables;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Modules\UserActivityLog\Traits\LogActivity;

class StateController extends Controller
{
    protected $stateService;

    protected $coverageCascadeService;

    public function __construct(StateService $stateService, CoverageCascadeService $coverageCascadeService)
    {
        $this->middleware('maintenance_mode');
        $this->stateService = $stateService;
        $this->coverageCascadeService = $coverageCascadeService;
    }

    public function index()
    {
        try{
            $countries = $this->stateService->getCountries();
            return view('setup::location.state.index',compact('countries'));
        }catch(Exception $e){
            LogActivity::errorLog($e->getMessage());
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json([
                'error' => $e->getMessage()
            ],503);
        }
    }

    public function getData(Request $request){
        try{
            $table = $request->get('table', 'all');
            $states = $this->stateService->getAll();
            
            if ($table === 'active') {
                $states->where('states.status', 1);
            } elseif ($table === 'inactive') {
                $states->where('states.status', 0);
            } elseif ($table === 'default') {
                $defaultCountry = \Modules\Setup\Entities\Country::where('is_default', 1)->first();
                if ($defaultCountry) {
                    $states->where('states.country_id', $defaultCountry->id);
                } else {
                    $states->whereRaw('1 = 0');
                }
            }
            
            return DataTables::of($states)
            ->orderColumn('name', 'states.name $1')
            ->addIndexColumn()
            ->addColumn('country', function($states){
                return @$states->country->name;
            })
            ->editColumn('name', function($states){
                return @$states->name;
            })
            ->addColumn('status', function($states){
                return view('setup::location.state.components.status_td',compact('states'));
            })
            ->addColumn('action',function($states){
                return view('setup::location.state.components.action_td',compact('states'));
            })
            ->rawColumns(['status','action'])
            ->toJson();
        }catch(Exception $e){
            LogActivity::errorLog($e->getMessage());
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json([
                'error' => $e->getMessage()
            ],503);
        }
    }

    public function store(Request $request){
        $request->validate([
            'name' =>'required|max:255',
            'country' =>'required'
        ]);

        try{
            $this->stateService->store($request->except('_token'));
            LogActivity::successLog('State created successfully');
            return $this->reloadWithData();
        }catch(Exception $e){
            LogActivity::errorLog($e->getMessage());
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json([
                'error' => $e->getMessage()
            ],503);
        }

    }

    public function edit($id){

        try{
            $state = $this->stateService->getById($id);
            $countries = $this->stateService->getCountries();
            return view('setup::location.state.components.edit',compact('state','countries'));
        }catch(Exception $e){
            LogActivity::errorLog($e->getMessage());
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json([
                'error' => $e->getMessage()
            ],503);
        }
    }

    public function update(Request $request){

        $request->validate([
            'name' =>'required|max:255',
            'country' => 'required'
        ]);

        try{
            $this->stateService->update($request->except('_token'));
            LogActivity::successLog('State updated successfully');
            return $this->reloadWithData();
        }catch(Exception $e){
            LogActivity::errorLog($e->getMessage());
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json([
                'error' => $e->getMessage()
            ],503);
        }
    }

    public function status(Request $request){
        try{
            $data = $request->except('_token');
            $id = (int) $data['id'];
            $newStatus = (int) $data['status'];

            if ($newStatus === 0) {
                $this->coverageCascadeService->deactivateState($id);
            } else {
                $this->coverageCascadeService->activateState($id);
            }

            LogActivity::successLog('State status change successfully');
            return true;
        }catch(DomainException $e){
            LogActivity::errorLog($e->getMessage());
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ],422);
        }catch(Exception $e){
            LogActivity::errorLog($e->getMessage());
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json([
                'error' => $e->getMessage()
            ],503);
        }
    }



    private function reloadWithData(){

        try{
            $countries = $this->stateService->getCountries();

            return response()->json([
                'TableData' =>  (string)view('setup::location.state.components.list'),
                'createForm' =>  (string)view('setup::location.state.components.create',compact('countries'))
            ],200);
        }catch(Exception $e){
            LogActivity::errorLog($e->getMessage());
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json([
                'error' => $e->getMessage()
            ],503);
        }
    }

    public function search(Request $request)
    {
        try {
            $countryId = $request->get('country_id');
            $search = $request->input('q', $request->input('search'));

            $states = $this->stateService->searchForSelect($countryId, $search, 5);

            return response()->json($states);

        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return response()->json([], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $id = $request->id;
            $this->stateService->destroy($id);
            LogActivity::successLog('State Deleted Successfully');
            return response()->json([
                'status' => 'success',
                'message' => __('common.deleted_successfully')
            ], 200);
        } catch (DomainException $e) {
            LogActivity::errorLog($e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => __('common.error_message')
            ], 503);
        }
    }

}
