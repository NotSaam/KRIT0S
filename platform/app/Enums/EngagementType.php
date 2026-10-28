<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum EngagementType: string
{
    use HasOptions;

    case Web = 'web';
    case Api = 'api';
    case Network = 'network';
    case Mobile = 'mobile';
    case Cloud = 'cloud';
    case ActiveDirectory = 'active_directory';

    public function label(): string
    {
        return match ($this) {
            self::Web => 'Aplicación web',
            self::Api => 'API',
            self::Network => 'Red interna/externa',
            self::Mobile => 'Aplicación móvil',
            self::Cloud => 'Cloud',
            self::ActiveDirectory => 'Active Directory',
        };
    }
}
