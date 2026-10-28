import type { EngagementStatus, FindingStatus, Role, Severity } from '@/types';

export const SEVERITIES: Severity[] = ['critical', 'high', 'medium', 'low', 'info'];

export const severityMeta: Record<Severity, { label: string; color: string; classes: string }> = {
    critical: { label: 'Crítica', color: '#f43f5e', classes: 'bg-rose-500/15 text-rose-400 ring-rose-500/30 dark:text-rose-300' },
    high: { label: 'Alta', color: '#fb923c', classes: 'bg-orange-500/15 text-orange-500 ring-orange-500/30 dark:text-orange-300' },
    medium: { label: 'Media', color: '#facc15', classes: 'bg-yellow-400/15 text-yellow-600 ring-yellow-400/30 dark:text-yellow-200' },
    low: { label: 'Baja', color: '#38bdf8', classes: 'bg-sky-400/15 text-sky-600 ring-sky-400/30 dark:text-sky-300' },
    info: { label: 'Info', color: '#8a8aa0', classes: 'bg-zinc-400/15 text-zinc-500 ring-zinc-400/30 dark:text-zinc-300' },
};

export const engagementStatusMeta: Record<EngagementStatus, { label: string; dot: string }> = {
    planning: { label: 'Planificación', dot: 'bg-zinc-400' },
    in_progress: { label: 'En curso', dot: 'bg-teal-400' },
    review: { label: 'Revisión', dot: 'bg-violet-400' },
    delivered: { label: 'Entregado', dot: 'bg-sky-400' },
};

export const findingStatusMeta: Record<FindingStatus, { label: string; dot: string }> = {
    open: { label: 'Abierto', dot: 'bg-rose-400' },
    retest: { label: 'Retest', dot: 'bg-violet-400' },
    remediated: { label: 'Remediado', dot: 'bg-teal-400' },
    accepted_risk: { label: 'Riesgo aceptado', dot: 'bg-zinc-400' },
};

export const roleLabel: Record<Role, string> = {
    admin: 'Administrador',
    pentester: 'Pentester',
    client: 'Cliente',
};

export const engagementTypeLabel: Record<string, string> = {
    web: 'Aplicación web',
    api: 'API',
    network: 'Red',
    mobile: 'Móvil',
    cloud: 'Cloud',
    active_directory: 'Active Directory',
};

export function formatDate(value: string | null | undefined): string {
    if (!value) return '—';
    return new Date(value).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
}

export function formatDateTime(value: string | null | undefined): string {
    if (!value) return '—';
    return new Date(value).toLocaleString('es-ES', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
}
