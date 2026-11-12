<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión — Lotto Digital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/panel.css') }}">
</head>
<body class="panel-body login-body">
    <main class="login-card">
        <section class="login-card__arte" aria-hidden="true">
            <span class="panel-logo">
                <span class="panel-logo__marca"></span>
                <span class="panel-logo__texto">LOTTO DIGITAL<small>DIGITAL RAFFLE PLATFORM</small></span>
            </span>
            <span class="login-card__forma login-card__forma--1"></span>
            <span class="login-card__forma login-card__forma--2"></span>
            <span class="login-card__copy">{{ now()->year }} © Lotto Digital</span>
        </section>

        <section class="login-card__form">
            <h1 class="login-card__titulo">¡Bienvenido!</h1>
            <p class="login-card__subtitulo">Inicie sesión en su cuenta.</p>

            <form method="POST" action="{{ route('login.store') }}" novalidate>
                @csrf

                <label class="panel-label" for="email">Usuario</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="panel-input"
                    autocomplete="username" required autofocus>

                <label class="panel-label" for="password">Contraseña</label>
                <div class="panel-input-grupo">
                    <input type="password" id="password" name="password" class="panel-input"
                        autocomplete="current-password" required>
                    <button type="button" class="panel-input-grupo__btn" data-toggle-password="password" aria-label="Mostrar contraseña">
                        <x-panel.icono nombre="ojo" />
                    </button>
                </div>

                @error('email')
                    <p class="panel-error">{{ $message }}</p>
                @enderror
                @error('password')
                    <p class="panel-error">{{ $message }}</p>
                @enderror

                <button type="submit" class="panel-btn panel-btn--primario panel-btn--bloque">Ingresar</button>
            </form>
        </section>
    </main>

    <script src="{{ asset('js/panel.js') }}" defer></script>
</body>
</html>
