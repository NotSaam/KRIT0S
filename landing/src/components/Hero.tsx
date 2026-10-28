import { useEffect, useRef, useState } from "react";
import type { PointerEvent } from "react";
import { motion, useReducedMotion, useScroll, useTransform } from "framer-motion";
import { EASE_SIGNATURE, staggerContainer, staggerItem } from "../lib/motion";
import { links } from "../data/content";
import { SeverityBadge } from "./ui";

const LETTERS = ["K", "R", "I", "T", "0", "S"];

/** Líneas que "teclea" la terminal del hero: un hallazgo naciendo. */
const SCRIPT: { t: string; cls?: string }[] = [
  { t: "$ krit0s finding:new --engagement KRT-2026-004", cls: "text-ink" },
  { t: "  title   » SQL injection en /api/orders?sort" },
  { t: "  asset   » api.nortia.test" },
  { t: "  vector  » CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H", cls: "text-blue-neon" },
  { t: "  score   » 9.8  (calculado)", cls: "text-accent" },
  { t: "✓ hallazgo KRT-014 añadido al informe", cls: "text-accent" },
];

function useScriptTyping(enabled: boolean) {
  const [shown, setShown] = useState(() => (enabled ? 0 : SCRIPT.reduce((n, l) => n + l.t.length, 0)));
  const total = SCRIPT.reduce((n, l) => n + l.t.length, 0);
  useEffect(() => {
    if (!enabled) return;
    let id: number;
    let n = 0;
    const tick = () => {
      n += 1;
      setShown(n);
      if (n < total) id = window.setTimeout(tick, n < SCRIPT[0].t.length ? 38 : 14);
      else id = window.setTimeout(() => { n = 0; setShown(0); id = window.setTimeout(tick, 600); }, 5200);
    };
    id = window.setTimeout(tick, 1100);
    return () => window.clearTimeout(id);
  }, [enabled, total]);
  return { shown, done: shown >= total };
}

