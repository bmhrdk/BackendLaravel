<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;

class InventoryController extends Controller
{
    public function newInventory(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Tidak memiliki hak akses..'
                ], 403);
            }
            // Admin Request
            if ($user->tokenCan('admin')) {
                $request->validate([
                    'adminId' => 'required|numeric',
                    'merek' => 'required|string',
                    'tipe' => 'required|string',
                    'stok' => 'required|numeric',
                    'harga'  => 'required|numeric',
                ]);


                $inventory = Inventory::create([
                    'admin_id' => $request->adminId,
                    'merek' => $request->merek,
                    'tipe' => $request->tipe,
                    'stok' => $request->stok,
                    'harga'=> $request->harga,
                ]);

                return response()->json(['message' => 'New Inventory Added'], 201);


            }

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed', 'message' => $e->getMessage()], 400);
        }
    }
    public function updateInventory(Request $request, $id)
    {
        try {
            $inventory = Inventory::find($id);
            if (!$inventory) {
                return response()->json(['error' => 'Data sparepart not found'], 404);
            }
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Tidak memiliki hak akses..'
                ], 403);
            }
            // Admin Request
            if ($user->tokenCan('admin')) {
                $request->validate([
                    'adminId' => 'required|numeric',
                    'merek' => 'required|string',
                    'tipe' => 'required|string',
                    'stok' => 'required|numeric',
                    'harga'=> 'required|numeric',
                ]);


                $inventory->update([
                    'admin_id' => $request->adminId,
                    'merek' => $request->merek,
                    'tipe' => $request->tipe,
                    'stok' => $request->stok,
                    'harga' => $request->harga,
                ]);


                return response()->json(['message' => 'Inventory Updated'], 201);


            }

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed', 'message' => $e->getMessage()], 400);
        }
    }

    public function getOneInventory(Request $request, $id)
    {

        $inventory = Inventory::find($id);
        if (!$inventory) {
            return response()->json(['error' => 'Data sparepart not found'], 404);
        }
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Tidak memiliki hak akses..'
            ], 403);
        }
        $formattedResponse = [
            'id' => $inventory->id,
            'adminId' => $inventory->admin_id,
            'merek' => $inventory->merek,
            'tipe' => $inventory->tipe,
            'stok' => $inventory->stok,
            'harga' => $inventory->harga,
        ];
        return response()->json($formattedResponse, 200);

    }
    public function getAllInventory(Request $request)
    {
        $inventory = Inventory::all();

        if ($inventory->count() > 0) {
            $formattedResponse = $inventory->map(function ($data) {
                return [
                    'id' => $data->id,
                    'adminId' => $data->admin_id,
                    'merek' => $data->merek,
                    'tipe' => $data->tipe,
                    'stok' => $data->stok,
                    'harga' => $data->harga,
                ];
            });
            return response()->json(['data' => $formattedResponse], 200);
        } else {
            return response()->json(['error' => 'Data tidak ditemukan...'], 404);
        }

    }
    public function searchInventory(Request $request, $name)
    {
        $inventory = Inventory::where('merek', 'like', "%{$name}%")->get();

        if ($inventory->count() > 0) {
            $formattedResponse = $inventory->map(function ($data) {
                return [
                    'id' => $data->id,
                    'adminId' => $data->admin_id,
                    'merek' => $data->merek,
                    'tipe' => $data->tipe,
                    'stok' => $data->stok,
                    'harga'=> $data->harga,
                ];
            });
            return response()->json(['data' => $formattedResponse], 200);
        } else {
            return response()->json(['error' => 'Data tidak ditemukan...'], 404);
        }

    }
    public function deleteOneInventory(Request $request, $id)
    {
        $data = Inventory::find($id);

        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Tidak memiliki hak akses..'
            ], 403);

        } else if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }
        $data->delete();

        return response()->json(['message' => 'Data berhasil dihapus..'], 200);
    }
    public function getAllAvailable(Request $request)
    {
        $inventory = Inventory::where('stok', '>', 0)->get();

        if ($inventory->count() > 0) {
            $formattedResponse = $inventory->map(function ($data) {
                return [
                    'id' => $data->id,
                    'adminId' => $data->admin_id,
                    'merek' => $data->merek,
                    'tipe' => $data->tipe,
                    'stok' => $data->stok,
                    'harga' => $data->harga,
                ];
            });
            return response()->json(['data' => $formattedResponse], 200);
        } else {
            return response()->json(['error' => 'Data tidak ditemukan...'], 404);
        }

    }

}
