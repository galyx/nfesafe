<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\SefazController;
use App\Http\Controllers\CompanyController;

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
Route::any('/cep/{cep}', [IndexController::class, 'cepConsulta']);

Route::get('/downloadTOTAL', [SefazController::class, 'downloadTOTAL']);

Route::get('/', [IndexController::class, 'dashboard']);
Route::get('/notas', [IndexController::class, 'notas'])->name('notas');
Route::get('/empresas', [IndexController::class, 'empresas'])->name('empresas');

Route::post('/leCertificado', [CompanyController::class, 'leCertificado'])->name('leCertificado');
Route::post('/novaEmpresa', [CompanyController::class, 'novaEmpresa'])->name('novaEmpresa');
Route::post('/alterarEmpresa', [CompanyController::class, 'alterarEmpresa'])->name('alterarEmpresa');
Route::post('/apagarEmpresa', [CompanyController::class, 'apagarEmpresa'])->name('apagarEmpresa');

Route::post('/buscaNotas', [IndexController::class, 'buscaNotas']);
Route::post('/buscaDadosNotas', [IndexController::class, 'buscaDadosNotas'])->name('buscaDadosNotas');
Route::get('/baixar-xml/{id}', [IndexController::class, 'baixarXml'])->name('baixarXml');

Route::get('/downloadNFE/{company_id}/{user_id}', [SefazController::class, 'downloadNFE']);
Route::get('/downloadCTE/{company_id}/{user_id}', [SefazController::class, 'downloadCTE']);

Route::get('/ciencia-op/{id}', [SefazController::class, 'cienciaOP']);
Route::get('/confirma-op/{id}', [SefazController::class, 'confirmaOP']);
Route::get('/desconhecimento-op/{id}', [SefazController::class, 'desconhecimentoOP']);
Route::get('/op-n-realizada/{id}', [SefazController::class, 'OPnRealizada']);

// Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
//     return view('dashboard');
// })->name('dashboard');
