<?php

use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

// Rutas API para gestión de tickets
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/tickets/queue',            [TicketController::class, 'queue']);
    Route::post('/tickets/{id}/call',       [TicketController::class, 'call']);
    Route::post('/tickets/{id}/finish',     [TicketController::class, 'finish']);
    Route::post('/tickets/{id}/email',      [TicketController::class, 'sendTicketEmail']);
    Route::post('/tickets/email/documento', [TicketController::class, 'sendDocumentoEmail']);
});
