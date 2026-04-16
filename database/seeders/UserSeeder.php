<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'coordinador@duoc.cl'],
            [
                'name'     => 'Coordinador 1',
                'password' => 'password123',
            ]
        );
    }
}
