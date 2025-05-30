<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Exception;
use Hash;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
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

            $admin = Admin::where('email', $request->email)->first();

            if (!$admin || !Hash::check($request->password, $admin->password)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }
            $admin->tokens()->delete();
            $token = $admin->createToken('admin-token', ['admin'])->plainTextToken;

            return response()->json(['token' => $token], 200);

        }catch(QueryException $e){
            return response()->json(['DB error' => $e->getMessage()], 403);
        }catch(Exception $e){
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

}
