<?php
namespace Database\Factories;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'rut'           => fake()->numerify('########-#'),
            'nombre'        => fake()->name(),
            'motivo'        => fake()->randomElement(['Académico', 'Práctica', 'Inclusión', 'Financiero']),
            'ticket_numero' => fake()->unique()->bothify('??###'),
            'status'        => Ticket::STATUS_ESPERA,
            'mesa'          => null,
            'user_id'       => null,
        ];
    }
}
