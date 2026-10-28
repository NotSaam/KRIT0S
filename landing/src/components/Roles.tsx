import type { PointerEvent } from "react";
import { Reveal, Section, SectionHead } from "./ui";

const ROLES = [
  {
    role: "Admin",
    tone: "var(--color-violet)",
    lead: "Gobierna la organización.",
    can: ["Usuarios, roles y clientes", "Publicar el veredicto al cliente", "Log de auditoría completo"],
  },
  {
    role: "Pentester",
    tone: "var(--color-accent)",
    lead: "Documenta y entrega.",
    can: ["Engagements asignados", "Hallazgos + CVSS + evidencias", "Biblioteca de plantillas", "Generar y publicar informes"],
  },
  {
    role: "Cliente",
    tone: "var(--color-blue-neon)",
    lead: "Lee su veredicto.",
    can: ["Portal de solo lectura", "Solo sus informes publicados", "Descarga del PDF"],
  },
];

function onMove(e: PointerEvent<HTMLElement>) {
  const el = e.currentTarget;
  const r = el.getBoundingClientRect();
  el.style.setProperty("--x", `${e.clientX - r.left}px`);
  el.style.setProperty("--y", `${e.clientY - r.top}px`);
}

export default function Roles() {
  return (
    <Section id="roles">
      <SectionHead index="06" kicker="Multiusuario" title={<>Tres roles. <span className="text-muted">Cero filtraciones.</span></>}>
        Autorización con Policies de Laravel en cada endpoint, no solo escondiendo botones. Un cliente nunca ve el borrador ni los datos de otro cliente; los tests lo comprueban.
      </SectionHead>

      <div className="grid gap-4 md:grid-cols-3">
        {ROLES.map((r, i) => (
          <Reveal key={r.role} delay={i * 0.08}>
            <article
              onPointerMove={onMove}
              className="group relative h-full overflow-hidden rounded-3xl border border-line bg-panel/70 p-7 transition-colors duration-500 hover:border-line-bright sm:p-8"
            >
              <div aria-hidden className="pointer-events-none absolute inset-0 opacity-0 transition-opacity duration-500 group-hover:opacity-100" style={{ background: `radial-gradient(320px circle at var(--x) var(--y), color-mix(in srgb, ${r.tone} 16%, transparent), transparent 70%)` }} />
              <p className="relative font-mono text-xs" style={{ color: r.tone }}>role:{r.role.toLowerCase()}</p>
              <h3 className="relative mt-3 font-display text-4xl font-bold tracking-tight">{r.role}</h3>
              <p className="relative mt-1 text-muted">{r.lead}</p>
              <ul className="relative mt-6 space-y-2.5 border-t border-line pt-5">
                {r.can.map((c) => (
                  <li key={c} className="flex items-start gap-2.5 text-sm">
                    <span aria-hidden style={{ color: r.tone }}>✓</span>
                    {c}
                  </li>
                ))}
              </ul>
            </article>
          </Reveal>
        ))}
      </div>
    </Section>
  );
}
