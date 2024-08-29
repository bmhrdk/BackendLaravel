<?php

namespace App\Http\Controllers;

use App\Models\Technician;
use App\Models\Service;
use Illuminate\Http\Request;


class TechnicianController extends Controller
{
    public function getTechnicianName(Request $request)
    {
        $technician = Technician::with('user')->get();

        if ($technician->count() > 0) {
            $formattedResponse = $technician->map(function ($data) {
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
    public function filterTechnician(Request $request, $name)
    {

        $technicianSearch = Technician::where('name', 'like', "%{$name}%")->with('user')->get();

        if ($technicianSearch->count() > 0) {
            $formattedResponse = $technicianSearch->map(function ($data) {
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

    public function updateTechnician(Request $request, $id)
    {
        try {
            $technician = Technician::find($id);
            if (!$technician) {
                return response()->json(['error' => 'Technician not found'], 404);
            }

            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email,' . $technician->user_id, // pengecualian untuk email yang sudah ada, kecuali milik technician yang sedang diupdate
                'password' => 'nullable|string',
                'phone' => 'required|regex:/^08[0-9]{8,11}$/', // Min 10, Max 13
                'address' => 'required|string',
                'active' => 'required|boolean',
            ]);

            if ($request->filled('password')) {
                $technician->user->update([
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                ]);
            }

            $technician->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'active' => $request->active,
            ]);

            return response()->json(['message' => 'Technician Updated'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed', 'message' => $e->getMessage()], 400);
        }
    }


    // public function deleteTechnician($id)
    // {
    //     $technician = Technician::find($id);

    //     if (!$technician) {
    //         return response()->json(['error' => 'Data tidak ditemukan'], 404);
    //     }

    //     $user = $technician->user;

    //     $technician->delete();
    //     $user->delete();

    //     return response()->json(['message' => 'Data berhasil dihapus..'], 200);
    // }

    public function searchTechnician(Request $request, $name, )
    {
        $technician = Technician::where('name', 'like', "%{$name}%")->get();

        if ($technician->count() > 0) {
            $formattedResponse = $technician->map(function ($data) {

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

    public function getOneTechnician(Request $request, $id)
    {

        $technician = Technician::find($id);
        if (!$technician) {
            return response()->json(['error' => 'Data customer not found'], 404);
        }
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Tidak memiliki hak akses..'
            ], 403);
        }
        $activeStatus = ($technician->active === 1) ? true : false;
        $formattedResponse = [
            'id' => $technician->id,
            'user_id' => $technician->user_id,
            'name' => $technician->name,
            'email' => $technician->user->email,
            'address' => $technician->address,
            'phone' => $technician->phone,
            'active' => $activeStatus
        ];
        return response()->json($formattedResponse, 200);

    }

    public function getActive()
    {

        $technician = Technician::with('user')->where('active', 1)->get();
        if ($technician->count() > 0) {
            $formattedResponse = $technician->map(function ($data) {
                // $activeStatus = ($data->active === 1) ? true : false;
                return [
                    'id' => $data->id,
                    'user_id' => $data->user_id,
                    'name' => $data->name,
                    'email' => $data->user->email,
                    'address' => $data->address,
                    'phone' => $data->phone,
                    'active' => true,
                ];
            });
            return response()->json(['data' => $formattedResponse], 200);
        } else {
            return response()->json(['error' => 'Data tidak ditemukan...'], 404);
        }

    }

    public function getHistory(Request $request, $id)
    {


        // $user = $request->user();
        $query = Service::query();
        $query->orderBy('created_at', 'desc');

        $query->where('technician_created_id', $id)
              ->where('status', 'Selesai');
        $services = $query->get();


        if ($services->count() > 0) {
            $formattedResponse = $services->map(function ($data) {
                $detailService = $data->detailservice;
                return [
                    'id' => $data->id,
                    'adminId' => $data->admin_id,
                    'customerId' => $data->customer_id,
                    'nama_customer' => $data->customer ? $data->customer->name : null,
                    'merek' => $data->merek,
                    'tipe' => $data->tipe,
                    'status' => $data->status,
                    'diagnosa_awal' => $data->diagnosa_awal,
                    'tanggalMasuk' => $data->created_at,
                    'tanggalKeluar' => $data->status === 'Selesai' ? $data->updated_at : null,
                    'detail_service' => $detailService ? [
                        'kerusakan' => $detailService->kerusakan,
                        'estimasi' => $detailService->estimasi,
                        'biayaKerusakan' => $detailService->biaya_kerusakan,
                    ] : null,
                ];
            });
            return response()->json(['data' => $formattedResponse], 200);
        } else {
            return response()->json(['error' => 'Data tidak ditemukan...'], 404);
        }
    }

}
