(function () {
    'use strict';

    const reloj = document.getElementById('panel-reloj');
    if (reloj) {
        const fecha = new Intl.DateTimeFormat('es-MX', { weekday: 'short', day: 'numeric', month: 'short' });
        const hora = new Intl.DateTimeFormat('es-MX', { hour: '2-digit', minute: '2-digit', hour12: true });

        const pintarReloj = () => {
            const ahora = new Date();
            reloj.textContent = `${fecha.format(ahora).replace(/\./g, '')} | ${hora.format(ahora).toUpperCase()}`;
        };

        pintarReloj();
        window.setInterval(pintarReloj, 30000);
    }

    document.getElementById('btn-fullscreen')?.addEventListener('click', () => {
        if (document.fullscreenElement) {
            document.exitFullscreen();
        } else {
            document.documentElement.requestFullscreen?.();
        }
    });

    document.getElementById('btn-tema')?.addEventListener('click', () => {
        const tema = document.documentElement.dataset.theme === 'light' ? 'dark' : 'light';
        document.documentElement.dataset.theme = tema;
        localStorage.setItem('panel-tema', tema);
    });

    document.querySelectorAll('[data-toggle-password]').forEach((boton) => {
        boton.addEventListener('click', () => {
            const input = document.getElementById(boton.dataset.togglePassword);
            if (input) {
                input.type = input.type === 'password' ? 'text' : 'password';
            }
        });
    });

    document.querySelectorAll('[data-autosubmit]').forEach((campo) => {
        campo.addEventListener('change', () => campo.form?.requestSubmit());
    });

    document.querySelectorAll('form[data-confirmar]').forEach((form) => {
        form.addEventListener('submit', (evento) => {
            if (!window.confirm(form.dataset.confirmar)) {
                evento.preventDefault();
            }
        });
    });
})();
