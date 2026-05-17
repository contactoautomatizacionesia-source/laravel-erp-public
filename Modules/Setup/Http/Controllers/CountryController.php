<?php

namespace Modules\Setup\Http\Controllers;

use DomainException;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Setup\Http\Requests\StoreCountryRequest;
use Modules\Setup\Http\Requests\UpdateCountryRequest;
use Modules\Setup\Services\CountryService;
use Modules\Setup\Services\CoverageCascadeService;
use Modules\Setup\Services\DefaultLocationGuard;
use Yajra\DataTables\Facades\DataTables;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Modules\UserActivityLog\Traits\LogActivity;

class CountryController extends Controller
{
    protected $countryService;

    protected $coverageCascadeService;

    protected $defaultLocationGuard;

    public function __construct(
        CountryService $countryService,
        CoverageCascadeService $coverageCascadeService,
        DefaultLocationGuard $defaultLocationGuard
    ) {
        $this->middleware('maintenance_mode');
        $this->countryService = $countryService;
        $this->coverageCascadeService = $coverageCascadeService;
        $this->defaultLocationGuard = $defaultLocationGuard;
    }

    public function index()
    {

        try{
            $countries = $this->countryService->getAll();

            return view('setup::location.country.index', compact('countries'));
        }catch(Exception $e){
            LogActivity::errorLog($e->getMessage());
            Toastr::error($e->getMessage(), 'Error!!');
            return $e->getMessage();
        }
    }

    public function getData(){
        try{
            $countries = $this->countryService->getAll();
            return DataTables::of($countries)
            ->addIndexColumn()
            ->editColumn('name',function($country){
                return $country->name;
            })
            ->editColumn('code',function($country){
                return $country->code;
            })
            ->editColumn('phonecode',function($country){
                return getNumberTranslate($country->phonecode);
            })
            ->addColumn('flag', function($country){
                return '<div class="list_flag_div"><img class="list_flag" src="' . showImage($country->flag ? $country->flag : 'flags/no_image.png') . '" alt=""></div>';
            })
            ->addColumn('status', function($country){
                return view('setup::location.country.components.status_td',compact('country'));
            })
            ->addColumn('action',function($country){
                return view('setup::location.country.components.action_td',compact('country'));
            })
            ->rawColumns(['flag','status','action'])
            ->toJson();
        }catch(Exception $e){
            LogActivity::errorLog($e->getMessage());
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json([
                'error' => $e->getMessage()
            ],503);
        }
    }

    public function store(StoreCountryRequest $request){


        try{
            $this->countryService->store($request->except('_token'));
            LogActivity::successLog('Country Created Successfully');
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
            $country = $this->countryService->getById($id);
            return view('setup::location.country.components.edit',compact('country',));
        }catch(Exception $e){
            LogActivity::errorLog($e->getMessage());
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json([
                'error' => $e->getMessage()
            ],503);
        }
    }

    public function update(UpdateCountryRequest $request){

        try{
            $this->countryService->update($request->except('_token'));
            LogActivity::successLog('Country Updated Successfully');
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
                $this->defaultLocationGuard->guardDeactivation($id);
                $this->coverageCascadeService->deactivateCountry($id);
            } else {
                $this->countryService->status($data);
            }

            LogActivity::successLog('country status updated successfully');
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

    public function previewCascade(Request $request)
    {
        try {
            $request->validate([
                'type' => 'required|string|in:country,state',
                'id' => 'required|integer',
            ]);

            $result = $this->coverageCascadeService->previewCascade(
                $request->input('type'),
                (int) $request->input('id')
            );

            return response()->json(['impact' => $result]);
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json([
                'error' => $e->getMessage()
            ], 503);
        }
    }

    public function toggleDefault(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
            ]);

            $this->defaultLocationGuard->setDefault((int) $request->input('id'));

            return response()->json(['status' => 'success']);
        } catch (DomainException $e) {
            LogActivity::errorLog($e->getMessage());
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json([
                'error' => $e->getMessage()
            ], 503);
        }
    }

    public function get_states(Request $request){
        $states = [];
        $db_states =  \Modules\Setup\Entities\State::where('country_id',$request->get('country_id'))->get();
        foreach($db_states as $st)
        {

             $states[] = [
                "id" => $st->id,
                "name" => $st->name,
                "country_id" => $st->country_id,
                "status" => $st->status,
                "created_at" => $st->created_at,
                "updated_at" => $st->updated_at,
                "torod_country_id" =>$st->torod_country_id,
                "torod_state_id" => $st->torod_state_id,
             ];
        }
        return $states;
    }

    public function get_cities(Request $request){
        $cities = [];
        $db_cities =  $this->countryService->getCityByState($request->get('state_id'));
        foreach($db_cities as $ct)
        {
             $cities[] = [
                "id" => $ct->id,
                "name" => $ct->name,
                "state_id" => $ct->state_id,
                "status" => $ct->status,
                "created_at" => $ct->created_at,
                "updated_at" => $ct->updated_at,
                "torod_state_id" => $ct->torod_state_id,
                "torod_city_id" => $ct->torod_city_id,
             ];
        }
        return $cities;
    }

    private function reloadWithData(){

        try{
            $countries = $this->countryService->getAll();
            return response()->json([

                'TableData' =>  (string)view('setup::location.country.components.list', compact('countries',)),
                'createForm' =>  (string)view('setup::location.country.components.create')
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
            $search = $request->input('q', $request->input('search'));

            $countries = $this->countryService->searchForSelect($search , 5 );

            return response()->json($countries);

        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return response()->json([], 500);
        }
    }

}
