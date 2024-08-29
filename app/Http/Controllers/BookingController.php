<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function newBooking(Request $request)
    {
        try {
            \Log::info('Memulai fungsi newBooking');

            $user = $request->user();
            if (!$user) {
                \Log::info('User tidak ditemukan');
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Tidak memiliki hak akses..'
                ], 403);
            }

            \Log::info('User ditemukan: ' . $user->id);

            // Admin Request
            if ($user->tokenCan('customer')) {
                \Log::info('User memiliki token admin');

                $request->validate([
                    'customerId' => 'required|numeric',
                    'merek' => 'required|string',
                    'tipe' => 'required|string',
                    'keluhan' => 'required|string',
                ]);

                \Log::info('Validasi berhasil');

                // Dapatkan nomor antrian terakhir
                $lastBooking = Booking::orderBy('id', 'desc')->first();
                \Log::info('Booking terakhir: ' . json_encode($lastBooking));

                $lastNumber = $lastBooking ? intval($lastBooking->nomor_antrian) : 0;

                // Tambahkan satu untuk membuat nomor antrian baru
                $newNumber = str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);
                \Log::info('Nomor antrian baru: ' . $newNumber);

                $booking = Booking::create([
                    'customer_id' => $request->customerId,
                    'merek' => $request->merek,
                    'tipe' => $request->tipe,
                    'keluhan' => $request->keluhan,
                    'nomor_antrian' => $newNumber,
                ]);

                \Log::info('Booking berhasil dibuat: ' . json_encode($booking));
                return response()->json([
                    'message' => 'New Booking Created',
                    'nomor_antrian' => $newNumber
                ], 201);
            } else {
                \Log::info('User tidak memiliki token admin');
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Tidak memiliki hak akses sebagai admin.'
                ], 403);
            }

        } catch (\Exception $e) {
            \Log::error('Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed', 'message' => $e->getMessage()], 400);
        }
    }

    public function getAllBooking(Request $request)
    {
        $bookings = Booking::all();

        if ($bookings->count() > 0) {
            $formattedResponse = $bookings->map(function ($data) {
                return [
                    'id' => $data->id,
                    'customerId' => $data->customer_id,
                    'nama_customer' => $data->customer ? $data->customer->name : null,
                    'telepon' => $data->customer ? $data->customer->phone : null,
                    'merek' => $data->merek,
                    'tipe' => $data->tipe,
                    'keluhan' => $data->keluhan,
                    'nomor_antrian' => $data->nomor_antrian,
                ];
            });
            return response()->json(['data' => $formattedResponse], 200);
        } else {
            return response()->json(['error' => 'Data tidak ditemukan...'], 404);
        }

    }
    public function deleteBooking(Request $request, $id)
    {
        $data = Booking::find($id);

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
    public function numberBooking(Request $request, $id){
        $booking = Booking::where('customer_id', $id)->latest()->first();

        if ($booking) {
            return response()->json([
                'nomor_antrian' => $booking->nomor_antrian,
            ], 200);
        } else {
            return response()->json([
                'error' => 'No booking found for this customer.',
            ], 404);
        }
    }
    public function searchBooking(Request $request, $id){
        $nomorId = preg_replace('/[^0-9]/', '', $id);
        $booking = Booking::where('nomor_antrian','like', "%{$nomorId}%")->get();

        if ($booking->count() > 0) {
            $formattedResponse = $booking->map(function ($data) {
                return [
                   'id' => $data->id,
                    'customerId' => $data->customer_id,
                    'nama_customer' => $data->customer ? $data->customer->name : null,
                    'telepon' => $data->customer ? $data->customer->phone : null,
                    'merek' => $data->merek,
                    'tipe' => $data->tipe,
                    'keluhan' => $data->keluhan,
                    'nomor_antrian' => $data->nomor_antrian,
                ];
            });
            return response()->json(['data' => $formattedResponse], 200);
        } else {
            return response()->json(['error' => 'Data tidak ditemukan...'], 404);
        }
    }
}
