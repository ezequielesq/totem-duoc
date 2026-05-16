@extends('layouts.app')

@section('title', 'Iniciar Sesión — Tótem Duoc UC')

@section('content')
<div style="min-height:100vh; display:flex; align-items:center; justify-content:center;">
    <div class="w3-card w3-round-large w3-overflow-hidden w3-animate-zoom" style="width:100%;max-width:420px;">

        {{-- Header --}}
        <div class="duoc-bg-azul w3-padding-32 w3-center" style="border-bottom:6px solid var(--amarillo);">
            <img src="https://www.duoc.cl/wp-content/uploads/2020/03/logo-duoc.png" width="140" style="filter:brightness(0) invert(1);">
            <p class="w3-small w3-margin-top" style="color:var(--amarillo); margin-bottom:0;">
                Sede San Bernardo — Panel de Coordinadores
            </p>
        </div>

        {{-- Body --}}
        <div class="duoc-bg-blanco w3-padding-large">
            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
                <label class="duoc-azul"><b>Correo institucional</b></label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="coordinador@duoc.cl"
                    autocomplete="email"
                    autofocus
                    required
                    class="w3-input w3-border w3-round w3-margin-bottom"
                    style="@error('email') border-color:var(--rojo) !important; @enderror"
                >
                @error('email')
                    <span class="w3-small" style="color:var(--rojo);">{{ $message }}</span>
                @enderror

                {{-- Password --}}
                <label class="duoc-azul w3-margin-top"><b>Contraseña</b></label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    required
                    class="w3-input w3-border w3-round w3-margin-bottom"
                    style="@error('password') border-color:var(--rojo) !important; @enderror"
                >
                @error('password')
                    <span class="w3-small" style="color:var(--rojo);">{{ $message }}</span>
                @enderror

                {{-- Recordarme --}}
                <div class="w3-margin-top w3-margin-bottom">
                    <label class="w3-text-grey">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }} class="w3-check">
                        &nbsp;Recordarme
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit" class="w3-button w3-block w3-round w3-padding-16 duoc-bg-azul">
                    <b>Iniciar sesión</b>
                </button>

            </form>
        </div>

        {{-- Footer --}}
        <div class="duoc-bg-gris-claro w3-center w3-padding w3-small" style="border-top:1px solid var(--gris-claro);">
            Tótem de Autoservicio — Duoc UC San Bernardo
        </div>

    </div>
</div>
@endsection
