<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Mail\OTPMail;
use App\Mail\VerifyAccountMail;
use App\Models\PasswordResetToken;
use App\Models\Rider;
use App\Models\Vehicle;
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
use Password;

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

            $token = rand(1000, 9999);
            $rider = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'username' => $request->username,
                'phone' => $request->phone,
                'nationality' => $request->nationality ?? null,
                'nat_id' => $request->nat_id ?? null,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'rider',
                'status' => 'inactive',
                'is_approved' => 'pending',
                'lat_long' => $request->lat_long,
                'device_id' => $request->device_id,
                'nat_id_photo' => $nat_id_photo,
                'avatar' => $avatar,
                'remember_token' => $token
            ]);

            Rider::create([
                'user_id' => $rider->id
            ]);
            Mail::to($request->email)->send(new VerifyAccountMail([
                'message' => 'Hi '.$rider->first_name. $rider->last_name.', This is your one time password',
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

            $is_verify = User::where('email', $email)->first();
            if($is_verify->email_verified_at != null)throw new Exception('Email already verified');
            if($validator->fails())throw new Exception($validator->errors()->first(),400);
            $user = User::where('remember_token', $token)->where('email', $email)->first();
            if (!$user) throw new Exception('Invalid Request');

            $user->email_verified_at = now();
            $user->remember_token = null;
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
            if (!$user) throw new Exception('Account not found', 404);
            if (!$user->email_verified_at) throw new Exception('Email not verified', 404);

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            $rider_info = Rider::where('user_id', $user->id)->first();
            $vehicle = Vehicle::where('vehicle_of', $user->id)->first();


            $user->tokens()->delete();
            $token = $user->createToken('rider-token', ['rider'])->plainTextToken;


            if(empty($rider_info) && empty($vehicle)) return response()->json(['message' => 'Lets complete your profile', 'token' => $token, 'user' => $user,], 200);

            // required list
            $pp = true;
            $dr_fnb = true;
            $vehicle_insurance = true;
            $rc = true;
            $background_verification = true;
            if(empty($user->avatar)) $pp = false;
            if(empty($rider_info->license_photo)) $dr_fnb = false;
            if(empty($vehicle->vehicle_insurance)) $vehicle_insurance = false;
            if(empty($vehicle->registration_certificate)) $rc = false;
            if(empty($rider_info->background_verification)) $background_verification = false;
            $list = [
                'profile Photo' => $pp,
                'Driving License' => $dr_fnb,
                'Vehicle Insurance' => $vehicle_insurance,
                'Registration Certificate' => $rc,
                'Background Verification' => $background_verification
            ];
            if(!$pp || !$dr_fnb || !$vehicle_insurance || !$rc || !$background_verification) return response()->json(['message' => 'Please complete your profile','token' => $token, 'user' => $user, 'list' => $list], 200);

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
            $rider = Auth::user();

            DB::beginTransaction();

            $validator = Validator::make($request->all(),[
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

            if($validator->fails())throw new Exception($validator->errors()->first(),400);

            if (!$rider) throw new Exception('Account not found');

            $license_photo = null;

            if ($request->hasFile('license_photo')) {
                $image = $request->file('license_photo');
                $image_name = 'r-license' . $request->license_number . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('rider-license'), $image_name);
                $license_photo = 'rider-license/' . $image_name;
            }

            $rider->update([
                'user_id' => $rider->id,
                'license_number' => $request->license_number,
                'license_expiry' => $request->license_expiry,
                'license_photo' => $license_photo,
                'total_rides' => 0,
                'driving_experience' => $request->driving_experience,
                'current_rating' => 0
            ]);

            DB::commit();
            return response()->json(['message' => 'Your account is setup successfully'], 200);

        }catch(QueryException $e){
            DB::rollBack();
            return response()->json(['DB error' => $e->getMessage()], 500);
        }catch(Exception $e){
            DB::rollBack();
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
            $token = rand(1000, 9999);
            PasswordResetToken::insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => now()
            ]);

            $user = User::where('email', $request->email)->first();

            Mail::to($request->email)->send(new OTPMail([
                'message' => 'Hi '.$user->first_name. $user->last_name. 'This is your one time password',
                'otp' => $token,
                'is_url'=>false
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

                    'password' => 'nullable|string|min:8',
                    'confirm_password' => 'nullable|string|min:8|same:password',
                ],
                [
                    'token.required' => 'Token required',

                    'password.string' => 'Password must be a string',
                    'password.min' => 'Password must be at least 8 characters',

                    'confirm_password.string' => 'Confirm Password must be a string',
                    'confirm_password.min' => 'Confirm Password must be at least 8 characters',
                    'confirm_password.same' => 'Confirm Password must be same as Password',
                ]
            );

            if ($validator->fails()) throw new Exception($validator->errors()->first(), 400);
            
            $data  = PasswordResetToken::where('token', $request->token)->first();
            if(empty($data)) throw new Exception('Invalid token', 400);

            // Phase 1: OTP Verified successfully
            if (empty($request->password) && empty($request->confirm_password)) {
                // If no password is provided, just return a success message for OTP verification
                return response()->json([
                    'message' => 'OTP verified successfully',
                ], 200);
            }
            
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

    public function profile(): JsonResponse
    {
        try{
            $user = Auth::user();
            return response()->json(['user' => $user], 200);
        }catch(QueryException $e){
            return response()->json(['DB error' => $e->getMessage()], 500);
        }catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function editProfile(Request $request): JsonResponse
    {
        try{
            $rider = Auth::user();
            DB::beginTransaction();
            $validator = Validator::make(request()->all(),[
                'first_name' => 'required',
                'last_name' => 'required',
                'phone' => 'required',
                'avatar' => 'nullable',
            ],[
                'first_name.required' => 'First name is required',
                'last_name.required' => 'Last name is required',
                'phone.required' => 'Phone number is required',
            ]);

            if($validator->fails())throw new Exception($validator->errors()->first(),400);

            $rider->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'avatar' => $request->avatar,
            ]);
            DB::commit();
            return response()->json(['user' => $rider], 200);
        }catch(QueryException $e){
            DB::rollBack();
            return response()->json(['DB error' => $e->getMessage()], 500);
        }catch(Exception $e){
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function resendCode(Request $request): JsonResponse
    {
        try{
            $validator = Validator::make($request->all(),[
                'email' => 'required|email',
                'type' => 'required|in:forget-password,email-verify',
            ],[
                'email.required' => 'Email is required',
                'email.email' => 'Invalid email format',

                'type.required' => 'Type is required',
                'type.in' => 'Invalid type',
            ]);

            if($validator->fails())throw new Exception($validator->errors()->first(),400);

            $rider = User::where('email', $request->email)->first();
            if (!$rider) throw new Exception('User not found', 404);
            $token = rand(1000, 9999);
            if($request->type == 'forget-password'){
                PasswordResetToken::where('email', $request->email)->delete();
                PasswordResetToken::insert([
                    'email' => $request->email,
                    'token' => $token,
                    'created_at' => now()
                ]);
                Mail::to($request->email)->send(new OTPMail([
                    'message' => 'Hi '.$rider->first_name. $rider->last_name.', This is your one time password',
                    'otp' => $token
                ]));
                
            }else if($request->type == 'email-verify'){
                if($rider->email_verified_at != null)throw new Exception('Email already verified');
                $rider->update([
                    'remember_token' => $token
                ]);
                Mail::to($request->email)->send(new VerifyAccountMail([
                    'message' => 'Hi '.$rider->first_name. $rider->last_name.', This is your one time password',
                    'otp' => $token,
                    'is_url'=>false
                ]));
            }
            
            return response()->json(['token' => $token], 200);
        }catch(QueryException $e){
            return response()->json(['DB error' => $e->getMessage()], 500);
        }catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function changePassword(Request $request): JsonResponse
    {
        try{
            $user = Auth::user();
            DB::beginTransaction();
            $validator = Validator::make($request->all(),[
                'current_password' => 'required',
                'new_password' => 'required',
                'confirm_password' => 'required|same:new_password',
            ],[
                'current_password.required' => 'Current password is required',
                'new_password.required' => 'New password is required',
                'confirm_password.required' => 'Confirm password is required',
                'confirm_password.same' => 'Confirm password must be same as new password',
            ]);

            if($validator->fails())throw new Exception($validator->errors()->first(),400);

            
            if (!$user || !Hash::check($request->current_password, $user->password)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);
            DB::commit();
            return response()->json(['message' => 'Password changed successfully'], 200);
        }catch(QueryException $e){
            DB::rollBack();
            return response()->json(['DB error' => $e->getMessage()], 500);
        }catch(Exception $e){
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
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

    // verifications apis

    public function profilePicture(Request $request): JsonResponse
    {
        try{
            $user = Auth::user();
            $validator = Validator::make(request()->all(),[
                'avatar' => 'required',
            ],[
                'avatar.required' => 'Avatar is required',
            ]);

            if($validator->fails())throw new Exception($validator->errors()->first(),400);

            // setp to move

            $avatar = null;
            // move photos to storage
            if ($request->hasFile('avatar')) {
                $image = $request->file('avatar');
                $image_name = 'r-avatar' . time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('rider-avatar'), $image_name);
                $avatar = 'rider-avatar/' . $image_name;
            }

            $user->update([
                'avatar' => $avatar
            ]);
            return response()->json(['user' => $user], 200);
        }catch(QueryException $e){
            return response()->json(['DB error' => $e->getMessage()], 500);
        }catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function drivingLicense(Request $request): JsonResponse
    {
        try{
            $user = Auth::user();
            $validator = Validator::make(request()->all(),[
                'front_side' => 'required',
                'back_side' => 'required',
            ],[
                'front_side.required' => 'Front side is required',
                'back_side.required' => 'Back side is required',
            ]);

            if($validator->fails())throw new Exception($validator->errors()->first(),400);

            // setp to move

            $front_side = null;
            $back_side = null;
            // move photos to storage
            if ($request->hasFile('front_side')) {
                $image = $request->file('front_side');
                $image_name = 'front-r-license' . time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('rider-license'), $image_name);
                $front_side = 'rider-license/' . $image_name;
            }

            if ($request->hasFile('back_side')) {
                $image = $request->file('back_side');
                $image_name = 'back-r-license' . time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('rider-license'), $image_name);
                $back_side = 'rider-license/' . $image_name;
            }
            $rider = Rider::find($user->id);
            $rider->update([
                'license_photo' => [$front_side, $back_side]
            ]);
            return response()->json(['user' => $user], 200);
        }catch(QueryException $e){
            return response()->json(['DB error' => $e->getMessage()], 500);
        }catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function vehicleInsurance(Request $request): JsonResponse
    {
        try{
            $user = Auth::user();
            $validator = Validator::make(request()->all(),[
                'vehicle_insurance' => 'required',
            ],[
                'vehicle_insurance.required' => 'Vehicle insurance is required',
            ]);

            if($validator->fails())throw new Exception($validator->errors()->first(),400);

            // setp to move

            $vehicle_insurance = null;
            // move photos to storage
            if ($request->hasFile('vehicle_insurance')) {
                $image = $request->file('vehicle_insurance');
                $image_name = 'vehicle-insurance-'. $user->id . '-' . time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('vehicle-insurance'), $image_name);
                $vehicle_insurance = 'vehicle-insurance/' . $image_name;
            }
            $vehicle = Vehicle::find($user->id);
            $vehicle->update([
                'vehicle_insurance' => $vehicle_insurance
            ]);
            return response()->json(['user' => $user], 200);
        }catch(QueryException $e){
            return response()->json(['DB error' => $e->getMessage()], 500);
        }catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function registrationCertificate(Request $request): JsonResponse
    {
        try{
            $user = Auth::user();
            $validator = Validator::make(request()->all(),[
                'registration_certificate' => 'required',
            ],[
                'registration_certificate.required' => 'Registration certificate is required',
            ]);

            if($validator->fails())throw new Exception($validator->errors()->first(),400);

            // setp to move

            $registration_certificate = null;
            // move photos to storage
            if ($request->hasFile('registration_certificate')) {
                $image = $request->file('registration_certificate');
                $image_name = 'registration-certificate-'. $user->id . '-' . time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('registration-certificate'), $image_name);
                $registration_certificate = 'registration-certificate/' . $image_name;
            }
            $vehicle = Vehicle::find($user->id);
            $vehicle->update([
                'registration_certificate' => $registration_certificate
            ]);
            return response()->json(['user' => $user], 200);
        }catch(QueryException $e){
            return response()->json(['DB error' => $e->getMessage()], 500);
        }catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function backgroundCheck(Request $request): JsonResponse
    {
        try{
            $user = Auth::user();
            $validator = Validator::make(request()->all(),[
                'background_check' => 'required|in:true,false',
            ],[
                'background_check.required' => 'background check is required',
                'background_check.in' => 'background check should be true or false',
            ]);

            if($validator->fails())throw new Exception($validator->errors()->first(),400);

            $rider = Rider::find($user->id);
            $rider->update([
                'background_check' => $request->background_check
            ])
            ;
            return response()->json(['user' => $user], 200);
        }catch(QueryException $e){
            return response()->json(['DB error' => $e->getMessage()], 500);
        }catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
