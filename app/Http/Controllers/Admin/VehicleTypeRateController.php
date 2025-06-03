<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleTypeRate;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class VehicleTypeRateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try{            
            $query = VehicleTypeRate::orderBy('id', 'desc');

            $perPage = $request->query('per_page', 25);
            $searchQuery = $request->query('search');

            if (!empty($searchQuery)) {
                $vehicleTypeRateIds = VehicleTypeRate::Where('title', 'like', '%' . $searchQuery . '%')
                        ->pluck('id')
                        ->toArray();
    
                    // Filter orders by the found Customers IDs
                    $query = $query->whereIn('id', $vehicleTypeRateIds);
            }
            // Execute the query with pagination
            $data = $query->paginate($perPage);

            return view('admin.vehicle-type-rates.index', compact('data'));

        }catch(Exception $e){
            Session::flash('error', [
                    'text' => $e->getMessage(),
                ]);
                return redirect()->back();
        }
    }

    public function create(){
        return view('admin.vehicle-type-rates.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            DB::beginTransaction();

            $validator = Validator::make($request->all(),[
                'title' => 'required',
                'current_base_price' => 'required',
                'current_price_per_km' => 'required',
                'current_price_per_min' => 'required',
                'description' => 'required',
            ],[
                'title.required' => 'Title is required',
                'current_base_price.required' => 'Current base price is required',
                'current_price_per_km.required' => 'Current price per km is required',
                'current_price_per_min.required' => 'Current price per min is required',
                'description.required' => 'Description is required',
            ]);
            if ($validator->fails()) {
                Session::flash('error', [
                    'text' => $validator->errors()->first(),
                ]);
                return redirect()->back();
            }
            
            VehicleTypeRate::create([
                'title' => $request->title,
                'current_base_price' => $request->current_base_price,
                'current_price_per_km' => $request->current_price_per_km,
                'current_price_per_min' => $request->current_price_per_min,
                'description' => $request->description
            ]);

            DB::commit();
            Session::flash('success', [
                    'text' => 'Vehicle type rate created successfully',
                ]);
                return redirect()->route('admin.vehicle-type-rates.index');

        }catch(Exception $e){
            DB::rollBack();
            Session::flash('error', [
                    'text' => $e->getMessage(),
                ]);
                return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request): JsonResponse
    {
        try{
            $admin = Auth::user();
            if (!$admin->tokenCan('admin')) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $data = VehicleTypeRate::find($id);
            return response()->json($data,200);

        }catch(QueryException $e){
            return response()->json(['DB error' => $e->getMessage()], 500);
        }catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try{
            $admin = Auth::user();
            DB::beginTransaction();
            if (!$admin->tokenCan('admin')) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $validator = Validator::make($request->all(),[
                'title' => 'required',
                'current_base_price' => 'required',
                'current_price_per_km' => 'required',
                'current_price_per_min' => 'required',
                'description' => 'required',
            ],[
                'title.required' => 'Title is required',
                'current_base_price.required' => 'Current base price is required',
                'current_price_per_km.required' => 'Current price per km is required',
                'current_price_per_min.required' => 'Current price per min is required',
                'description.required' => 'Description is required',
            ]);

            if ($validator->fails())throw new Exception($validator->errors()->first(),400);

            VehicleTypeRate::find($id)->update([
                'title' => $request->title,
                'current_base_price' => $request->current_base_price,
                'current_price_per_km' => $request->current_price_per_km,
                'current_price_per_min' => $request->current_price_per_min,
                'description' => $request->description
            ]);

            DB::commit();
            return response()->json(['message' => 'Vehicle type rate updated successfully'], 200);
        }catch(QueryException $e){
            DB::rollBack();
            return response()->json(['DB error' => $e->getMessage()], 500);
        }catch(Exception $e){
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
    
    }

    public function list(): JsonResponse
    {
        $data = VehicleTypeRate::all();
        return response()->json($data,200);
    }
}
