<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try{
            $user = Auth::user();
            $validator = Validator::make(request()->all(),[
                'vehcile_type' => 'required|exists:vehicle_type_rates,id',
                'registration_number' => 'required',
                'model' => 'required',
                'make' => 'required',
                'color' => 'required',
                'year' => 'required',
                'photos' => 'required|array',
                'photos.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'number_plate' => 'required',
            ],[
                'vehcile_type.required' => 'Vehicle type is required',
                'vehcile_type.exists' => 'Vehicle type does not exist',
                'registration_number.required' => 'Registration number is required',
                'model.required' => 'Model is required',
                'make.required' => 'Make is required',
                'color.required' => 'Color is required',
                'year.required' => 'Year is required',
                'photos.required' => 'Photos is required',
                'photos.*.required' => 'Photos is required',
                'photos.*.image' => 'Photos must be an image',
                'photos.*.mimes' => 'Photos must be a file of type: jpeg, png, jpg, gif, svg',
                'photos.*.max' => 'Photos may not be greater than 2mb',
                'number_plate.required' => 'Number plate is required',
            ]);

            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);

            // move images
            $photos = [];
            foreach($request->photos as $photo){
                $image = $photo;
                $image_name = 'r-vehcile-' . $user->id . '-' . time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('rider-vehicle'), $image_name);
                $photos[] = 'rider-vehicle/' . $image_name;
            }
            $vehicle = Vehicle::create([
                'user_id' => $user->id,
                'vehcile_type' => $request->vehcile_type,
                'registration_number' => $request->registration_number,
                'model' => $request->model,
                'make' => $request->make,
                'color' => $request->color,
                'year' => $request->year,
                'number_plate' => $request->number_plate,
                'photos' => $photos,
            ]);

            return response()->json(['vehicle' => $vehicle], 200);
        }catch(QueryException $e){
            return response()->json(['DB message' => $e->getMessage(),], 500);
        }catch(Exception $e){
            return response()->json(['message' => $e->getMessage(),], $e->getCode());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
