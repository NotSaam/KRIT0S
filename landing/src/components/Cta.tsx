import { links } from "../data/content";
import { Reveal } from "./ui";

export default function Cta() {
  return (
    <section id="contacto" className="relative overflow-hidden border-t border-line px-4 py-28 sm:px-8 sm:py-40">
      <div aria-hidden className="grid-overlay pointer-events-none absolute inset-0 rotate-180" />
      <Reveal className="relative mx-auto max-w-7xl text-center">
        <p className="hud-label mb-6">El veredicto es tuyo</p>
        <h2 className="font-display text-[clamp(2.6rem,10vw,8.5rem)] leading-[0.9] font-bold tracking-[-0.04em] text-balance">
          Deja de escribir <span className="text-outline">informes.</span>
          <br />
          <span className="text-gradient-animated">Emite veredictos.</span>
        </h2>
        <div className="mt-12 flex flex-wrap justify-center gap-3">
          <a href={links.repo} target="_blank" rel="noreferrer" className="inline-flex items-center gap-2 rounded-full bg-accent px-7 py-3.5 font-mono text-xs font-bold tracking-widest text-night uppercase glow-accent transition-transform hover:-translate-y-0.5">
            Código en GitHub ↗
          </a>
          <a href={links.portfolio} target="_blank" rel="noreferrer" className="inline-flex items-center gap-2 rounded-full border border-line-bright px-7 py-3.5 font-mono text-xs tracking-widest uppercase transition-colors hover:border-accent hover:text-accent">
            Más proyectos de Flako ↗
          </a>
        </div>
      </Reveal>
    </section>
  );
}
