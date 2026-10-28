import { marqueeItems } from "../data/content";

function Row({ reverse = false }: { reverse?: boolean }) {
  const items = [...marqueeItems, ...marqueeItems];
  return (
    <div className={`marquee ${reverse ? "marquee-reverse" : ""}`}>
      {items.map((t, i) => (
        <span key={i} className="flex items-center gap-6 pr-6 font-display text-[clamp(1.5rem,4vw,3rem)] font-bold tracking-tight whitespace-nowrap">
          <span className={i % 3 === 1 ? "text-outline" : i % 3 === 2 ? "text-accent" : "text-ink"}>{t}</span>
          <span aria-hidden className="text-violet">✦</span>
        </span>
      ))}
    </div>
  );
}

/** Cinta de vulnerabilidades: lo que KRIT0S documenta cada día. */
export default function Marquee() {
  return (
    <div aria-hidden className="relative -rotate-1 overflow-hidden border-y border-line bg-panel/60 py-5">
      <Row />
      <div className="mt-2 opacity-60">
        <Row reverse />
      </div>
      <div className="pointer-events-none absolute inset-y-0 left-0 w-16 bg-gradient-to-r from-night to-transparent sm:w-32" />
      <div className="pointer-events-none absolute inset-y-0 right-0 w-16 bg-gradient-to-l from-night to-transparent sm:w-32" />
    </div>
  );
}
