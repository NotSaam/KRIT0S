import { links } from "../data/content";
import { Logo } from "./Nav";

export default function Footer() {
  return (
    <footer className="border-t border-line">
      <div className="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-10 sm:flex-row sm:items-center sm:justify-between sm:px-8">
        <div>
          <Logo />
          <p className="mt-2 font-mono text-[0.7rem] text-faint">κριτής — el que juzga. Proyecto de portfolio, datos de ejemplo ficticios.</p>
        </div>
        <p className="font-mono text-xs text-muted">
          Diseñado y construido por{" "}
          <a href={links.portfolio} target="_blank" rel="noreferrer" className="text-ink underline decoration-line-bright underline-offset-4 hover:decoration-accent">
            Samuel 'Flako' Fuentes
          </a>
        </p>
      </div>
    </footer>
  );
}
