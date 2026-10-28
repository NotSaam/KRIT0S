import { useRef, useState } from "react";
import { AnimatePresence, motion, useMotionValueEvent, useScroll } from "framer-motion";
import { T_QUICK } from "../lib/motion";
import { SectionHead, SeverityBadge } from "./ui";

const STEPS = [
  { key: "scope", title: "Alcance", text: "Crea el engagement: cliente, fechas, metodología y activos en scope. Asigna al equipo de pentesters." },
  { key: "finding", title: "Hallazgo", text: "Documenta cada vulnerabilidad desde cero o desde tu biblioteca de plantillas: descripción, impacto, evidencias y remediación." },
  { key: "cvss", title: "CVSS 3.1", text: "Elige las métricas y KRIT0S calcula puntuación y severidad con el redondeo exacto de la especificación." },
  { key: "report", title: "Informe", text: "Portada, resumen ejecutivo, gráfico de severidades y detalle técnico. PDF generado en cola con Redis." },
  { key: "verdict", title: "Veredicto", text: "Publicas y el cliente lo ve en su portal de solo lectura. Cada acción queda en el log de auditoría." },
] as const;

function Visual({ step }: { step: number }) {
  switch (STEPS[step].key) {
    case "scope":
      return (
        <div className="space-y-3">
          <p className="hud-label">KRT-2026-004 · Nortia Retail (ficticio)</p>
          {["api.nortia.test", "portal.nortia.test", "auth.nortia.test", "10.20.0.0/24"].map((a, i) => (
            <motion.div key={a} initial={{ opacity: 0, x: -10 }} animate={{ opacity: 1, x: 0 }} transition={{ ...T_QUICK, delay: i * 0.07 }} className="flex items-center justify-between rounded-lg border border-line bg-night/60 px-4 py-3 font-mono text-xs sm:text-sm">
              <span className="flex items-center gap-3"><span className="text-accent">✓</span>{a}</span>
              <span className="text-faint">{i === 3 ? "interno" : "web"}</span>
            </motion.div>
          ))}
          <div className="flex gap-2 pt-2">
            {["MA", "LC", "SF"].map((u) => (
              <span key={u} className="grid h-8 w-8 place-items-center rounded-full border border-line-bright bg-panel-2 font-mono text-[0.6rem]">{u}</span>
            ))}
          </div>
        </div>
      );
    case "finding":
      return (
        <div className="space-y-4">
          <div className="flex flex-wrap items-center justify-between gap-2">
            <p className="font-display text-xl font-semibold">IDOR en descarga de facturas</p>
            <span className="rounded border border-violet/40 bg-violet/10 px-2 py-0.5 font-mono text-[0.6rem] text-violet uppercase">plantilla · A01</span>
          </div>
          <p className="text-sm text-muted">El endpoint no comprueba que la factura pertenezca al usuario autenticado.</p>
          <pre className="overflow-x-auto rounded-lg border border-line bg-night p-4 font-mono text-[0.7rem] leading-relaxed text-muted">
            <span className="text-blue-neon">GET</span> /invoices/<span className="text-sev-high">48213</span>/pdf{"\n"}Cookie: session=user_7…{"\n"}{"\n"}<span className="text-accent">HTTP/1.1 200 OK</span>{"\n"}Content-Type: application/pdf
          </pre>
        </div>
      );
    case "cvss":
      return (
        <div className="space-y-5">
          <div className="flex flex-wrap gap-1.5">
            {["AV:N", "AC:L", "PR:L", "UI:N", "S:U", "C:H", "I:H", "A:N"].map((m, i) => (
              <motion.span key={m} initial={{ opacity: 0, y: 6 }} animate={{ opacity: 1, y: 0 }} transition={{ ...T_QUICK, delay: i * 0.05 }} className="rounded-md border border-accent/30 bg-accent/10 px-2 py-1 font-mono text-xs text-accent">{m}</motion.span>
            ))}
          </div>
          <div className="flex items-end gap-4">
            <span className="font-display text-7xl leading-none font-bold tracking-tighter">8.1</span>
            <SeverityBadge severity="High" />
          </div>
          <div className="h-2 overflow-hidden rounded-full bg-line">
            <motion.div initial={{ width: 0 }} animate={{ width: "81%" }} transition={{ duration: 0.9, ease: [0.16, 1, 0.3, 1] }} className="h-full rounded-full bg-gradient-to-r from-sev-medium to-sev-high" />
          </div>
        </div>
      );
    case "report":
      return (
        <div className="relative mx-auto h-64 w-48">
          {[2, 1, 0].map((i) => (
            <motion.div key={i} initial={{ rotate: 0, x: 0 }} animate={{ rotate: (i - 1) * 6, x: (i - 1) * 22 }} transition={{ duration: 0.7, ease: [0.16, 1, 0.3, 1] }} className="paper absolute inset-0 rounded-md p-4">
              <div className="h-2 w-10 rounded bg-[#0d9488]" />
              <div className="mt-6 h-3 w-28 rounded bg-[#15151f]" />
              <div className="mt-2 h-2 w-20 rounded bg-[#15151f]/30" />
              <div className="mt-6 space-y-1.5">{[90, 75, 82, 60].map((w) => <div key={w} className="h-1.5 rounded bg-[#15151f]/15" style={{ width: `${w}%` }} />)}</div>
              <div className="absolute right-4 bottom-4 left-4 flex h-12 items-end gap-1.5">{[["#f43f5e", 40], ["#fb923c", 85], ["#fbbf24", 60], ["#38bdf8", 30]].map(([c, h]) => <div key={c} className="flex-1 rounded-sm" style={{ background: c as string, height: `${h}%` }} />)}</div>
            </motion.div>
          ))}
        </div>
      );
    default:
      return (
        <div className="relative space-y-3">
          <p className="hud-label">portal del cliente · solo lectura</p>
          <div className="rounded-xl border border-line bg-night/60 p-5">
            <p className="font-display text-lg font-semibold">Informe final · KRT-2026-004</p>
            <p className="mt-1 font-mono text-xs text-muted">v1.2 · publicado 28/10/2026</p>
            <div className="mt-4 flex flex-wrap gap-2">
              <SeverityBadge severity="Critical" score={9.8} />
              <SeverityBadge severity="High" score={8.1} />
              <SeverityBadge severity="Medium" score={6.1} />
            </div>
          </div>
          <motion.div initial={{ scale: 1.6, opacity: 0, rotate: -18 }} animate={{ scale: 1, opacity: 1, rotate: -8 }} transition={{ type: "spring", stiffness: 260, damping: 16, delay: 0.15 }} className="absolute -top-2 right-2 rounded-md border-2 border-accent px-3 py-1 font-display text-lg font-bold tracking-widest text-accent uppercase">
            Veredicto
          </motion.div>
          <p className="font-mono text-[0.7rem] text-faint">audit · report.published · s.fuentes · 12:04:31</p>
        </div>
      );
  }
}

