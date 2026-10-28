<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $engagement->code }} · Informe de pentest</title>
    <style>
        @page { margin: 22mm 18mm 20mm; }
        * { font-family: "DejaVu Sans", sans-serif; }
        body { color: #1b1b29; font-size: 10pt; line-height: 1.5; }
        h1, h2, h3 { margin: 0; color: #06060a; }
        h2 { font-size: 15pt; margin-bottom: 10px; padding-bottom: 6px; border-bottom: 2px solid #2dd4bf; }
        h3 { font-size: 12pt; }
        .muted { color: #6b6b80; }
        .mono { font-family: "DejaVu Sans Mono", monospace; }
        .label { font-size: 7.5pt; letter-spacing: 2px; text-transform: uppercase; color: #6b6b80; }
        .page-break { page-break-after: always; }

        /* Cover */
        /* Absolutely positioned so the full-bleed cover never spills onto extra pages. */
        .cover { position: absolute; top: -22mm; left: -18mm; width: 174mm; height: 257mm; background: #06060a; color: #e9e9f3; padding: 40mm 18mm 0; }
        .cover .brand { font-size: 11pt; letter-spacing: 6px; color: #2dd4bf; }
        .cover h1 { color: #e9e9f3; font-size: 30pt; line-height: 1.15; margin: 18mm 0 6mm; }
        .cover .client { font-size: 14pt; color: #a7a7bd; }
        .cover table.meta { margin-top: 30mm; width: 100%; border-collapse: collapse; }
        .cover table.meta td { padding: 8px 0; border-top: 1px solid #2c2c44; color: #e9e9f3; vertical-align: top; }
        .cover table.meta td.k { width: 38%; color: #8a8aa0; font-size: 8pt; letter-spacing: 2px; text-transform: uppercase; }
        .cover .confidential { margin-top: 26mm; font-size: 8pt; color: #8a8aa0; border-left: 3px solid #8b5cf6; padding-left: 10px; }

        /* Severity */
        .sev { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 7.5pt; font-weight: bold; color: #06060a; text-transform: uppercase; letter-spacing: 1px; }
        .sev-critical { background: #f43f5e; color: #fff; }
        .sev-high { background: #fb923c; }
        .sev-medium { background: #facc15; }
        .sev-low { background: #38bdf8; }
        .sev-info { background: #d4d4de; }

        table.grid { width: 100%; border-collapse: collapse; margin: 8px 0 16px; }
        table.grid th { text-align: left; font-size: 7.5pt; letter-spacing: 1px; text-transform: uppercase; color: #6b6b80; border-bottom: 1px solid #c9c9d6; padding: 6px 6px; }
        table.grid td { border-bottom: 1px solid #ececf2; padding: 7px 6px; vertical-align: top; }

        .bar-track { background: #ececf2; height: 10px; width: 100%; }
        .bar { height: 10px; }

        .kpis { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin: 0 -6px 14px; }
        .kpis td { border: 1px solid #e1e1ea; padding: 10px; text-align: center; }
        .kpis .n { font-size: 20pt; font-weight: bold; }

        .finding { margin-bottom: 18px; page-break-inside: auto; }
        .finding-head { border-left: 4px solid #2dd4bf; padding: 6px 0 6px 10px; margin-bottom: 8px; background: #f6f6fa; }
        .finding-meta { width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 8.5pt; }
        .finding-meta td { padding: 4px 6px; border: 1px solid #ececf2; }
        .finding-meta td.k { width: 22%; background: #fafafc; color: #6b6b80; }
        .section-title { font-size: 8pt; letter-spacing: 1.5px; text-transform: uppercase; color: #0d9488; margin: 10px 0 3px; font-weight: bold; }
        pre { white-space: pre-wrap; background: #0b0b12; color: #d6f5f0; padding: 8px 10px; font-size: 8pt; font-family: "DejaVu Sans Mono", monospace; border-radius: 3px; }

        footer { position: fixed; bottom: -12mm; left: 0; right: 0; font-size: 7.5pt; color: #8a8aa0; }
        footer table { width: 100%; }
    </style>
</head>
<body>

<footer>
    <table><tr>
        <td>KRIT0S · {{ $engagement->code }} · v{{ $version }}</td>
        <td style="text-align:right">CONFIDENCIAL — {{ $engagement->client->name }}</td>
    </tr></table>
</footer>

{{-- ───────────── Portada ───────────── --}}
<div class="cover">
    <div class="brand mono">KRIT0S</div>
    <h1>{{ $engagement->title }}</h1>
    <div class="client">{{ $engagement->client->name }}</div>

    <table class="meta">
        <tr><td class="k">Referencia</td><td class="mono">{{ $engagement->code }}</td></tr>
        <tr><td class="k">Tipo de auditoría</td><td>{{ $engagement->type->label() }}</td></tr>
        <tr><td class="k">Ventana de pruebas</td><td>{{ $engagement->starts_on->format('d/m/Y') }} — {{ $engagement->ends_on->format('d/m/Y') }}</td></tr>
        <tr><td class="k">Responsable</td><td>{{ $engagement->lead?->name ?? '—' }}</td></tr>
        <tr><td class="k">Versión del informe</td><td>v{{ $version }} · {{ $generatedAt->format('d/m/Y H:i') }}</td></tr>
    </table>

    <div class="confidential">
        Documento confidencial. Contiene información sensible sobre la seguridad de {{ $engagement->client->name }}
        y su distribución está limitada a las personas autorizadas.
    </div>
</div>
<div class="page-break"></div>

{{-- ───────────── Resumen ejecutivo ───────────── --}}
<h2>1. Resumen ejecutivo</h2>
<p>{!! nl2br(e($engagement->executive_summary ?: 'Pendiente de redactar.')) !!}</p>

<table class="kpis"><tr>
    @foreach ($severities as $severity)
        <td>
            <div class="n" style="color: {{ $severity->color() }}">{{ $breakdown[$severity->value] }}</div>
            <div class="label">{{ $severity->label() }}</div>
        </td>
    @endforeach
</tr></table>

@php($max = max(1, max($breakdown)))
<table class="grid">
    <tr><th style="width:22%">Severidad</th><th>Distribución</th><th style="width:10%; text-align:right">Nº</th></tr>
    @foreach ($severities as $severity)
        <tr>
            <td><span class="sev sev-{{ $severity->value }}">{{ $severity->label() }}</span></td>
            <td><div class="bar-track"><div class="bar" style="width: {{ round($breakdown[$severity->value] / $max * 100) }}%; background: {{ $severity->color() }}"></div></div></td>
            <td style="text-align:right">{{ $breakdown[$severity->value] }}</td>
        </tr>
    @endforeach
</table>

<h2>2. Alcance y metodología</h2>
<p>{!! nl2br(e($engagement->scope)) !!}</p>
@if ($engagement->methodology)
    <p class="muted">Metodología: {{ $engagement->methodology }}. Las puntuaciones siguen CVSS v3.1 (FIRST).</p>
@endif

<h2>3. Resumen de hallazgos</h2>
<table class="grid">
    <tr><th style="width:9%">ID</th><th>Hallazgo</th><th style="width:15%">Severidad</th><th style="width:9%">CVSS</th><th style="width:17%">Estado</th></tr>
    @forelse ($findings as $finding)
        <tr>
            <td class="mono">{{ $finding->reference }}</td>
            <td>{{ $finding->title }}</td>
            <td><span class="sev sev-{{ $finding->severity->value }}">{{ $finding->severity->label() }}</span></td>
            <td class="mono">{{ number_format($finding->cvss_score, 1) }}</td>
            <td>{{ $finding->status->label() }}</td>
        </tr>
    @empty
        <tr><td colspan="5" class="muted">No se han identificado hallazgos.</td></tr>
    @endforelse
</table>
<div class="page-break"></div>

{{-- ───────────── Detalle ───────────── --}}
<h2>4. Detalle de hallazgos</h2>
@foreach ($findings as $finding)
    <div class="finding">
        <div class="finding-head" style="border-left-color: {{ $finding->severity->color() }}">
            <span class="mono muted">{{ $finding->reference }}</span>
            <h3>{{ $finding->title }}</h3>
        </div>
        <table class="finding-meta">
            <tr>
                <td class="k">Severidad</td><td><span class="sev sev-{{ $finding->severity->value }}">{{ $finding->severity->label() }}</span> &nbsp;{{ number_format($finding->cvss_score, 1) }}</td>
                <td class="k">Estado</td><td>{{ $finding->status->label() }}</td>
            </tr>
            <tr><td class="k">Vector CVSS</td><td colspan="3" class="mono">{{ $finding->cvss_vector }}</td></tr>
            <tr><td class="k">Activos afectados</td><td colspan="3">{!! nl2br(e($finding->affected_assets)) !!}</td></tr>
        </table>

        <div class="section-title">Descripción</div>
        <div>{!! nl2br(e($finding->description)) !!}</div>

        @if ($finding->proof_of_concept)
            <div class="section-title">Prueba de concepto</div>
            <pre>{{ $finding->proof_of_concept }}</pre>
        @endif

        <div class="section-title">Impacto</div>
        <div>{!! nl2br(e($finding->impact)) !!}</div>

        <div class="section-title">Remediación</div>
        <div>{!! nl2br(e($finding->remediation)) !!}</div>

        @if ($finding->references)
            <div class="section-title">Referencias</div>
            <div class="mono" style="font-size:8pt">{!! nl2br(e($finding->references)) !!}</div>
        @endif
    </div>
@endforeach

</body>
</html>
