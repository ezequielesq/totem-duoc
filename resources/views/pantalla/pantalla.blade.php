@extends('layouts.app')

@section('title', 'Pantalla de Turnos — Duoc UC')

@section('content')

<div style="display:flex; flex-direction:column; height:100vh; overflow:hidden;" class="duoc-bg-gris-claro">

    {{-- Header --}}
    <header class="duoc-bg-azul w3-padding" style="display:flex; justify-content:space-between; align-items:center; border-bottom:4px solid var(--amarillo);">
        <div>
            <img src="https://www.duoc.cl/wp-content/uploads/2020/03/logo-duoc.png" height="36" style="filter:brightness(0) invert(1);">
            <p class="w3-small w3-margin-top" style="color:var(--amarillo); margin:0;">Sede San Bernardo — Sistema de Turnos</p>
        </div>
        <div id="reloj" class="w3-text-white w3-xxlarge" style="font-weight:900; font-variant-numeric:tabular-nums;">--:--:--</div>
    </header>

    {{-- Contenido --}}
    <div style="display:grid; grid-template-columns:2fr 1fr; gap:16px; padding:16px; flex:1; overflow:hidden;">

        {{-- Ticket llamado --}}
        <div id="areaCalling" class="w3-card w3-round duoc-bg-blanco" style="display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; padding:40px;">
            <p class="sin-llamado">Sin llamados activos</p>
        </div>

        {{-- Lista de espera --}}
        <div class="w3-card w3-round" style="display:flex; flex-direction:column; overflow:hidden;">
            <div class="duoc-bg-azul w3-padding w3-round-top" style="border-bottom:2px solid var(--amarillo);">
                <h4 class="w3-text-white" style="margin:0;">En Espera</h4>
            </div>
            <div id="listaEspera" class="duoc-bg-blanco" style="flex:1; overflow-y:auto; padding:8px;">
                <p class="sin-espera">No hay alumnos esperando</p>
            </div>
        </div>

    </div>

    {{-- Footer --}}
    <footer class="duoc-bg-azul w3-center w3-padding w3-small w3-text-white">
        Tótem de Autoservicio — Duoc UC San Bernardo
    </footer>

</div>

@endsection

@section('scripts')
<script>
    // Reloj
    function actualizarReloj() {
        document.getElementById('reloj').innerText = new Date().toLocaleTimeString('es-CL');
    }
    setInterval(actualizarReloj, 1000);
    actualizarReloj();

    // Cargar tickets
    async function cargarTurnos() {
        try {
            const response = await fetch('/api/tickets/queue');
            const data = await response.json();

            const areaCalling = document.getElementById('areaCalling');
            const listaEspera = document.getElementById('listaEspera');

            // Llamados
            if (data.llamados && data.llamados.length > 0) {
                areaCalling.innerHTML = data.llamados.map(t => `
                    <div style="text-align:center; flex:1;">
                        <p class="llamando-label">Mesa ${t.mesa}</p>
                        <div class="llamando-numero">${t.ticket_numero}</div>
                        <div class="llamando-nombre">${t.nombre}</div>
                    </div>
                `).join('<div style="width:2px; background:var(--gris-claro);"></div>');
                areaCalling.style.display = 'flex';
                areaCalling.style.flexDirection = 'row';
                areaCalling.style.alignItems = 'center';
                areaCalling.style.gap = '10px';
            } else {
                areaCalling.innerHTML = `<p class="sin-llamado">Sin llamados activos</p>`;
                areaCalling.style.flexDirection = 'column';
            }

            // Espera
            if (data.espera && data.espera.length > 0) {
                listaEspera.innerHTML = data.espera.map(t => `
                    <div class="espera-item">
                        <span class="numero">${t.ticket_numero}</span>
                        <div class="detalle">
                            ${t.nombre}<br>
                            <small>${t.motivo}</small>
                        </div>
                    </div>
                `).join('');
            } else {
                listaEspera.innerHTML = `<p class="sin-espera">No hay alumnos esperando</p>`;
            }

        } catch (error) {
            console.error('Error cargando turnos:', error);
        }
    }

    setInterval(cargarTurnos, 3000);
    cargarTurnos();
</script>
@endsection
