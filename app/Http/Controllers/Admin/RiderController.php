<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

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

            return view('Admin.Riders.index', compact('data'));

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
