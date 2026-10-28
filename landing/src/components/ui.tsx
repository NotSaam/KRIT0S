import { motion } from "framer-motion";
import type { ReactNode } from "react";
import { revealVariants } from "../lib/motion";
import { severityColor, severityEs, type SeverityName } from "../data/content";

export function Reveal({ children, className, delay = 0 }: { children: ReactNode; className?: string; delay?: number }) {
  return (
    <motion.div
      className={className}
      variants={revealVariants}
      initial="hidden"
      whileInView="show"
      viewport={{ once: true, margin: "-80px" }}
      transition={{ delay }}
    >
      {children}
    </motion.div>
  );
}

/** Cabecera de sección: índice mono + título grande + entradilla. */
export function SectionHead({ index, kicker, title, children }: { index: string; kicker: string; title: ReactNode; children?: ReactNode }) {
  return (
    <Reveal className="mb-12 max-w-3xl sm:mb-16">
      <p className="hud-label mb-4 flex items-center gap-3">
        <span className="text-accent">{index}</span>
        <span className="h-px w-8 bg-line-bright" />
        {kicker}
      </p>
      <h2 className="font-display text-[clamp(2rem,6.5vw,4.25rem)] leading-[1] font-bold tracking-tight text-balance">{title}</h2>
      {children && <p className="mt-5 max-w-2xl text-pretty text-[clamp(0.95rem,2.6vw,1.1rem)] text-muted">{children}</p>}
    </Reveal>
  );
}

export function SeverityBadge({ severity, score }: { severity: SeverityName | "None"; score?: number }) {
  const c = severityColor[severity];
  return (
    <span
      className="inline-flex items-center gap-1.5 rounded-md border px-2 py-0.5 font-mono text-[0.68rem] font-medium whitespace-nowrap uppercase"
      style={{ color: c, borderColor: `color-mix(in srgb, ${c} 40%, transparent)`, background: `color-mix(in srgb, ${c} 10%, transparent)` }}
    >
      <span className="h-1.5 w-1.5 rounded-full" style={{ background: c }} />
      {severityEs[severity]}
      {score !== undefined && <span className="opacity-80">{score.toFixed(1)}</span>}
    </span>
  );
}

export function Section({ id, children, className = "" }: { id?: string; children: ReactNode; className?: string }) {
  return (
    <section id={id} className={`relative mx-auto w-full max-w-7xl scroll-mt-20 px-4 py-24 sm:px-8 sm:py-32 ${className}`}>
      {children}
    </section>
  );
}
