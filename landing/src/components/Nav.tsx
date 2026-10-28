import { motion, useScroll, useSpring } from "framer-motion";
import { useEffect, useState } from "react";
import { links, nav } from "../data/content";

export function Logo({ className = "" }: { className?: string }) {
  return (
    <a href="#inicio" className={`group flex items-center gap-2.5 ${className}`} aria-label="KRIT0S, inicio">
      <svg width="26" height="26" viewBox="0 0 64 64" aria-hidden>
        <rect width="64" height="64" rx="14" fill="#0b0b12" stroke="#2c2c44" />
        <path d="M20 14v36M20 32l18-18M26 27l14 23" stroke="#2dd4bf" strokeWidth="6" strokeLinecap="round" strokeLinejoin="round" fill="none" />
        <circle cx="46" cy="18" r="4" fill="#8b5cf6" />
      </svg>
      <span className="font-display text-lg font-bold tracking-tight">
        KRIT<span className="text-accent">0</span>S
      </span>
    </a>
  );
}

export default function Nav() {
  const { scrollYProgress } = useScroll();
  const progress = useSpring(scrollYProgress, { stiffness: 140, damping: 30, mass: 0.3 });
  const [scrolled, setScrolled] = useState(false);
  const [open, setOpen] = useState(false);

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 24);
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  return (
    <header className={`fixed inset-x-0 top-0 z-50 transition-colors duration-500 ${scrolled || open ? "border-b border-line bg-night/75 backdrop-blur-xl" : "border-b border-transparent"}`}>
      <motion.div aria-hidden className="absolute inset-x-0 bottom-0 h-px origin-left bg-gradient-to-r from-accent via-blue-neon to-violet" style={{ scaleX: progress }} />
      <nav className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-8" aria-label="Principal">
        <Logo />
        <ul className="hidden items-center gap-8 md:flex">
          {nav.map((n) => (
            <li key={n.href}>
              <a href={n.href} className="font-mono text-xs tracking-widest text-muted uppercase transition-colors hover:text-ink">
                {n.label}
              </a>
            </li>
          ))}
        </ul>
        <div className="flex items-center gap-2">
          <a
            href={links.repo}
            target="_blank"
            rel="noreferrer"
            className="hidden rounded-full border border-line-bright px-4 py-1.5 font-mono text-xs tracking-wider text-ink uppercase transition-colors hover:border-accent hover:text-accent sm:inline-block"
          >
            GitHub ↗
          </a>
          <button
            type="button"
            className="grid h-9 w-9 place-items-center rounded-full border border-line-bright md:hidden"
            aria-expanded={open}
            aria-controls="menu-movil"
            aria-label={open ? "Cerrar menú" : "Abrir menú"}
            onClick={() => setOpen((v) => !v)}
          >
            <span className="relative block h-3 w-4">
              <span className={`absolute left-0 h-px w-4 bg-ink transition-transform duration-300 ${open ? "top-1.5 rotate-45" : "top-0"}`} />
              <span className={`absolute left-0 h-px w-4 bg-ink transition-transform duration-300 ${open ? "top-1.5 -rotate-45" : "top-3"}`} />
            </span>
          </button>
        </div>
      </nav>
      {open && (
        <ul id="menu-movil" className="flex flex-col gap-1 px-4 pb-5 md:hidden">
          {[...nav, { label: "GitHub ↗", href: links.repo }].map((n) => (
            <li key={n.href}>
              <a href={n.href} onClick={() => setOpen(false)} className="block border-b border-line py-3 font-display text-2xl font-semibold">
                {n.label}
              </a>
            </li>
          ))}
        </ul>
      )}
    </header>
  );
}
