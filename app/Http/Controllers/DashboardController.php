<?php
namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $coordinadores = User::orderBy('name')->get(['id', 'name']);
        return view('dashboard.dashboard', compact('coordinadores'));
    }

    public function stats(Request $request): JsonResponse
    {
        $request->validate([
            'desde'   => 'required|date',
            'hasta'   => 'required|date|after_or_equal:desde',
            'user_id' => 'nullable|integer|exists:users,id',
        ]);

        $desde   = $request->desde . ' 00:00:00';
        $hasta   = $request->hasta . ' 23:59:59';
        $user_id = $request->user_id;

        // Base query — incluye soft deleted para ver finalizados
        $base = Ticket::withTrashed()
            ->whereBetween('created_at', [$desde, $hasta])
            ->when($user_id, fn($q) => $q->where('user_id', $user_id));

        // =====================================================================
        // KPIs
        // =====================================================================

        $total       = (clone $base)->count();
        $finalizados = (clone $base)->whereNotNull('deleted_at')->count();
        $enEspera    = Ticket::where('status', Ticket::STATUS_ESPERA)->count();   // tiempo real
        $enAtencion  = Ticket::where('status', Ticket::STATUS_LLAMANDO)->count(); // tiempo real

        $tasaFinalizacion = $total > 0 ? round($finalizados / $total * 100, 1) : 0;

        // Tiempo promedio de espera: updated_at - created_at (cuando fue llamado)
        $promedioEspera = (clone $base)
            ->whereNotNull('deleted_at')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (updated_at - created_at))) as promedio')
            ->value('promedio');

        // Tiempo promedio de atención: deleted_at - updated_at
        $promedioAtencion = (clone $base)
            ->whereNotNull('deleted_at')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (deleted_at - updated_at))) as promedio')
            ->value('promedio');

        // Hora pico
        $horaPico = (clone $base)
            ->selectRaw('EXTRACT(HOUR FROM created_at) as hora, COUNT(*) as total')
            ->groupBy('hora')
            ->orderByDesc('total')
            ->first();

        // Área más demandada
        $areaMasDemanda = (clone $base)
            ->selectRaw('motivo, COUNT(*) as total')
            ->groupBy('motivo')
            ->orderByDesc('total')
            ->first();

        // Coordinador top
        $coordinadorTop = (clone $base)
            ->whereNotNull('user_id')
            ->selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->with('user:id,name')
            ->first();

        // Mesas activas ahora
        $mesasActivas = Ticket::where('status', Ticket::STATUS_LLAMANDO)
            ->distinct('mesa')
            ->count('mesa');

        // Ticket más antiguo en espera
        $ticketMasAntiguo = Ticket::where('status', Ticket::STATUS_ESPERA)
            ->orderBy('created_at')
            ->first(['ticket_numero', 'nombre', 'motivo', 'created_at']);

        // =====================================================================
        // Alertas
        // =====================================================================

        $ticketsEsperaLarga = Ticket::where('status', Ticket::STATUS_ESPERA)
            ->where('created_at', '<=', now()->subMinutes(15))
            ->get(['ticket_numero', 'nombre', 'motivo', 'created_at'])
            ->map(fn($t) => [
                'ticket_numero' => $t->ticket_numero,
                'nombre'        => $t->nombre,
                'motivo'        => $t->motivo,
                'minutos' => round($t->created_at->diffInMinutes(now()), 1),
            ]);

        $mesasSinActividad = Ticket::where('status', Ticket::STATUS_LLAMANDO)
            ->where('updated_at', '<=', now()->subMinutes(30))
            ->get(['mesa', 'ticket_numero', 'nombre', 'updated_at'])
            ->map(fn($t) => [
                'mesa'          => $t->mesa,
                'ticket_numero' => $t->ticket_numero,
                'nombre'        => $t->nombre,
                'minutos' => round($t->updated_at->diffInMinutes(now()), 1),
            ]);

        // =====================================================================
        // Gráficos
        // =====================================================================

        // Tickets por día
        $ticketsPorDia = (clone $base)
            ->selectRaw("DATE(created_at) as dia, COUNT(*) as total")
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        // Tickets por área
        $ticketsPorArea = (clone $base)
            ->selectRaw('motivo, COUNT(*) as total')
            ->groupBy('motivo')
            ->orderByDesc('total')
            ->get();

        // Tickets por hora
        $ticketsPorHora = (clone $base)
            ->selectRaw('EXTRACT(HOUR FROM created_at) as hora, COUNT(*) as total')
            ->groupBy('hora')
            ->orderBy('hora')
            ->get();

        // Tickets por área por coordinador
        $ticketsAreaCoordinador = (clone $base)
            ->whereNotNull('user_id')
            ->selectRaw('user_id, motivo, COUNT(*) as total')
            ->groupBy('user_id', 'motivo')
            ->with('user:id,name')
            ->get()
            ->map(fn($t) => [
                'coordinador' => $t->user?->name ?? 'Sin asignar',
                'motivo'      => $t->motivo,
                'total'       => $t->total,
            ]);

        // Comparativa coordinadores
        $comparativaCoordinadores = (clone $base)
            ->whereNotNull('user_id')
            ->whereNotNull('deleted_at')
            ->selectRaw('user_id, COUNT(*) as total, AVG(EXTRACT(EPOCH FROM (deleted_at - updated_at))) as promedio_atencion')
            ->groupBy('user_id')
            ->with('user:id,name')
            ->get()
            ->map(fn($t) => [
                'coordinador'       => $t->user?->name ?? 'Sin asignar',
                'total'             => $t->total,
                'promedio_atencion' => round($t->promedio_atencion / 60, 1),
            ]);

        // =====================================================================
        // Tabla por coordinador
        // =====================================================================

        $tablaCoordinadores = (clone $base)
            ->whereNotNull('user_id')
            ->selectRaw('
                user_id,
                COUNT(*) as total_atendidos,
                AVG(EXTRACT(EPOCH FROM (deleted_at - updated_at))) as promedio_atencion,
                AVG(EXTRACT(EPOCH FROM (updated_at - created_at))) as promedio_espera,
                MIN(updated_at) as primera_atencion,
                MAX(updated_at) as ultima_atencion
            ')
            ->whereNotNull('deleted_at')
            ->groupBy('user_id')
            ->with('user:id,name')
            ->get()
            ->map(function ($t) use ($base) {
                $areaMasAtendida = (clone $base)
                    ->where('user_id', $t->user_id)
                    ->selectRaw('motivo, COUNT(*) as total')
                    ->groupBy('motivo')
                    ->orderByDesc('total')
                    ->first();

                return [
                    'coordinador'       => $t->user?->name ?? 'Sin asignar',
                    'total_atendidos'   => $t->total_atendidos,
                    'promedio_atencion' => round($t->promedio_atencion / 60, 1),
                    'promedio_espera'   => round($t->promedio_espera / 60, 1),
                    'primera_atencion'  => $t->primera_atencion,
                    'ultima_atencion'   => $t->ultima_atencion,
                    'area_mas_atendida' => $areaMasAtendida?->motivo ?? '-',
                ];
            });

        // =====================================================================
        // Tabla detalle
        // =====================================================================

        $detalle = (clone $base)
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(500)
            ->get()
            ->map(fn($t) => [
                'ticket_numero'   => $t->ticket_numero,
                'nombre'          => $t->nombre,
                'rut'             => $t->rut,
                'motivo'          => $t->motivo,
                'coordinador'     => $t->user?->name ?? '-',
                'mesa'            => $t->mesa ?? '-',
                'status'          => $t->status,
                'tiempo_espera'   => $t->deleted_at && $t->updated_at && $t->created_at
                    ? round($t->created_at->diffInMinutes($t->updated_at), 1)
                    : '-',
                'tiempo_atencion' => $t->deleted_at && $t->updated_at
                    ? round($t->updated_at->diffInMinutes($t->deleted_at), 1)
                    : '-',
                'created_at'      => $t->created_at->format('d/m/Y H:i'),
            ]);

        // =====================================================================
        // Respuesta
        // =====================================================================

        return response()->json([
            'kpis'          => [
                'total_tickets'            => $total,
                'finalizados'              => $finalizados,
                'en_espera'                => $enEspera,
                'en_atencion'              => $enAtencion,
                'tasa_finalizacion'        => $tasaFinalizacion,
                'tiempo_promedio_espera'   => $promedioEspera ? round($promedioEspera / 60, 1) : 0,
                'tiempo_promedio_atencion' => $promedioAtencion ? round($promedioAtencion / 60, 1) : 0,
                'hora_pico'                => $horaPico ? (int) $horaPico->hora . ':00' : '-',
                'area_mas_demanda'         => $areaMasDemanda?->motivo ?? '-',
                'coordinador_top'          => $coordinadorTop?->user?->name ?? '-',
                'mesas_activas'            => $mesasActivas,
                'ticket_mas_antiguo'       => $ticketMasAntiguo ? [
                    'ticket_numero' => $ticketMasAntiguo->ticket_numero,
                    'nombre'        => $ticketMasAntiguo->nombre,
                    'minutos' => round($ticketMasAntiguo->created_at->diffInMinutes(now()), 1),
                ] : null,
            ],
            'alertas'       => [
                'tickets_espera_larga' => $ticketsEsperaLarga,
                'mesas_sin_actividad'  => $mesasSinActividad,
            ],
            'graficos'      => [
                'tickets_por_dia'           => $ticketsPorDia,
                'tickets_por_area'          => $ticketsPorArea,
                'tickets_por_hora'          => $ticketsPorHora,
                'tickets_area_coordinador'  => $ticketsAreaCoordinador,
                'comparativa_coordinadores' => $comparativaCoordinadores,
            ],
            'coordinadores' => $tablaCoordinadores,
            'detalle'       => $detalle,
        ]);
    }
}
