let miMesa = null;
let estoyOcupado = false;

// API_BASE viene desde la vista Blade: {{ url("/api") }}

function iniciarSesion() {
    miMesa = document.getElementById('mesaSelector').value;
    document.getElementById('labelMesa').innerText = "ASESOR - MESA " + miMesa;
    document.getElementById('setup').style.display = "none";
    document.getElementById('mainPanel').style.display = "block";

    cargarTickets();
    setInterval(cargarTickets, 2000);
}

async function cargarTickets() {
    try {
        const response = await fetch(`${API_BASE}/tickets/queue`);
        const data = await response.json();

        const lista = document.getElementById('listaEspera');
        const area = document.getElementById('areaAtencion');
        const otrosList = document.getElementById('otrosAsesoresList');

        lista.innerHTML = "";
        otrosList.innerHTML = "";

        // Separar mi ticket del de otros asesores
        let miTicket = null;
        let otrosTickets = [];

        if (data.llamados && Array.isArray(data.llamados)) {
            data.llamados.forEach(ticket => {
                if (ticket.mesa == miMesa) {
                    miTicket = ticket;
                } else {
                    otrosTickets.push(ticket);
                }
            });
        }

        // Mostrar ticket que estoy atendiendo
        if (miTicket) {
            estoyOcupado = true;
            area.innerHTML = `
                <div style="color:#666;">ATENDIENDO AHORA:</div>
                <div class="big-ticket">${miTicket.ticket_numero}</div>
                <div class="student-details">
                    <strong>${miTicket.nombre}</strong>
                    ${miTicket.rut} — ${miTicket.motivo}
                </div>
                <button class="btn-finish" onclick="finalizar(${miTicket.id})">
                    Finalizar Atención
                </button>
            `;
        } else {
            estoyOcupado = false;
            area.innerHTML = `
                <p class="empty-attention">
                    Mesa Disponible.<br>Llama a un alumno.
                </p>
            `;
        }

        // Mostrar otros asesores
        if (otrosTickets.length > 0) {
            otrosTickets.forEach(t => {
                otrosList.innerHTML += `
                    <div class="advisor-card">
                        <span class="advisor-badge">Mesa ${t.mesa}</span>
                        <div>
                            <strong>${t.ticket_numero}</strong> — ${t.nombre}
                        </div>
                    </div>
                `;
            });
        } else {
            otrosList.innerHTML = `
                <p style="color:#999; font-size:0.9rem;">
                    Nadie más está atendiendo.
                </p>
            `;
        }

        // Mostrar lista de espera
        if (data.espera && data.espera.length > 0) {
            data.espera.forEach(t => {
                const disabled = estoyOcupado ? 'disabled' : '';
                lista.innerHTML += `
                    <div class="ticket-card">
                        <div class="ticket-info">
                            <span>${t.ticket_numero}</span>
                            <small>${t.nombre} — ${t.motivo}</small>
                        </div>
                        <button class="btn-call ${disabled ? 'btn-disabled' : ''}"
                            ${disabled}
                            onclick="llamar(${t.id})">
                            Llamar
                        </button>
                    </div>
                `;
            });
        } else {
            lista.innerHTML = `
                <p class="empty-state">No hay alumnos esperando.</p>
            `;
        }

    } catch (error) {
        console.error('Error cargando tickets:', error);
    }
}

async function llamar(id) {
    if (estoyOcupado) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    await fetch(`${API_BASE}/tickets/${id}/call`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ mesa: miMesa })
    });

    cargarTickets();
}

async function finalizar(id) {
    if (!confirm('¿Terminar atención?')) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    await fetch(`${API_BASE}/tickets/${id}/finish`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({})
    });

    cargarTickets();
}

// Exponer funciones al HTML
window.iniciarSesion = iniciarSesion;
window.llamar = llamar;
window.finalizar = finalizar;
