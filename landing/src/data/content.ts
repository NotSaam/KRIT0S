/** Textos y enlaces de la landing. Edita aquí, los componentes leen de este fichero. */

export const links = {
  repo: "https://github.com/NotSaam/KRIT0S",
  portfolio: "https://flako-dev.vercel.app",
};

export const nav = [
  { label: "Flujo", href: "#flujo" },
  { label: "CVSS", href: "#cvss" },
  { label: "Informe", href: "#informe" },
  { label: "Roles", href: "#roles" },
  { label: "Stack", href: "#stack" },
];

export type SeverityName = "Critical" | "High" | "Medium" | "Low";

export interface DemoFinding {
  id: string;
  title: string;
  asset: string;
  severity: SeverityName;
  score: number;
  status: "Abierto" | "En revisión" | "Corregido";
}

/** Datos ficticios para los mocks (cliente inventado). */
export const demoFindings: DemoFinding[] = [
  { id: "KRT-014", title: "SQL injection en /api/orders?sort", asset: "api.nortia.test", severity: "Critical", score: 9.8, status: "Abierto" },
  { id: "KRT-011", title: "IDOR en descarga de facturas", asset: "portal.nortia.test", severity: "High", score: 8.1, status: "En revisión" },
  { id: "KRT-009", title: "JWT firmado con secreto débil", asset: "auth.nortia.test", severity: "High", score: 7.4, status: "Abierto" },
  { id: "KRT-007", title: "XSS reflejado en búsqueda", asset: "www.nortia.test", severity: "Medium", score: 6.1, status: "Corregido" },
  { id: "KRT-005", title: "Cabeceras CSP ausentes", asset: "www.nortia.test", severity: "Medium", score: 4.3, status: "Abierto" },
  { id: "KRT-002", title: "Banner de versión expuesto", asset: "mail.nortia.test", severity: "Low", score: 3.1, status: "Corregido" },
];

export const severityColor: Record<SeverityName | "None", string> = {
  Critical: "var(--color-sev-critical)",
  High: "var(--color-sev-high)",
  Medium: "var(--color-sev-medium)",
  Low: "var(--color-sev-low)",
  None: "var(--color-sev-none)",
};

export const severityEs: Record<SeverityName | "None", string> = {
  Critical: "Crítica",
  High: "Alta",
  Medium: "Media",
  Low: "Baja",
  None: "Ninguna",
};

export const marqueeItems = [
  "SQL injection",
  "IDOR",
  "SSRF",
  "XSS almacenado",
  "RCE",
  "Path traversal",
  "JWT none",
  "CSRF",
  "Open redirect",
  "XXE",
  "Deserialización insegura",
  "Subdomain takeover",
];
