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

    // O token de ingestão (bot -> dashboard) não vem mais do .env: é gerado
    // automaticamente e guardado no banco. Ver App\Services\IngestTokenManager.

];
