<?php

return [

    'whatsapp' => '(331) 859 3647',

    'whatsapp_link' => 'https://wa.me/523318593647',

    /*
    | Dominio público donde se publican las listas de cada rifa (columna URL del panel).
    */
    'sitio' => 'www.rifasuperbike.com',

    'contacto_email' => 'contacto.lottodigital@gmail.com',

    'contacto_web' => 'https://www.lottodigital.mx/',

    /*
    | Usuario administrador que crea el seeder para entrar al panel.
    */
    'admin' => [
        'name' => env('PANEL_ADMIN_NAME', 'Administrador'),
        'email' => env('PANEL_ADMIN_EMAIL', 'admin@rifassuperbike.com'),
        'password' => env('PANEL_ADMIN_PASSWORD'),
    ],

    /*
    | Mensajes de WhatsApp del Panel de Boletos. Variables: :nombre, :boletos, :rifa.
    */
    'mensajes' => [
        'whats' => 'Hola :nombre, te escribimos de Rifas Superbike sobre tus boletos :boletos de la rifa :rifa.',
        'rec1' => 'Hola :nombre, te recordamos que tus boletos :boletos de la rifa :rifa siguen apartados. Realiza tu pago para no perderlos.',
        'rec2' => 'Hola :nombre, ÚLTIMO AVISO: tus boletos :boletos de la rifa :rifa se liberarán pronto si no recibimos tu pago.',
        'boleto' => '¡Gracias :nombre! Tu pago fue confirmado. Tus boletos :boletos de la rifa :rifa ya están PAGADOS. ¡Mucha suerte!',
    ],

];
