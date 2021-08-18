<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\SefazController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/downloadTOTAL', [SefazController::class, 'downloadTOTAL']);

Route::get('/', [IndexController::class, 'dashboard']);
Route::get('/notas', [IndexController::class, 'notas']);
Route::post('/buscaNotas', [IndexController::class, 'buscaNotas']);

Route::get('/downloadNFE/{company_id}/{user_id}', [SefazController::class, 'downloadNFE']);
Route::get('/downloadCTE/{company_id}/{user_id}', [SefazController::class, 'downloadCTE']);

// Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
//     return view('dashboard');
// })->name('dashboard');
