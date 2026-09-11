<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Str;

/**
 * O token que o bot usa no header X-Ingest-Token não é mais configurado
 * manualmente no .env — é gerado automaticamente na primeira vez que é
 * necessário e guardado no banco (tabela settings), com opção de gerar
 * um novo a qualquer momento pelo painel.
 */
class IngestTokenManager
{
    private const KEY = 'ingest_token';

    public static function current(): string
    {
        $setting = Setting::firstOrCreate(
            ['key' => self::KEY],
            ['value' => Str::random(40)]
        );

        return $setting->value;
    }

    public static function regenerate(): string
    {
        $token = Str::random(40);

        Setting::updateOrCreate(['key' => self::KEY], ['value' => $token]);

        return $token;
    }
}
