<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum FindingStatus: string
{
    use HasOptions;

    case Open = 'open';
    case Retest = 'retest';
    case Remediated = 'remediated';
    case AcceptedRisk = 'accepted_risk';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Abierto',
            self::Retest => 'Pendiente de retest',
            self::Remediated => 'Remediado',
            self::AcceptedRisk => 'Riesgo aceptado',
        };
    }
}
