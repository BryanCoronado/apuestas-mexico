<?php

use App\Http\Controllers\RuletaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RuletaController::class, 'index'])->name('ruleta.index');
