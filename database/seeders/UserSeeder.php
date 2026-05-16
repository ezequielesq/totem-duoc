<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles
        $rolCoordinador = DB::table('roles')->insertGetId([
            'name'        => 'coordinador',
            'description' => 'Atiende tickets en el panel de coordinadores',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $rolDirectora = DB::table('roles')->insertGetId([
            'name'        => 'directora',
            'description' => 'Accede al dashboard de estadísticas',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Coordinadores
        $coordinadores = [
            ['name' => 'Ana García', 'email' => 'ana@duoc.cl'],
            ['name' => 'Carlos Pérez', 'email' => 'carlos@duoc.cl'],
            ['name' => 'María López', 'email' => 'maria@duoc.cl'],
            ['name' => 'Jorge Muñoz', 'email' => 'jorge@duoc.cl'],
            ['name' => 'Claudia Soto', 'email' => 'claudia@duoc.cl'],
        ];

        foreach ($coordinadores as $c) {
            $user = User::firstOrCreate(
                ['email' => $c['email']],
                ['name' => $c['name'], 'password' => Hash::make('password')]
            );

            DB::table('user_has_role')->insertOrIgnore([
                'user_id'    => $user->id,
                'role_id'    => $rolCoordinador,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Directora
        $directora = User::firstOrCreate(
            ['email' => 'directora@duoc.cl'],
            ['name' => 'Carmen Directora', 'password' => Hash::make('password')]
        );

        DB::table('user_has_role')->insertOrIgnore([
            'user_id'    => $directora->id,
            'role_id'    => $rolDirectora,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
