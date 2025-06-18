<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use App\Models\User;
use App\Models\Vehicle;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class RiderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try{
            
            $query = User::select('users.*')
            ->join('riders', 'users.id', '=', 'riders.user_id')->orderBy('id', 'desc');

            $perPage = $request->query('per_page', 25);
            $searchQuery = $request->query('search');

            if (!empty($searchQuery)) {
                $customerIds = Rider::where('username', 'like', '%' . $searchQuery . '%')
                        ->orWhere('email', 'like', '%' . $searchQuery . '%')
                        ->pluck('id')
                        ->toArray();
    
                    // Filter orders by the found Customers IDs
                    $query = $query->whereIn('users.id', $customerIds);
            }
            // Execute the query with pagination
            $data = $query->paginate($perPage);

            return view('admin.riders.index', compact('data'));

        }catch(Exception $e){
            Session::flash('error', [
                'text' => "something went wrong. Please try again",
            ]);
            return redirect()->back();
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try{
            $data = User::select('users.*','riders.status as online_status','riders.*','users.id as user_id')
            ->join('riders', 'users.id', '=', 'riders.user_id')->where('users.id', $id)->first();

            $vehicles = Vehicle::where('vehicle_of', $data->user_id)->get();
            
            return view('admin.riders.show', compact('data','vehicles'));
        }catch(Exception $e){
            Session::flash('error', [
                'text' => "something went wrong. Please try again",
            ]);
            return redirect()->back();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        
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

    public function updateStatus(Request $request)
    {
        try{
            $data = User::find($request->rider_id);
            if($request->status ==1){
                $data->status = 'active';
            }else{
                $data->status = 'inactive';
            }
            $data->save();
            return response()->json(['success' => 'Status updated successfully'], 200);
        }catch(QueryException $e){
            return response()->json(['DB error' => $e->getMessage()], 500);
        }catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function approvedStatus(string $id, string $status)
    {
        try{
            $data = User::find($id);
            if (!$data) {
                Session::flash('error', [
                    'text' => "Rider not found",
                ]);
                return redirect()->back();
            }
            $validator = Validator::make([
                'status' => $status,
            ], [
                'status.required' => 'Status is required',
                'status.in:approved,suspended' => 'Status must be approved or rejected',
            ]);
            if ($validator->fails()) {
                Session::flash('error', [
                    'text' => $validator->errors()->first(),
                ]);
                return redirect()->back();
            }
            $data->is_approved = $status;
            $data->save();
            Session::flash('success', [
                'text' => "Rider Approval updated successfully",
            ]);
            return redirect()->back();
        }catch(Exception $e){
            Session::flash('error', [
                'text' => "something went wrong. Please try again",
            ]);
            return redirect()->back();
        }
    }
}
