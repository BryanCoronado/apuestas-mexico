<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') — Lotto Digital</title>
    <script>
        document.documentElement.dataset.theme = localStorage.getItem('panel-tema') || 'dark';
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/panel.css') }}">
    @stack('head')
</head>
<body class="panel-body">
    <div class="panel-topbar">
        <span class="panel-topbar__item">
            <x-panel.icono nombre="reloj" />
            <span id="panel-reloj"></span>
        </span>
        <span class="panel-topbar__links">
            <a href="mailto:{{ config('ruleta.contacto_email') }}" class="panel-topbar__item">
                <x-panel.icono nombre="correo" /> {{ config('ruleta.contacto_email') }}
            </a>
            <a href="{{ config('ruleta.contacto_web') }}" target="_blank" rel="noopener noreferrer" class="panel-topbar__item">
                <x-panel.icono nombre="globo" /> {{ config('ruleta.contacto_web') }}
            </a>
        </span>
    </div>

    <header class="panel-header">
        <div class="panel-container panel-header__inner">
            <a href="{{ route('panel.dashboard') }}" class="panel-logo" aria-label="Lotto Digital">
                <span class="panel-logo__marca"></span>
                <span class="panel-logo__texto">LOTTO DIGITAL<small>DIGITAL RAFFLE PLATFORM</small></span>
            </a>

            <div class="panel-header__acciones">
                <button type="button" class="panel-icon-btn" id="btn-fullscreen" title="Pantalla completa">
                    <x-panel.icono nombre="fullscreen" />
                </button>
                <button type="button" class="panel-icon-btn" id="btn-tema" title="Cambiar tema">
                    <x-panel.icono nombre="luna" class="icono tema-oscuro" />
                    <x-panel.icono nombre="sol" class="icono tema-claro" />
                </button>

                <details class="panel-usuario">
                    <summary>
                        <span class="panel-usuario__avatar"><x-panel.icono nombre="usuario" /></span>
                        <span class="panel-usuario__email">{{ auth()->user()->email }}</span>
                    </summary>
                    <div class="panel-usuario__menu">
                        <span class="panel-usuario__nombre">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"><x-panel.icono nombre="salir" /> Cerrar sesión</button>
                        </form>
                    </div>
                </details>
            </div>
        </div>
    </header>

    <nav class="panel-menu" aria-label="Panel">
        <div class="panel-container panel-menu__inner">
            <a href="{{ route('panel.boletos.index') }}" @class(['activo' => request()->routeIs('panel.boletos.*')])>
                <x-panel.icono nombre="boletos" /> Panel de Boletos
            </a>
            <a href="#" class="deshabilitado" title="Próximamente"><x-panel.icono nombre="config" /> Config Generales</a>
            <a href="{{ route('panel.rifas.index') }}" @class(['activo' => request()->routeIs('panel.rifas.*')])>
                <x-panel.icono nombre="rifas" /> Panel de Rifas
            </a>
            <a href="#" class="deshabilitado" title="Próximamente"><x-panel.icono nombre="analisis" /> Análisis de Datos</a>
            <a href="#" class="deshabilitado" title="Próximamente"><x-panel.icono nombre="contactos" /> Contactos</a>
        </div>
    </nav>

    @if (session('status'))
        <div class="panel-flash" role="status">{{ session('status') }}</div>
    @endif

    @if ($errors->any() && ! request()->routeIs('panel.rifas.*'))
        <div class="panel-flash panel-flash--error" role="alert">{{ $errors->first() }}</div>
    @endif

    <main class="panel-main">
        @yield('content')
    </main>

    <footer class="panel-footer">
        <div class="panel-container">{{ now()->year }} © Lotto Digital.</div>
    </footer>

    <script src="{{ asset('js/panel.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
