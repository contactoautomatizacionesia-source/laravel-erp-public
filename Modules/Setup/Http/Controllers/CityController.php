<?php

namespace Modules\Setup\Http\Controllers;

use DomainException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Setup\Http\Requests\StoreCityRequest;
use Modules\Setup\Http\Requests\UpdateCityRequest;
use Modules\Setup\Services\CityService;
use Modules\Setup\Services\CoverageCascadeService;
use Yajra\DataTables\Facades\DataTables;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Modules\UserActivityLog\Traits\LogActivity;

class CityController extends Controller
{
    protected $cityService;

    protected $coverageCascadeService;

    public function __construct(CityService $cityService, CoverageCascadeService $coverageCascadeService)
    {
        $this->middleware('maintenance_mode');
        $this->cityService = $cityService;
        $this->coverageCascadeService = $coverageCascadeService;
    }

    public function index()
    {
        try{
            $countries = $this->cityService->getCountries();
            $states  = $this->cityService->getStates();
            return view('setup::location.city.index',compact('countries','states'));
        }catch(Exception $e){
            LogActivity::errorLog($e->getMessage());
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json([
                'error' => $e->getMessage()
            ],503);
        }
    }

    public function getData()
    {
        try {
            $cities = $this->cityService->getAll();

            $response = DataTables::of($cities)
            ->orderColumn('name', 'cities.name $1')
            ->orderColumn('state.name', 'states.name $1')
            ->orderColumn('state.country.name', 'countries.name $1')
            ->orderColumn('status', 'cities.status $1')
            ->addIndexColumn()
            ->editColumn('name',function($cities){
                return $cities->name;
            })
            ->addColumn('country', function($cities){
                return @$cities->state->country->name;

            })
            ->addColumn('state', function($cities){
                return @$cities->state->name;

            })
            ->addColumn('status', function($cities){
                return view('setup::location.city.components.status_td',compact('cities'));
            })
            ->addColumn('action',function($cities){
                return view('setup::location.city.components.action_td',compact('cities'));
            })
            ->rawColumns(['status','action'])
            ->toJson();

            return $response;
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json([
                'error' => $e->getMessage()
            ],503);
        }
    }

    public function getState(Request $request){
        try{
            $states = $this->cityService->getStateByCountry($request->country_id);
            return view('setup::location.city.components.get_state_by_country',compact('states'));
        }catch(Exception $e){
            LogActivity::errorLog($e->getMessage());
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json([
                'error' => $e->getMessage()
            ],503);
        }
    }

    public function store(StoreCityRequest $request){

        try{
            $this->cityService->store($request->except('_token'));
            LogActivity::successLog('city created successfully');
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
            $city = $this->cityService->getById($id);
            $countries = $this->cityService->getCountries();
            return view('setup::location.city.components.edit',compact('city','countries'));
        }catch(Exception $e){
            LogActivity::errorLog($e->getMessage());
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json([
                'error' => $e->getMessage()
            ],503);
        }
    }

    public function update(UpdateCityRequest $request){

        try{
            $this->cityService->update($request->except('_token'));
            LogActivity::successLog('city updated successfully');
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
                $this->coverageCascadeService->deactivateCity($id);
            } else {
                $this->coverageCascadeService->activateCity($id);
            }

            LogActivity::successLog('city status change successfully');
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
            $countries = $this->cityService->getCountries();

            return response()->json([
                'TableData' =>  (string)view('setup::location.city.components.list'),
                'createForm' =>  (string)view('setup::location.city.components.create',compact('countries'))
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
            $stateId = $request->get('state_id');
            $search = $request->get('search', $request->get('q', ''));

            $cities = $this->cityService->searchForSelect($stateId, $search, 5);

            return response()->json($cities);

        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return response()->json([], 500);
        }
    }

    public function searchForSelect(Request $request)
    {
        $search = $request->input('q', $request->input('search'));
        $limit = $request->get('limit', 10);

        return $this->cityService->searchCityFull($search, $limit);
    }

}
