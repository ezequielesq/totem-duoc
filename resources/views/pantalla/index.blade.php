<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pantalla de Turnos — Duoc UC</title>
    <style>
        :root {
            --duoc-blue: #003D7A;
            --duoc-yellow: #FFB800;
            --duoc-gray: #f4f7f9;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--duoc-gray);
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        header {
            background: var(--duoc-blue);
            border-bottom: 6px solid var(--duoc-yellow);
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            color: white;
            font-size: 28px;
        }

        header p {
            color: var(--duoc-yellow);
            font-size: 16px;
        }

        #reloj {
            color: white;
            font-size: 32px;
            font-weight: bold;
            font-variant-numeric: tabular-nums;
        }

        .main {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            padding: 20px;
            flex: 1;
            overflow: hidden;
        }

        /* Columna izquierda — llamado actual */
        .llamando {
            background: white;
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }

        .llamando-label {
            font-size: 22px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 10px;
        }

        .llamando-numero {
            font-size: 160px;
            font-weight: bold;
            color: var(--duoc-blue);
            line-height: 1;
            animation: pulse 1.5s infinite;
        }

        .llamando-mesa {
            font-size: 28px;
            color: var(--duoc-yellow);
            background: var(--duoc-blue);
            padding: 10px 30px;
            border-radius: 50px;
            margin-top: 20px;
            font-weight: bold;
        }

        .llamando-nombre {
            font-size: 26px;
            color: #444;
            margin-top: 15px;
        }

        .sin-llamado {
            color: #ccc;
            font-size: 24px;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.03);
            }
        }

        /* Columna derecha — lista de espera */
        .espera {
            background: white;
            border-radius: 20px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .espera h2 {
            color: var(--duoc-blue);
            font-size: 20px;
            border-bottom: 2px solid var(--duoc-gray);
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .espera-lista {
            flex: 1;
            overflow-y: auto;
        }

        .espera-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            background: var(--duoc-gray);
        }

        .espera-item .numero {
            font-size: 24px;
            font-weight: bold;
            color: var(--duoc-blue);
        }

        .espera-item .detalle {
            font-size: 14px;
            color: #666;
            text-align: right;
        }

        .sin-espera {
            color: #ccc;
            font-style: italic;
            text-align: center;
            margin-top: 30px;
        }

        footer {
            background: var(--duoc-blue);
            color: white;
            text-align: center;
            padding: 10px;
            font-size: 13px;
        }
    </style>
</head>

<body>

    <header>
        <div>
            <h1>Duoc UC</h1>
            <p>Sede San Bernardo — Sistema de Turnos</p>
        </div>
        <div id="reloj">--:--:--</div>
    </header>

    <div class="main">

        {{-- Ticket llamado --}}
        <div class="llamando" id="areaCalling">
            <p class="sin-llamado">Sin llamados activos</p>
        </div>

        {{-- Lista de espera --}}
        <div class="espera">
            <h2>En Espera</h2>
            <div class="espera-lista" id="listaEspera">
                <p class="sin-espera">No hay alumnos esperando</p>
            </div>
        </div>

    </div>

    <footer>
        Tótem de Autoservicio — Duoc UC San Bernardo
    </footer>

    <script>
        // Reloj
        function actualizarReloj() {
            const now = new Date();
            document.getElementById('reloj').innerText = now.toLocaleTimeString('es-CL');
        }
        setInterval(actualizarReloj, 1000);
        actualizarReloj();

        // Cargar tickets
        async function cargarTurnos() {
            try {
                const response = await fetch('https://totem.ezsuarez.org/api/tickets/queue');
                const data = await response.json();

                const areaCalling = document.getElementById('areaCalling');
                const listaEspera = document.getElementById('listaEspera');

                // Mostrar llamados
                if (data.llamados && data.llamados.length > 0) {
                    areaCalling.innerHTML = data.llamados.map(t => `
        <div style="text-align:center; flex:1;">
            <p class="llamando-label">Mesa ${t.mesa}</p>
            <div class="llamando-numero" style="font-size:100px;">${t.ticket_numero}</div>
            <div class="llamando-nombre">${t.nombre}</div>
        </div>
    `).join('<div style="width:2px; background:#eee;"></div>');

                    areaCalling.style.display = 'flex';
                    areaCalling.style.flexDirection = 'row';
                    areaCalling.style.alignItems = 'center';
                    areaCalling.style.gap = '10px';
                } else {
                    areaCalling.innerHTML = `<p class="sin-llamado">Sin llamados activos</p>`;
                    areaCalling.style.flexDirection = 'column';
                }

                // Mostrar espera
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

</body>

</html>
