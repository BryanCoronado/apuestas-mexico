<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Ruletazo de la Suerte') — Rifas Económicas Guadalajara</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Barlow:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/ruleta.css') }}">
    @stack('head')
</head>
<body class="rifas-body">
    <header class="site-header">
        <div class="site-header__inner">
            {{-- Móvil: 2 enlaces a los lados del logo --}}
            <a href="#" class="site-nav__mobile site-nav__mobile--left">MÉTODOS DE PAGO</a>

            <a href="{{ route('ruleta.index') }}" class="site-header__logo">
                <img src="{{ asset('Logotipo-1.png') }}" alt="Rifas Económicas Guadalajara">
            </a>

            <a href="#" class="site-nav__mobile site-nav__mobile--right">BOLETOS DISPONIBLES</a>

            {{-- Escritorio: menú completo --}}
            <nav class="site-nav" aria-label="Principal">
                <a href="#">Inicio</a>
                <a href="#">Preguntas Frecuentes</a>
                <a href="#">Contacto</a>
                <a href="#">Métodos de Pago</a>
                <a href="#">Comprar Boletos</a>
            </nav>
        </div>
    </header>

    @yield('banner')

    <main class="site-main">
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="site-footer__whatsapp">
            <a href="{{ config('ruleta.whatsapp_link') }}" target="_blank" rel="noopener noreferrer">
                PREGUNTAS AL WHATSAPP<br>{{ config('ruleta.whatsapp') }}
            </a>
        </div>
        <div class="site-footer__legal">
            <a href="#">Sitio desarrollado por Lotto Digital</a>
            <a href="#">Aviso de Privacidad</a>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
