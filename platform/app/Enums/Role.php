<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum Role: string
{
    use HasOptions;

    case Admin = 'admin';
    case Pentester = 'pentester';
    case Client = 'client';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Pentester => 'Pentester',
            self::Client => 'Cliente',
        };
    }
}
