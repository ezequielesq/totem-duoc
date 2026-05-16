<?php

use App\Http\Controllers\TicketController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/asesor');
});

Route::get('/pantalla', [TicketController::class, 'pantalla']);

Auth::routes(['register' => false]);

Route::middleware('auth')->group(function () {
    Route::get('/asesor', [TicketController::class, 'panel']);
    Route::get('/dashboard', [DashboardController::class, 'dashboard']);
});
