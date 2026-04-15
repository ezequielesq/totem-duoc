<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes;

    protected $table = 'tickets';

    protected $fillable = [
        'rut',
        'nombre',
        'motivo',
        'ticket_numero',
        'status',
        'mesa',
        'user_id',
    ];

    // Prefijos por área
    const PREFIJOS = [
        'Académico'  => 'ACA',
        'Práctica'   => 'PRA',
        'Inclusión'  => 'INC',
        'Financiero' => 'FIN',
    ];

    // Estados válidos
    const STATUS_ESPERA   = 'espera';
    const STATUS_LLAMANDO = 'llamando';
    const STATUS_ATENDIDO = 'atendido';

    /**
     * Genera el número de ticket según el motivo
     * Ejemplo: ACA-001, PRA-012
     */
    public static function generarNumero(string $motivo): string
    {
        $prefijo = self::PREFIJOS[$motivo] ?? 'GEN';

        $ultimo = self::withTrashed()
            ->where('motivo', $motivo)
            ->max('id') ?? 0;

        return $prefijo . '-' . str_pad($ultimo + 1, 3, '0', STR_PAD_LEFT);
    }

    // Scopes útiles para consultas
    public function scopeEnEspera($query)
    {
        return $query->where('status', self::STATUS_ESPERA)
            ->orderBy('created_at', 'asc');
    }

    public function scopeLlamando($query)
    {
        return $query->where('status', self::STATUS_LLAMANDO);
    }

    public function coordinador()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
