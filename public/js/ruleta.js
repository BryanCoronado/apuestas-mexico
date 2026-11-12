(function () {
    'use strict';

    const app = document.getElementById('ruleta-app');
    if (!app) {
        return;
    }

    const SPIN_MS = 3000;

    const stepEntrada = document.getElementById('step-entrada');
    const stepJuego = document.getElementById('step-juego');
    const btnEntrar = document.getElementById('btn-entrar');
    const btnIniciar = document.getElementById('btn-iniciar');
    const sorteoSelect = document.getElementById('sorteo-select');
    const fechaInicio = document.getElementById('fecha-inicio');
    const fechaFin = document.getElementById('fecha-fin');
    const minBoletos = document.getElementById('min-boletos');
    const ruletaWrap = document.getElementById('ruleta-wheel-wrap');
    const ruletaGif = document.getElementById('ruleta-gif');
    const boletoCard = document.getElementById('boleto-ganador');
    const premioImg = document.getElementById('premio-img');
    const premioVacio = document.getElementById('premio-vacio');

    const pickerOpciones = {
        enableTime: true,
        dateFormat: 'Y-m-d H:i',
        altInput: true,
        altFormat: 'F j, Y h:i K',
        locale: {
            ...(window.flatpickr?.l10ns?.es || {}),
            months: {
                shorthand: ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'],
                longhand: ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'],
            },
        },
        disableMobile: true,
    };

    const pickerInicio = window.flatpickr ? window.flatpickr(fechaInicio, pickerOpciones) : null;
    const pickerFin = window.flatpickr ? window.flatpickr(fechaFin, pickerOpciones) : null;

    function setFecha(picker, input, valor) {
        if (picker) {
            picker.setDate(valor, false, 'Y-m-d H:i');
        } else if (input) {
            input.value = valor;
        }
    }

    let spinTimer = null;

    function sorteoActual() {
        return sorteoSelect?.selectedOptions?.[0] || null;
    }

    function puedeIniciar() {
        return Boolean(sorteoSelect?.value);
    }

    function sincronizarBoton() {
        if (btnIniciar && !spinTimer) {
            btnIniciar.disabled = !puedeIniciar();
        }
    }

    function actualizarSorteo() {
        const opt = sorteoActual();
        if (!opt || !opt.value) {
            sincronizarBoton();
            return;
        }

        setFecha(pickerInicio, fechaInicio, opt.dataset.inicio);
        setFecha(pickerFin, fechaFin, opt.dataset.fin);

        const espaciado = (opt.dataset.codigo || '').split('').join(' ');
        app.querySelectorAll('[data-codigo]').forEach((el) => {
            el.textContent = espaciado;
        });

        const premio = opt.dataset.premio;
        if (premioImg && premioVacio) {
            if (premio) {
                premioImg.src = premio;
                premioImg.hidden = false;
                premioVacio.hidden = true;
            } else {
                premioImg.hidden = true;
                premioVacio.hidden = false;
            }
        }

        sincronizarBoton();
    }

    async function elegirGanador() {
        const params = new URLSearchParams({
            rifa: sorteoSelect?.value || '',
            inicio: fechaInicio?.value || '',
            fin: fechaFin?.value || '',
            minimo: minBoletos?.value || '0',
        });

        const respuesta = await fetch(`${app.dataset.ganadorUrl}?${params}`, {
            headers: { Accept: 'application/json' },
        });
        const datos = await respuesta.json().catch(() => ({}));

        if (!respuesta.ok) {
            throw new Error(datos.message || 'No se pudo elegir un ganador.');
        }

        return datos.ganador;
    }

    function pintarGanador(g) {
        const campos = {
            'ganador-boleto': g.boleto,
            'ganador-nombre': g.nombre,
            'ganador-apellido': g.apellido,
            'ganador-estado': g.estado,
            'ganador-status': g.status || 'NO PAGADO',
            'ganador-compra': g.compra || '-',
        };

        Object.entries(campos).forEach(([id, valor]) => {
            const el = document.getElementById(id);
            if (el) {
                el.textContent = valor || '';
            }
        });
    }

    function iniciar() {
        if (spinTimer || !puedeIniciar()) {
            return;
        }

        const ganador = elegirGanador();
        const giro = new Promise((resolve) => {
            spinTimer = window.setTimeout(resolve, SPIN_MS);
        });

        btnIniciar.disabled = true;
        boletoCard.hidden = true;
        ruletaWrap.hidden = false;

        const src = ruletaGif.getAttribute('src');
        ruletaGif.setAttribute('src', '');
        ruletaGif.setAttribute('src', src);

        Promise.allSettled([ganador, giro]).then(([resultado]) => {
            ruletaWrap.hidden = true;
            spinTimer = null;
            sincronizarBoton();

            if (resultado.status === 'fulfilled') {
                pintarGanador(resultado.value);
                boletoCard.hidden = false;
            } else {
                window.alert(resultado.reason.message);
            }
        });
    }

    btnEntrar?.addEventListener('click', () => {
        stepEntrada.hidden = true;
        stepJuego.hidden = false;
        actualizarSorteo();
    });

    sorteoSelect?.addEventListener('change', () => {
        actualizarSorteo();
        boletoCard.hidden = true;
    });

    btnIniciar?.addEventListener('click', iniciar);

    actualizarSorteo();
    sincronizarBoton();
})();
