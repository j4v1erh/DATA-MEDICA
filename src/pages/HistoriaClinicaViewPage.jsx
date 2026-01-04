import React, { useEffect, useMemo, useState } from "react";
import { useNavigate, useParams, useLocation } from "react-router-dom";
import { supabase } from "../lib/supabaseClient.js";

/* =========================
   Helpers
========================= */
function getQueryParam(search, key) {
  const sp = new URLSearchParams(search);
  return sp.get(key);
}

function normalizeSex(sex) {
  const s = String(sex || "").toLowerCase();
  if (s === "f" || s.includes("fem")) return "femenino";
  if (s === "m" || s.includes("masc")) return "masculino";
  return s || "desconocido";
}

function formatDateMX(isoOrDate) {
  if (!isoOrDate) return "—";
  const d = new Date(isoOrDate);
  if (Number.isNaN(d.getTime())) return String(isoOrDate);
  return d.toLocaleDateString("es-MX", { year: "numeric", month: "2-digit", day: "2-digit" });
}

function formatTimeMX(isoOrTs) {
  if (!isoOrTs) return "—";
  const d = new Date(isoOrTs);
  if (Number.isNaN(d.getTime())) return "—";
  return d.toLocaleTimeString("es-MX", { hour: "2-digit", minute: "2-digit" });
}

function stripLeadingNumberTitle(title) {
  return String(title || "").replace(/^\s*\d+\)\s*/g, "");
}

function titleUpperNoNumber(title) {
  return stripLeadingNumberTitle(title).toUpperCase();
}

function hasText(v) {
  return String(v ?? "").trim().length > 0;
}

