/**
 * Servicio de Tickets — Integración con Laravel
 * Reemplaza el ticketService.js de la maqueta
 */

const TICKET_API_BASE = '/api';

const AREA_TO_MOTIVO = {
    'ACA': 'Académico',
    'PRA': 'Práctica',
    'INC': 'Inclusión',
    'FIN': 'Financiero',
};

/**
 * Obtiene el CSRF token del meta tag del HTML
 */
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

/**
 * Crea un ticket de atención.
 * @param {string} rut
 * @param {string} nombre
 * @param {string} areaPrefix - ACA, PRA, INC, FIN
 */
export async function createTicket(rut, nombre, areaPrefix) {
    const motivo = AREA_TO_MOTIVO[areaPrefix] || 'Académico';

    try {
        console.log(`[TicketService] Creando ticket: ${nombre} (${rut}) - ${motivo}`);

        const response = await fetch(`${TICKET_API_BASE}/tickets`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({ rut, nombre, motivo })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            console.log(`[TicketService] Ticket creado: ${data.ticketNumber}`);
            return {
                success: true,
                ticketNumber: data.ticketNumber,
                ticketId: data.ticketId
            };
        } else {
            console.warn('[TicketService] Error del servidor:', data);
            return {
                success: false,
                error: data.message || 'Error al crear ticket'
            };
        }

    } catch (error) {
        console.error('[TicketService] Error de red:', error);
        return {
            success: false,
            error: 'Error de conexión con el servidor'
        };
    }
}

/**
 * Obtiene la cola de espera actual.
 */
export async function getQueue() {
    try {
        const response = await fetch(`${TICKET_API_BASE}/tickets/queue`);
        return await response.json();
    } catch (error) {
        console.error('[TicketService] Error obteniendo cola:', error);
        return { espera: [], llamados: [] };
    }
}
