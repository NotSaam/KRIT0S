/**
 * CVSS v3.1 — cálculo de la puntuación base según la especificación de FIRST
 * (https://www.first.org/cvss/v3.1/specification-document). Autocontenido:
 * sin dependencias, reutilizable en el frontend de la plataforma.
 */

export type AV = "N" | "A" | "L" | "P";
export type AC = "L" | "H";
export type PR = "N" | "L" | "H";
export type UI = "N" | "R";
export type S = "U" | "C";
export type CIA = "H" | "L" | "N";

export interface CvssMetrics {
  AV: AV;
  AC: AC;
  PR: PR;
  UI: UI;
  S: S;
  C: CIA;
  I: CIA;
  A: CIA;
}

export type Severity = "None" | "Low" | "Medium" | "High" | "Critical";

export const METRIC_ORDER = ["AV", "AC", "PR", "UI", "S", "C", "I", "A"] as const;
export type MetricKey = (typeof METRIC_ORDER)[number];

/** Definición de cada métrica para pintar la UI. */
export const METRICS: Record<MetricKey, { label: string; options: { value: string; label: string }[] }> = {
  AV: { label: "Attack Vector", options: [{ value: "N", label: "Network" }, { value: "A", label: "Adjacent" }, { value: "L", label: "Local" }, { value: "P", label: "Physical" }] },
  AC: { label: "Attack Complexity", options: [{ value: "L", label: "Low" }, { value: "H", label: "High" }] },
  PR: { label: "Privileges Required", options: [{ value: "N", label: "None" }, { value: "L", label: "Low" }, { value: "H", label: "High" }] },
  UI: { label: "User Interaction", options: [{ value: "N", label: "None" }, { value: "R", label: "Required" }] },
  S: { label: "Scope", options: [{ value: "U", label: "Unchanged" }, { value: "C", label: "Changed" }] },
  C: { label: "Confidentiality", options: [{ value: "H", label: "High" }, { value: "L", label: "Low" }, { value: "N", label: "None" }] },
  I: { label: "Integrity", options: [{ value: "H", label: "High" }, { value: "L", label: "Low" }, { value: "N", label: "None" }] },
  A: { label: "Availability", options: [{ value: "H", label: "High" }, { value: "L", label: "Low" }, { value: "N", label: "None" }] },
};

const W = {
  AV: { N: 0.85, A: 0.62, L: 0.55, P: 0.2 },
  AC: { L: 0.77, H: 0.44 },
  PR_U: { N: 0.85, L: 0.62, H: 0.27 },
  PR_C: { N: 0.85, L: 0.68, H: 0.5 },
  UI: { N: 0.85, R: 0.62 },
  CIA: { H: 0.56, L: 0.22, N: 0 },
} as const;

/** Roundup de la spec (Apéndice A): evita errores de coma flotante. */
export function roundUp(input: number): number {
  const int = Math.round(input * 100000);
  if (int % 10000 === 0) return int / 100000;
  return (Math.floor(int / 10000) + 1) / 10;
}

export interface CvssResult {
  score: number;
  severity: Severity;
  impact: number;
  exploitability: number;
  vector: string;
}

export function calculate(m: CvssMetrics): CvssResult {
  const changed = m.S === "C";
  const iss = 1 - (1 - W.CIA[m.C]) * (1 - W.CIA[m.I]) * (1 - W.CIA[m.A]);
  const impact = changed ? 7.52 * (iss - 0.029) - 3.25 * Math.pow(iss - 0.02, 15) : 6.42 * iss;
  const pr = changed ? W.PR_C[m.PR] : W.PR_U[m.PR];
  const exploitability = 8.22 * W.AV[m.AV] * W.AC[m.AC] * pr * W.UI[m.UI];

  let score = 0;
  if (impact > 0) {
    score = changed
      ? roundUp(Math.min(1.08 * (impact + exploitability), 10))
      : roundUp(Math.min(impact + exploitability, 10));
  }

  return {
    score,
    severity: severityOf(score),
    impact: Math.max(0, Math.round(impact * 10) / 10),
    exploitability: Math.round(exploitability * 10) / 10,
    vector: toVector(m),
  };
}

export function severityOf(score: number): Severity {
  if (score === 0) return "None";
  if (score < 4) return "Low";
  if (score < 7) return "Medium";
  if (score < 9) return "High";
  return "Critical";
}

export function toVector(m: CvssMetrics): string {
  return "CVSS:3.1/" + METRIC_ORDER.map((k) => `${k}:${m[k]}`).join("/");
}

/** Parsea "CVSS:3.1/AV:N/..." (el prefijo es opcional). Lanza si falta o sobra algo. */
export function parseVector(vector: string): CvssMetrics {
  const parts = vector.trim().replace(/^CVSS:3\.[01]\//, "").split("/");
  const out: Partial<Record<MetricKey, string>> = {};
  for (const p of parts) {
    const [k, v] = p.split(":");
    if (!(METRIC_ORDER as readonly string[]).includes(k)) throw new Error(`Métrica desconocida: ${k}`);
    const key = k as MetricKey;
    if (!METRICS[key].options.some((o) => o.value === v)) throw new Error(`Valor inválido ${k}:${v}`);
    out[key] = v;
  }
  for (const k of METRIC_ORDER) if (!out[k]) throw new Error(`Falta la métrica ${k}`);
  return out as unknown as CvssMetrics;
}

export const scoreVector = (vector: string) => calculate(parseVector(vector));
