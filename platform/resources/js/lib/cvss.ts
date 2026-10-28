/**
 * CVSS v3.1 base score, mirror of App\Support\Cvss\Cvss31 so the form can
 * preview the score live. The server always recomputes it on save.
 */
import type { Severity } from '@/types';

export const METRIC_ORDER = ['AV', 'AC', 'PR', 'UI', 'S', 'C', 'I', 'A'] as const;
export type MetricKey = (typeof METRIC_ORDER)[number];
export type Metrics = Record<MetricKey, string>;

export const METRICS: Record<MetricKey, { label: string; options: { value: string; label: string }[] }> = {
    AV: { label: 'Vector de ataque', options: [{ value: 'N', label: 'Red' }, { value: 'A', label: 'Adyacente' }, { value: 'L', label: 'Local' }, { value: 'P', label: 'Física' }] },
    AC: { label: 'Complejidad', options: [{ value: 'L', label: 'Baja' }, { value: 'H', label: 'Alta' }] },
    PR: { label: 'Privilegios', options: [{ value: 'N', label: 'Ninguno' }, { value: 'L', label: 'Bajos' }, { value: 'H', label: 'Altos' }] },
    UI: { label: 'Interacción', options: [{ value: 'N', label: 'Ninguna' }, { value: 'R', label: 'Requerida' }] },
    S: { label: 'Alcance', options: [{ value: 'U', label: 'Sin cambio' }, { value: 'C', label: 'Cambiado' }] },
    C: { label: 'Confidencialidad', options: [{ value: 'H', label: 'Alta' }, { value: 'L', label: 'Baja' }, { value: 'N', label: 'Ninguna' }] },
    I: { label: 'Integridad', options: [{ value: 'H', label: 'Alta' }, { value: 'L', label: 'Baja' }, { value: 'N', label: 'Ninguna' }] },
    A: { label: 'Disponibilidad', options: [{ value: 'H', label: 'Alta' }, { value: 'L', label: 'Baja' }, { value: 'N', label: 'Ninguna' }] },
};

const W = {
    AV: { N: 0.85, A: 0.62, L: 0.55, P: 0.2 },
    AC: { L: 0.77, H: 0.44 },
    PR_U: { N: 0.85, L: 0.62, H: 0.27 },
    PR_C: { N: 0.85, L: 0.68, H: 0.5 },
    UI: { N: 0.85, R: 0.62 },
    CIA: { H: 0.56, L: 0.22, N: 0 },
} as Record<string, Record<string, number>>;

export const DEFAULT_VECTOR = 'CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:N/I:N/A:N';

export function roundUp(input: number): number {
    const int = Math.round(input * 100000);
    if (int % 10000 === 0) return int / 100000;
    return (Math.floor(int / 10000) + 1) / 10;
}

export function severityOf(score: number): Severity {
    if (score >= 9) return 'critical';
    if (score >= 7) return 'high';
    if (score >= 4) return 'medium';
    if (score > 0) return 'low';
    return 'info';
}

export function baseScore(m: Metrics): number {
    const changed = m.S === 'C';
    const iss = 1 - (1 - W.CIA[m.C]) * (1 - W.CIA[m.I]) * (1 - W.CIA[m.A]);
    const impact = changed ? 7.52 * (iss - 0.029) - 3.25 * Math.pow(iss - 0.02, 15) : 6.42 * iss;
    if (impact <= 0) return 0;

    const pr = changed ? W.PR_C[m.PR] : W.PR_U[m.PR];
    const exploitability = 8.22 * W.AV[m.AV] * W.AC[m.AC] * pr * W.UI[m.UI];

    return changed ? roundUp(Math.min(1.08 * (impact + exploitability), 10)) : roundUp(Math.min(impact + exploitability, 10));
}

export function toVector(m: Metrics): string {
    return 'CVSS:3.1/' + METRIC_ORDER.map((k) => `${k}:${m[k]}`).join('/');
}

/** Returns null instead of throwing so the form can show a soft error. */
export function parseVector(vector: string): Metrics | null {
    const parts = vector.trim().split('/');
    if (parts.shift() !== 'CVSS:3.1') return null;

    const out: Partial<Metrics> = {};
    for (const part of parts) {
        const [key, value] = part.split(':') as [MetricKey, string];
        if (!METRICS[key] || out[key] || !METRICS[key].options.some((o) => o.value === value)) return null;
        out[key] = value;
    }

    return METRIC_ORDER.every((k) => out[k]) ? (out as Metrics) : null;
}
