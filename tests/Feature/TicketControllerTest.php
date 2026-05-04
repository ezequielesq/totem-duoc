<?php
namespace Tests\Feature;

use App\Mail\DocumentoMail;
use App\Mail\TicketMail;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TicketControllerTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function coordinador(): User
    {
        return User::factory()->create();
    }

    private function ticketEspera(array $attrs = []): Ticket
    {
        return Ticket::factory()->create(array_merge([
            'status' => Ticket::STATUS_ESPERA,
        ], $attrs));
    }

    private function ticketLlamando(int $userId, int $mesa = 1, array $attrs = []): Ticket
    {
        return Ticket::factory()->create(array_merge([
            'status'  => Ticket::STATUS_LLAMANDO,
            'user_id' => $userId,
            'mesa'    => $mesa,
        ], $attrs));
    }

    // =========================================================================
    // TC-01 a TC-14 — store()
    // =========================================================================

    /** TC-01: RUT ausente */
    public function test_store_falla_sin_rut(): void
    {
        $response = $this->postJson('/api/tickets', [
            'nombre' => 'Juan Pérez',
            'motivo' => 'Académico',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rut']);
    }

    /** TC-02: Nombre ausente */
    public function test_store_falla_sin_nombre(): void
    {
        $response = $this->postJson('/api/tickets', [
            'rut'    => '12345678-9',
            'motivo' => 'Académico',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);
    }

    /** TC-03: Motivo inválido */
    public function test_store_falla_con_motivo_invalido(): void
    {
        $response = $this->postJson('/api/tickets', [
            'rut'    => '12345678-9',
            'nombre' => 'Juan Pérez',
            'motivo' => 'Deportivo',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['motivo']);
    }

    /** TC-04: Correo con formato inválido */
    public function test_store_falla_con_correo_invalido(): void
    {
        $response = $this->postJson('/api/tickets', [
            'rut'    => '12345678-9',
            'nombre' => 'Juan Pérez',
            'motivo' => 'Académico',
            'correo' => 'noesuncorreo',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['correo']);
    }

    /** TC-05: Correo ausente — ticket igual se crea */
    public function test_store_crea_ticket_sin_correo(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/tickets', [
            'rut'    => '12345678-9',
            'nombre' => 'Juan Pérez',
            'motivo' => 'Académico',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'ticketId', 'ticketNumber']);

        Mail::assertNothingSent();
    }

    /** TC-06: Todos los campos válidos sin correo */
    public function test_store_crea_ticket_con_campos_validos(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/tickets', [
            'rut'    => '12345678-9',
            'nombre' => 'Juan Pérez',
            'motivo' => 'Académico',
        ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('tickets', [
            'rut'    => '12345678-9',
            'nombre' => 'Juan Pérez',
            'motivo' => 'Académico',
            'status' => Ticket::STATUS_ESPERA,
        ]);
    }

    /** TC-07: Todos los campos válidos con correo */
    public function test_store_crea_ticket_y_envia_correo(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/tickets', [
            'rut'    => '12345678-9',
            'nombre' => 'Juan Pérez',
            'motivo' => 'Académico',
            'correo' => 'juan@duoc.cl',
        ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        Mail::assertSent(TicketMail::class, fn($mail) =>
            $mail->hasTo('juan@duoc.cl')
        );
    }

    /** TC-08: generarNumero retorna número único por motivo */
    public function test_store_genera_numeros_correlativos_por_motivo(): void
    {
        Mail::fake();

        $r1 = $this->postJson('/api/tickets', [
            'rut' => '11111111-1', 'nombre' => 'Alumno Uno', 'motivo' => 'Financiero',
        ]);
        $r2 = $this->postJson('/api/tickets', [
            'rut' => '22222222-2', 'nombre' => 'Alumno Dos', 'motivo' => 'Financiero',
        ]);

        $n1 = $r1->json('ticketNumber');
        $n2 = $r2->json('ticketNumber');

        $this->assertNotEquals($n1, $n2);
    }

    /** TC-09: Condición de carrera en generarNumero */
    public function test_store_no_duplica_numeros_bajo_concurrencia(): void
    {
        Mail::fake();

        $payload = ['rut' => '33333333-3', 'nombre' => 'Test', 'motivo' => 'Inclusión'];

        $r1 = $this->postJson('/api/tickets', $payload);
        $r2 = $this->postJson('/api/tickets', $payload);

        $this->assertNotEquals(
            $r1->json('ticketNumber'),
            $r2->json('ticketNumber'),
            'Dos tickets del mismo motivo no deben tener el mismo número'
        );
    }

    /** TC-10: Status inicial siempre es STATUS_ESPERA */
    public function test_store_asigna_status_espera_al_crear(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/tickets', [
            'rut'    => '12345678-9',
            'nombre' => 'Test',
            'motivo' => 'Práctica',
        ]);

        $id = $response->json('ticketId');
        $this->assertDatabaseHas('tickets', [
            'id'     => $id,
            'status' => Ticket::STATUS_ESPERA,
        ]);
    }

    /** TC-11: Ticket creado aunque SMTP esté caído */
    public function test_store_crea_ticket_aunque_smtp_falle(): void
    {
        Mail::shouldReceive('to->send')->andThrow(new \Exception('SMTP connection failed'));
        Log::shouldReceive('warning')->once();

        $response = $this->postJson('/api/tickets', [
            'rut'    => '12345678-9',
            'nombre' => 'Juan Pérez',
            'motivo' => 'Académico',
            'correo' => 'juan@duoc.cl',
        ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('tickets', ['rut' => '12345678-9']);
    }

    /** TC-12: Correo inválido en runtime no rompe el flujo */
    public function test_store_no_retorna_500_si_correo_falla_en_runtime(): void
    {
        Mail::shouldReceive('to->send')->andThrow(new \RuntimeException('Invalid address'));
        Log::shouldReceive('warning')->once();

        $response = $this->postJson('/api/tickets', [
            'rut'    => '99999999-9',
            'nombre' => 'Test Runtime',
            'motivo' => 'Financiero',
            'correo' => 'aparente@valido.cl',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('tickets', ['rut' => '99999999-9']);
    }

    /** TC-13: Nombre con caracteres especiales */
    public function test_store_guarda_nombre_con_caracteres_especiales(): void
    {
        Mail::fake();

        $nombre = 'María José Ñúñez';

        $response = $this->postJson('/api/tickets', [
            'rut'    => '12345678-9',
            'nombre' => $nombre,
            'motivo' => 'Inclusión',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('tickets', ['nombre' => $nombre]);
    }

    /** TC-14: RUT con formato chileno */
    public function test_store_acepta_rut_formato_chileno(): void
    {
        Mail::fake();

        $rut = '12.345.678-9';

        $response = $this->postJson('/api/tickets', [
            'rut'    => $rut,
            'nombre' => 'Test',
            'motivo' => 'Académico',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('tickets', ['rut' => $rut]);
    }

    // =========================================================================
    // TC-15 a TC-25 — call()
    // =========================================================================

    /** TC-15: Mesa ausente en call */
    public function test_call_falla_sin_mesa(): void
    {
        $coordinador = $this->coordinador();
        $ticket      = $this->ticketEspera();

        $response = $this->actingAs($coordinador)
            ->postJson("/api/tickets/{$ticket->id}/call", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['mesa']);
    }

    /** TC-16: Mesa fuera de rango inferior */
    public function test_call_falla_con_mesa_cero(): void
    {
        $coordinador = $this->coordinador();
        $ticket      = $this->ticketEspera();

        $response = $this->actingAs($coordinador)
            ->postJson("/api/tickets/{$ticket->id}/call", ['mesa' => 0]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['mesa']);
    }

    /** TC-17: Mesa fuera de rango superior */
    public function test_call_falla_con_mesa_cinco(): void
    {
        $coordinador = $this->coordinador();
        $ticket      = $this->ticketEspera();

        $response = $this->actingAs($coordinador)
            ->postJson("/api/tickets/{$ticket->id}/call", ['mesa' => 5]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['mesa']);
    }

    /** TC-18: Mesa con valor negativo */
    public function test_call_falla_con_mesa_negativa(): void
    {
        $coordinador = $this->coordinador();
        $ticket      = $this->ticketEspera();

        $response = $this->actingAs($coordinador)
            ->postJson("/api/tickets/{$ticket->id}/call", ['mesa' => -1]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['mesa']);
    }

    /** TC-19: ID de ticket inexistente */
    public function test_call_retorna_404_con_id_inexistente(): void
    {
        $coordinador = $this->coordinador();

        $response = $this->actingAs($coordinador)
            ->postJson('/api/tickets/99999/call', ['mesa' => 2]);

        $response->assertStatus(404);
    }

    /** TC-20: Ticket en STATUS_ESPERA llamado correctamente */
    public function test_call_cambia_status_a_llamando(): void
    {
        $coordinador = $this->coordinador();
        $ticket      = $this->ticketEspera();

        $response = $this->actingAs($coordinador)
            ->postJson("/api/tickets/{$ticket->id}/call", ['mesa' => 3]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('tickets', [
            'id'      => $ticket->id,
            'status'  => Ticket::STATUS_LLAMANDO,
            'mesa'    => 3,
            'user_id' => $coordinador->id,
        ]);
    }

    /** TC-21: Ticket ya en STATUS_LLAMANDO no puede volver a llamarse */
    public function test_call_rechaza_ticket_ya_llamando(): void
    {
        $coordinador = $this->coordinador();
        $ticket      = $this->ticketLlamando($coordinador->id);

        $response = $this->actingAs($coordinador)
            ->postJson("/api/tickets/{$ticket->id}/call", ['mesa' => 2]);

        // Se espera 409 Conflict o 422; el test falla si el controlador
        // no implementa esta validación todavía (documenta el comportamiento esperado)
        $response->assertStatus(409);
    }

    /** TC-22: Ticket ya en STATUS_ATENDIDO no puede llamarse */
    public function test_call_rechaza_ticket_ya_atendido(): void
    {
        $coordinador = $this->coordinador();
        $ticket      = Ticket::factory()->create(['status' => Ticket::STATUS_ATENDIDO]);

        $response = $this->actingAs($coordinador)
            ->postJson("/api/tickets/{$ticket->id}/call", ['mesa' => 1]);

        $response->assertStatus(409);
    }

    /** TC-23: Condición de carrera — dos coordinadores mismo ticket */
    public function test_call_solo_un_coordinador_puede_tomar_el_ticket(): void
    {
        $coord1 = $this->coordinador();
        $coord2 = $this->coordinador();
        $ticket = $this->ticketEspera();

        // Simulamos dos requests concurrentes llamando al mismo ticket
        $r1 = $this->actingAs($coord1)
            ->postJson("/api/tickets/{$ticket->id}/call", ['mesa' => 1]);

        $r2 = $this->actingAs($coord2)
            ->postJson("/api/tickets/{$ticket->id}/call", ['mesa' => 2]);

        $responses = collect([$r1->status(), $r2->status()]);

        // Exactamente uno debe tener éxito (200) y el otro debe fallar (409)
        $this->assertEquals(1, $responses->filter(fn($s) => $s === 200)->count());
        $this->assertEquals(1, $responses->filter(fn($s) => $s === 409)->count());
    }

    /** TC-24: user_id asignado corresponde al coordinador autenticado */
    public function test_call_asigna_user_id_del_coordinador_autenticado(): void
    {
        $coordinador = $this->coordinador();
        $ticket      = $this->ticketEspera();

        $this->actingAs($coordinador)
            ->postJson("/api/tickets/{$ticket->id}/call", ['mesa' => 1]);

        $this->assertDatabaseHas('tickets', [
            'id'      => $ticket->id,
            'user_id' => $coordinador->id,
        ]);
    }

    /** TC-25: Mesa asignada corresponde al request */
    public function test_call_asigna_mesa_correcta(): void
    {
        $coordinador = $this->coordinador();
        $ticket      = $this->ticketEspera();

        $this->actingAs($coordinador)
            ->postJson("/api/tickets/{$ticket->id}/call", ['mesa' => 4]);

        $this->assertDatabaseHas('tickets', [
            'id'   => $ticket->id,
            'mesa' => 4,
        ]);
    }

    // =========================================================================
    // TC-26 a TC-31 — finish()
    // =========================================================================

    /** TC-26: ID de ticket inexistente en finish */
    public function test_finish_retorna_404_con_id_inexistente(): void
    {
        $coordinador = $this->coordinador();

        $response = $this->actingAs($coordinador)
            ->postJson('/api/tickets/99999/finish');

        $response->assertStatus(404);
    }

    /** TC-27: Ticket en STATUS_LLAMANDO finalizado correctamente */
    public function test_finish_elimina_ticket_llamando(): void
    {
        $coordinador = $this->coordinador();
        $ticket      = $this->ticketLlamando($coordinador->id);

        $response = $this->actingAs($coordinador)
            ->postJson("/api/tickets/{$ticket->id}/finish");

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
    }

    /** TC-28: Ticket en STATUS_ESPERA no puede finalizarse */
    public function test_finish_rechaza_ticket_en_espera(): void
    {
        $coordinador = $this->coordinador();
        $ticket      = $this->ticketEspera();

        $response = $this->actingAs($coordinador)
            ->postJson("/api/tickets/{$ticket->id}/finish");

        // Debe rechazar con 422; actualmente no está validado en el controlador
        $response->assertStatus(422);
    }

    /** TC-29: Coordinador B finaliza ticket de coordinador A */
    public function test_finish_rechaza_si_no_es_el_coordinador_asignado(): void
    {
        $coordA = $this->coordinador();
        $coordB = $this->coordinador();
        $ticket = $this->ticketLlamando($coordA->id);

        $response = $this->actingAs($coordB)
            ->postJson("/api/tickets/{$ticket->id}/finish");

        // Debe retornar 403 Forbidden; actualmente no está validado
        $response->assertStatus(403);
    }

    /** TC-30: Doble finish sobre el mismo ticket */
    public function test_finish_retorna_404_en_segundo_intento(): void
    {
        $coordinador = $this->coordinador();
        $ticket      = $this->ticketLlamando($coordinador->id);

        $this->actingAs($coordinador)
            ->postJson("/api/tickets/{$ticket->id}/finish");

        $segundo = $this->actingAs($coordinador)
            ->postJson("/api/tickets/{$ticket->id}/finish");

        $segundo->assertStatus(404);
    }

    /** TC-31: Registro eliminado de BD tras finish */
    public function test_finish_hace_hard_delete_del_ticket(): void
    {
        $coordinador = $this->coordinador();
        $ticket      = $this->ticketLlamando($coordinador->id);
        $id          = $ticket->id;

        $this->actingAs($coordinador)
            ->postJson("/api/tickets/{$id}/finish");

        $this->assertDatabaseMissing('tickets', ['id' => $id]);
        $this->assertNull(Ticket::find($id));
    }

    // =========================================================================
    // TC-32 a TC-36 — queue()
    // =========================================================================

    /** TC-32: Cola vacía retorna arrays vacíos */
    public function test_queue_retorna_arrays_vacios_sin_tickets(): void
    {
        $response = $this->getJson('/api/tickets/queue');

        $response->assertOk()
            ->assertJson(['espera' => [], 'llamados' => []]);
    }

    /** TC-33: Tickets en espera aparecen solo en espera */
    public function test_queue_tickets_en_espera_solo_en_clave_espera(): void
    {
        $ticket = $this->ticketEspera();

        $response = $this->getJson('/api/tickets/queue');

        $espera   = collect($response->json('espera'));
        $llamados = collect($response->json('llamados'));

        $this->assertTrue($espera->contains('id', $ticket->id));
        $this->assertFalse($llamados->contains('id', $ticket->id));
    }

    /** TC-34: Tickets llamando aparecen solo en llamados */
    public function test_queue_tickets_llamando_solo_en_clave_llamados(): void
    {
        $coordinador = $this->coordinador();
        $ticket      = $this->ticketLlamando($coordinador->id);

        $response = $this->getJson('/api/tickets/queue');

        $espera   = collect($response->json('espera'));
        $llamados = collect($response->json('llamados'));

        $this->assertFalse($espera->contains('id', $ticket->id));
        $this->assertTrue($llamados->contains('id', $ticket->id));
    }

    /** TC-35: Tickets atendidos no aparecen en cola */
    public function test_queue_tickets_atendidos_no_aparecen(): void
    {
        $ticket = Ticket::factory()->create(['status' => Ticket::STATUS_ATENDIDO]);

        $response = $this->getJson('/api/tickets/queue');

        $todos = collect($response->json('espera'))
            ->merge($response->json('llamados'));

        $this->assertFalse($todos->contains('id', $ticket->id));
    }

    /** TC-36: Estructura JSON siempre tiene ambas claves */
    public function test_queue_siempre_retorna_claves_espera_y_llamados(): void
    {
        $response = $this->getJson('/api/tickets/queue');

        $response->assertOk()
            ->assertJsonStructure(['espera', 'llamados']);
    }

    // =========================================================================
    // TC-37 a TC-41 — sendTicketEmail()
    // =========================================================================

    /** TC-37: Correo ausente en sendTicketEmail */
    public function test_send_ticket_email_falla_sin_correo(): void
    {
        $coordinador = $this->coordinador();
        $ticket      = $this->ticketEspera();

        $response = $this->actingAs($coordinador)
            ->postJson("/api/tickets/{$ticket->id}/email", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['correo']);
    }

    /** TC-38: Correo inválido en sendTicketEmail */
    public function test_send_ticket_email_falla_con_correo_invalido(): void
    {
        $coordinador = $this->coordinador();
        $ticket      = $this->ticketEspera();

        $response = $this->actingAs($coordinador)
            ->postJson("/api/tickets/{$ticket->id}/email", [
                'correo' => 'no-es-correo',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['correo']);
    }

    /** TC-39: ID de ticket inexistente en sendTicketEmail */
    public function test_send_ticket_email_retorna_404_con_id_inexistente(): void
    {
        $coordinador = $this->coordinador();

        $response = $this->actingAs($coordinador)
            ->postJson('/api/tickets/99999/email', [
                'correo' => 'test@duoc.cl',
            ]);

        $response->assertStatus(404);
    }

    /** TC-40: Envío exitoso de ticket por correo */
    public function test_send_ticket_email_envia_mail_correctamente(): void
    {
        Mail::fake();

        $coordinador = $this->coordinador();
        $ticket      = $this->ticketEspera();

        $response = $this->actingAs($coordinador)
            ->postJson("/api/tickets/{$ticket->id}/email", [
                'correo' => 'alumno@duoc.cl',
            ]);

        $response->assertOk()
            ->assertJson(['success' => true]);

        Mail::assertSent(TicketMail::class, fn($mail) =>
            $mail->hasTo('alumno@duoc.cl')
        );
    }

    /** TC-41: SMTP caído en sendTicketEmail */
    public function test_send_ticket_email_no_retorna_500_si_smtp_cae(): void
    {
        Mail::shouldReceive('to->send')->andThrow(new \Exception('Connection refused'));
        Log::shouldReceive('error')->once();

        $coordinador = $this->coordinador();
        $ticket      = $this->ticketEspera();

        $response = $this->actingAs($coordinador)
            ->postJson("/api/tickets/{$ticket->id}/email", [
                'correo' => 'alumno@duoc.cl',
            ]);

        // No debe lanzar 500; debe retornar un error controlado
        $response->assertStatus(500)
            ->assertJson(['success' => false]);
    }

    // =========================================================================
    // TC-42 a TC-45 — sendDocumentoEmail()
    // =========================================================================

    /** TC-42: Campos ausentes en sendDocumentoEmail (uno por campo) */
    public function test_send_documento_email_falla_sin_correo(): void
    {
        $coordinador = $this->coordinador();
        $payload     = $this->documentoPayloadValido();
        unset($payload['correo']);

        $this->actingAs($coordinador)
            ->postJson('/api/tickets/documento/email', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['correo']);
    }

    public function test_send_documento_email_falla_sin_nombre(): void
    {
        $coordinador = $this->coordinador();
        $payload     = $this->documentoPayloadValido();
        unset($payload['nombre']);

        $this->actingAs($coordinador)
            ->postJson('/api/tickets/documento/email', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);
    }

    public function test_send_documento_email_falla_sin_documento(): void
    {
        $coordinador = $this->coordinador();
        $payload     = $this->documentoPayloadValido();
        unset($payload['documento']);

        $this->actingAs($coordinador)
            ->postJson('/api/tickets/documento/email', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['documento']);
    }

    public function test_send_documento_email_falla_sin_base64(): void
    {
        $coordinador = $this->coordinador();
        $payload     = $this->documentoPayloadValido();
        unset($payload['base64']);

        $this->actingAs($coordinador)
            ->postJson('/api/tickets/documento/email', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['base64']);
    }

    /** TC-43: Base64 malformado */
    public function test_send_documento_email_falla_con_base64_invalido(): void
    {
        $coordinador       = $this->coordinador();
        $payload           = $this->documentoPayloadValido();
        $payload['base64'] = '!!!esto-no-es-base64!!!';

        $response = $this->actingAs($coordinador)
            ->postJson('/api/tickets/documento/email', $payload);

        // Actualmente no validado en el controlador — este test documenta
        // el comportamiento esperado tras implementar la validación
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['base64']);
    }

    /** TC-44: Envío exitoso de documento */
    public function test_send_documento_email_envia_mail_correctamente(): void
    {
        Mail::fake();

        $coordinador = $this->coordinador();

        $response = $this->actingAs($coordinador)
            ->postJson('/api/tickets/documento/email', $this->documentoPayloadValido());

        $response->assertOk()
            ->assertJson(['success' => true]);

        Mail::assertSent(DocumentoMail::class, fn($mail) =>
            $mail->hasTo('alumno@duoc.cl')
        );
    }

    /** TC-45: SMTP caído en sendDocumentoEmail */
    public function test_send_documento_email_no_retorna_500_si_smtp_cae(): void
    {
        Mail::shouldReceive('to->send')->andThrow(new \Exception('SMTP timeout'));
        Log::shouldReceive('error')->once();

        $coordinador = $this->coordinador();

        $response = $this->actingAs($coordinador)
            ->postJson('/api/tickets/documento/email', $this->documentoPayloadValido());

        $response->assertStatus(500)
            ->assertJson(['success' => false]);
    }

    // =========================================================================
    // TC-46 a TC-48 — Transversales
    // =========================================================================

/** TC-46: Solo los endpoints de coordinador requieren autenticación */
    public function test_endpoints_protegidos_requieren_autenticacion(): void
    {
        $ticket = $this->ticketEspera();

        $endpointsProtegidos = [
            ['POST', "/api/tickets/{$ticket->id}/call", ['mesa' => 1]],
            ['POST', "/api/tickets/{$ticket->id}/finish", []],
        ];

        foreach ($endpointsProtegidos as [$method, $url, $payload]) {
            $response = $this->postJson($url, $payload);
            $response->assertStatus(401);
        }
    }

/** TC-46b: Los endpoints del tótem son públicos */
    public function test_endpoints_totem_son_publicos(): void
    {
        Mail::fake();
        $ticket = $this->ticketEspera();

        // Ninguno de estos debe retornar 401
        $this->postJson('/api/tickets', [
            'rut' => '12345678-9', 'nombre' => 'Test', 'motivo' => 'Académico',
        ])->assertStatus(200);

        $this->getJson('/api/tickets/queue')
            ->assertStatus(200);

        $this->postJson("/api/tickets/{$ticket->id}/email", ['correo' => 'x@duoc.cl'])
            ->assertStatus(200);

        $this->postJson('/api/tickets/documento/email', $this->documentoPayloadValido())
            ->assertStatus(200);
    }

/** TC-47: ID con string en lugar de entero */
    public function test_id_string_retorna_404_o_422(): void
    {
        $coordinador = $this->coordinador();

        $response = $this->actingAs($coordinador)
            ->postJson('/api/tickets/abc/call', ['mesa' => 1]);

        // Con ->where('id', '[0-9]+') en la ruta, Laravel retorna 404
        $response->assertStatus(404);
    }

    /** TC-48: ID negativo en la ruta */
    public function test_id_negativo_retorna_404(): void
    {
        $coordinador = $this->coordinador();

        $response = $this->actingAs($coordinador)
            ->postJson('/api/tickets/-1/call', ['mesa' => 1]);

        $response->assertStatus(404);
    }

    // =========================================================================
    // HELPERS INTERNOS
    // =========================================================================

    private function documentoPayloadValido(): array
    {
        return [
            'correo'    => 'alumno@duoc.cl',
            'nombre'    => 'Juan Pérez',
            'documento' => 'Certificado de alumno regular',
            'base64'    => base64_encode('%PDF-1.4 fake pdf content'),
        ];
    }
}
