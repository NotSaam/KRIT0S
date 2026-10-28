import { useRef, type ReactNode } from "react";
import { motion, useReducedMotion, useScroll, useTransform, type MotionValue } from "framer-motion";
import { demoFindings } from "../data/content";
import { Section, SectionHead } from "./ui";

const SEV = [
  { k: "Crítica", n: 1, c: "#f43f5e" },
  { k: "Alta", n: 2, c: "#fb923c" },
  { k: "Media", n: 2, c: "#fbbf24" },
  { k: "Baja", n: 1, c: "#38bdf8" },
];

function Cover() {
  return (
    <div className="flex h-full flex-col p-[8%]">
      <div className="flex items-center gap-2">
        <span className="h-3 w-3 rounded-sm bg-[#0d9488]" />
        <span className="font-display text-[0.7em] font-bold tracking-tight">KRIT0S</span>
      </div>
      <div className="mt-auto">
        <p className="font-mono text-[0.45em] tracking-[0.2em] text-[#0d9488] uppercase">Informe de pentest · Confidencial</p>
        <p className="mt-2 font-display text-[1.35em] leading-[1.05] font-bold">Nortia Retail S.L.</p>
        <p className="mt-1 text-[0.55em] text-[#15151f]/60">Aplicación web y API · Caja gris</p>
      </div>
      <div className="mt-[10%] flex justify-between border-t border-[#15151f]/15 pt-2 font-mono text-[0.4em] text-[#15151f]/60">
        <span>KRT-2026-004</span>
        <span>v1.2 · 28/10/2026</span>
      </div>
    </div>
  );
}

function Summary() {
  return (
    <div className="flex h-full flex-col p-[8%]">
      <p className="font-mono text-[0.42em] tracking-[0.2em] text-[#0d9488] uppercase">01 · Resumen ejecutivo</p>
      <p className="mt-2 font-display text-[0.85em] leading-tight font-bold">Riesgo global: alto</p>
      <div className="mt-2 space-y-1">{[95, 88, 70].map((w) => <div key={w} className="h-[0.3em] rounded bg-[#15151f]/12" style={{ width: `${w}%` }} />)}</div>
      <div className="mt-auto flex h-[38%] items-end gap-[6%] border-b border-[#15151f]/15 pb-1">
        {SEV.map((s) => (
          <div key={s.k} className="flex flex-1 flex-col items-center gap-1">
            <span className="font-mono text-[0.42em] font-bold">{s.n}</span>
            <div className="w-full rounded-t-sm" style={{ background: s.c, height: `${s.n * 38}%`, minHeight: "0.6em" }} />
          </div>
        ))}
      </div>
      <div className="mt-1 flex gap-[6%] font-mono text-[0.36em] text-[#15151f]/60">{SEV.map((s) => <span key={s.k} className="flex-1 text-center">{s.k}</span>)}</div>
    </div>
  );
}

function Detail() {
  const f = demoFindings[0];
  return (
    <div className="flex h-full flex-col p-[8%]">
      <p className="font-mono text-[0.42em] tracking-[0.2em] text-[#0d9488] uppercase">03 · Hallazgos</p>
      <div className="mt-2 flex items-start justify-between gap-2">
        <p className="font-display text-[0.7em] leading-tight font-bold">{f.id} · {f.title}</p>
        <span className="shrink-0 rounded px-1.5 py-0.5 font-mono text-[0.42em] font-bold text-white" style={{ background: "#f43f5e" }}>{f.score}</span>
      </div>
      <p className="mt-1 font-mono text-[0.36em] break-all text-[#15151f]/55">CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H</p>
      {["Descripción", "Evidencia", "Remediación"].map((h, i) => (
        <div key={h} className="mt-[6%]">
          <p className="text-[0.48em] font-semibold">{h}</p>
          {i === 1 ? (
            <div className="mt-1 rounded bg-[#15151f] p-[4%] font-mono text-[0.36em] leading-relaxed text-[#2dd4bf]">?sort=id;SELECT pg_sleep(5)--<br /><span className="text-white/50">→ respuesta en 5.02 s</span></div>
          ) : (
            <div className="mt-1 space-y-[0.25em]">{[92, 80].map((w) => <div key={w} className="h-[0.28em] rounded bg-[#15151f]/12" style={{ width: `${w}%` }} />)}</div>
          )}
        </div>
      ))}
    </div>
  );
}

const PAGES = [
  { C: Cover, label: "Portada" },
  { C: Summary, label: "Resumen ejecutivo" },
  { C: Detail, label: "Detalle técnico" },
];

function Page({ i, progress, children }: { i: number; progress: MotionValue<number>; children: ReactNode }) {
  const reduce = useReducedMotion();
  const offset = i - 1; // -1, 0, 1
  const x = useTransform(progress, [0, 1], ["0%", `${offset * 62}%`]);
  const rotate = useTransform(progress, [0, 1], [offset * -3, offset * 5]);
  const y = useTransform(progress, [0, 1], [i * -6, Math.abs(offset) * 26]);
  return (
    <motion.div
      style={reduce ? { x: `${offset * 62}%`, rotate: offset * 5, y: Math.abs(offset) * 26 } : { x, rotate, y }}
      className="paper absolute top-0 left-1/2 aspect-[1/1.414] w-[40%] -translate-x-1/2 overflow-hidden rounded-[0.4em] text-[clamp(10px,2.4vw,26px)]"
    >
      {children}
    </motion.div>
  );
}

export default function ReportPreview() {
  const ref = useRef<HTMLDivElement>(null);
  const { scrollYProgress } = useScroll({ target: ref, offset: ["start end", "center center"] });

  return (
    <Section id="informe">
      <SectionHead index="04" kicker="El entregable" title={<>Un PDF que <span className="text-accent">no da vergüenza</span> enviar.</>}>
        Portada confidencial, resumen ejecutivo para dirección, gráfico de severidades y fichas técnicas con vector CVSS, prueba de concepto y remediación. Se genera en una cola de Redis y cada versión queda sellada con su SHA-256.
      </SectionHead>

      <div ref={ref} className="relative mx-auto aspect-[16/10] w-full max-w-5xl">
        <div aria-hidden className="absolute inset-x-[10%] bottom-0 h-1/3 rounded-full bg-accent/10 blur-3xl" />
        {PAGES.map(({ C, label }, i) => (
          <Page key={label} i={i} progress={scrollYProgress}>
            <span className="sr-only">{label}</span>
            <C />
          </Page>
        ))}
      </div>

      <ul className="mx-auto mt-6 flex max-w-3xl flex-wrap justify-center gap-x-8 gap-y-2 font-mono text-xs text-muted">
        {["Plantilla Blade + dompdf", "Huella SHA-256 por versión", "Versiones inmutables", "Generación en cola (Redis)"].map((t) => (
          <li key={t} className="flex items-center gap-2"><span className="text-accent">▪</span>{t}</li>
        ))}
      </ul>
    </Section>
  );
}
