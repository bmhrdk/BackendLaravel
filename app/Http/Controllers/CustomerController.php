<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;


class CustomerController extends Controller
{
    public function getCustomerName(Request $request)
    {
        $customer = Customer::with('user')->get();

        if ($customer->count() > 0) {
            $formattedResponse = $customer->map(function ($data) {
                return [
                    'id' => $data->id,
                    'user_id' => $data->user_id,
                    'name' => $data->name,
                    'email' => $data->user->email,
                    'address' => $data->address,
                    'phone' => $data->phone,
                ];
            });
            return response()->json(['data' => $formattedResponse], 200);
        } else {
            return response()->json(['error' => 'Data tidak ditemukan...'], 404);
        }

    }
    public function filterCustomer(Request $request, $name)
    {

        $customerSearch = Customer::where('name', 'like', "%{$name}%")->with('user')->get();

        if ($customerSearch->count() > 0) {
            $formattedResponse = $customerSearch->map(function ($data) {
                return [
                    'id' => $data->id,
                    'user_id' => $data->user_id,
                    'name' => $data->name,
                    'email' => $data->user->email,
                    'address' => $data->address,
                    'phone' => $data->phone,
                ];
            });
            return response()->json(['data' => $formattedResponse], 200);
        } else {
            return response()->json(['error' => 'Data tidak ditemukan...'], 404);
        }
    }

    public function updateCustomer(Request $request, $id)
    {
        try {
            $customer = Customer::find($id);
            if (!$customer) {
                return response()->json(['error' => 'Data customer not found'], 404);
            }

            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email,' . $customer->user_id, // pengecualian untuk email yang sudah ada, kecuali milik customer yang sedang diupdate
                'password' => 'nullable|string',
                'phone' => 'required|regex:/^08[0-9]{8,11}$/', // Min 10, Max 13
                'address' => 'required|string',
            ]);

            if ($request->filled('password')) {
                $customer->user->update([
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                ]);
            }

            $customer->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);

            return response()->json(['message' => 'Customer Updated'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed', 'message' => $e->getMessage()], 400);
        }
    }


    public function deleteCustomer($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $user = $customer->user;

        $customer->delete();
        $user->delete();

        return response()->json(['message' => 'Data berhasil dihapus..'], 200);
    }

    public function searchCustomer(Request $request, $name, )
    {
        $customer = Customer::where('name', 'like', "%{$name}%")->get();

        if ($customer->count() > 0) {
            $formattedResponse = $customer->map(function ($data) {

                return [
                    'id' => $data->id,
                    'user_Id' => $data->user_id,
                    'name' => $data->name,
                    'email' => $data->user->email,
                    'address' => $data->address,
                    'phone' => $data->phone,
                ];
            });
            return response()->json(['data' => $formattedResponse], 200);
        } else {
            return response()->json(['error' => 'Data tidak ditemukan...'], 404);
        }
    }

    public function getOneCustomer(Request $request, $id)
    {

        $customer = Customer::find($id);
        if (!$customer) {
            return response()->json(['error' => 'Data customer not found'], 404);
        }
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Tidak memiliki hak akses..'
            ], 403);
        }
        $formattedResponse = [
                'id' => $customer->id,
                'user_id' => $customer->user_id,
                'name' => $customer->name,
                'email' => $customer->user->email,
                'address' => $customer->address,
                'phone' => $customer->phone,
            ];
        return response()->json($formattedResponse, 200);

    }

}
