<?php

namespace App\Support\Cvss;

use App\Enums\Severity;
use InvalidArgumentException;

/**
 * CVSS v3.1 base score calculator, implemented straight from the FIRST
 * specification (https://www.first.org/cvss/v3.1/specification-document).
 */
final class Cvss31
{
    public const PREFIX = 'CVSS:3.1';

    public const METRICS = ['AV', 'AC', 'PR', 'UI', 'S', 'C', 'I', 'A'];

    /** @var array<string, array<string, float>> */
    private const WEIGHTS = [
        'AV' => ['N' => 0.85, 'A' => 0.62, 'L' => 0.55, 'P' => 0.2],
        'AC' => ['L' => 0.77, 'H' => 0.44],
        'UI' => ['N' => 0.85, 'R' => 0.62],
        'C' => ['H' => 0.56, 'L' => 0.22, 'N' => 0.0],
        'I' => ['H' => 0.56, 'L' => 0.22, 'N' => 0.0],
        'A' => ['H' => 0.56, 'L' => 0.22, 'N' => 0.0],
    ];

    /** Privileges Required weight depends on Scope. */
    private const PR = [
        'U' => ['N' => 0.85, 'L' => 0.62, 'H' => 0.27],
        'C' => ['N' => 0.85, 'L' => 0.68, 'H' => 0.5],
    ];

    /**
     * @param  array<string, string>  $metrics
     */
    private function __construct(public readonly array $metrics) {}

    public static function parse(string $vector): self
    {
        $parts = explode('/', trim($vector));

        if (array_shift($parts) !== self::PREFIX) {
            throw new InvalidArgumentException('El vector debe empezar por CVSS:3.1/');
        }

        $metrics = [];
        foreach ($parts as $part) {
            [$key, $value] = array_pad(explode(':', $part, 2), 2, null);

            if (! in_array($key, self::METRICS, true) || isset($metrics[$key])) {
                throw new InvalidArgumentException("Métrica no válida o repetida: {$part}");
            }

            $allowed = match ($key) {
                'PR' => ['N', 'L', 'H'],
                'S' => ['U', 'C'],
                default => array_keys(self::WEIGHTS[$key]),
            };

            if (! in_array($value, $allowed, true)) {
                throw new InvalidArgumentException("Valor no válido para {$key}: {$value}");
            }

            $metrics[$key] = $value;
        }

        if (count($metrics) !== count(self::METRICS)) {
            throw new InvalidArgumentException('Faltan métricas base en el vector');
        }

        return new self($metrics);
    }

    public static function isValid(?string $vector): bool
    {
        if ($vector === null) {
            return false;
        }

        try {
            self::parse($vector);

            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    public function baseScore(): float
    {
        $m = $this->metrics;
        $changed = $m['S'] === 'C';

        $iss = 1 - (
            (1 - self::WEIGHTS['C'][$m['C']])
            * (1 - self::WEIGHTS['I'][$m['I']])
            * (1 - self::WEIGHTS['A'][$m['A']])
        );

        $impact = $changed
            ? 7.52 * ($iss - 0.029) - 3.25 * (($iss - 0.02) ** 15)
            : 6.42 * $iss;

        if ($impact <= 0) {
            return 0.0;
        }

        $exploitability = 8.22
            * self::WEIGHTS['AV'][$m['AV']]
            * self::WEIGHTS['AC'][$m['AC']]
            * self::PR[$m['S']][$m['PR']]
            * self::WEIGHTS['UI'][$m['UI']];

        return $changed
            ? self::roundUp(min(1.08 * ($impact + $exploitability), 10))
            : self::roundUp(min($impact + $exploitability, 10));
    }

    public function severity(): Severity
    {
        return Severity::fromScore($this->baseScore());
    }

    public function toString(): string
    {
        $pairs = array_map(fn (string $key) => "{$key}:{$this->metrics[$key]}", self::METRICS);

        return self::PREFIX.'/'.implode('/', $pairs);
    }

    /**
     * Round-up as defined in Appendix A of the spec, avoiding float drift.
     */
    public static function roundUp(float $value): float
    {
        $int = (int) round($value * 100000);

        if ($int % 10000 === 0) {
            return $int / 100000.0;
        }

        return (floor($int / 10000) + 1) / 10.0;
    }
}
