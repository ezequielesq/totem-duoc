@extends('layouts.app')

@section('title', 'Panel Asesor - Duoc UC San Bernardo')

@section('content')

{{-- Setup: selección de mesa --}}
<div id="setup" style="display:flex; align-items:center; justify-content:center; min-height:100vh;" class="duoc-bg-gris-carbon">
    <div class="w3-card w3-round-large w3-overflow-hidden w3-animate-zoom" style="width:100%; max-width:420px;">

        {{-- Header carta --}}
        <div class="duoc-bg-azul w3-padding-32 w3-center" style="border-bottom:6px solid var(--amarillo);">
            <img src="https://www.duoc.cl/wp-content/uploads/2020/03/logo-duoc.png" width="160" style="filter:brightness(0) invert(1);">
            <p class="w3-small w3-margin-top" style="color:var(--amarillo); margin-bottom:0;">
                Sede San Bernardo — Panel de Coordinadores
            </p>
        </div>

        {{-- Body carta --}}
        <div class="duoc-bg-blanco w3-padding-large w3-center">
            <p class="w3-small" style="color:var(--gris-medio);">Bienvenido, <b>{{ auth()->user()->name }}</b></p>
            <h3 class="duoc-azul" style="margin-top:0;">Selecciona tu puesto</h3>
            <select id="mesaSelector" class="w3-select w3-border w3-round w3-padding w3-margin-bottom" style="font-size:18px;">
                <option value="1">Mesa 1</option>
                <option value="2">Mesa 2</option>
                <option value="3">Mesa 3</option>
                <option value="4">Mesa 4</option>
            </select>
            <button onclick="iniciarSesion()" class="w3-button w3-block w3-round w3-padding-16 duoc-bg-amarillo" style="font-size:18px; font-weight:bold;">
                Comenzar Jornada
            </button>
        </div>

        {{-- Footer carta --}}
        <div class="duoc-bg-gris-claro w3-center w3-padding w3-small" style="border-top:1px solid var(--gris-claro);">
            Tótem de Autoservicio — Duoc UC San Bernardo
        </div>

    </div>
</div>

{{-- Panel principal --}}
<div id="mainPanel" style="display:none; flex-direction:column; min-height:100vh;" class="duoc-bg-gris-claro">

    {{-- Header --}}
    <header class="duoc-bg-azul w3-padding" style="display:flex; align-items:center; justify-content:space-between; border-bottom:4px solid var(--amarillo);">
        <img src="https://www.duoc.cl/wp-content/uploads/2020/03/logo-duoc.png" height="36" style="filter:brightness(0) invert(1);">
        <span id="labelMesa" class="w3-text-white w3-large" style="font-weight:900; letter-spacing:2px;">Mesa --</span>
        <div style="display:flex; gap:8px;">
            <button onclick="location.reload()" class="w3-button w3-round w3-border w3-border-white w3-text-white w3-small" style="background:transparent;">
                Cambiar Mesa
            </button>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w3-button w3-round w3-small duoc-bg-rojo">
                    Cerrar Sesión
                </button>
            </form>
        </div>
    </header>

    {{-- Contenido --}}
    <div style="display:grid; grid-template-columns:2fr 1fr; gap:16px; padding:16px; flex:1;">

        {{-- Columna izquierda --}}
        <div style="display:flex; flex-direction:column; gap:16px;">

            {{-- Ticket en atención --}}
            <div id="areaAtencion" class="w3-card w3-round duoc-bg-blanco w3-padding-large" style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:300px;">
                <p class="empty-attention">Mesa Disponible.<br>Llama a un alumno.</p>
            </div>

            {{-- Otros asesores --}}
            <div class="w3-card w3-round duoc-bg-blanco w3-padding">
                <h4 class="duoc-azul" style="margin:0 0 8px 0; border-bottom:2px solid var(--gris-claro); padding-bottom:8px;">
                    Otros Asesores
                </h4>
                <div id="otrosAsesoresList"></div>
            </div>

        </div>

        {{-- Columna derecha: lista de espera --}}
        <div class="w3-card w3-round" style="display:flex; flex-direction:column; max-height:calc(100vh - 80px);">
            <div class="duoc-bg-azul w3-padding w3-round-top" style="border-bottom:2px solid var(--amarillo);">
                <h4 class="w3-text-white" style="margin:0;">En Espera</h4>
            </div>
            <div id="listaEspera" class="duoc-bg-blanco" style="flex:1; overflow-y:auto; padding:8px;"></div>
        </div>

    </div>

</div>

@endsection

@section('scripts')
<script>
    const API_BASE = '/api';
</script>
@endsection