/* =========================
   Page
========================= */
export default function HistoriaClinicaViewPage() {
  const navigate = useNavigate();
  const { patientId: patientIdParam } = useParams();
  const location = useLocation();

  const patientId =
    patientIdParam ||
    getQueryParam(location.search, "patientId") ||
    getQueryParam(location.search, "id");

  const [loading, setLoading] = useState(true);
  const [patient, setPatient] = useState(null);
  const [histories, setHistories] = useState([]);
  const [selected, setSelected] = useState(null);

  const patientSex = useMemo(() => normalizeSex(patient?.sex), [patient?.sex]);
  const isFemale = patientSex === "femenino";

  useEffect(() => {
    let cancelled = false;

    async function load() {
      try {
        setLoading(true);

        const { data: s, error: se } = await supabase.auth.getSession();
        if (se) throw se;
        if (!s?.session) {
          navigate("/login", { replace: true });
          return;
        }

        if (!patientId) throw new Error("No se encontró patientId para cargar el historial.");

        const { data: pat, error: pe } = await supabase
          .from("patients")
          .select("id, full_name, curp, sex, age, birth_date")
          .eq("id", patientId)
          .single();
        if (pe) throw pe;

        const { data: hs, error: he } = await supabase
          .from("clinical_histories")
          .select("id, created_at, created_by, data")
          .eq("patient_id", patientId)
          .order("created_at", { ascending: false });
        if (he) throw he;

        if (cancelled) return;
        setPatient(pat);
        setHistories(hs || []);
        setSelected((hs && hs[0]) || null);
      } catch (e) {
        console.error(e);
        alert(`Error cargando historias clínicas: ${e.message || e}`);
      } finally {
        if (!cancelled) setLoading(false);
      }
    }

    load();
    return () => {
      cancelled = true;
    };
  }, [navigate, patientId]);

  if (loading) {
    return <div className="min-h-screen bg-slate-950 text-slate-100 p-6">Cargando…</div>;
  }

  return (
    <div className="min-h-screen bg-slate-950 text-slate-100">
      {/* ✅ PRINT ONLY */}
      <div className="print-only">
        <PrintableHistoriaClinicaWordLike selected={selected} patient={patient} isFemale={isFemale} mode="print" />
      </div>

      {/* ✅ PRINT CSS */}
      <style>{`
        /* ✅ Más margen inferior REAL para que el footer nunca se coma texto */
        @page {
          size: A4;
          margin: 8mm 8mm 32mm 8mm; /* 🔥 subimos a 32mm */
        }

        @media print {
          .no-print { display: none !important; }
          .print-only { display: block !important; }
          html, body { background: #fff !important; }

          /* ✅ Solo para bloques cortos */
          .avoid-break { break-inside: avoid; page-break-inside: avoid; }

          /* ✅ Permite que bloques largos se partan y NO dejen huecos */
          .allow-break { break-inside: auto; page-break-inside: auto; }
        }

        @media screen {
          .print-only { display: none; }
        }
      `}</style>

      {/* ✅ UI */}
      <div className="no-print">
        <div className="mx-auto max-w-6xl px-4 py-8">
          <div className="flex items-center justify-between gap-3">
            <button
              onClick={() => navigate(-1)}
              className="rounded-2xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:border-white/20 hover:bg-white/10"
            >
              ← Volver
            </button>

            <div className="text-right">
              <div className="text-lg font-semibold">Historial de Historia Clínica</div>
              <div className="text-xs text-slate-300">
                Paciente: <span className="text-slate-100 font-medium">{patient?.full_name}</span>
              </div>
            </div>

            <button
              onClick={() => window.print()}
              disabled={!selected}
              className="rounded-2xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200 hover:border-white/20 hover:bg-white/10 disabled:opacity-50"
            >
              Imprimir (A4)
            </button>
          </div>

          <div className="mt-6 rounded-3xl border border-white/10 bg-white/5 p-6">
            <div className="text-sm text-slate-300">Datos del paciente</div>
            <div className="mt-2 text-xl font-semibold">{patient?.full_name}</div>
            <div className="mt-1 text-sm text-slate-300">
              CURP: {patient?.curp || "—"} · Sexo: {patientSex} · Edad: {patient?.age ?? "—"}
            </div>
          </div>

          <div className="mt-6 grid grid-cols-1 gap-5 md:grid-cols-[360px_1fr]">
            <div className="rounded-3xl border border-white/10 bg-white/5 p-5">
              <div className="text-sm font-semibold">Registros guardados</div>
              <div className="mt-3 space-y-3">
                {histories.length === 0 && (
                  <div className="text-sm text-slate-300">No hay historias clínicas guardadas para este paciente.</div>
                )}

                {histories.map((h) => {
                  const active = selected?.id === h.id;
                  const dt = new Date(h.created_at).toLocaleString("es-MX");
                  return (
                    <button
                      key={h.id}
                      onClick={() => setSelected(h)}
                      className={[
                        "w-full rounded-2xl border p-4 text-left transition",
                        active
                          ? "border-white/30 bg-white/10"
                          : "border-white/10 bg-white/5 hover:border-white/20 hover:bg-white/10",
                      ].join(" ")}
                    >
                      <div className="text-sm font-semibold text-slate-100">{dt}</div>
                      <div className="mt-1 text-xs text-slate-300">ID: {h.id.slice(0, 8)}…</div>
                    </button>
                  );
                })}
              </div>
            </div>

            {/* ✅ Vista previa MISMO layout que impresión (A4 real) */}
            <div className="rounded-3xl border border-white/10 bg-white/5 p-6">
              {!selected ? (
                <div className="text-sm text-slate-300">Selecciona un registro para ver el contenido.</div>
              ) : (
                <ScreenPreviewPaper>
                  <PrintableHistoriaClinicaWordLike selected={selected} patient={patient} isFemale={isFemale} mode="screen" />
                </ScreenPreviewPaper>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

/* ✅ Preview wrapper (pantalla) A4 real */
function ScreenPreviewPaper({ children }) {
  return (
    <div className="flex justify-center overflow-x-auto">
      <div
        className="rounded-2xl border border-white/10 bg-white text-black shadow-xl"
        style={{
          width: "210mm",
          minHeight: "297mm",
          padding: "8mm 8mm 32mm 8mm", // ✅ igual que @page
          boxSizing: "border-box",
          overflow: "hidden",
          transform: "scale(0.9)",
          transformOrigin: "top center",
        }}
      >
        {children}
      </div>
    </div>
  );
}

/* =========================
   Printable
========================= */
const FOOTER_SPACE = "26mm"; // ✅ espacio real reservado para evitar texto cortado

function PrintableHistoriaClinicaWordLike({ selected, patient, isFemale, mode = "print" }) {
  const data = selected?.data || {};
  const meta = data?.meta || {};
  const historia = data?.historia_clinica || {};

  const medicoNombre = meta?.medico?.nombre || data?.medico?.nombre || "—";
  const medicoCedula = meta?.medico?.cedula || data?.medico?.cedula || "—";
  const servicio = meta?.medico?.servicio || data?.medico?.servicio || "—";

  const pacienteNombre = meta?.paciente?.full_name || patient?.full_name || "—";
  const pacienteEdad = meta?.paciente?.age ?? patient?.age ?? "—";
  const pacienteSexo = meta?.paciente?.sex || patient?.sex || "—";
  const pacienteNac = meta?.paciente?.birth_date || patient?.birth_date || "—";

  const createdAt = selected?.created_at || null;
  const fechaElab = formatDateMX(createdAt);
  const horaElab = formatTimeMX(createdAt);

  const diagnostico =
    typeof historia?.diagnostico === "string"
      ? historia.diagnostico
      : (historia?.diagnostico?.notas || "");

  const sv = historia?.signos_vitales || {};
  const ef = historia?.exploracion_fisica || {};
  const apnp = historia?.antecedentes_personales_no_patologicos || {};
  const ago = historia?.antecedentes_ginecoobstetricos || null;

  const labs = historia?.estudios?.laboratorio || "—";
  const imagen = historia?.estudios?.imagen || "—";

  const showIG = hasText(ef?.inspeccionGeneral);

  return (
    <div style={mode === "screen" ? printStyles.pageScreen : printStyles.page}>
      {/* ENCABEZADO */}
      <div style={printStyles.title}>CLINICA SAN AGUSTIN</div>
      <div style={printStyles.subtitle}>
        Prolongación Ramón López Velarde N°30. Col. El Toloque. Cárdenas, Tabasco.
      </div>

      {/* PACIENTE */}
      <div style={{ ...printStyles.sectionLabel, marginTop: 6 }}>PACIENTE:</div>
      <div style={printStyles.box}>
        <div style={printStyles.kvRowCompact}>
          <span style={printStyles.k}>NOMBRE:</span> <span style={printStyles.v}>{pacienteNombre}</span>
        </div>

        <div style={printStyles.pacienteGrid}>
          <div style={printStyles.kvInline}>
            <span style={printStyles.k}>EDAD:</span> <span style={printStyles.v}>{pacienteEdad}</span>
          </div>

          <div style={printStyles.kvInline}>
            <span style={printStyles.k}>FECHA DE NACIMIENTO:</span>{" "}
            <span style={printStyles.v}>{formatDateMX(pacienteNac)}</span>
          </div>

          <div style={printStyles.kvInline}>
            <span style={printStyles.k}>SEXO:</span> <span style={printStyles.v}>{pacienteSexo}</span>
          </div>
        </div>
      </div>

      {/* HISTORIA CLINICA */}
      <div style={printStyles.sectionTitleCenter}>HISTORIA CLINICA</div>

      <HCBlock title="1) Antecedentes heredofamiliares" text={historia?.antecedentes_heredofamiliares || "—"} avoidBreak />

      <HCBlock title="2) Antecedentes personales no patológicos" avoidBreak>
        <TwoCol>
          <KV2
            label="Lugar y fecha de nacimiento"
            value={`${apnp?.lugarNacimiento || "—"} · ${formatDateMX(apnp?.fechaNacimiento || pacienteNac)}`}
          />
          <KV2 label="Estado civil" value={apnp?.estadoCivil || "—"} />
          <KV2 label="Religión" value={apnp?.religion || "—"} />
          <KV2 label="Habitación" value={apnp?.habitacion || "—"} />
          <KV2 label="Higiene personal" value={apnp?.higienePersonal || "—"} />
          <KV2 label="Escolaridad" value={apnp?.escolaridad || "—"} />
          <KV2 label="Alimentación" value={apnp?.alimentacion || "—"} />
          <KV2 label="Ocupación" value={apnp?.ocupacion || "—"} />
          <KV2 label="Tipo de sangre" value={apnp?.tipoSangre || "—"} />
        </TwoCol>
      </HCBlock>

      {isFemale && ago && (
        <HCBlock title="3) Antecedentes ginecoobstétricos" avoidBreak>
          <TwoCol>
            {Object.entries(ago).map(([k, v]) => (
              <KV2 key={k} label={String(k)} value={String(v || "—")} />
            ))}
          </TwoCol>
        </HCBlock>
      )}

      <HCBlock title="4) Padecimiento actual" text={historia?.padecimiento_actual || "—"} avoidBreak />

      {/* ✅ estos pueden ser largos => NO evitar cortes */}
      <HCBlock title="5) Estudios de laboratorio" text={labs} avoidBreak={false} />
      <HCBlock title="6) Estudios de imagen" text={imagen} avoidBreak={false} />

      <HCBlock title="Signos vitales" avoidBreak>
        <TwoCol>
          <KV2 label="Estatura (cm)" value={sv.estaturaCm ?? "—"} />
          <KV2 label="Peso (kg)" value={sv.pesoKg ?? "—"} />
          <KV2 label="IMC" value={sv.imc ?? "—"} />
          <KV2 label="Temperatura" value={sv.temperatura ?? "—"} />
          <KV2 label="Presión arterial" value={sv.presionArterial ?? "—"} />
          <KV2 label="Frecuencia cardiaca" value={sv.frecuenciaCardiaca ?? "—"} />
          <KV2 label="Frecuencia respiratoria" value={sv.frecuenciaRespiratoria ?? "—"} />
        </TwoCol>
      </HCBlock>

      {/* ✅ EXPLORACIÓN FÍSICA: BLOQUE LARGO => permitir cortes (SIN huecos gigantes) */}
      <HCBlock title="7) Exploración física" avoidBreak={false}>
        <OneCol compact={!showIG}>
          {showIG && <KV1 label="Inspección general" value={ef.inspeccionGeneral} big />}
          <KV1 label="Cabeza" value={ef.cabeza || "—"} />
          <KV1 label="Cuello" value={ef.cuello || "—"} />
          <KV1 label="Tórax" value={ef.torax || "—"} />
          <KV1 label="Abdomen" value={ef.abdomen || "—"} />
          <KV1 label="Columna vertebral" value={ef.columnaVertebral || "—"} />
          <KV1 label="Genitales externos" value={ef.genitalesExternos || "—"} />
          {isFemale && <KV1 label="Tacto vaginal" value={ef.tactoVaginal || "—"} />}
          <KV1 label="Extremidades" value={ef.extremidades || "—"} />
        </OneCol>
      </HCBlock>

      <HCBlock title="8) Diagnóstico" text={diagnostico || "—"} avoidBreak={false} />

      {/* ✅ ESPACIO REAL antes del footer (evita texto cortado) */}
      <div style={{ height: FOOTER_SPACE }} />

      {/* ✅ FOOTER fijo por página */}
      <div style={printStyles.footer}>
        <div style={printStyles.footerRow}>
          <div style={printStyles.footerLeft}>
            <div style={printStyles.footerLabel}>FECHA Y HORA DE ELABORACIÓN:</div>
            <div style={printStyles.footerValue}>
              {fechaElab} {horaElab ? ` ${horaElab}` : ""}
            </div>

            <div style={{ marginTop: 3 }}>
              <span style={printStyles.footerK}>SERVICIO:</span>{" "}
              <span style={printStyles.footerV}>{servicio}</span>
            </div>
          </div>

          <div style={printStyles.footerRight}>
            <div style={printStyles.footerLabel}>EXPEDIDA POR:</div>

            <div style={printStyles.footerKV}>
              <span style={printStyles.footerK}>MÉDICO:</span>
              <span style={printStyles.footerV}>{medicoNombre}</span>
            </div>

            <div style={printStyles.footerKV}>
              <span style={printStyles.footerK}>CÉD. PROF:</span>
              <span style={printStyles.footerV}>{medicoCedula}</span>
            </div>

            <div style={printStyles.footerSignBox}>
              <div style={printStyles.footerSignLine} />
              <div style={printStyles.footerSignText}>FIRMA</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

/* =========================
   Blocks
========================= */
function HCBlock({ title, text, children, avoidBreak = true }) {
  return (
    <div style={printStyles.block} className={avoidBreak ? "avoid-break" : "allow-break"}>
      <div style={printStyles.blockTitle}>{titleUpperNoNumber(title)}</div>
      {text !== undefined ? (
        <div style={printStyles.blockText}>{text}</div>
      ) : (
        <div style={{ marginTop: 2 }}>{children}</div>
      )}
    </div>
  );
}

function TwoCol({ children }) {
  return <div style={printStyles.twoCol}>{children}</div>;
}

function OneCol({ children, compact = false }) {
  return <div style={compact ? printStyles.oneColCompact : printStyles.oneCol}>{children}</div>;
}

function KV2({ label, value }) {
  return (
    <div style={printStyles.kv2}>
      <div style={printStyles.kv2Label}>{String(label || "").toUpperCase()}:</div>
      <div style={printStyles.kv2Value}>{value || "—"}</div>
    </div>
  );
}

function KV1({ label, value, big = false }) {
  if (big) {
    return (
      <div style={printStyles.kv1Big}>
        <div style={printStyles.kv1BigLabel}>{String(label || "").toUpperCase()}:</div>
        <div style={printStyles.kv1BigValue}>{value || "—"}</div>
      </div>
    );
  }

  return (
    <div style={printStyles.kv1}>
      <div style={printStyles.kv1Label}>{String(label || "").toUpperCase()}:</div>
      <div style={printStyles.kv1Value}>{value || "—"}</div>
    </div>
  );
}

/* =========================
   Styles
========================= */
const printStyles = {
  page: {
    fontFamily: "Arial, Helvetica, sans-serif",
    color: "#111",
    fontSize: 10.3,
    lineHeight: 1.05,
  },

  pageScreen: {
    fontFamily: "Arial, Helvetica, sans-serif",
    color: "#111",
    fontSize: 10.6,
    lineHeight: 1.08,
  },

  title: {
    textAlign: "center",
    fontWeight: 700,
    fontSize: 13,
    textTransform: "uppercase",
    marginTop: 0,
  },
  subtitle: {
    textAlign: "center",
    fontSize: 9.4,
    marginTop: 2,
    marginBottom: 2,
  },

  sectionLabel: {
    marginTop: 6,
    fontWeight: 700,
    fontSize: 10.2,
  },
  sectionTitleCenter: {
    marginTop: 7,
    textAlign: "center",
    fontWeight: 700,
    fontSize: 11.2,
    textTransform: "uppercase",
  },

  box: {
    marginTop: 3,
    border: "1px solid #111",
    padding: "6px 8px",
  },

  k: { fontWeight: 700, textTransform: "uppercase" },
  v: { fontWeight: 400 },

  kvInline: { display: "flex", gap: 6, alignItems: "baseline" },

  pacienteGrid: {
    marginTop: 4,
    display: "grid",
    gridTemplateColumns: "1fr 1.2fr",
    gap: 4,
  },

  kvRowCompact: { marginTop: 2 },

  /* ✅ separador estable */
  block: {
    marginTop: 5,
    paddingTop: 4,
    borderTop: "none",
    backgroundImage: "linear-gradient(#111,#111)",
    backgroundRepeat: "no-repeat",
    backgroundSize: "100% 1px",
    backgroundPosition: "0 0",
  },

  blockTitle: {
    fontWeight: 700,
    fontSize: 10.2,
    marginBottom: 2,
  },

  blockText: {
    whiteSpace: "pre-wrap",
    fontSize: 10.2,
    lineHeight: 1.12,
  },

  twoCol: {
    display: "grid",
    gridTemplateColumns: "1fr 1fr",
    gap: "2px 10px",
    marginTop: 2,
  },

  kv2: {
    display: "grid",
    gridTemplateColumns: "165px 1fr",
    gap: 6,
    alignItems: "baseline",
  },
  kv2Label: { fontWeight: 700, fontSize: 10.1 },
  kv2Value: { fontSize: 10.1, whiteSpace: "pre-wrap" },

  oneCol: {
    display: "flex",
    flexDirection: "column",
    gap: 7,
    marginTop: 6,
  },
  oneColCompact: {
    display: "flex",
    flexDirection: "column",
    gap: 4,
    marginTop: 6,
  },

  kv1: { display: "flex", flexDirection: "column", gap: 2 },
  kv1Label: { fontWeight: 900, textTransform: "uppercase" },
  kv1Value: { whiteSpace: "pre-wrap", lineHeight: 1.18 },

  kv1Big: {
    paddingBottom: 6,
    marginBottom: 2,
    borderBottom: "none",
    backgroundImage: "linear-gradient(#111,#111)",
    backgroundRepeat: "no-repeat",
    backgroundSize: "100% 1px",
    backgroundPosition: "0 100%",
    display: "flex",
    flexDirection: "column",
    gap: 4,
  },
  kv1BigLabel: { fontWeight: 900, textTransform: "uppercase" },
  kv1BigValue: { whiteSpace: "pre-wrap", lineHeight: 1.28 },

  footer: {
    position: "fixed",
    left: 0,
    right: 0,
    bottom: 0,
    borderTop: "1px solid #111",
    padding: "5mm 8mm",
    background: "#fff",
    boxSizing: "border-box",
  },

  footerRow: {
    display: "grid",
    gridTemplateColumns: "1fr 1fr",
    gap: 12,
    alignItems: "end",
  },

  footerLeft: { fontSize: 9.8 },
  footerRight: { fontSize: 9.8 },

  footerLabel: { fontWeight: 900, textTransform: "uppercase", marginBottom: 2 },
  footerValue: { fontWeight: 400 },

  footerKV: {
    display: "grid",
    gridTemplateColumns: "85px 1fr",
    gap: 6,
    alignItems: "baseline",
    marginTop: 2,
  },

  footerK: { fontWeight: 900, textTransform: "uppercase" },
  footerV: { fontWeight: 400 },

  footerSignBox: {
    marginTop: 6,
    display: "flex",
    flexDirection: "column",
    alignItems: "center",
  },

  footerSignLine: { width: "100%", borderTop: "1px solid #111", height: 1, marginTop: 10 },
  footerSignText: { marginTop: 2, fontWeight: 900, fontSize: 9.2, textTransform: "uppercase" },
};
