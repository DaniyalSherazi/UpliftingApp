<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Hash;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function signup(Request $request): JsonResponse
    {
        try{
            Validator::make($request->all(),[
                'first_name' => 'required',
                'last_name' => 'required',
                'username' => 'required|unique:users,username',
                'email' => 'required|email|unique:users,email',
                'phone' => 'required',
                'password' => 'required',
                'nationality' => 'required',
                'nat_id' => 'required',
                'nat_id_photo' => 'required',
                'avatar' => 'nullable',
                'device_id' => 'required',
                'lat_long' => 'required',

            ],[
                'first_name.required' => 'First name is required',
                'last_name.required' => 'Last name is required',
                'username.required' => 'Username is required',
                'username.unique' => 'Username already exists',
                'email.required' => 'Email is required',
                'email.email' => 'Invalid email format',
                'email.unique' => 'Email already exists',
                'phone.required' => 'Phone number is required',
                'nationality.required' => 'Nationality is required',
                'nat_id.required' => 'National ID number is required',
                'password.required' => 'Password is required',
                'nat_id_photo.required' => 'National ID photo is required',
                'device_id.required' => 'Device ID is required',
                'lat_long.required' => 'Lat long is required',
            ]);

            $avatar = null;
            $nat_id_photo = null;
            // move photos to storage
            if ($request->hasFile('avatar')) {
                $image = $request->file('avatar');
                $image_name = 'r-avatar' . time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('rider-avatar'), $image_name);
                $avatar = 'rider-avatar/' . $image_name;
            }

            if ($request->hasFile('nat_id_photo')) {
                $image = $request->file('nat_id_photo');
                $image_name = 'r-nat-id' . time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('rider-nat-id'), $image_name);
                $nat_id_photo = 'rider-nat-id/' . $image_name;
            }


            User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'username' => $request->username,
                'phone' => $request->phone,
                'nationality' => $request->nationality,
                'nat_id' => $request->nat_id,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'rider',
                'status' => 'pending',
                'lat_long' => $request->lat_long,
                'device_id' => $request->device_id,
                'nat_id_photo' => $nat_id_photo,
                'avatar' => $avatar

            ]);

            return response()->json(['message' => 'Your account has been created successfully'], 200);
        }catch(QueryException $e){
            return response()->json(['DB error' => $e->getMessage()], 500);
        }catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    } 

    public function signin(Request $request): JsonResponse
    {
        try{
            Validator::make($request->all(),[
                'email' => 'required|email',
                'password' => 'required',
            ],[
                'email.required' => 'Email is required',
                'email.email' => 'Invalid email format',
                'password.required' => 'Password is required',
            ]);      

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            $token = $user->createToken('rider-token', ['rider'])->plainTextToken;

            return response()->json(['token' => $token], 200);

        }catch(QueryException $e){
            return response()->json(['DB error' => $e->getMessage()], 500);
        }catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function setup(Request $request): JsonResponse
    {
        try{
            $user = Auth::user();

            Validator::make($request->all(),[
                'license_number' => 'required',
                'license_expiry' => 'required',
                'license_photo' => 'required',
                'driving_experience' => 'required',
            ],[
                'license_number.required' => 'License number is required',
                'license_expiry.required' => 'License expiry is required',
                'license_photo.required' => 'License photo is required',
                'driving_experience.required' => 'Driving experience is required',
            ]);

            if (!$user) throw new Exception('Account not found');

            $license_photo = null;

            if ($request->hasFile('license_photo')) {
                $image = $request->file('license_photo');
                $image_name = 'r-license' . $request->license_number . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('rider-license'), $image_name);
                $license_photo = 'rider-license/' . $image_name;
            }

            Rider::create([
                'user_id' => $user->id,
                'license_number' => $request->license_number,
                'license_expiry' => $request->license_expiry,
                'license_photo' => $license_photo,
                'total_rides' => 0,
                'driving_experience' => $request->driving_experience,
                'current_rating' => 0
            ]);

            return response()->json(['message' => 'Your account is setup successfully'], 200);

        }catch(QueryException $e){
            return response()->json(['DB error' => $e->getMessage()], 500);
        }catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
