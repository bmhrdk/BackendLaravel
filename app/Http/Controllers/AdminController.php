<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{

    public function getAdminName(Request $request)
    {
        $admin = Admin::with('user')->get();

        if ($admin->count() > 0) {
            $formattedResponse = $admin->map(function ($data) {
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
   
    public function updateAdmin(Request $request, $id)
    {
        try {
            $admin = Admin::find($id);
            if (!$admin) {
                return response()->json(['error' => 'Admin not found'], 404);
            }

            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email,' . $admin->user_id, // pengecualian untuk email yang sudah ada, kecuali milik admin yang sedang diupdate
                'password' => 'nullable|string',
                'phone' => 'required|regex:/^08[0-9]{8,11}$/', // Min 10, Max 13
                'address' => 'required|string',
            ]);

            if ($request->filled('password')) {
                $admin->user->update([
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                ]);
            }

            $admin->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);

            return response()->json(['message' => 'Admin Updated'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed', 'message' => $e->getMessage()], 400);
        }
    }


    public function deleteAdmin($id)
    {
        $admin = Admin::find($id);

        if (!$admin) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $user = $admin->user;

        $admin->delete();
        $user->delete();

        return response()->json(['message' => 'Data berhasil dihapus..'], 200);
    }

    public function getOneAdmin(Request $request, $id)
    {

        $admin = Admin::find($id);
        if (!$admin) {
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
                'id' => $admin->id,
                'user_id' => $admin->user_id,
                'name' => $admin->name,
                'email' => $admin->user->email,
                'address' => $admin->address,
                'phone' => $admin->phone,
            ];
        return response()->json($formattedResponse, 200);

    }
    public function dashboardStats()
    {
        // $data = [
        //     'transaction' => Transaction::count(),
        //     'spareparts' => Sparepart::count(),
        //     'machines' => Machine::count(),
        //     'customers' => Customer::count(),
        // ];

        // return response()->json($data, 200);
    }
    public function chartMonth()
{
    // Filter transaksi dengan status 'SELESAI'
    $services = Service::where('status', 'Selesai')
        ->select(DB::raw('MONTHNAME(updated_at) as month'), DB::raw('count(*) as count'))
        ->groupBy(DB::raw('MONTHNAME(updated_at)'))
        ->orderBy(DB::raw('MONTH(updated_at)'))
        ->get();

    // Menyiapkan respons terformat
    $formattedResponse = [];
    foreach ($services as $service) {
        $formattedResponse[$service->month] = $service->count;
    }

    $labels = array_keys($formattedResponse);
    $data = array_values($formattedResponse);

    $response = [
        'labels' => $labels,
        'data' => $data,
    ];

    return response()->json($response, 200);
}

    
}