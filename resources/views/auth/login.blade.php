@extends('layouts.app')

@section('title', 'Iniciar Sesión — Tótem Duoc UC')

@section('content')
<style>
    body {
        background-color: var(--duoc-blue);
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
        margin: 0;
    }

    .login-wrapper {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        width: 100%;
        max-width: 420px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .login-header {
        background-color: var(--duoc-blue);
        border-bottom: 6px solid var(--duoc-yellow);
        padding: 30px 20px;
        text-align: center;
    }

    .login-header h1 {
        color: white;
        margin: 0;
        font-size: 28px;
        font-weight: bold;
        letter-spacing: 1px;
    }

    .login-header p {
        color: var(--duoc-yellow);
        margin: 6px 0 0;
        font-size: 14px;
    }

    .login-body {
        padding: 35px 40px;
    }

    .login-body label {
        font-weight: 600;
        color: var(--duoc-blue);
        font-size: 14px;
        margin-bottom: 6px;
        display: block;
    }

    .login-body input[type="email"],
    .login-body input[type="password"] {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 15px;
        box-sizing: border-box;
        transition: border-color 0.2s;
        outline: none;
    }

    .login-body input:focus {
        border-color: var(--duoc-blue);
    }

    .login-body input.is-invalid {
        border-color: #dc3545;
    }

    .invalid-feedback {
        color: #dc3545;
        font-size: 13px;
        margin-top: 4px;
        display: block;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-check {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 25px;
    }

    .form-check input {
        width: 16px;
        height: 16px;
        accent-color: var(--duoc-blue);
    }

    .form-check label {
        font-size: 14px;
        color: #555;
        margin: 0;
        font-weight: normal;
    }

    .btn-login {
        width: 100%;
        background-color: var(--duoc-blue);
        color: white;
        border: none;
        padding: 14px;
        border-radius: 10px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.2s;
        letter-spacing: 0.5px;
    }

    .btn-login:hover {
        background-color: #002a57;
    }

    .login-footer {
        text-align: center;
        padding: 15px;
        border-top: 1px solid #f0f0f0;
        font-size: 12px;
        color: #aaa;
    }
</style>

<div class="login-wrapper">
    <div class="login-header">
        <h1>Duoc UC</h1>
        <p>Sede San Bernardo — Panel de Coordinadores</p>
    </div>

    <div class="login-body">
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email">Correo institucional</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="@error('email') is-invalid @enderror"
                    required
                    autocomplete="email"
                    autofocus
                    placeholder="coordinador@duoc.cl"
                >
                @error('email')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="@error('password') is-invalid @enderror"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                >
                @error('password')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-check">
                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                <label for="remember">Recordarme</label>
            </div>

            <button type="submit" class="btn-login">Iniciar sesión</button>
        </form>
    </div>

    <div class="login-footer">
        Tótem de Autoservicio — Duoc UC San Bernardo
    </div>
</div>
@endsection
