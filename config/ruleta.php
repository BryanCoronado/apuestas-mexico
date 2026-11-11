<?php

return [

    'whatsapp' => '(331) 859 3647',

    'whatsapp_link' => 'https://wa.me/523318593647',

    'sorteos' => [
        [
            'codigo' => 'S102',
            'inicio' => '2026-11-11 16:46:00',
            'fin' => '2026-11-13 16:46:00',
            'premio_imagen' => 'premio-demo.jpg',
        ],
        [
            'codigo' => 'S101',
            'inicio' => '2026-10-01 10:00:00',
            'fin' => '2026-10-15 20:00:00',
            'premio_imagen' => 'premio-demo.jpg',
        ],
    ],

    /*
    | Boletos de demostración (sin BD). Al pulsar INICIAR se elige uno al azar
    | del sorteo seleccionado; si hay "Mín. boletos pagados", solo entran PAGADO.
    */
    'boletos' => [
        ['sorteo' => 'S102', 'boleto' => '82318', 'nombre' => 'MANUEL', 'apellido' => 'CORDOVA TEJERO', 'estado' => 'YUCATÁN', 'status' => 'PAGADO', 'compra' => 'NOV 05 2026 15:54:28'],
        ['sorteo' => 'S102', 'boleto' => '15444', 'nombre' => '', 'apellido' => '', 'estado' => '', 'status' => 'NO PAGADO', 'compra' => '-'],
        ['sorteo' => 'S102', 'boleto' => '08912', 'nombre' => 'MARÍA', 'apellido' => 'LÓPEZ RUIZ', 'estado' => 'JALISCO', 'status' => 'PAGADO', 'compra' => 'NOV 08 2026 10:12:03'],
        ['sorteo' => 'S102', 'boleto' => '12003', 'nombre' => 'CARLOS', 'apellido' => 'REYES MORA', 'estado' => 'CDMX', 'status' => 'PAGADO', 'compra' => 'NOV 09 2026 18:40:51'],
        ['sorteo' => 'S102', 'boleto' => '13789', 'nombre' => '', 'apellido' => '', 'estado' => '', 'status' => 'NO PAGADO', 'compra' => '-'],
        ['sorteo' => 'S102', 'boleto' => '14220', 'nombre' => 'ANA', 'apellido' => 'GARCÍA PÉREZ', 'estado' => 'GUANAJUATO', 'status' => 'PAGADO', 'compra' => 'NOV 10 2026 09:05:17'],
        ['sorteo' => 'S102', 'boleto' => '17001', 'nombre' => 'PEDRO', 'apellido' => 'SÁNCHEZ DÍAZ', 'estado' => 'NAYARIT', 'status' => 'PAGADO', 'compra' => 'NOV 11 2026 12:33:09'],
        ['sorteo' => 'S101', 'boleto' => '04567', 'nombre' => 'ROSA', 'apellido' => 'HERNÁNDEZ', 'estado' => 'JALISCO', 'status' => 'PAGADO', 'compra' => 'SEP 28 2026 16:20:44'],
        ['sorteo' => 'S101', 'boleto' => '05123', 'nombre' => '', 'apellido' => '', 'estado' => '', 'status' => 'NO PAGADO', 'compra' => '-'],
        ['sorteo' => 'S101', 'boleto' => '06234', 'nombre' => 'JORGE', 'apellido' => 'RAMÍREZ', 'estado' => 'COLIMA', 'status' => 'PAGADO', 'compra' => 'SEP 30 2026 11:02:38'],
    ],

];
