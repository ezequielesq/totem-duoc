<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $userIds  = User::pluck('id')->toArray();
        $motivos  = ['Académico', 'Práctica', 'Inclusión', 'Financiero'];
        $prefijos = ['Académico' => 'AC', 'Práctica' => 'PR', 'Inclusión' => 'IN', 'Financiero' => 'FI'];
        $mesas    = [1, 2, 3, 4];

        // Distribución realista por motivo
        $pesoMotivos = ['Académico' => 50, 'Práctica' => 25, 'Inclusión' => 10, 'Financiero' => 15];

        // Horas pico realistas
        $horasPico = [8, 9, 9, 9, 10, 10, 10, 10, 11, 11, 11, 12, 12, 13, 14, 15, 15, 16, 16, 17];

        // Nombres chilenos variados
        $nombres = [
            'Juan Pérez', 'María Soto', 'Pedro Gómez', 'Ana Martínez', 'Luis Castro',
            'Carmen Díaz', 'Roberto Vargas', 'Patricia Rojas', 'Diego Fuentes', 'Valentina Cruz',
            'Andrés Torres', 'Sofía Herrera', 'Felipe Morales', 'Camila Jiménez', 'Matías Romero',
            'Isabella Navarro', 'Sebastián Ramos', 'Daniela Silva', 'Tomás Mendoza', 'Gabriela Ríos',
            'Nicolás Parra', 'Javiera Araya', 'Cristóbal Vega', 'Antonia Flores', 'Benjamín Reyes',
            'Constanza Moya', 'Emilio Cortés', 'Florencia Ibáñez', 'Gonzalo Espinoza', 'Isidora Bravo',
            'Joaquín Vera', 'Karina Poblete', 'Leonardo Salinas', 'Marcela Fuentes', 'Olivia Sandoval',
            'Pablo Contreras', 'Renata Espinoza', 'Samuel Rojas', 'Tamara Vidal', 'Ulises Mora',
            'Valentina Pérez', 'Waldo Fuentes', 'Ximena Castro', 'Yerlan Díaz', 'Zoe Morales',
            'Agustín Reyes', 'Bárbara Silva', 'César Mendoza', 'Esteban Parra', 'Francisca Lagos',
        ];

        $ruts = [
            '12.345.678-9', '13.456.789-0', '14.567.890-1', '15.678.901-2', '16.789.012-3',
            '17.890.123-4', '18.901.234-5', '19.012.345-6', '20.123.456-7', '21.234.567-8',
            '22.345.678-9', '23.456.789-0', '24.567.890-1', '25.678.901-2', '26.789.012-3',
            '27.890.123-4', '28.901.234-5', '29.012.345-6', '30.123.456-7', '31.234.567-8',
            '32.345.678-9', '33.456.789-0', '34.567.890-1', '35.678.901-2', '36.789.012-3',
            '37.890.123-4', '38.901.234-5', '39.012.345-6', '40.123.456-7', '41.234.567-8',
            '42.345.678-9', '43.456.789-0', '44.567.890-1', '45.678.901-2', '46.789.012-3',
            '47.890.123-4', '48.901.234-5', '49.012.345-6', '50.123.456-7', '51.234.567-8',
            '52.345.678-9', '53.456.789-0', '54.567.890-1', '55.678.901-2', '56.789.012-3',
            '57.890.123-4', '58.901.234-5', '59.012.345-6', '60.123.456-7', '61.234.567-8',
        ];

        $contadores = ['AC' => 0, 'PR' => 0, 'IN' => 0, 'FI' => 0];

        for ($i = 0; $i < 100; $i++) {
            // Motivo con peso
            $rand   = rand(1, 100);
            $acum   = 0;
            $motivo = 'Académico';
            foreach ($pesoMotivos as $m => $peso) {
                $acum += $peso;
                if ($rand <= $acum) {$motivo = $m;
                    break;}
            }

            $prefijo = $prefijos[$motivo];
            $contadores[$prefijo]++;
            $numero = $prefijo . str_pad($contadores[$prefijo], 3, '0', STR_PAD_LEFT);

            // Fecha aleatoria últimos 30 días en hora pico
            $diasAtras = rand(0, 30);
            $hora      = $horasPico[array_rand($horasPico)];
            $creado    = now()
                ->subDays($diasAtras)
                ->setHour($hora)
                ->setMinute(rand(0, 59))
                ->setSecond(rand(0, 59));

            // 75% finalizado, 20% en espera, 5% llamando
            $rand2 = rand(1, 100);

            if ($rand2 <= 75) {
                // Finalizado
                $espera     = rand(3, 25);
                $atencion   = rand(5, 35);
                $llamado    = $creado->copy()->addMinutes($espera);
                $finalizado = $llamado->copy()->addMinutes($atencion);
                $userId     = $userIds[array_rand($userIds)];
                $mesa       = $mesas[array_rand($mesas)];

                DB::table('tickets')->insert([
                    'rut'           => $ruts[$i % count($ruts)],
                    'nombre'        => $nombres[$i % count($nombres)],
                    'motivo'        => $motivo,
                    'ticket_numero' => $numero,
                    'status'        => 'atendido',
                    'mesa'          => $mesa,
                    'user_id'       => $userId,
                    'created_at'    => $creado,
                    'updated_at'    => $llamado,
                    'deleted_at'    => $finalizado,
                ]);

            } elseif ($rand2 <= 95) {
                // En espera — solo de hoy
                $creado = now()->subMinutes(rand(1, 60));

                DB::table('tickets')->insert([
                    'rut'           => $ruts[$i % count($ruts)],
                    'nombre'        => $nombres[$i % count($nombres)],
                    'motivo'        => $motivo,
                    'ticket_numero' => $numero,
                    'status'        => 'espera',
                    'mesa'          => null,
                    'user_id'       => null,
                    'created_at'    => $creado,
                    'updated_at'    => $creado,
                    'deleted_at'    => null,
                ]);
            } else {
                // Llamando
                $espera  = rand(3, 25);
                $llamado = $creado->copy()->addMinutes($espera);
                $userId  = $userIds[array_rand($userIds)];
                $mesa    = $mesas[array_rand($mesas)];

                DB::table('tickets')->insert([
                    'rut'           => $ruts[$i % count($ruts)],
                    'nombre'        => $nombres[$i % count($nombres)],
                    'motivo'        => $motivo,
                    'ticket_numero' => $numero,
                    'status'        => 'llamando',
                    'mesa'          => $mesa,
                    'user_id'       => $userId,
                    'created_at'    => $creado,
                    'updated_at'    => $llamado,
                    'deleted_at'    => null,
                ]);
            }
        }
    }
}
