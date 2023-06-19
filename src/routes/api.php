<?php

use App\Http\Controllers\Api\GeneralController;
use App\Http\Controllers\Api\TestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test',  [TestController::class, 'test']);
Route::get('/env',  [TestController::class, 'env']);
Route::get('/db',  [TestController::class, 'db']);


Route::post('/login',  [GeneralController::class, 'login']);
Route::get('/user/{id}',  [GeneralController::class, 'getUser']);
Route::get('/table/code/{code}', [GeneralController::class, 'getTableByCode']);
Route::post('/table/insert/new_user', [GeneralController::class, 'insertNewUserOnTable']);
Route::post('/table/order/make', [GeneralController::class, 'makeOrder']);
Route::post('/table/order/pay', [GeneralController::class, 'payOrders']);
Route::post('/table/order/help', [GeneralController::class, 'helpWithOrder']);
Route::post('/table/order/not_help', [GeneralController::class, 'notHelpWithOrder']);
Route::post('/table/order/delivered', [GeneralController::class, 'setOrderAsDelivered']);
