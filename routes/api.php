<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TechnicianController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/newService', [ServiceController::class, 'newService']);
    Route::put('/updateService/{id}', [ServiceController::class, 'updateService']);
    Route::get('/filterStatus/{status}', [ServiceController::class, 'filterStatus']);
    Route::get('/getOneService/{id}', [ServiceController::class, 'getOneService']);
    Route::get('/getAllService', [ServiceController::class, 'getAllService']);
    Route::get('/searchService/{name}', [ServiceController::class, 'searchService']);
    Route::get('/searchBrand/{name}', [ServiceController::class,'searchBrand']);
    Route::get('/downloadNotaPDF/{id}', [ServiceController::class,'downloadNotaPDF']);

    Route::post('/newInventory', [InventoryController::class, 'newInventory']);
    Route::put('/updateInventory/{id}', [InventoryController::class, 'updateInventory']);
    Route::get('/getOneInventory/{id}', [InventoryController::class, 'getOneInventory']);
    Route::get('/getAllInventory', [InventoryController::class, 'getAllInventory']);
    Route::get('/searchInventory/{name}', [InventoryController::class, 'searchInventory']);
    Route::delete('/deleteOneInventory/{id}', [InventoryController::class,'deleteOneInventory']);
    Route::get('/getAllAvailable', [InventoryController::class, 'getAllAvailable']);

    Route::post('/newBooking', [BookingController::class,'newBooking']);
    Route::get('/getAllBooking', [BookingController::class, 'getAllBooking']);
    Route::delete('/deleteBooking/{id}', [BookingController::class,'deleteBooking']);
    Route::get('/numberBooking/{id}', [BookingController::class,'numberBooking']);
    Route::get('/searchBooking/{id}', [BookingController::class,'searchBooking']);

    Route::get('/getCustomerName', [CustomerController::class,'getCustomerName']);
    Route::get('/filterCustomer/{name}', [CustomerController::class,'filterCustomer']);
    Route::put('/updateCustomer/{id}', [CustomerController::class,'updateCustomer']);
    Route::delete('/deleteCustomer/{id}', [CustomerController::class,'deleteCustomer']);
    Route::get('/searchCustomer/{name}', [CustomerController::class,'searchCustomer']);
    Route::get('/getOneCustomer/{id}', [CustomerController::class,'getOneCustomer']);

    Route::get('/getTechnicianName', [TechnicianController::class,'getTechnicianName']);
    Route::get('/filterTechnician/{name}', [TechnicianController::class,'filterTechnician']);
    Route::put('/updateTechnician/{id}', [TechnicianController::class,'updateTechnician']);
    Route::delete('/deleteTechnician/{id}', [TechnicianController::class,'deleteTechnician']);
    Route::get('/searchTechnician/{name}', [TechnicianController::class,'searchTechnician']);
    Route::get('/getOneTechnician/{id}', [TechnicianController::class,'getOneTechnician']);
    Route::get('/getActive', [TechnicianController::class,'getActive']);
    Route::get('/getHistory/{id}', [TechnicianController::class,'getHistory']);

    Route::get('/getAdminName', [AdminController::class,'getAdminName']);
    Route::get('/filterAdmin/{name}', [AdminController::class,'filterAdmin']);
    Route::put('/updateAdmin/{id}', [AdminController::class,'updateAdmin']);
    Route::delete('/deleteAdmin/{id}', [AdminController::class,'deleteAdmin']);
    Route::get('/getOneAdmin/{id}', [AdminController::class,'getOneAdmin']);


    Route::get('/chartMonth', [AdminController::class,'chartMonth']);



    Route::get('/user', function (Request $request) {
        $user = $request->user();
        if ($user->tokenCan('admin')) {
            return response()->json([
                'user_type' => 'admin',
                'name' => $user->admin?->name ?? null,
                'user' => $user,
            ]);
        } else if ($user->tokenCan('customer')) {
            return response()->json([
                'user_type' => 'customer',
                'name' => $user->customer?->name ?? null,
                'user' => $user,
            ]);
        } else if ($user->tokenCan('technician')) {
            return response()->json([
                'user_type' => 'technician',
                'name' => $user->technician?->name ?? null,
                'user' => $user,
            ]);
        }
    });
});
Route::post('/registerTechnician', [AuthenticationController::class, 'registerTechnician']);
Route::post('/registerCustomer', [AuthenticationController::class, 'registerCustomer']);
Route::post('/registerAdmin', [AuthenticationController::class, 'registerAdmin']);
Route::post('/login', [AuthenticationController::class, 'login']);
Route::post('logOut', [AuthenticationController::class,'logOut']);
