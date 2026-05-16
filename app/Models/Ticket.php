<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    // =========================================================================
    // Constantes de status
    // =========================================================================

    const STATUS_ESPERA   = 'espera';
    const STATUS_LLAMANDO = 'llamando';
    const STATUS_ATENDIDO = 'atendido';

    // =========================================================================
    // Prefijos por motivo para numeración
    // =========================================================================

    const PREFIJOS = [
        'Académico'  => 'AC',
        'Práctica'   => 'PR',
        'Inclusión'  => 'IN',
        'Financiero' => 'FI',
    ];

    protected $fillable = [
        'rut',
        'nombre',
        'motivo',
        'ticket_numero',
        'status',
        'mesa',
        'user_id',
    ];

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopeEnEspera($query)
    {
        return $query->where('status', self::STATUS_ESPERA);
    }

    public function scopeLlamando($query)
    {
        return $query->where('status', self::STATUS_LLAMANDO);
    }

    // =========================================================================
    // generarNumero()
    // TC-08: retorna número único y correlativo por motivo
    // TC-09: es atómico — usa lockForUpdate para evitar duplicados bajo concurrencia
    //
    // IMPORTANTE: este método debe llamarse siempre dentro de una DB::transaction()
    // activa (como se hace en TicketController@store) para que el lockForUpdate
    // sea efectivo. Si se llama fuera de una transacción, el lock no tiene efecto.
    // =========================================================================

    public static function generarNumero(string $motivo): string
    {
        $prefijo = self::PREFIJOS[$motivo] ?? strtoupper(substr($motivo, 0, 2));

        // lockForUpdate bloquea la fila del último ticket con este prefijo
        // hasta que la transacción termine, evitando que dos procesos
        // simultáneos lean el mismo correlativo y generen duplicados.
        $ultimo = self::where('ticket_numero', 'like', $prefijo . '%')
            ->lockForUpdate()
            ->orderByDesc('ticket_numero')
            ->first();

        if ($ultimo === null) {
            $siguiente = 1;
        } else {
            // Extrae el número del final del ticket_numero (ej. "AC005" -> 5)
            $numeroActual = (int) substr($ultimo->ticket_numero, strlen($prefijo));
            $siguiente    = $numeroActual + 1;
        }

        // Formato: prefijo + número con 3 dígitos (AC001, AC002, ... AC999)
        return $prefijo . str_pad($siguiente, 3, '0', STR_PAD_LEFT);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
