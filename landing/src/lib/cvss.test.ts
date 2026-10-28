import { describe, expect, it } from "vitest";
import { parseVector, roundUp, scoreVector, severityOf } from "./cvss";

describe("CVSS 3.1", () => {
  it.each([
    ["CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H", 9.8, "Critical"],
    ["CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:C/C:H/I:H/A:H", 10.0, "Critical"],
    ["CVSS:3.1/AV:L/AC:H/PR:H/UI:R/S:U/C:L/I:N/A:N", 1.8, "Low"],
    ["CVSS:3.1/AV:N/AC:L/PR:N/UI:R/S:C/C:L/I:L/A:N", 6.1, "Medium"], // XSS reflejado típico
    ["CVSS:3.1/AV:N/AC:L/PR:L/UI:N/S:U/C:H/I:H/A:H", 8.8, "High"],
    ["CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:N/I:N/A:N", 0, "None"],
  ])("%s → %d %s", (vector, score, severity) => {
    const r = scoreVector(vector);
    expect(r.score).toBe(score);
    expect(r.severity).toBe(severity);
  });

  it("roundUp evita errores de coma flotante", () => {
    expect(roundUp(4.02)).toBe(4.1);
    expect(roundUp(4.0)).toBe(4.0);
    expect(roundUp(4.000001)).toBe(4.0);
  });

  it("severityOf respeta los umbrales", () => {
    expect(severityOf(3.9)).toBe("Low");
    expect(severityOf(4)).toBe("Medium");
    expect(severityOf(7)).toBe("High");
    expect(severityOf(9)).toBe("Critical");
  });

  it("parseVector rechaza vectores incompletos o inválidos", () => {
    expect(() => parseVector("AV:N/AC:L")).toThrow();
    expect(() => parseVector("CVSS:3.1/AV:X/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H")).toThrow();
  });
});