export default function Hero() {
  const reduce = useReducedMotion();
  const ref = useRef<HTMLElement>(null);
  const { scrollYProgress } = useScroll({ target: ref, offset: ["start start", "end start"] });
  const wordY = useTransform(scrollYProgress, [0, 1], ["0%", "-28%"]);
  const wordOpacity = useTransform(scrollYProgress, [0, 0.8], [1, 0.15]);
  const { shown, done } = useScriptTyping(!reduce);

  function onPointerMove(e: PointerEvent<HTMLElement>) {
    const el = ref.current;
    if (!el || reduce) return;
    const r = el.getBoundingClientRect();
    el.style.setProperty("--mx", `${e.clientX - r.left}px`);
    el.style.setProperty("--my", `${e.clientY - r.top}px`);
  }

  let budget = shown;

  return (
    <section
      id="inicio"
      ref={ref}
      onPointerMove={onPointerMove}
      className="relative flex min-h-[100svh] flex-col justify-center overflow-hidden px-4 pt-24 pb-16 sm:px-8"
    >
      <div aria-hidden className="grid-overlay pointer-events-none absolute inset-0" />
      <div
        aria-hidden
        className="pointer-events-none absolute inset-0"
        style={{ background: "radial-gradient(620px circle at var(--mx, 70%) var(--my, 30%), rgba(45,212,191,0.12), transparent 65%)" }}
      />

      <div className="relative z-10 mx-auto w-full max-w-7xl">
        <motion.div
          initial={reduce ? false : { opacity: 0, y: 12 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.7, ease: EASE_SIGNATURE }}
          className="mb-6 flex flex-wrap items-center gap-x-4 gap-y-2"
        >
          <span className="inline-flex items-center gap-2.5 rounded-full border border-line bg-panel/60 px-3.5 py-1.5 font-mono text-[0.65rem] tracking-widest text-muted uppercase backdrop-blur sm:text-xs">
            <span className="pulse-ring inline-block h-2 w-2 rounded-full bg-accent" />
            Plataforma de reporting de pentest
          </span>
          <span className="hud-label hidden sm:inline">κριτής · el que juzga</span>
        </motion.div>

        {/* Wordmark gigante, letra a letra */}
        <motion.h1 style={reduce ? undefined : { y: wordY, opacity: wordOpacity }} className="font-display leading-[0.82] font-bold tracking-[-0.04em]">
          <span className="sr-only">KRIT0S</span>
          <span aria-hidden className="glitch block text-[clamp(4.5rem,21vw,19rem)] text-ink" data-text="KRIT0S">
            {LETTERS.map((l, i) => (
              <motion.span
                key={i}
                className={`inline-block ${l === "0" ? "font-mono font-bold tracking-[-0.08em] text-accent [text-shadow:0_0_60px_rgba(45,212,191,0.45)]" : ""}`}
                initial={reduce ? false : { y: "0.6em", opacity: 0, filter: "blur(12px)" }}
                animate={{ y: 0, opacity: 1, filter: "blur(0px)" }}
                transition={{ duration: 1.1, ease: EASE_SIGNATURE, delay: 0.1 + i * 0.07 }}
              >
                {l}
              </motion.span>
            ))}
          </span>
        </motion.h1>

        <motion.div
          variants={staggerContainer}
          initial={reduce ? false : "hidden"}
          animate="show"
          className="mt-8 grid items-end gap-10 lg:mt-10 lg:grid-cols-[1fr_1.1fr] lg:gap-16"
        >
          <div>
            <motion.p variants={staggerItem} className="font-display text-[clamp(1.6rem,5vw,3rem)] leading-[1.05] font-semibold tracking-tight text-balance">
              Del hallazgo <span className="text-outline">al</span> <span className="text-accent">veredicto.</span>
            </motion.p>
            <motion.p variants={staggerItem} className="mt-5 max-w-xl text-pretty text-[clamp(0.95rem,2.6vw,1.1rem)] text-muted">
              Gestiona engagements, documenta hallazgos con CVSS 3.1 calculado al vuelo, reutiliza tu biblioteca de plantillas y entrega un PDF versionado. El cliente lo consulta en su propio portal.
            </motion.p>
            <motion.div variants={staggerItem} className="mt-8 flex flex-wrap gap-3">
              <a href="#cvss" className="group relative inline-flex items-center gap-2 overflow-hidden rounded-full bg-accent px-6 py-3 font-mono text-xs font-bold tracking-widest text-night uppercase glow-accent">
                <span className="relative z-10">Probar el CVSS</span>
                <span className="relative z-10 transition-transform duration-300 group-hover:translate-x-1">→</span>
                <span aria-hidden className="absolute inset-0 -translate-x-full bg-white/30 transition-transform duration-500 ease-[var(--ease-signature)] group-hover:translate-x-0" />
              </a>
              <a href={links.repo} target="_blank" rel="noreferrer" className="inline-flex items-center gap-2 rounded-full border border-line-bright px-6 py-3 font-mono text-xs tracking-widest uppercase transition-colors hover:border-accent hover:text-accent">
                Ver código ↗
              </a>
            </motion.div>
          </div>

          {/* Terminal: un hallazgo tecleándose */}
          <motion.div variants={staggerItem} className="hud-corners scanlines relative min-w-0 overflow-hidden rounded-2xl border border-line bg-panel/80 shadow-2xl shadow-black/50 backdrop-blur">
            <div className="flex items-center justify-between border-b border-line px-4 py-2.5">
              <div className="flex gap-1.5" aria-hidden>
                <span className="h-2.5 w-2.5 rounded-full bg-line-bright" />
                <span className="h-2.5 w-2.5 rounded-full bg-line-bright" />
                <span className="h-2.5 w-2.5 rounded-full bg-line-bright" />
              </div>
              <span className="hud-label">engagement · KRT-2026-004</span>
            </div>
            <pre aria-label="Ejemplo: alta de un hallazgo SQL injection con CVSS 9.8" className="min-h-[13.5rem] overflow-x-auto p-4 font-mono text-[0.7rem] leading-relaxed text-muted sm:p-5 sm:text-[0.78rem]">
              {SCRIPT.map((line, i) => {
                const take = Math.max(0, Math.min(line.t.length, budget));
                budget -= line.t.length;
                if (take === 0 && i > 0) return null;
                const typing = take < line.t.length;
                return (
                  <div key={i} className={line.cls}>
                    {line.t.slice(0, take)}
                    {typing && <span className="type-cursor text-accent">▍</span>}
                  </div>
                );
              })}
            </pre>
            <div className="flex items-center justify-between gap-3 border-t border-line px-4 py-3">
              <span className="hud-label truncate">severidad</span>
              <motion.div animate={{ opacity: done ? 1 : 0.25, scale: done ? 1 : 0.96 }} transition={{ duration: 0.4 }}>
                <SeverityBadge severity="Critical" score={9.8} />
              </motion.div>
            </div>
          </motion.div>
        </motion.div>
      </div>

      <a href="#problema" className="hud-label absolute bottom-6 left-1/2 hidden -translate-x-1/2 items-center gap-2 transition-colors hover:text-ink sm:flex">
        scroll <span aria-hidden>↓</span>
      </a>
    </section>
  );
}
