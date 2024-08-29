<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\View;
use App\Models\Service;
use App\Models\InventoryDetail;
use Illuminate\Http\Request;
use App\Models\DetailService;

class ServiceController extends Controller
{
    public function newService(Request $request)
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
                    'technicianId' => 'required|numeric',
                    'customerId' => 'required|numeric',
                    'merek' => 'required|string',
                    'tipe' => 'required|string',
                    'diagnosaAwal' => 'required|string',
                    // 'kerusakan' => 'required|array',
                    // 'kerusakan.*' => 'required|string',
                    // 'estimasi' => 'required|string',
                    // 'biaya' => 'required|string',
                    'inventory' => 'nullable|array',
                    'inventory.*.inventoryId' => 'numeric',
                    'inventory.*.jumlah_sparepart' => 'numeric',
                    'inventory.*.harga_satuan' => 'numeric',
                ]);


                $service = Service::create([
                    'admin_created_id' => $request->adminId,
                    'technician_created_id' => $request->technicianId,
                    'customer_id' => $request->customerId,
                    'merek' => $request->merek,
                    'tipe' => $request->tipe,
                    'diagnosa_awal' => $request->diagnosaAwal,
                    'status' => 'Diproses',
                ]);

                // $service->detailservice()->create([
                //     // 'kerusakan' => json_encode($request->kerusakan),
                //     // 'biaya' => $request->biaya,
                //     'estimasi' => $request->estimasi,
                // ]);

                if ($request->has('inventory')) {
                    foreach ($request->inventory as $inventory) {
                        InventoryDetail::create([
                            'service_id' => $service->id,
                            'inventory_id' => $inventory['inventoryId'],
                            'jumlah_sparepart' => $inventory['jumlah_sparepart'],
                            'harga_satuan' => $inventory['harga_satuan'],
                        ]);
                    }
                }


                return response()->json(['message' => 'New Service Created'], 201);


            } else if ($user->tokenCan('technician')) {
                $request->validate([
                    'technicianId' => 'required|numeric',
                    'customerId' => 'required|numeric',
                    'merek' => 'required|string',
                    'tipe' => 'required|string',
                    'diagnosaAwal' => 'required|string',
                    // 'kerusakan' => 'required|array',
                    // 'kerusakan.*' => 'required|string',
                    // 'estimasi' => 'required|string',
                    // 'biaya' => 'required|string',
                    'inventory' => 'nullable|array',
                    'inventory.*.inventoryId' => 'numeric',
                    'inventory.*.jumlah_sparepart' => 'numeric',
                    'inventory.*.harga_satuan' => 'numeric',
                ]);


                $service = Service::create([
                    'technician_created_id' => $request->technicianId,
                    'customer_id' => $request->customerId,
                    'merek' => $request->merek,
                    'tipe' => $request->tipe,
                    'diagnosa_awal' => $request->diagnosaAwal,
                    'status' => 'Diproses',
                ]);

                // $service->detailservice()->create([
                //     // 'kerusakan' => json_encode($request->kerusakan),
                //     // 'biaya' => $request->biaya,
                //     'estimasi' => $request->estimasi,
                // ]);

                if ($request->has('inventory')) {
                    foreach ($request->inventory as $inventory) {
                        InventoryDetail::create([
                            'service_id' => $service->id,
                            'inventory_id' => $inventory['inventoryId'],
                            'jumlah_sparepart' => $inventory['jumlah_sparepart'],
                            'harga_satuan' => $inventory['harga_satuan'],
                        ]);
                    }
                }


                return response()->json(['message' => 'New Service Created'], 201);


            }

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed', 'message' => $e->getMessage()], 400);
        }
    }
    public function updateService(Request $request, $id)
    {
        try {
            $service = Service::find($id);
            if (!$service) {
                return response()->json(['error' => 'Data service not found'], 404);
            }

            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Tidak memiliki hak akses..'
                ], 403);
            }

            // Admin Request
            if ($user->tokenCan('admin') || $user->tokenCan('technician')) {
                $request->validate([
                    // 'adminId' => 'required|numeric',
                    'processedId' => 'nullable|numeric',
                    'finishedId' => 'nullable|numeric',
                    'customerId' => 'required|numeric',
                    'merek' => 'required|string',
                    'tipe' => 'required|string',
                    'kerusakan' => 'nullable|array',
                    'kerusakan.*' => 'nullable|string',
                    'estimasi' => 'nullable|string',
                    'status' => 'required|string',
                    'biayaKerusakan' => 'nullable|array',
                    'biayaKerusakan.*' => 'nullable|string',
                    'inventory' => 'nullable|array',
                    'inventory.*.inventoryId' => 'numeric',
                    'inventory.*.jumlah_sparepart' => 'numeric',

                ]);

                $statusSelesai = $request->status === 'Selesai' && $service->status !== 'Selesai';
                $statusMenungguPembayaran = $request->status === 'Menunggu Pembayaran';
                $stokSudahDikurangi = $service->status === 'Menunggu Pembayaran' && ($statusSelesai || $statusMenungguPembayaran);

                // Update service data
                $updateData = [
                    // 'admin_id' => $request->adminId,
                    'customer_id' => $request->customerId,
                    'merek' => $request->merek,
                    'tipe' => $request->tipe,
                    'status' => $request->status,
                ];

                // Jika processedId ada, tambahkan ke dalam updateData
                if ($request->has('processedId')) {
                    $updateData['admin_processed_id'] = $request->processedId;
                }

                // Jika finishedId ada, tambahkan ke dalam updateData
                if ($request->has('finishedId')) {
                    $updateData['admin_finished_id'] = $request->finishedId;
                }

                $service->update($updateData);

                // Check if detail service exists, then update or create
                $detailService = $service->detailservice()->first();
                if ($detailService) {
                    $detailService->update([
                        'kerusakan' => json_encode($request->kerusakan),
                        'biaya_kerusakan' => json_encode($request->biayaKerusakan),
                        'estimasi' => $request->estimasi,
                    ]);
                } else {
                    $service->detailservice()->create([
                        'kerusakan' => json_encode($request->kerusakan),
                        'biaya_kerusakan' => json_encode($request->biayaKerusakan),
                        'estimasi' => $request->estimasi,
                    ]);
                }

                // Update inventory details
                if ($request->has('inventory')) {
                    // Hapus inventory lama yang terkait dengan service ini
                    InventoryDetail::where('service_id', $service->id)->delete();

                    // Tambahkan inventory baru
                    foreach ($request->inventory as $inventory) {
                        // Ambil harga_satuan dan stok dari tabel inventory berdasarkan inventory_id
                        $inventoryItem = \DB::table('inventories')
                            ->where('id', $inventory['inventoryId'])
                            ->first();

                        if ($inventoryItem) {
                            $harga_satuan = $inventoryItem->harga;
                            $stok = $inventoryItem->stok;


                            // Periksa apakah stok cukup
                            if ($stok >= $inventory['jumlah_sparepart']) {
                                // Kurangi stok jika status Menunggu Pembayaran atau Selesai (tetapi hanya sekali jika Menunggu Pembayaran sudah dilakukan)
                                if (($statusMenungguPembayaran || $statusSelesai) && !$stokSudahDikurangi) {
                                    \DB::table('inventories')
                                        ->where('id', $inventory['inventoryId'])
                                        ->update(['stok' => $stok - $inventory['jumlah_sparepart']]);
                                }
                                // Buat record InventoryDetail dengan harga_satuan yang diambil dari tabel inventory
                                InventoryDetail::create([
                                    'service_id' => $service->id,
                                    'inventory_id' => $inventory['inventoryId'],
                                    'jumlah_sparepart' => $inventory['jumlah_sparepart'],
                                    'harga_satuan' => $harga_satuan,
                                ]);
                            } else {
                                return response()->json(['error' => 'Stok tidak cukup untuk item dengan ID: ' . $inventory['inventoryId']], 400);
                            }
                        } else {
                            return response()->json(['error' => 'Inventory not found'], 404);
                        }
                    }
                }
                return response()->json(['message' => 'Service Updated'], 201);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed', 'message' => $e->getMessage()], 400);
        }
    }

    public function filterStatus(Request $request, $status)
    {
        if ($status === null || $status != 'Pengerjaan Selesai' && $status != 'Diproses' && $status != 'Selesai' && $status != 'Menunggu Pembayaran' && $status != 'Sedang Dikerjakan') {
            return response()->json(['error' => 'Status filter tidak ditemukan'], 400);
        }

        $user = $request->user();

        if ($status === 'Sedang Dikerjakan') {
            $query = Service::where(function ($query) {
                $query->where('status', 'Diproses')
                      ->orWhere('status', 'Pengerjaan Selesai');
            });
        } else {
            $query = Service::where('status', $status);
        }

        // Filter berdasarkan role user
        if ($user->tokenCan('admin')) {
            $query->where('admin_id', $user->admin->id);
        } else if ($user->tokenCan('customer')) {
            $query->where('customer_id', $user->customer->id);
        } else if ($user->tokenCan('technician')) {
            $query->where('technician_created_id', $user->technician->id);
        }

        $filterData = $query->get();

        if ($filterData->count() > 0) {
            $formattedResponse = $filterData->map(function ($data) {
                $detailService = $data->detailservice;
                return [
                    'id' => $data->id,
                    'adminId' => $data->admin_id,
                    'customerId' => $data->customer_id,
                    'nama_customer' => $data->customer ? $data->customer->name : null,
                    'merek' => $data->merek,
                    'tipe' => $data->tipe,
                    'status' => $data->status,
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
    public function getOneService(Request $request, $id)
    {
        $service = Service::find($id);
        if (!$service) {
            return response()->json(['error' => 'Data service not found'], 404);
        }

        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Tidak memiliki hak akses..'
            ], 403);
        }

        $detailService = $service->detailservice;
        $kerusakan = $detailService ? json_decode($detailService->kerusakan, true) : null;

        $biayaKerusakan = $detailService ? json_decode($detailService->biaya_kerusakan, true) : null;

        // Ambil data inventory yang terkait dengan service
        $inventoryDetails = InventoryDetail::where('service_id', $service->id)
            ->with('inventory') // Pastikan ada relasi dengan model Inventory
            ->get();

        // Format data inventory untuk response
        $formattedInventory = $inventoryDetails->map(function ($inventoryDetail) {
            return [
                'inventoryId' => $inventoryDetail->inventory_id,
                'merek_sparepart' => $inventoryDetail->inventory->merek,
                'tipe_sparepart' => $inventoryDetail->inventory->tipe,
                'jumlah_sparepart' => $inventoryDetail->jumlah_sparepart,
                'harga_satuan' => $inventoryDetail->harga_satuan,
            ];
        });

        $createdBy = null;
        $role = null;
        if ($service->admin_created_id && $service->technician_created_id) {
            $createdBy = $service->adminCreated ? $service->adminCreated->name : null;
            $role = 'Admin';
        } elseif ($service->technician_created_id) {
            $createdBy = $service->technicianCreated ? $service->technicianCreated->name : null;
            $role = 'Teknisi';
        }

        $formattedResponse = [
            'id' => $service->id,
            // 'adminId' => $service->admin_id,
            'createdBy' =>[
                'name' => $createdBy,
                'role' => $role
            ],
            'processedBy' => $service->adminProcessed ? $service->adminProcessed->name : null,
            'finishedBy' => $service->adminFinished ? $service->adminFinished->name : null,
            'technician'  =>  $service->technicianCreated ? $service->technicianCreated->name : null,
            'customerId' => $service->customer_id,
            'nama_customer' => $service->customer ? $service->customer->name : null,
            'merek' => $service->merek,
            'tipe' => $service->tipe,
            'diagnosaAwal' => $service->diagnosa_awal,
            'status' => $service->status,
            'tanggalMasuk' => $service->created_at,
            'tanggalKeluar' => $service->status === 'Selesai' ? $service->updated_at : null,
            'detail_service' => $detailService ? [
                'kerusakan' => $kerusakan,
                'estimasi' => $detailService->estimasi,
                'biayaKerusakan' => $biayaKerusakan,
            ] : null,
            'inventory' => $formattedInventory,
        ];

        return response()->json($formattedResponse, 200);
    }
    public function getAllService(Request $request)
    {
        $user = $request->user();
        $query = Service::query();
        $query->orderBy('created_at', 'desc');
        // if ($user->tokenCan('admin')) {
        //     $query->where('admin_created_id', $user->admin->id);
        // } 
        if ($user->tokenCan('customer')) {
            $query->where('customer_id', $user->customer->id);
        } else if ($user->tokenCan('technician')) {
            $query->where('technician_created_id', $user->technician->id);
        }
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
    public function searchService(Request $request, $name)
    {
        $services = Service::whereHas('customer', function ($query) use ($name) {
            $query->where('name', 'like', "%{$name}%");
        })->get();

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
    public function searchBrand(Request $request, $name)
    {
        $services = Service::where('merek', 'like', "%{$name}%")->get();


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
    public function downloadNotaPDF(Request $request, $id)
    {
        $service = Service::find($id);
        if (!$service) {
            return response()->json(['error' => 'Data service not found'], 404);
        }

        if ($service->status !== 'Selesai' && $service->status !== 'Menunggu Pembayaran') {
            return response()->json(['error' => 'Error, tidak bisa menampilkan PDF'], 403);
        }

        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Tidak memiliki hak akses.'
            ], 403);
        }

        $detailService = $service->detailservice;
        $kerusakan = $detailService ? json_decode($detailService->kerusakan, true) : null;
        $biayaKerusakan = $detailService ? json_decode($detailService->biaya_kerusakan, true) : null;

        $inventoryDetails = InventoryDetail::where('service_id', $service->id)
            ->with('inventory')
            ->get();

        $formattedInventory = $inventoryDetails->map(function ($inventoryDetail) {
            return [
                'inventoryId' => $inventoryDetail->inventory_id,
                'merek_sparepart' => $inventoryDetail->inventory->merek,
                'tipe_sparepart' => $inventoryDetail->inventory->tipe,
                'jumlah_sparepart' => $inventoryDetail->jumlah_sparepart,
                'harga_satuan' => $inventoryDetail->harga_satuan,
                'total_harga' => $inventoryDetail->jumlah_sparepart * $inventoryDetail->harga_satuan,
            ];
        });

        $totalHargaKeseluruhan = $formattedInventory->sum('total_harga');

        $createdBy = null;
        $role = null;
        if ($service->admin_created_id && $service->technician_created_id) {
            $createdBy = $service->adminCreated ? $service->adminCreated->name : null;
            $role = 'Admin';
        } elseif ($service->technician_created_id) {
            $createdBy = $service->technicianCreated ? $service->technicianCreated->name : null;
            $role = 'Teknisi';
        }

        $formattedResponse = [
            'id' => $service->id,
            'adminId' => $service->admin_id,
            'createdBy' =>[
                'name' => $createdBy,
                'role' => $role
            ],
            'processedBy' => $service->adminProcessed ? $service->adminProcessed->name : null,
            'finishedBy' => $service->adminFinished ? $service->adminFinished->name : null,
            'technician'  =>  $service->technicianCreated ? $service->technicianCreated->name : null,
            'customerId' => $service->customer_id,
            'nama_customer' => $service->customer ? $service->customer->name : null,
            'merek' => $service->merek,
            'tipe' => $service->tipe,
            'status' => $service->status,
            'tanggalMasuk' => $service->created_at,
            'tanggalKeluar' => $service->status === 'Selesai' ? $service->updated_at : null,
            'detail_service' => $detailService ? [
                'kerusakan' => $kerusakan,
                'estimasi' => $detailService->estimasi,
                'biayaKerusakan' => $biayaKerusakan,
            ] : null,
            'inventory' => $formattedInventory,
            'totalHargaKeseluruhan' => $totalHargaKeseluruhan,
        ];

        // Render view ke HTML
        $pdfView = View::make('pdf.nota', ['service' => $formattedResponse])->render();

        // Setup PDF options
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->setIsRemoteEnabled(true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($pdfView);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Mengirimkan output PDF sebagai download response
        return $dompdf->stream('nota_service_' . $service->id . '.pdf');
    }
}
