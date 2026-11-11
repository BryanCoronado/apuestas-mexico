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

    let boletos = [];
    try {
        boletos = JSON.parse(app.dataset.boletos || '[]');
    } catch (e) {
        boletos = [];
    }

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

    function sorteoActual() {
        return sorteoSelect?.selectedOptions?.[0] || null;
    }

    function actualizarSorteo() {
        const opt = sorteoActual();
        if (!opt) {
            return;
        }

        setFecha(pickerInicio, fechaInicio, opt.dataset.inicio);
        setFecha(pickerFin, fechaFin, opt.dataset.fin);

        const espaciado = opt.value.split('').join(' ');
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
    }

    function esPagado(status) {
        return String(status || '').toUpperCase() === 'PAGADO';
    }

    function elegirGanador() {
        const codigo = sorteoSelect?.value;
        const min = parseInt(minBoletos?.value || '', 10);

        let pool = boletos.filter((b) => b.sorteo === codigo);

        if (!Number.isNaN(min) && min > 0) {
            const pagados = pool.filter((b) => esPagado(b.status));
            if (pagados.length > 0) {
                pool = pagados;
            }
        }

        if (pool.length === 0) {
            return {
                boleto: String(Math.floor(Math.random() * 100000)).padStart(5, '0'),
                nombre: '',
                apellido: '',
                estado: '',
                status: 'NO PAGADO',
                compra: '-',
            };
        }

        return pool[Math.floor(Math.random() * pool.length)];
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

    let spinTimer = null;

    function iniciar() {
        if (spinTimer) {
            return;
        }

        const ganador = elegirGanador();

        btnIniciar.disabled = true;
        boletoCard.hidden = true;
        ruletaWrap.hidden = false;

        const src = ruletaGif.getAttribute('src');
        ruletaGif.setAttribute('src', '');
        ruletaGif.setAttribute('src', src);

        spinTimer = window.setTimeout(() => {
            pintarGanador(ganador);
            ruletaWrap.hidden = true;
            boletoCard.hidden = false;
            btnIniciar.disabled = false;
            spinTimer = null;
        }, SPIN_MS);
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
})();
