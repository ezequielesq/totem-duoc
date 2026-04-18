<?php
namespace Database\Seeders;

use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        if (! Ticket::where('ticket_numero', 'ACA-001')->exists()) {
            Ticket::create([
                'ticket_numero' => 'ACA-001',
                'rut'           => '12.345.678-9',
                'nombre'        => 'Juan Pérez',
                'motivo'        => 'Académico',
                'status'        => 'espera',
            ]);
        }
    }
}
