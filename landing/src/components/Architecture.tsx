import { Reveal, Section, SectionHead } from "./ui";

const STACK = ["Laravel 12", "PHP 8.3", "Vue 3", "Inertia.js", "TypeScript", "Tailwind CSS", "PostgreSQL 16", "Redis 7", "dompdf", "Pest", "Docker Compose", "GitHub Actions"];

/** Nodo del diagrama (coordenadas en el viewBox 800x360). */
function Node({ x, y, w = 170, title, sub, tone = "#2dd4bf" }: { x: number; y: number; w?: number; title: string; sub: string; tone?: string }) {
  return (
    <g>
      <rect x={x} y={y} width={w} height="64" rx="12" fill="#0b0b12" stroke={tone} strokeOpacity="0.55" />
      <text x={x + 16} y={y + 28} fill="#e9e9f3" fontFamily="Space Grotesk, sans-serif" fontWeight="700" fontSize="16">{title}</text>
      <text x={x + 16} y={y + 47} fill="#8a8aa0" fontFamily="JetBrains Mono, monospace" fontSize="10.5">{sub}</text>
    </g>
  );
}

function Wire({ d, tone = "#2dd4bf" }: { d: string; tone?: string }) {
  return (
    <>
      <path d={d} fill="none" stroke="#1c1c2b" strokeWidth="2" />
      <path d={d} fill="none" stroke={tone} strokeWidth="1.5" className="flow-line" />
    </>
  );
}

export default function Architecture() {
  return (
    <Section id="stack">
      <SectionHead index="07" kicker="Arquitectura" title={<>Monolito moderno, <span className="text-accent">bien hecho.</span></>}>
        Laravel sirve la API y las páginas vía Inertia; Vue pinta la interfaz sin montar una SPA aparte. PostgreSQL guarda los datos, Redis hace de caché, sesiones y cola para el PDF. Todo se levanta con un <code className="font-mono text-ink">docker compose up</code>.
      </SectionHead>

      <Reveal>
        <div className="hud-corners overflow-x-auto rounded-3xl border border-line bg-panel/70 p-4 sm:p-8">
          <svg viewBox="0 0 800 360" className="mx-auto w-full min-w-[620px] max-w-4xl" role="img" aria-label="Diagrama: el navegador con Vue e Inertia habla con Laravel; Laravel usa PostgreSQL y Redis; un worker de la cola genera el PDF con dompdf y lo guarda en storage.">
            <Wire d="M190 72 H 315" />
            <Wire d="M485 60 C 540 60, 540 40, 600 40" />
            <Wire d="M485 84 C 540 84, 540 140, 600 140" tone="#8b5cf6" />
            <Wire d="M685 172 V 220" tone="#8b5cf6" />
            <Wire d="M600 252 H 485" tone="#38bdf8" />
            <Wire d="M400 104 V 220" tone="#38bdf8" />
            <Node x={20} y={40} title="Vue 3 + Inertia" sub="TS · Tailwind · Vite" />
            <Node x={315} y={40} title="Laravel 12" sub="Policies · Form Requests" />
            <Node x={600} y={8} w={180} title="PostgreSQL" sub="engagements · findings" tone="#38bdf8" />
            <Node x={600} y={108} w={180} title="Redis" sub="cache · sesión · cola" tone="#8b5cf6" />
            <Node x={600} y={220} w={180} title="Queue worker" sub="GenerateReportPdf" tone="#8b5cf6" />
            <Node x={315} y={220} title="PDF · storage" sub="dompdf · versionado" tone="#38bdf8" />
            <text x="200" y="62" fill="#54546a" fontFamily="JetBrains Mono, monospace" fontSize="10">HTTPS · CSRF</text>
            <text x="20" y="150" fill="#54546a" fontFamily="JetBrains Mono, monospace" fontSize="10">// audit_log en cada mutación</text>
            <text x="20" y="168" fill="#54546a" fontFamily="JetBrains Mono, monospace" fontSize="10">// CvssCalculator (PHP) = cvss.ts (TS)</text>
            <rect x="20" y="300" width="760" height="40" rx="10" fill="none" stroke="#1c1c2b" strokeDasharray="4 4" />
            <text x="40" y="325" fill="#8a8aa0" fontFamily="JetBrains Mono, monospace" fontSize="11">docker compose · app · pgsql · redis · queue · vite      CI: Pint · Pest · build</text>
          </svg>
        </div>
      </Reveal>

      <ul className="mt-8 flex flex-wrap gap-2">
        {STACK.map((s) => (
          <li key={s} className="rounded-full border border-line-bright bg-panel/60 px-4 py-1.5 font-mono text-xs text-muted transition-colors hover:border-accent hover:text-ink">{s}</li>
        ))}
      </ul>
    </Section>
  );
}
