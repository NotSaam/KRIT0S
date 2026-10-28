<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum EngagementStatus: string
{
    use HasOptions;

    case Planning = 'planning';
    case InProgress = 'in_progress';
    case Review = 'review';
    case Delivered = 'delivered';

    public function label(): string
    {
        return match ($this) {
            self::Planning => 'Planificación',
            self::InProgress => 'En curso',
            self::Review => 'Revisión',
            self::Delivered => 'Entregado',
        };
    }
}
