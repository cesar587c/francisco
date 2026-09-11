<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Acesso único ao painel
    |--------------------------------------------------------------------------
    |
    | O sistema não tem cadastro de usuários: existe apenas um administrador,
    | criado/atualizado pelo seeder a partir destas credenciais.
    |
    */
    'admin' => [
        'name' => env('ADMIN_NAME', 'Administrador'),
        'email' => env('ADMIN_EMAIL', 'admin@aguasclaras.local'),
        'password' => env('ADMIN_PASSWORD', 'password'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Token de ingestão (bot -> dashboard)
    |--------------------------------------------------------------------------
    |
    | O bot do WhatsApp envia este token no header X-Ingest-Token para
    | alimentar os endpoints POST /api/ingest/*.
    |
    */
    'ingest_token' => env('INGEST_API_TOKEN'),

];
