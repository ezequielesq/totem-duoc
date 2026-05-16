<?php

use App\Http\Controllers\TicketController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Ruta para React del tótem
Route::post('/tickets', [TicketController::class, 'store']);
Route::get('/tickets/queue', [TicketController::class, 'queue']);
Route::post('/tickets/documento/email', [TicketController::class, 'sendDocumentoEmail']);

// Rutas para enviar correos electrónicos
Route::post('/tickets/{id}/email', [TicketController::class, 'sendTicketEmail']);

// Rutas API para gestión de tickets
Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/tickets/{id}/call',   [TicketController::class, 'call'])  ->where('id', '[0-9]+');
    Route::post('/tickets/{id}/finish', [TicketController::class, 'finish'])->where('id', '[0-9]+');
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
});
