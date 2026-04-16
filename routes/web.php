<?php

use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/asesor');
});

Auth::routes(['register' => false]);

Route::middleware('auth')->group(function () {
    Route::get('/asesor', [TicketController::class, 'panel']);
});
