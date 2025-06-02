<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Exception;
use Hash;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(){
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.signin');
    }
    public function signin(Request $request)
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

            if ($validator->fails()) {
                Session::flash('error', [
                    'text' => $validator->errors()->first(),
                ]);
                return redirect()->back();
            }
            
            // Conditions
            if (!Admin::where('email', $request->email)->exists()) {
                Session::flash('error', [
                    'text' => "This email address don't have an account",
                ]);
                return redirect()->back();
            }


            $admin = Admin::where('email', $request->email)->first();
            if (!Hash::check($request->password, $admin->password)) {
                Session::flash('error', [
                    'text' => 'Invalid email address or password',
                ]);
                return redirect()->back();
            }

            Auth::guard('admin')->login($admin);
            $request->session()->regenerate();
            // dd($admin);
            // logActivity('admin logged in');
            Session::flash('success', [
                'text' => 'Welcome! ' . $admin->name,
            ]);
            return redirect()->route('admin.dashboard');
    

        }catch(QueryException $e){
            Session::flash('error', [
                    'text' => $e->getMessage(),
                ]);
                return redirect()->back();
        }catch(Exception $e){
            Session::flash('error', [
                    'text' => $e->getMessage(),
                ]);
                return redirect()->back();
        }
    }

    public function logout(Request $request)
    {
        try{
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            Session::flash('success', [
                'text' => 'Successfully logged out',
            ]);
            return redirect()->route('admin.login');
        }catch(Exception $e){
            Session::flash('error', [
                'text' => "something went wrong. Please try again",
            ]);
            return redirect()->back();
        }
    }
}
