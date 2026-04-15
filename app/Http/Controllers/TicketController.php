<?php
namespace App\Http\Controllers;

use App\Mail\DocumentoMail;
use App\Mail\TicketMail;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TicketController extends Controller
{
    /**
     * Crea un nuevo ticket.
     * Reemplaza crear_ticket.php
     * POST /api/tickets
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'rut'    => 'required|string',
            'nombre' => 'required|string',
            'motivo' => 'required|in:Académico,Práctica,Inclusión,Financiero',
        ]);

        $ticketNumero = Ticket::generarNumero($request->motivo);

        $ticket = Ticket::create([
            'rut'           => $request->rut,
            'nombre'        => $request->nombre,
            'motivo'        => $request->motivo,
            'ticket_numero' => $ticketNumero,
            'status'        => Ticket::STATUS_ESPERA,
        ]);

        return response()->json([
            'success'      => true,
            'ticketId'     => $ticket->id,
            'ticketNumber' => $ticket->ticket_numero,
        ]);
    }

    /**
     * Retorna la cola actual.
     * Reemplaza api_turnos.php
     * GET /api/tickets/queue
     */
    public function queue(): JsonResponse
    {
        return response()->json([
            'espera'   => Ticket::enEspera()->get(),
            'llamados' => Ticket::llamando()->get(),
        ]);
    }

    /**
     * Llama a un ticket (cambia status a llamando).
     * Reemplaza gestionar_ticket.php accion=llamar
     * POST /api/tickets/{id}/call
     */
public function call(Request $request, int $id): JsonResponse
{
    $request->validate([
        'mesa' => 'required|integer|between:1,4',
    ]);

    $ticket = Ticket::findOrFail($id);

    $ticket->update([
        'status'  => Ticket::STATUS_LLAMANDO,
        'mesa'    => $request->mesa,
        'user_id' => auth()->id(),
    ]);

    return response()->json(['success' => true]);
}

    /**
     * Finaliza la atención de un ticket.
     * Reemplaza gestionar_ticket.php accion=finalizar
     * POST /api/tickets/{id}/finish
     */
    public function finish(int $id): JsonResponse
    {
        $ticket = Ticket::findOrFail($id);

        $ticket->update([
            'status' => Ticket::STATUS_ATENDIDO,
        ]);

        $ticket->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Muestra el panel del coordinador.
     * Reemplaza asesor.php
     * GET /asesor
     */
    public function panel()
    {
        return view('asesor.index');
    }

    // Métodos adicionales para enviar correos
    /**
     * Envía el ticket por correo al estudiante.
     * Reemplaza enviar_ticket.php
     * POST /api/tickets/{id}/email
     */
    public function sendTicketEmail(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'correo' => 'required|email',
        ]);

        $ticket = Ticket::findOrFail($id);

        Mail::to($request->correo)->send(new TicketMail($ticket));

        return response()->json(['success' => true]);
    }

/**
 * Envía un documento PDF por correo al estudiante.
 * Reemplaza enviar_documento.php
 * POST /api/tickets/email/documento
 */
    public function sendDocumentoEmail(Request $request): JsonResponse
    {
        $request->validate([
            'correo'    => 'required|email',
            'nombre'    => 'required|string',
            'documento' => 'required|string',
            'base64'    => 'required|string',
        ]);

        Mail::to($request->correo)->send(new DocumentoMail(
            $request->nombre,
            $request->documento,
            $request->base64
        ));

        return response()->json(['success' => true]);
    }
}
