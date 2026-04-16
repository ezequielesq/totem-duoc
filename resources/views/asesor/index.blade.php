@extends('layouts.app')

@section('title', 'Panel Asesor - Duoc UC San Bernardo')

@section('content')

    {{-- Pantalla de selección de mesa --}}
    <div id="setup" class="setup-screen">
        <img src="https://www.duoc.cl/wp-content/uploads/2020/03/logo-duoc.png" width="200" style="margin-bottom: 30px;">
        <h2>Selecciona tu puesto</h2>
        <select id="mesaSelector">
            <option value="1">Mesa 1</option>
            <option value="2">Mesa 2</option>
            <option value="3">Mesa 3</option>
            <option value="4">Mesa 4</option>
        </select>
        <button class="btn-start" onclick="iniciarSesion()">Comenzar Jornada</button>
    </div>

    {{-- Panel principal --}}
    <div id="mainPanel" style="display:none;">
        <header>
            <img src="https://www.duoc.cl/wp-content/uploads/2020/03/logo-duoc.png" class="logo-header">
            <h2 id="labelMesa">Mesa --</h2>
            <button onclick="location.reload()" class="btn-change-mesa">
                Cambiar Mesa
            </button>
        </header>

        <div class="container">

            {{-- Columna izquierda --}}
            <div style="display:flex; flex-direction:column; gap:20px;">

                {{-- Ticket en atención --}}
                <div class="serving-now" id="areaAtencion">
                    <p class="empty-attention">Mesa Disponible</p>
                </div>

                {{-- Otros asesores --}}
                <div class="other-advisors">
                    <h3>Otros Asesores</h3>
                    <div id="otrosAsesoresList">Cargando...</div>
                </div>

            </div>

            {{-- Columna derecha: lista de espera --}}
            <div class="queue-list">
                <h3>En Espera</h3>
                <div class="scroll-area" id="listaEspera">Cargando...</div>
            </div>

        </div>
    </div>

@endsection

@section('scripts')
    <script>
        const API_BASE = '/api';
    </script>
@endsection
