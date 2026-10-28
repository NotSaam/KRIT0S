<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum Severity: string
{
    use HasOptions;

    case Critical = 'critical';
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';
    case Info = 'info';

    /**
     * Qualitative rating from the CVSS v3.1 specification (section 5).
     */
    public static function fromScore(float $score): self
    {
        return match (true) {
            $score >= 9.0 => self::Critical,
            $score >= 7.0 => self::High,
            $score >= 4.0 => self::Medium,
            $score > 0.0 => self::Low,
            default => self::Info,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Critical => 'Crítica',
            self::High => 'Alta',
            self::Medium => 'Media',
            self::Low => 'Baja',
            self::Info => 'Informativa',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Critical => '#f43f5e',
            self::High => '#fb923c',
            self::Medium => '#facc15',
            self::Low => '#38bdf8',
            self::Info => '#8a8aa0',
        };
    }

    /**
     * @return list<self>
     */
    public static function ordered(): array
    {
        return [self::Critical, self::High, self::Medium, self::Low, self::Info];
    }
}
