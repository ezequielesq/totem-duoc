<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/pantalla', [TicketController::class, 'pantalla']);

Auth::routes(['register' => false]);

Route::middleware(['auth', 'role:coordinador'])->group(function () {
    Route::get('/asesor', [TicketController::class, 'asesor']);
});

Route::middleware(['auth', 'role:directora'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard']);
});
