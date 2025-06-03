<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Mail\OTPMail;
use App\Mail\VerifyAccountMail;
use App\Models\Customer;
use DB;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Hash;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\PasswordResetToken;

class AuthController extends Controller
{
    public function signup(Request $request): JsonResponse
    {
        try{
            DB::beginTransaction();
            $validator = Validator::make($request->all(),[
                'first_name' => 'required',
                'last_name' => 'required',
                'username' => 'required|unique:users,username',
                'email' => 'required|email|unique:users,email',
                'phone' => 'required',
                'password' => 'required',
                'nationality' => 'required',
                'nat_id' => 'nullable',
                'nat_id_photo' => 'nullable',
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
                'password.required' => 'Password is required',
                'device_id.required' => 'Device ID is required',
                'lat_long.required' => 'Lat long is required',
            ]);

            if($validator->fails())throw new Exception($validator->errors()->first(),400);

            $avatar = null;
            $nat_id_photo = null;
            // move photos to storage
            if ($request->hasFile('avatar')) {
                $image = $request->file('avatar');
                $image_name = 'c-avatar' . time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('customer-avatar'), $image_name);
                $avatar = 'customer-avatar/' . $image_name;
            }

            if ($request->hasFile('nat_id_photo')) {
                $image = $request->file('nat_id_photo');
                $image_name = 'c-nat-id' . time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('customer-nat-id'), $image_name);
                $nat_id_photo = 'customer-nat-id/' . $image_name;
            }

            $token = rand(100000, 999999);
            $customer = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'username' => $request->username,
                'phone' => $request->phone,
                'nationality' => $request->nationality ?? null,
                'nat_id' => $request->nat_id ?? null,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'customer',
                'status' => 'active',
                'is_approved' => 'approved',
                'lat_long' => $request->lat_long,
                'device_id' => $request->device_id,
                'nat_id_photo' => $nat_id_photo,
                'avatar' => $avatar,
                'remember_token' => $token

            ]);

            Customer::create([
                'user_id' => $customer->id
            ]);

            Mail::to($request->email)->send(new VerifyAccountMail([
                'message' => 'Hi '.$customer->first_name. $customer->last_name.', This is your one time password',
                'otp' => $token,
                'is_url'=>false
            ]));
            DB::commit();
            return response()->json(['message' => 'Your account has been created successfully'], 200);
        }catch(QueryException $e){
            DB::rollBack();
            return response()->json(['DB error' => $e->getMessage()], 500);
        }catch(Exception $e){
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function verification(String $token, String $email): JsonResponse
    {
        try{
            DB::beginTransaction();
            $validator = Validator::make([
                'token' => $token,
                'email' => $email,
            ],[
                'token.required' => 'Token is required',
                'email.required' => 'Email is required',
            ]);

            if($validator->fails())throw new Exception($validator->errors()->first(),400);
            $user = User::where('remember_token', $token)->where('email', $email)->first();
            if (!$user) throw new Exception('Invalid Request');

            $user->email_verified_at = now();
            $user->save();

            DB::commit();

            return response()->json(['message' => 'Your account has been verified successfully'], 200);
        }catch(QueryException $e){
            return response()->json(['DB error' => $e->getMessage()], 500);
        }catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function signin(Request $request): JsonResponse
    {
        try{
            $validator = Validator::make($request->all(),[
                'email' => 'required|email',
                'password' => 'required',
            ],[
                'email.required' => 'Email is required',
                'email.email' => 'Invalid email format',
                'password.required' => 'Password is required',
            ]);      

            if($validator->fails())throw new Exception($validator->errors()->first(),400);

            $user = User::where('email', $request->email)->first();

            if (!$user) throw new Exception('User not found', 404);

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            $user->tokens()->delete();
            $token = $user->createToken('customer-token', ['customer'])->plainTextToken;

            return response()->json(['token' => $token], 200);

        }catch(QueryException $e){
            return response()->json(['DB error' => $e->getMessage()], 500);
        }catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $validator = validator(
                $request->all(),
                [
                    'email' => 'required|email|exists:users',
                ],
                [
                    'email.required' => 'Email Address required',
                    'email.email' => 'Invalid Email',
                    'email.exists' => 'Invalid Email Address',
                ]
            );

            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);

            $tokenExist = PasswordResetToken::where('email', $request->email)->exists();
            if ($tokenExist) PasswordResetToken::where('email', $request->email)->delete();

            //  otp 6 number
            $token = rand(100000, 999999);
            PasswordResetToken::insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => now()
            ]);

            $user = User::where('email', $request->email)->first();

            Mail::to($request->email)->send(new OTPMail([
                'message' => 'Hi '.$user->first_name. $user->last_name.', This is your one time password',
                'otp' => $token
            ]));
            return response()->json([
                'message' => 'Reset OTP sent successfully',
            ], 200);
        }catch (QueryException $e) {
            return response()->json(['DB error' => $e->getMessage()], 400);
        }catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()],400);
        }
    }

    public function resetPassword(Request $request): JsonResponse
    {
        try{
            $validator = validator(
                $request->all(),
                [
                    'token' => 'required|string',

                    'password' => 'required|string|min:8',
                    'confirm_password' => 'required|string|min:8|same:password',
                ],
                [
                    'token.required' => 'Token required',

                    'password.required' => 'Password required',
                    'password.string' => 'Password must be a string',
                    'password.min' => 'Password must be at least 8 characters',

                    'confirm_password.required' => 'Confirm Password required',
                    'confirm_password.string' => 'Confirm Password must be a string',
                    'confirm_password.min' => 'Confirm Password must be at least 8 characters',
                    'confirm_password.same' => 'Confirm Password must be same as Password',
                ]
            );

            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);
            
            $data  = PasswordResetToken::where('token', $request->token)->first();
            $user = User::where('email', $data->email)->first();
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            PasswordResetToken::where('token', $request->token)->delete();

            return response()->json([
                'message' => 'Password reset successfully',
            ], 200);
        }catch (QueryException $e) {
            return response()->json(['DB error' => $e->getMessage()], 400);
        }catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()],400);
        }
    }

    public function logout(): JsonResponse
    {
        try{
            Auth::user()->tokens()->delete();
            return response()->json(['message' => 'Logout successfully'], 200);
        }catch(QueryException $e){
            return response()->json(['DB error' => $e->getMessage()], 500);
        }catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function editProfile(Request $request): JsonResponse
    {
        try{
            $customer  = Auth::user();
            $validator = Validator::make($request->all(),[
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email',

            ],[
                'first_name.required' => 'First Name is required',
                'last_name.required' => 'Last Name is required',
                'email.required' => 'Email is required',
                'email.email' => 'Invalid email format',
            ]);

            if($validator->fails())throw new Exception($validator->errors()->first(),400);

            $old_avatar = $customer->avatar;
            if ($request->hasFile('avatar')) {
                $avatar = $request->file('avatar');
                $avatar_name = time() . '.' . $avatar->getClientOriginalExtension();
                $avatar->move(public_path('customer-avatar'), $avatar_name);
                $customer->update([
                    'avatar' => $avatar_name,
                ]);
                if ($old_avatar && file_exists(public_path('customer-avatar' . $old_avatar))) {
                    unlink(public_path('customer-avatar' . $old_avatar));
                }
            }

            $customer->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
            ]);

            return response()->json(['message' => 'Profile updated successfully'], 200);

        }catch(QueryException $e){
            return response()->json(['DB error' => $e->getMessage()], 500);
        }catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
