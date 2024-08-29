<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use App\Models\Admin;
use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Str;

class AuthenticationController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        try {
            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $userType = null;

                if ($user->isAdmin()) {
                    $userType = 'admin';
                } else if ($user->isCustomer()) {
                    $userType = 'customer';
                } else if ($user->isTechnician()) {
                    $userType = 'technician';
                }

                if ($userType === 'admin') {
                    $token = $user->createToken('authToken', [$userType])->plainTextToken;
                    return response()->json([
                        'token' => $token,
                        'user_type' => $userType,
                        'user' => $user
                    ]);
                } else if ($userType === 'customer') {
                    $token = $user->createToken('authToken', [$userType])->plainTextToken;
                    return response()->json([
                        'token' => $token,
                        'user_type' => $userType,
                        'customer_id' => $user->customer ? $user->customer->id : null,
                        'user' => $user
                    ]);
                } else if ($userType === 'technician') {
                    $token = $user->createToken('authToken', [$userType])->plainTextToken;
                    return response()->json([
                        'token' => $token,
                        'user_type' => $userType,
                        'technician_id' => $user->technician ? $user->technician->id : null,
                        'user' => $user
                    ]);
                } else {
                    return response()->json([
                        'message' => "Akun tidak terdaftar..",
                    ], 401);
                }
            }
            throw new AuthenticationException('Invalid Account');
        } catch (AuthenticationException $e) {
            return response()->json(['error' => 'Authentication failed', 'message' => $e->getMessage()], 401);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Login failed', 'message' => $e->getMessage()], 400);
        }
    }
    public function registerCustomer(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string',
                'phone' => 'required|regex:/^08[0-9]{8,11}$/',
                'address' => 'required|string',
            ]);

            $user = User::create([
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            Customer::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);

            return response()->json(['message' => 'Customer registered successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Registration failed', 'message' => $e->getMessage()], 400);
        }
    }
    public function registerTechnician(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string',
                'phone' => 'required|regex:/^08[0-9]{8,11}$/',
                'address' => 'required|string',
            ]);

            $user = User::create([
                'email' => $validatedData['email'],
                'password' => bcrypt($validatedData['password']),
            ]);

            $validatedData['user_id'] = $user->id;
            $validatedData['active'] = true;

            Technician::create($validatedData);

            return response()->json(['message' => 'Customer registered successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Registration failed', 'message' => $e->getMessage()], 400);
        }
    }
    public function registerAdmin(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string',
                'phone' => 'required|regex:/^08[0-9]{8,11}$/',
                'address' => 'required|string',
            ]);

            $user = User::create([
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            Admin::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);

            return response()->json(['message' => 'Customer registered successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Registration failed', 'message' => $e->getMessage()], 400);
        }
    }
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Failed to revoke token...'], 500);
        }
        return response()->json(['message' => 'Successfully logged out']);
    }
}
