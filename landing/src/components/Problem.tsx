import { Reveal, Section, SectionHead } from "./ui";

const PAINS = [
  { stat: "40%", label: "del tiempo de un pentest se va en escribir el informe", fix: "Plantillas reutilizables y PDF en un clic." },
  { stat: "×3", label: "versiones del mismo .docx circulando por email", fix: "Una sola fuente de verdad por engagement." },
  { stat: "CVSS", label: "calculado a mano en otra pestaña, con errores de redondeo", fix: "Vector → puntuación según la spec, al vuelo." },
];

export default function Problem() {
  return (
    <Section id="problema">
      <SectionHead index="01" kicker="El problema" title={<>El pentest acaba. <span className="text-muted">El informe, no.</span></>}>
        Encuentras la vulnerabilidad en una tarde y pasas dos días peleándote con Word, capturas sueltas y una calculadora CVSS en otra pestaña. KRIT0S convierte esa parte en un flujo.
      </SectionHead>

      <div className="grid gap-px overflow-hidden rounded-2xl border border-line bg-line md:grid-cols-3">
        {PAINS.map((p, i) => (
          <Reveal key={p.stat} delay={i * 0.08} className="group relative bg-night p-7 transition-colors duration-500 hover:bg-panel sm:p-9">
            <p className="font-display text-[clamp(3rem,9vw,5.5rem)] leading-none font-bold tracking-tighter text-outline transition-colors duration-500 group-hover:text-ink">{p.stat}</p>
            <p className="mt-4 text-pretty text-ink">{p.label}</p>
            <p className="mt-6 flex items-start gap-2 border-t border-line pt-4 font-mono text-xs text-accent">
              <span aria-hidden>→</span>
              {p.fix}
            </p>
          </Reveal>
        ))}
      </div>
      <p className="mt-4 font-mono text-[0.65rem] text-faint">* Cifras orientativas basadas en la experiencia habitual del sector, no un estudio.</p>
    </Section>
  );
}
