<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ClientController;
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

Route::get('/clients/{client}/accounts', [ClientController::class, 'accounts']);

Route::get('/accounts/{account}/transactions', [AccountController::class, 'transactions']);
