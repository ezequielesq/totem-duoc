<?php
namespace App\Http\Controllers;

use App\Mail\DocumentoMail;
use App\Mail\TicketMail;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TicketController extends Controller
{
    // =========================================================================
    // store()
    // Cubre: TC-01 al TC-14
    // POST /api/tickets
    // =========================================================================

    public function store(Request $request): JsonResponse
    {
        // TC-01: rut requerido
        // TC-02: nombre requerido
        // TC-03: motivo solo acepta valores definidos
        // TC-04: correo debe ser email válido si se envía
        $request->validate([
            'rut'    => 'required|string',
            'nombre' => 'required|string',
            'motivo' => 'required|in:Académico,Práctica,Inclusión,Financiero',
            'correo' => 'nullable|email',
        ]);

        // TC-08, TC-09: generarNumero debe ser atómico para evitar duplicados
        // Se ejecuta dentro de una transacción con bloqueo para garantizar
        // que dos requests simultáneos no obtengan el mismo número
        $ticket = DB::transaction(function () use ($request) {
            // lockForUpdate en la consulta interna de generarNumero previene
            // condición de carrera; el modelo debe implementarlo así
            $ticketNumero = Ticket::generarNumero($request->motivo);

            // TC-10: status inicial siempre STATUS_ESPERA
            return Ticket::create([
                'rut'           => $request->rut,
                'nombre'        => $request->nombre,
                'motivo'        => $request->motivo,
                'ticket_numero' => $ticketNumero,
                'status'        => Ticket::STATUS_ESPERA,
            ]);
        });

        // TC-05: si no viene correo, no se intenta enviar
        // TC-07: si viene correo válido, se envía el mail
        // TC-11, TC-12: si el SMTP falla, el ticket ya está creado y
        //               se loguea el error sin romper la respuesta
        if ($request->correo) {
            try {
                Mail::to($request->correo)->send(new TicketMail($ticket));
            } catch (\Exception $e) {
                Log::warning('Correo de ticket no enviado', [
                    'ticket_id' => $ticket->id,
                    'correo'    => $request->correo,
                    'error'     => $e->getMessage(),
                ]);
                // No se relanza: el ticket existe, el correo es secundario
            }
        }

        // TC-06: respuesta incluye success, ticketId y ticketNumber
        return response()->json([
            'success'      => true,
            'ticketId'     => $ticket->id,
            'ticketNumber' => $ticket->ticket_numero,
        ]);
    }

    // =========================================================================
    // queue()
    // Cubre: TC-32 al TC-36
    // GET /api/tickets/queue
    // =========================================================================

    public function queue(): JsonResponse
    {
        // TC-32: sin tickets retorna arrays vacíos en ambas claves
        // TC-33: STATUS_ESPERA solo aparece en 'espera'
        // TC-34: STATUS_LLAMANDO solo aparece en 'llamados'
        // TC-35: STATUS_ATENDIDO (soft/hard deleted) no aparece en ninguna
        // TC-36: la estructura siempre tiene ambas claves
        return response()->json([
            'espera'   => Ticket::enEspera()->get(),
            'llamados' => Ticket::llamando()->get(),
        ]);
    }

    // =========================================================================
    // call()
    // Cubre: TC-15 al TC-25
    // POST /api/tickets/{id}/call
    // =========================================================================

    public function call(Request $request, int $id): JsonResponse
    {
        // TC-15: mesa requerida
        // TC-16, TC-17, TC-18: mesa debe ser entero entre 1 y 4
        $request->validate([
            'mesa' => 'required|integer|between:1,4',
        ]);

        // TC-23: lockForUpdate garantiza que dos coordinadores no puedan
        //        tomar el mismo ticket simultáneamente. El segundo request
        //        esperará a que la transacción del primero termine y luego
        //        verá el status ya actualizado, recibiendo un 409.
        return DB::transaction(function () use ($request, $id) {

            // TC-19: findOrFail retorna 404 si el ticket no existe
            $ticket = Ticket::lockForUpdate()->findOrFail($id);

            // TC-21: rechazar si ya está siendo atendido
            // TC-22: rechazar si ya fue atendido
            // TC-23: el segundo coordinador llegará aquí y verá status != ESPERA
            if ($ticket->status !== Ticket::STATUS_ESPERA) {
                abort(409, 'El ticket ya fue tomado o está en un estado que no permite ser llamado.');
            }

            // TC-20: cambia status a LLAMANDO
            // TC-24: asigna user_id del coordinador autenticado
            // TC-25: asigna la mesa del request
            $ticket->update([
                'status'  => Ticket::STATUS_LLAMANDO,
                'mesa'    => $request->mesa,
                'user_id' => auth()->id(),
            ]);

            return response()->json(['success' => true]);
        });
    }

    // =========================================================================
    // finish()
    // Cubre: TC-26 al TC-31
    // POST /api/tickets/{id}/finish
    // =========================================================================

    public function finish(int $id): JsonResponse
    {
        // TC-26: findOrFail retorna 404 si el ticket no existe
        // TC-30: segundo intento también retorna 404 porque el registro fue eliminado
        $ticket = Ticket::findOrFail($id);

        // TC-28: solo se pueden finalizar tickets en estado LLAMANDO
        if ($ticket->status !== Ticket::STATUS_LLAMANDO) {
            abort(422, 'Solo se pueden finalizar tickets que estén en estado llamando.');
        }

        // TC-29: solo el coordinador asignado puede finalizar el ticket
        if ($ticket->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para finalizar este ticket.');
        }

        // TC-27: cambia status a ATENDIDO y elimina el registro
        // TC-31: hard delete confirmado — assertDatabaseMissing pasará
        $ticket->update(['status' => Ticket::STATUS_ATENDIDO]);
        $ticket->delete();

        return response()->json(['success' => true]);
    }

    // =========================================================================
    // panel() y pantalla()
    // =========================================================================

    public function panel()
    {
        return view('asesor.index');
    }

    public function pantalla()
    {
        return view('pantalla.index');
    }

    // =========================================================================
    // sendTicketEmail()
    // Cubre: TC-37 al TC-41
    // POST /api/tickets/{id}/email
    // =========================================================================

    public function sendTicketEmail(Request $request, int $id): JsonResponse
    {
        // TC-37: correo requerido
        // TC-38: correo debe tener formato válido
        $request->validate([
            'correo' => 'required|email',
        ]);

        // TC-39: findOrFail retorna 404 si el ticket no existe
        $ticket = Ticket::findOrFail($id);

        // TC-40: mail enviado correctamente cuando todo es válido
        // TC-41: SMTP caído retorna error controlado, no 500 genérico
        try {
            Mail::to($request->correo)->send(new TicketMail($ticket));
        } catch (\Exception $e) {
            Log::error('Error enviando ticket por correo', [
                'ticket_id' => $id,
                'correo'    => $request->correo,
                'error'     => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error'   => 'No se pudo enviar el correo. Intenta nuevamente.',
            ], 500);
        }

        return response()->json(['success' => true]);
    }

    // =========================================================================
    // sendDocumentoEmail()
    // Cubre: TC-42 al TC-45
    // POST /api/tickets/documento/email
    // =========================================================================

    public function sendDocumentoEmail(Request $request): JsonResponse
    {
        // TC-42: todos los campos son requeridos (correo, nombre, documento, base64)
        // TC-43: base64 debe ser una cadena base64 válida
        $request->validate([
            'correo'    => 'required|email',
            'nombre'    => 'required|string',
            'documento' => 'required|string',
            'base64'    => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (base64_decode($value, strict: true) === false) {
                        $fail('El campo base64 no contiene datos válidos.');
                    }
                },
            ],
        ]);

        // TC-44: mail enviado correctamente cuando todo es válido
        // TC-45: SMTP caído retorna error controlado, no 500 genérico
        try {
            Mail::to($request->correo)->send(new DocumentoMail(
                $request->nombre,
                $request->documento,
                $request->base64
            ));
        } catch (\Exception $e) {
            Log::error('Error enviando documento por correo', [
                'correo'    => $request->correo,
                'documento' => $request->documento,
                'error'     => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error'   => 'No se pudo enviar el documento. Intenta nuevamente.',
            ], 500);
        }

        return response()->json(['success' => true]);
    }
}
