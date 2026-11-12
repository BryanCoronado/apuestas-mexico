<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Panel\BoletoController;
use App\Http\Controllers\Panel\RifaController;
use App\Http\Controllers\RuletaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RuletaController::class, 'index'])->name('ruleta.index');
Route::get('/ruleta/ganador', [RuletaController::class, 'ganador'])
    ->middleware('throttle:30,1')
    ->name('ruleta.ganador');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:login')->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->prefix('panel')->name('panel.')->group(function () {
    Route::view('/', 'panel.dashboard')->name('dashboard');

    Route::get('/boletos', [BoletoController::class, 'index'])->name('boletos.index');
    Route::get('/boletos/exportar/{rifa}', [BoletoController::class, 'exportar'])->name('boletos.exportar');
    Route::patch('/boletos/pagar', [BoletoController::class, 'pagar'])->name('boletos.pagar');
    Route::delete('/boletos', [BoletoController::class, 'destroy'])->name('boletos.destroy');
    Route::patch('/boletos/{boleto}/senalar', [BoletoController::class, 'senalar'])->name('boletos.senalar');

    Route::resource('rifas', RifaController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('/rifas/{rifa}/duplicar', [RifaController::class, 'duplicar'])->name('rifas.duplicar');
    Route::patch('/rifas/{rifa}/abierta', [RifaController::class, 'alternarAbierta'])->name('rifas.abierta');
});
