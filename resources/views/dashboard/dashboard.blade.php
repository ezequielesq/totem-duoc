@extends('layouts.app')

@section('title', 'Dashboard — Duoc UC')

@section('content')

    <div style="display:flex; flex-direction:column; min-height:100vh;" class="duoc-bg-gris-claro">

        {{-- Header --}}
        <header class="duoc-bg-azul w3-padding"
            style="display:flex; align-items:center; justify-content:space-between; border-bottom:4px solid var(--amarillo);">
            <img src="https://www.duoc.cl/wp-content/uploads/2020/03/logo-duoc.png" height="36"
                style="filter:brightness(0) invert(1);">
            <span class="w3-text-white w3-large" style="font-weight:900; letter-spacing:2px;">DASHBOARD</span>
            <div style="display:flex; gap:8px; align-items:center;">
                <span class="w3-text-white w3-small">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w3-button w3-round w3-small duoc-bg-rojo">Cerrar Sesión</button>
                </form>
                <button onclick="window.print()" class="w3-button w3-round w3-small duoc-bg-gris-medio">Exportar PDF</button>
            </div>
        </header>

        <div class="w3-padding">

            {{-- Filtros --}}
            <div class="w3-card w3-round duoc-bg-blanco w3-padding w3-margin-bottom">
                <div style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
                    <div>
                        <label class="w3-small duoc-azul"><b>Desde</b></label>
                        <input type="date" id="filtroDesde" class="w3-input w3-border w3-round" style="width:160px;">
                    </div>
                    <div>
                        <label class="w3-small duoc-azul"><b>Hasta</b></label>
                        <input type="date" id="filtroHasta" class="w3-input w3-border w3-round" style="width:160px;">
                    </div>
                    <div>
                        <label class="w3-small duoc-azul"><b>Coordinador</b></label>
                        <select id="filtroCoordinador" class="w3-select w3-border w3-round" style="width:200px;">
                            <option value="">Todos</option>
                            @foreach ($coordinadores as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button onclick="aplicarFiltros()" class="w3-button w3-round duoc-bg-azul" style="height:38px;">
                            <b>Aplicar</b>
                        </button>
                    </div>
                    <div style="display:flex; gap:6px; margin-left:auto;">
                        <button onclick="setRango('hoy')" class="w3-button w3-round w3-border w3-small" style="border-color:var(--azul); color:var(--azul);">Hoy</button>
                        <button onclick="setRango('semana')" class="w3-button w3-round w3-border w3-small" style="border-color:var(--azul); color:var(--azul);">Esta semana</button>
                        <button onclick="setRango('mes')" class="w3-button w3-round w3-border w3-small" style="border-color:var(--azul); color:var(--azul);">Este mes</button>
                    </div>
                </div>
            </div>

            {{-- Alertas --}}
            <div id="seccionAlertas" style="display:none;" class="w3-margin-bottom">
                <div id="alertaEspera" style="display:none; background:var(--gris-claro) !important;" class="w3-panel w3-round">
                    <h4 style="color:var(--gris-carbon); margin:0 0 8px 0;">⚠ Tickets esperando más de 15 minutos</h4>
                    <div id="listaAlertaEspera"></div>
                </div>
                <div id="alertaMesas" style="display:none; background:var(--gris-claro) !important;" class="w3-panel w3-round w3-margin-top">
                    <h4 style="color:var(--gris-carbon); margin:0 0 8px 0;">⚠ Mesas sin actividad más de 30 minutos</h4>
                    <div id="listaAlertaMesas"></div>
                </div>
            </div>

            {{-- KPIs --}}
            <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:12px; margin-bottom:16px;">
                <div class="w3-card w3-round duoc-bg-blanco w3-padding w3-center">
                    <div class="w3-small" style="color:var(--gris-medio);">Total tickets</div>
                    <div id="kpiTotal" style="font-size:36px; font-weight:900; color:var(--azul);">—</div>
                </div>
                <div class="w3-card w3-round duoc-bg-blanco w3-padding w3-center">
                    <div class="w3-small" style="color:var(--gris-medio);">Finalizados</div>
                    <div id="kpiFinalizados" style="font-size:36px; font-weight:900; color:var(--verde);">—</div>
                    <div id="kpiTasa" class="w3-small" style="color:var(--gris-medio);"></div>
                </div>
                <div class="w3-card w3-round duoc-bg-blanco w3-padding w3-center">
                    <div class="w3-small" style="color:var(--gris-medio);">En espera ahora</div>
                    <div id="kpiEspera" style="font-size:36px; font-weight:900; color:var(--amarillo);">—</div>
                </div>
                <div class="w3-card w3-round duoc-bg-blanco w3-padding w3-center">
                    <div class="w3-small" style="color:var(--gris-medio);">En atención ahora</div>
                    <div id="kpiAtencion" style="font-size:36px; font-weight:900; color:var(--azul);">—</div>
                </div>
                <div class="w3-card w3-round duoc-bg-blanco w3-padding w3-center">
                    <div class="w3-small" style="color:var(--gris-medio);">T. promedio espera</div>
                    <div id="kpiPromedioEspera" style="font-size:28px; font-weight:900; color:var(--azul);">—</div>
                    <div class="w3-small" style="color:var(--gris-medio);">minutos</div>
                </div>
                <div class="w3-card w3-round duoc-bg-blanco w3-padding w3-center">
                    <div class="w3-small" style="color:var(--gris-medio);">T. promedio atención</div>
                    <div id="kpiPromedioAtencion" style="font-size:28px; font-weight:900; color:var(--azul);">—</div>
                    <div class="w3-small" style="color:var(--gris-medio);">minutos</div>
                </div>
                <div class="w3-card w3-round duoc-bg-blanco w3-padding w3-center">
                    <div class="w3-small" style="color:var(--gris-medio);">Hora pico</div>
                    <div id="kpiHoraPico" style="font-size:28px; font-weight:900; color:var(--azul);">—</div>
                </div>
                <div class="w3-card w3-round duoc-bg-blanco w3-padding w3-center">
                    <div class="w3-small" style="color:var(--gris-medio);">Área más demandada</div>
                    <div id="kpiArea" style="font-size:20px; font-weight:900; color:var(--azul);">—</div>
                </div>
                <div class="w3-card w3-round duoc-bg-blanco w3-padding w3-center">
                    <div class="w3-small" style="color:var(--gris-medio);">Coordinador top</div>
                    <div id="kpiCoordinadorTop" style="font-size:18px; font-weight:900; color:var(--azul);">—</div>
                </div>
                <div class="w3-card w3-round duoc-bg-blanco w3-padding w3-center">
                    <div class="w3-small" style="color:var(--gris-medio);">Mesas activas</div>
                    <div id="kpiMesas" style="font-size:36px; font-weight:900; color:var(--azul);">—</div>
                </div>
                <div class="w3-card w3-round duoc-bg-blanco w3-padding w3-center" style="grid-column:span 2;">
                    <div class="w3-small" style="color:var(--gris-medio);">Ticket más antiguo en espera</div>
                    <div id="kpiTicketAntiguo" style="font-size:16px; font-weight:700; color:var(--amarillo);">—</div>
                </div>
            </div>

            {{-- Gráficos fila 1 --}}
            <div style="display:grid; grid-template-columns:2fr 1fr; gap:12px; margin-bottom:16px;">
                <div class="w3-card w3-round duoc-bg-blanco w3-padding">
                    <h4 class="duoc-azul" style="margin:0 0 12px 0;">Tickets por día</h4>
                    <canvas id="graficoDia" height="100"></canvas>
                </div>
                <div class="w3-card w3-round duoc-bg-blanco w3-padding">
                    <h4 class="duoc-azul" style="margin:0 0 12px 0;">Tickets por área</h4>
                    <canvas id="graficoArea" height="100"></canvas>
                </div>
            </div>

            {{-- Gráficos fila 2 --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                <div class="w3-card w3-round duoc-bg-blanco w3-padding">
                    <h4 class="duoc-azul" style="margin:0 0 12px 0;">Tickets por hora del día</h4>
                    <canvas id="graficoHora" height="100"></canvas>
                </div>
                <div class="w3-card w3-round duoc-bg-blanco w3-padding">
                    <h4 class="duoc-azul" style="margin:0 0 12px 0;">Comparativa coordinadores</h4>
                    <canvas id="graficoComparativa" height="100"></canvas>
                </div>
            </div>

            {{-- Gráfico área por coordinador --}}
            <div class="w3-card w3-round duoc-bg-blanco w3-padding w3-margin-bottom">
                <h4 class="duoc-azul" style="margin:0 0 12px 0;">Tickets por área por coordinador</h4>
                <canvas id="graficoAreaCoordinador" height="60"></canvas>
            </div>

            {{-- Tabla coordinadores --}}
            <div class="w3-card w3-round duoc-bg-blanco w3-margin-bottom">
                <div class="duoc-bg-azul w3-padding w3-round-top" style="border-bottom:2px solid var(--amarillo);">
                    <h4 class="w3-text-white" style="margin:0;">Resumen por coordinador</h4>
                </div>
                <div style="overflow-x:auto;">
                    <table class="w3-table w3-striped">
                        <thead>
                            <tr class="duoc-bg-gris-claro">
                                <th>Coordinador</th>
                                <th>Atendidos</th>
                                <th>T. espera prom.</th>
                                <th>T. atención prom.</th>
                                <th>Área más atendida</th>
                                <th>Primera atención</th>
                                <th>Última atención</th>
                            </tr>
                        </thead>
                        <tbody id="tablaCoordinadores">
                            <tr><td colspan="7" class="w3-center empty-state">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="paginadorCoordinadores"></div>
            </div>

            {{-- Tabla detalle --}}
            <div class="w3-card w3-round duoc-bg-blanco w3-margin-bottom">
                <div class="duoc-bg-azul w3-padding w3-round-top" style="border-bottom:2px solid var(--amarillo);">
                    <h4 class="w3-text-white" style="margin:0;">Detalle de tickets <span id="totalDetalle" class="w3-small"></span></h4>
                </div>
                <div style="overflow-x:auto;">
                    <table class="w3-table w3-striped">
                        <thead>
                            <tr class="duoc-bg-gris-claro">
                                <th>Ticket</th>
                                <th>Nombre</th>
                                <th>RUT</th>
                                <th>Área</th>
                                <th>Coordinador</th>
                                <th>Mesa</th>
                                <th>Estado</th>
                                <th>T. espera</th>
                                <th>T. atención</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody id="tablaDetalle">
                            <tr><td colspan="10" class="w3-center empty-state">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="paginadorDetalle"></div>
            </div>

        </div>
    </div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const API_BASE = '/api';
let charts = {};

// =========================================================================
// Rangos rápidos
// =========================================================================
function setRango(tipo) {
    const hoy = new Date();
    const fmt = d => d.toISOString().split('T')[0];
    if (tipo === 'hoy') {
        document.getElementById('filtroDesde').value = fmt(hoy);
        document.getElementById('filtroHasta').value = fmt(hoy);
    } else if (tipo === 'semana') {
        const lunes = new Date(hoy);
        lunes.setDate(hoy.getDate() - hoy.getDay() + 1);
        document.getElementById('filtroDesde').value = fmt(lunes);
        document.getElementById('filtroHasta').value = fmt(hoy);
    } else if (tipo === 'mes') {
        document.getElementById('filtroDesde').value = fmt(new Date(hoy.getFullYear(), hoy.getMonth(), 1));
        document.getElementById('filtroHasta').value = fmt(hoy);
    }
    aplicarFiltros();
}

// =========================================================================
// Aplicar filtros
// =========================================================================
async function aplicarFiltros() {
    const desde       = document.getElementById('filtroDesde').value;
    const hasta       = document.getElementById('filtroHasta').value;
    const coordinador = document.getElementById('filtroCoordinador').value;
    if (!desde || !hasta) return;

    const params = new URLSearchParams({ desde, hasta });
    if (coordinador) params.append('user_id', coordinador);

    try {
        const res  = await fetch(`${API_BASE}/dashboard/stats?${params}`);
        const data = await res.json();
        renderKpis(data.kpis);
        renderAlertas(data.alertas);
        renderGraficos(data.graficos);
        renderTablaCoordinadores(data.coordinadores);
        renderTablaDetalle(data.detalle);
    } catch (e) {
        console.error('Error cargando dashboard:', e);
    }
}

// =========================================================================
// KPIs
// =========================================================================
function renderKpis(k) {
    document.getElementById('kpiTotal').innerText            = k.total_tickets;
    document.getElementById('kpiFinalizados').innerText      = k.finalizados;
    document.getElementById('kpiTasa').innerText             = `${k.tasa_finalizacion}% finalización`;
    document.getElementById('kpiEspera').innerText           = k.en_espera;
    document.getElementById('kpiAtencion').innerText         = k.en_atencion;
    document.getElementById('kpiPromedioEspera').innerText   = k.tiempo_promedio_espera;
    document.getElementById('kpiPromedioAtencion').innerText = k.tiempo_promedio_atencion;
    document.getElementById('kpiHoraPico').innerText         = k.hora_pico;
    document.getElementById('kpiArea').innerText             = k.area_mas_demanda;
    document.getElementById('kpiCoordinadorTop').innerText   = k.coordinador_top;
    document.getElementById('kpiMesas').innerText            = k.mesas_activas;
    if (k.ticket_mas_antiguo) {
        document.getElementById('kpiTicketAntiguo').innerText =
            `${k.ticket_mas_antiguo.ticket_numero} —gi ${k.ticket_mas_antiguo.nombre} (${k.ticket_mas_antiguo.minutos} min)`;
    } else {
        document.getElementById('kpiTicketAntiguo').innerText = 'Ninguno en espera';
    }
}

// =========================================================================
// Alertas
// =========================================================================
function renderAlertas(alertas) {
    const seccion     = document.getElementById('seccionAlertas');
    const divEspera   = document.getElementById('alertaEspera');
    const divMesas    = document.getElementById('alertaMesas');
    const listaEspera = document.getElementById('listaAlertaEspera');
    const listaMesas  = document.getElementById('listaAlertaMesas');
    let hayAlertas = false;

    if (alertas.tickets_espera_larga.length > 0) {
        hayAlertas = true;
        divEspera.style.display = 'block';
        listaEspera.innerHTML = alertas.tickets_espera_larga.map(t =>
            `<span class="w3-tag w3-round w3-margin-right w3-margin-bottom" style="background:var(--gris-carbon);">
                ${t.ticket_numero} — ${t.nombre} (${t.minutos} min)
            </span>`
        ).join('');
    } else {
        divEspera.style.display = 'none';
    }

    if (alertas.mesas_sin_actividad.length > 0) {
        hayAlertas = true;
        divMesas.style.display = 'block';
        listaMesas.innerHTML = alertas.mesas_sin_actividad.map(t =>
            `<span class="w3-tag w3-round w3-margin-right w3-margin-bottom" style="background:var(--gris-carbon);">
                Mesa ${t.mesa} — ${t.ticket_numero} (${t.minutos} min)
            </span>`
        ).join('');
    } else {
        divMesas.style.display = 'none';
    }

    seccion.style.display = hayAlertas ? 'block' : 'none';
}

// =========================================================================
// Gráficos
// =========================================================================
function destroyChart(id) {
    if (charts[id]) { charts[id].destroy(); delete charts[id]; }
}

function renderGraficos(g) {
    destroyChart('dia');
    charts['dia'] = new Chart(document.getElementById('graficoDia'), {
        type: 'bar',
        data: {
            labels: g.tickets_por_dia.map(d => d.dia),
            datasets: [{ label: 'Tickets', data: g.tickets_por_dia.map(d => d.total), backgroundColor: '#045174' }]
        },
        options: { plugins: { legend: { display: false } } }
    });

    destroyChart('area');
    charts['area'] = new Chart(document.getElementById('graficoArea'), {
        type: 'doughnut',
        data: {
            labels: g.tickets_por_area.map(d => d.motivo),
            datasets: [{ data: g.tickets_por_area.map(d => d.total), backgroundColor: ['#045174','#e8a020','#2b7a4b','#a63232'] }]
        }
    });

    destroyChart('hora');
    charts['hora'] = new Chart(document.getElementById('graficoHora'), {
        type: 'bar',
        data: {
            labels: g.tickets_por_hora.map(d => `${d.hora}:00`),
            datasets: [{ label: 'Tickets', data: g.tickets_por_hora.map(d => d.total), backgroundColor: '#e8a020' }]
        },
        options: { plugins: { legend: { display: false } } }
    });

    destroyChart('comparativa');
    charts['comparativa'] = new Chart(document.getElementById('graficoComparativa'), {
        type: 'bar',
        data: {
            labels: g.comparativa_coordinadores.map(d => d.coordinador),
            datasets: [
                { label: 'Tickets atendidos',       data: g.comparativa_coordinadores.map(d => d.total),             backgroundColor: '#045174' },
                { label: 'T. atención prom. (min)', data: g.comparativa_coordinadores.map(d => d.promedio_atencion), backgroundColor: '#2b7a4b' }
            ]
        }
    });

    destroyChart('areaCoord');
    const coordinadores = [...new Set(g.tickets_area_coordinador.map(d => d.coordinador))];
    const areas         = [...new Set(g.tickets_area_coordinador.map(d => d.motivo))];
    const colores       = ['#045174','#e8a020','#2b7a4b','#a63232'];
    charts['areaCoord'] = new Chart(document.getElementById('graficoAreaCoordinador'), {
        type: 'bar',
        data: {
            labels: coordinadores,
            datasets: areas.map((area, i) => ({
                label: area,
                data: coordinadores.map(coord => {
                    const found = g.tickets_area_coordinador.find(d => d.coordinador === coord && d.motivo === area);
                    return found ? found.total : 0;
                }),
                backgroundColor: colores[i % colores.length],
            }))
        },
        options: { scales: { x: { stacked: true }, y: { stacked: true } } }
    });
}

// =========================================================================
// Paginación
// =========================================================================
const paginacion = {
    detalle:       { datos: [], pagina: 1, porPagina: 15 },
    coordinadores: { datos: [], pagina: 1, porPagina: 10 },
};

function paginar(key) {
    const { datos, pagina, porPagina } = paginacion[key];
    const inicio = (pagina - 1) * porPagina;
    return datos.slice(inicio, inicio + porPagina);
}

function totalPaginas(key) {
    return Math.ceil(paginacion[key].datos.length / paginacion[key].porPagina);
}

function renderPaginador(key, containerId) {
    const total   = totalPaginas(key);
    const current = paginacion[key].pagina;
    const el      = document.getElementById(containerId);
    if (total <= 1) { el.innerHTML = ''; return; }

    let html = `<div style="display:flex; gap:6px; align-items:center; padding:12px; justify-content:flex-end;">`;
    html += `<button onclick="cambiarPagina('${key}', ${current - 1})" class="w3-button w3-round w3-border w3-small" style="border-color:var(--azul); color:var(--azul);" ${current === 1 ? 'disabled' : ''}>‹</button>`;

    for (let i = 1; i <= total; i++) {
        if (i === 1 || i === total || (i >= current - 2 && i <= current + 2)) {
            html += `<button onclick="cambiarPagina('${key}', ${i})" class="w3-button w3-round w3-small ${i === current ? 'duoc-bg-azul' : 'w3-border'}" style="${i !== current ? 'border-color:var(--azul); color:var(--azul);' : ''}">${i}</button>`;
        } else if (i === current - 3 || i === current + 3) {
            html += `<span style="color:var(--gris-medio);">…</span>`;
        }
    }

    html += `<button onclick="cambiarPagina('${key}', ${current + 1})" class="w3-button w3-round w3-border w3-small" style="border-color:var(--azul); color:var(--azul);" ${current === total ? 'disabled' : ''}>›</button>`;
    html += `<span class="w3-small" style="color:var(--gris-medio);">Página ${current} de ${total}</span>`;
    html += `</div>`;
    el.innerHTML = html;
}

function cambiarPagina(key, pagina) {
    const total = totalPaginas(key);
    if (pagina < 1 || pagina > total) return;
    paginacion[key].pagina = pagina;
    if (key === 'detalle')       renderTablaDetalle();
    if (key === 'coordinadores') renderTablaCoordinadores();
}

// =========================================================================
// Tabla coordinadores
// =========================================================================
function renderTablaCoordinadores(data) {
    if (data) { paginacion.coordinadores.datos = data; paginacion.coordinadores.pagina = 1; }
    const tbody = document.getElementById('tablaCoordinadores');
    const page  = paginar('coordinadores');

    if (!paginacion.coordinadores.datos.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="w3-center empty-state">Sin datos</td></tr>`;
        document.getElementById('paginadorCoordinadores').innerHTML = '';
        return;
    }

    tbody.innerHTML = page.map(c => `
        <tr>
            <td><b>${c.coordinador}</b></td>
            <td>${c.total_atendidos}</td>
            <td>${c.promedio_espera} min</td>
            <td>${c.promedio_atencion} min</td>
            <td>${c.area_mas_atendida}</td>
            <td>${c.primera_atencion ?? '-'}</td>
            <td>${c.ultima_atencion ?? '-'}</td>
        </tr>
    `).join('');

    renderPaginador('coordinadores', 'paginadorCoordinadores');
}

// =========================================================================
// Tabla detalle
// =========================================================================
function renderTablaDetalle(data) {
    if (data) { paginacion.detalle.datos = data; paginacion.detalle.pagina = 1; }
    const tbody = document.getElementById('tablaDetalle');
    const page  = paginar('detalle');

    document.getElementById('totalDetalle').innerText = `(${paginacion.detalle.datos.length} registros)`;

    const colores = { 'espera': 'var(--amarillo)', 'llamando': 'var(--azul)', 'atendido': 'var(--verde)' };

    if (!paginacion.detalle.datos.length) {
        tbody.innerHTML = `<tr><td colspan="10" class="w3-center empty-state">Sin datos</td></tr>`;
        document.getElementById('paginadorDetalle').innerHTML = '';
        return;
    }

    tbody.innerHTML = page.map(t => `
        <tr>
            <td><b>${t.ticket_numero}</b></td>
            <td>${t.nombre}</td>
            <td>${t.rut}</td>
            <td>${t.motivo}</td>
            <td>${t.coordinador}</td>
            <td>${t.mesa}</td>
            <td><span class="w3-tag w3-round w3-small" style="background:${colores[t.status] ?? '#ccc'};">${t.status}</span></td>
            <td>${t.tiempo_espera !== '-' ? t.tiempo_espera + ' min' : '-'}</td>
            <td>${t.tiempo_atencion !== '-' ? t.tiempo_atencion + ' min' : '-'}</td>
            <td>${t.created_at}</td>
        </tr>
    `).join('');

    renderPaginador('detalle', 'paginadorDetalle');
}

// =========================================================================
// Iniciar con "hoy" por defecto
// =========================================================================
setRango('hoy');
</script>
@endsection
