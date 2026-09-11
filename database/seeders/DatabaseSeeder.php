<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * O painel tem um único acesso: um administrador criado (ou atualizado)
     * a partir das credenciais definidas em ADMIN_EMAIL / ADMIN_PASSWORD no .env.
     * Não existe rota de cadastro — apenas este usuário pode logar.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => config('dashboard.admin.email')],
            [
                'name' => config('dashboard.admin.name'),
                'password' => config('dashboard.admin.password'),
            ]
        );
    }
}
