import { useState } from "react";
import { motion } from "framer-motion";
import { demoFindings, severityColor, type SeverityName } from "../data/content";
import { Reveal, Section, SectionHead, SeverityBadge } from "./ui";

const ORDER: SeverityName[] = ["Critical", "High", "Medium", "Low"];

function Donut({ filter, onPick }: { filter: SeverityName | null; onPick: (s: SeverityName | null) => void }) {
  const counts = ORDER.map((s) => demoFindings.filter((f) => f.severity === s).length);
  const total = counts.reduce((a, b) => a + b, 0);
  const R = 52;
  const C = 2 * Math.PI * R;
  let acc = 0;
  return (
    <div className="flex items-center gap-6">
      <svg viewBox="0 0 140 140" className="h-32 w-32 shrink-0" role="img" aria-label={`Hallazgos por severidad: ${ORDER.map((s, i) => `${s} ${counts[i]}`).join(", ")}`}>
        {ORDER.map((s, i) => {
          const len = (counts[i] / total) * C;
          const el = (
            <circle key={s} cx="70" cy="70" r={R} fill="none" stroke={severityColor[s]} strokeWidth={filter === s ? 18 : 13}
              strokeDasharray={`${len - 3} ${C - len + 3}`} strokeDashoffset={-acc} transform="rotate(-90 70 70)"
              opacity={filter && filter !== s ? 0.25 : 1} className="cursor-pointer transition-all duration-300" onClick={() => onPick(filter === s ? null : s)} />
          );
          acc += len;
          return el;
        })}
        <text x="70" y="68" textAnchor="middle" className="fill-ink font-display text-[28px] font-bold">{total}</text>
        <text x="70" y="86" textAnchor="middle" className="fill-muted font-mono text-[9px] tracking-widest uppercase">hallazgos</text>
      </svg>
      <ul className="space-y-1.5 text-sm">
        {ORDER.map((s, i) => (
          <li key={s}>
            <button type="button" onClick={() => onPick(filter === s ? null : s)} className={`flex items-center gap-2 transition-opacity ${filter && filter !== s ? "opacity-40" : ""}`} aria-pressed={filter === s}>
              <span className="h-2 w-2 rounded-full" style={{ background: severityColor[s] }} />
              <span className="text-muted">{s}</span>
              <span className="font-mono text-ink">{counts[i]}</span>
            </button>
          </li>
        ))}
      </ul>
    </div>
  );
}

/** Mock del panel de la plataforma con datos ficticios, filtrable. */
export default function AppMock() {
  const [filter, setFilter] = useState<SeverityName | null>(null);
  const rows = filter ? demoFindings.filter((f) => f.severity === filter) : demoFindings;

  return (
    <Section id="app">
      <SectionHead index="05" kicker="La plataforma" title={<>Todo el engagement, <span className="text-muted">de un vistazo.</span></>}>
        Panel con KPIs cacheados en Redis, hallazgos filtrables por severidad y estado, y actividad del equipo. Pulsa el donut para filtrar.
      </SectionHead>

      <Reveal>
        <div className="overflow-hidden rounded-3xl border border-line bg-panel/80 shadow-2xl shadow-black/60">
          {/* Barra de ventana */}
          <div className="flex items-center gap-3 border-b border-line px-4 py-3">
            <div className="flex gap-1.5" aria-hidden>{[0, 1, 2].map((i) => <span key={i} className="h-2.5 w-2.5 rounded-full bg-line-bright" />)}</div>
            <div className="mx-auto min-w-0 truncate rounded-md border border-line bg-night px-3 py-1 font-mono text-[0.65rem] text-faint">app.krit0s.test/engagements/KRT-2026-004</div>
          </div>

          <div className="grid md:grid-cols-[200px_1fr]">
            <aside className="hidden border-r border-line p-4 md:block" aria-hidden>
              {["Panel", "Engagements", "Hallazgos", "Plantillas", "Informes", "Clientes", "Auditoría"].map((n, i) => (
                <div key={n} className={`mb-1 rounded-lg px-3 py-2 text-sm ${i === 2 ? "bg-accent/10 text-accent" : "text-muted"}`}>{n}</div>
              ))}
            </aside>

            <div className="min-w-0 p-4 sm:p-6">
              <div className="grid grid-cols-2 gap-3 lg:grid-cols-4">
                {[["Engagements activos", "7"], ["Hallazgos abiertos", "23"], ["Críticos", "3"], ["Informes publicados", "41"]].map(([l, v]) => (
                  <div key={l} className="rounded-xl border border-line bg-night/60 p-3 sm:p-4">
                    <p className="hud-label truncate">{l}</p>
                    <p className="mt-1 font-display text-2xl font-bold sm:text-3xl">{v}</p>
                  </div>
                ))}
              </div>

              <div className="mt-4 grid gap-4 xl:grid-cols-[auto_1fr]">
                <div className="rounded-xl border border-line bg-night/60 p-4">
                  <p className="hud-label mb-3">por severidad</p>
                  <Donut filter={filter} onPick={setFilter} />
                </div>

                <div className="min-w-0 overflow-x-auto rounded-xl border border-line bg-night/60">
                  <table className="w-full min-w-[540px] text-left text-sm">
                    <caption className="sr-only">Hallazgos del engagement (datos ficticios)</caption>
                    <thead>
                      <tr className="border-b border-line">
                        {["ID", "Hallazgo", "Severidad", "Estado"].map((h) => <th key={h} scope="col" className="hud-label px-4 py-3 font-normal">{h}</th>)}
                      </tr>
                    </thead>
                    <tbody>
                      {rows.map((f) => (
                        <motion.tr layout key={f.id} initial={{ opacity: 0 }} animate={{ opacity: 1 }} className="border-b border-line/60 transition-colors last:border-0 hover:bg-panel-2">
                          <td className="px-4 py-3 font-mono text-xs whitespace-nowrap text-faint">{f.id}</td>
                          <td className="px-4 py-3"><p className="text-ink">{f.title}</p><p className="font-mono text-[0.7rem] text-faint">{f.asset}</p></td>
                          <td className="px-4 py-3"><SeverityBadge severity={f.severity} score={f.score} /></td>
                          <td className="px-4 py-3 font-mono text-xs text-muted">{f.status}</td>
                        </motion.tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Reveal>
      <p className="mt-4 font-mono text-[0.65rem] text-faint">Datos ficticios · cliente inventado · dominios .test</p>
    </Section>
  );
}