/** Sección fijada: el scroll avanza el flujo de trabajo paso a paso. */
export default function Workflow() {
  const ref = useRef<HTMLDivElement>(null);
  const [active, setActive] = useState(0);
  const { scrollYProgress } = useScroll({ target: ref, offset: ["start start", "end end"] });
  useMotionValueEvent(scrollYProgress, "change", (p) => setActive(Math.min(STEPS.length - 1, Math.floor(p * STEPS.length))));

  return (
    <section id="flujo" className="relative scroll-mt-16">
      <div className="mx-auto max-w-7xl px-4 pt-24 sm:px-8 sm:pt-32">
        <SectionHead index="02" kicker="El flujo" title={<>Cinco pasos. <span className="text-muted">Un solo sitio.</span></>} />
      </div>

      {/* Escritorio: pin + scroll */}
      <div ref={ref} className="relative hidden lg:block" style={{ height: `${STEPS.length * 85}vh` }}>
        <div className="sticky top-0 flex h-screen items-center">
          <div className="mx-auto grid w-full max-w-7xl grid-cols-[1fr_1.05fr] gap-16 px-8">
            <div className="flex flex-col justify-center">
              <ol className="space-y-1">
                {STEPS.map((s, i) => (
                  <li key={s.key} className="flex items-baseline gap-5">
                    <span className={`font-mono text-xs transition-colors duration-500 ${i === active ? "text-accent" : "text-faint"}`}>0{i + 1}</span>
                    <span className={`font-display text-[clamp(2.5rem,5vw,4.5rem)] leading-[1.05] font-bold tracking-tight transition-all duration-500 ${i === active ? "text-ink" : "text-outline"}`}>{s.title}</span>
                  </li>
                ))}
              </ol>
              <AnimatePresence mode="wait">
                <motion.p key={active} initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: -10 }} transition={T_QUICK} className="mt-8 max-w-md text-lg text-muted">
                  {STEPS[active].text}
                </motion.p>
              </AnimatePresence>
            </div>
            <div className="hud-corners relative flex min-h-[26rem] items-center rounded-3xl border border-line bg-panel/70 p-10 backdrop-blur">
              <div className="absolute top-5 right-6 left-6 flex gap-1.5" aria-hidden>
                {STEPS.map((s, i) => (
                  <span key={s.key} className="h-0.5 flex-1 overflow-hidden rounded bg-line">
                    <span className={`block h-full bg-accent transition-transform duration-500 ${i <= active ? "translate-x-0" : "-translate-x-full"}`} />
                  </span>
                ))}
              </div>
              <AnimatePresence mode="wait">
                <motion.div key={active} className="w-full" initial={{ opacity: 0, y: 24, filter: "blur(6px)" }} animate={{ opacity: 1, y: 0, filter: "blur(0px)" }} exit={{ opacity: 0, y: -16, filter: "blur(6px)" }} transition={T_QUICK}>
                  <Visual step={active} />
                </motion.div>
              </AnimatePresence>
            </div>
          </div>
        </div>
      </div>

      {/* Móvil / tablet: lista vertical */}
      <ol className="mx-auto max-w-2xl space-y-6 px-4 pb-24 sm:px-8 lg:hidden">
        {STEPS.map((s, i) => (
          <li key={s.key} className="rounded-2xl border border-line bg-panel/70 p-5 sm:p-7">
            <p className="font-mono text-xs text-accent">0{i + 1}</p>
            <h3 className="mt-1 font-display text-3xl font-bold tracking-tight">{s.title}</h3>
            <p className="mt-2 text-muted">{s.text}</p>
            <div className="mt-6 min-w-0"><Visual step={i} /></div>
          </li>
        ))}
      </ol>
    </section>
  );
}
