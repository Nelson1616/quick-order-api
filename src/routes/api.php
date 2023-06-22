<?php

use App\Http\Controllers\Api\GeneralController;
use Illuminate\Support\Facades\Route;

Route::post('/login',  [GeneralController::class, 'login']);
Route::get('/user/{id}',  [GeneralController::class, 'getUser']);
Route::get('/official/{id}',  [GeneralController::class, 'getOfficial']);
Route::get('/table/code/{code}', [GeneralController::class, 'getTableByCode']);
Route::post('/table/insert/new_user', [GeneralController::class, 'insertNewUserOnTable']);
Route::post('/table/order/make', [GeneralController::class, 'makeOrder']);
Route::post('/table/order/pay', [GeneralController::class, 'payOrders']);
Route::post('/table/order/help', [GeneralController::class, 'helpWithOrder']);
Route::post('/table/order/not_help', [GeneralController::class, 'notHelpWithOrder']);
Route::post('/table/order/update_status', [GeneralController::class, 'updateOrderStatus']);
Route::post('/table/order/cancel', [GeneralController::class, 'cancelOrder']);
Route::post('/table/waiter/call/make', [GeneralController::class, 'makeWaiterCall']);
Route::post('/table/waiter/call/update', [GeneralController::class, 'updateWaiterCall']);
Route::post('/table/order/waiter/pay', [GeneralController::class, 'waiterPayOrders']);
