/**
 * Servicio de Emails — Integración con Laravel
 * Reemplaza el emailService.js de la maqueta
 */

const EMAIL_API_BASE = '/api';

/**
 * Obtiene el CSRF token del meta tag del HTML
 */
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

/**
 * Envía el ticket por correo al estudiante.
 * @param {Object} userData - { nombre, correo }
 * @param {number} ticketId - ID del ticket en la BD
 */
export async function sendTicketEmail(userData, ticketId) {
    if (!userData?.correo) return false;

    try {
        const response = await fetch(`${EMAIL_API_BASE}/tickets/${ticketId}/email`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({
                correo: userData.correo
            })
        });

        const result = await response.json();
        if (result.success) {
            console.log('Ticket enviado por correo');
            return true;
        }
        console.error('Error servidor:', result.message);
        return false;

    } catch (error) {
        console.error('Error enviando ticket:', error);
        return false;
    }
}

/**
 * Envía un documento PDF por correo.
 * @param {Object} userData - { nombre, correo }
 * @param {string} tipoDocumento - 'Certificado de Alumno Regular' | 'Horario de Clases'
 * @param {string} base64Pdf - PDF en base64
 */
export async function sendDocumentoEmail(userData, tipoDocumento, base64Pdf) {
    if (!userData?.correo) return false;

    try {
        const response = await fetch(`${EMAIL_API_BASE}/tickets/email/documento`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({
                correo:    userData.correo,
                nombre:    userData.nombre,
                documento: tipoDocumento,
                base64:    base64Pdf || ''
            })
        });

        const result = await response.json();
        if (result.success) {
            console.log(`${tipoDocumento} enviado por correo`);
            return true;
        }
        console.error('Error servidor:', result.message);
        return false;

    } catch (error) {
        console.error('Error enviando documento:', error);
        return false;
    }
}

/**
 * Envía el certificado por correo.
 */
export async function sendCertificadoEmail(userData, base64Pdf) {
    return sendDocumentoEmail(userData, 'Certificado de Alumno Regular', base64Pdf);
}

/**
 * Envía el horario por correo.
 */
export async function sendHorarioEmail(userData, base64Pdf) {
    return sendDocumentoEmail(userData, 'Horario de Clases', base64Pdf);
}
