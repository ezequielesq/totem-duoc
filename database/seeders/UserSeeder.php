<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $coordinadores = [
            ['name' => 'Ana García',     'email' => 'ana@duoc.cl'],
            ['name' => 'Carlos Pérez',   'email' => 'carlos@duoc.cl'],
            ['name' => 'María López',    'email' => 'maria@duoc.cl'],
            ['name' => 'Jorge Muñoz',    'email' => 'jorge@duoc.cl'],
            ['name' => 'Claudia Soto',   'email' => 'claudia@duoc.cl'],
        ];

        foreach ($coordinadores as $c) {
            User::firstOrCreate(
                ['email' => $c['email']],
                ['name' => $c['name'], 'password' => Hash::make('password')]
            );
        }
    }
}
