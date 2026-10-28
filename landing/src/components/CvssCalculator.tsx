import { useMemo, useState } from "react";
import { motion, useReducedMotion } from "framer-motion";
import { calculate, METRIC_ORDER, METRICS, parseVector, type CvssMetrics, type MetricKey } from "../lib/cvss";
import { severityColor, severityEs } from "../data/content";
import { Section, SectionHead } from "./ui";

const PRESETS: { name: string; vector: string }[] = [
  { name: "SQLi sin auth", vector: "CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H" },
  { name: "XSS reflejado", vector: "CVSS:3.1/AV:N/AC:L/PR:N/UI:R/S:C/C:L/I:L/A:N" },
  { name: "IDOR", vector: "CVSS:3.1/AV:N/AC:L/PR:L/UI:N/S:U/C:H/I:H/A:N" },
  { name: "Local, difícil", vector: "CVSS:3.1/AV:L/AC:H/PR:H/UI:R/S:U/C:L/I:N/A:N" },
];

/** Gauge circular de 0 a 10. */
function Gauge({ score, color }: { score: number; color: string }) {
  const reduce = useReducedMotion();
  const R = 74;
  const C = 2 * Math.PI * R;
  return (
    <svg viewBox="0 0 180 180" className="h-44 w-44 sm:h-52 sm:w-52" aria-hidden>
      <circle cx="90" cy="90" r={R} fill="none" stroke="var(--color-line)" strokeWidth="10" />
      {Array.from({ length: 40 }).map((_, i) => {
        const a = (i / 40) * Math.PI * 2 - Math.PI / 2;
        return <line key={i} x1={90 + Math.cos(a) * 86} y1={90 + Math.sin(a) * 86} x2={90 + Math.cos(a) * (i % 4 === 0 ? 80 : 83)} y2={90 + Math.sin(a) * (i % 4 === 0 ? 80 : 83)} stroke="var(--color-line-bright)" strokeWidth="1" />;
      })}
      <motion.circle
        cx="90" cy="90" r={R} fill="none" stroke={color} strokeWidth="10" strokeLinecap="round"
        strokeDasharray={C}
        initial={false}
        animate={{ strokeDashoffset: C * (1 - score / 10), stroke: color }}
        transition={reduce ? { duration: 0 } : { duration: 0.8, ease: [0.16, 1, 0.3, 1] }}
        transform="rotate(-90 90 90)"
        style={{ filter: `drop-shadow(0 0 10px ${color})` }}
      />
    </svg>
  );
}

export default function CvssCalculator() {
  const [m, setM] = useState<CvssMetrics>(() => parseVector(PRESETS[0].vector));
  const r = useMemo(() => calculate(m), [m]);
  const color = severityColor[r.severity];
  const [copied, setCopied] = useState(false);

  const set = (k: MetricKey, v: string) => setM((prev) => ({ ...prev, [k]: v }) as CvssMetrics);

  async function copy() {
    try {
      await navigator.clipboard.writeText(r.vector);
      setCopied(true);
      window.setTimeout(() => setCopied(false), 1400);
    } catch {
      /* portapapeles no disponible */
    }
  }

  return (
    <Section id="cvss">
      <SectionHead index="03" kicker="Pruébalo" title={<>CVSS 3.1, <span className="text-accent">en vivo.</span></>}>
        La misma lógica que usa la plataforma: fórmula oficial de FIRST, con su redondeo hacia arriba. Toca las métricas y mira cómo cambia el veredicto.
      </SectionHead>

      <div className="grid gap-6 lg:grid-cols-[1.35fr_1fr]">
        <div className="min-w-0 rounded-3xl border border-line bg-panel/70 p-5 sm:p-8">
          <div className="mb-6 flex flex-wrap gap-2">
            <span className="hud-label mr-2 self-center">presets</span>
            {PRESETS.map((p) => (
              <button key={p.name} type="button" onClick={() => setM(parseVector(p.vector))} className="rounded-full border border-line-bright px-3 py-1 font-mono text-[0.7rem] text-muted transition-colors hover:border-accent hover:text-accent">
                {p.name}
              </button>
            ))}
          </div>
          <div className="grid gap-x-8 gap-y-5 sm:grid-cols-2">
            {METRIC_ORDER.map((k) => (
              <fieldset key={k} className="min-w-0">
                <legend className="mb-2 flex items-baseline gap-2 text-sm">
                  <span className="font-mono text-xs text-accent">{k}</span>
                  <span className="text-muted">{METRICS[k].label}</span>
                </legend>
                <div className="flex flex-wrap gap-1.5">
                  {METRICS[k].options.map((o) => {
                    const on = m[k] === o.value;
                    return (
                      <button
                        key={o.value}
                        type="button"
                        aria-pressed={on}
                        onClick={() => set(k, o.value)}
                        className={`relative rounded-lg border px-3 py-1.5 font-mono text-xs transition-colors ${on ? "border-accent text-night" : "border-line-bright text-muted hover:border-faint hover:text-ink"}`}
                      >
                        {on && <motion.span layoutId={`pill-${k}`} className="absolute inset-0 rounded-[7px] bg-accent" transition={{ type: "spring", stiffness: 420, damping: 32 }} />}
                        <span className="relative">{o.label}</span>
                      </button>
                    );
                  })}
                </div>
              </fieldset>
            ))}
          </div>
        </div>

        <div className="hud-corners scanlines relative flex min-w-0 flex-col items-center justify-center overflow-hidden rounded-3xl border border-line bg-panel/70 p-6 text-center sm:p-8" aria-live="polite">
          <div aria-hidden className="pointer-events-none absolute inset-0 opacity-30 transition-colors duration-700" style={{ background: `radial-gradient(circle at 50% 40%, ${color}, transparent 60%)` }} />
          <div className="relative">
            <Gauge score={r.score} color={color} />
            <div className="absolute inset-0 flex flex-col items-center justify-center">
              <span className="font-display text-6xl leading-none font-bold tracking-tighter tabular-nums sm:text-7xl">{r.score.toFixed(1)}</span>
              <span className="mt-1 font-mono text-xs tracking-[0.25em] uppercase" style={{ color }}>{severityEs[r.severity]}</span>
            </div>
          </div>
          <dl className="relative mt-6 grid w-full grid-cols-2 gap-3 text-left">
            <div className="rounded-xl border border-line bg-night/60 p-3"><dt className="hud-label">impacto</dt><dd className="mt-1 font-mono text-lg">{r.impact.toFixed(1)}</dd></div>
            <div className="rounded-xl border border-line bg-night/60 p-3"><dt className="hud-label">explotabilidad</dt><dd className="mt-1 font-mono text-lg">{r.exploitability.toFixed(1)}</dd></div>
          </dl>
          <button type="button" onClick={copy} className="relative mt-3 w-full rounded-xl border border-line bg-night/60 p-3 text-left transition-colors hover:border-accent" aria-label={`Copiar vector ${r.vector}`}>
            <span className="hud-label flex justify-between"><span>vector</span><span className="text-accent">{copied ? "copiado ✓" : "copiar"}</span></span>
            <code className="mt-1 block font-mono text-[0.68rem] break-all text-blue-neon sm:text-xs">{r.vector}</code>
          </button>
        </div>
      </div>
    </Section>
  );
}
