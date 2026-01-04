import { useEffect, useMemo, useState } from "react";
import { useLocation, useNavigate, useParams } from "react-router-dom";
import { supabase } from "../lib/supabaseClient.js";

// ============================
// Helpers
// ============================
function getQueryParam(search, key) {
  const sp = new URLSearchParams(search);
  return sp.get(key);
}

function nowLocalStrings() {
  const now = new Date();
  const date = now.toLocaleDateString("es-MX", {
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
  });
  const time = now.toLocaleTimeString("es-MX", {
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
  });
  return { date, time, iso: now.toISOString() };
}

function toNumberOrEmpty(v) {
  if (v === "" || v === null || v === undefined) return "";
  const n = Number(v);
  return Number.isFinite(n) ? n : "";
}

function calcBMI(heightCm, weightKg) {
  const h = Number(heightCm);
  const w = Number(weightKg);
  if (!Number.isFinite(h) || !Number.isFinite(w) || h <= 0 || w <= 0) return "";
  const hm = h / 100;
  const bmi = w / (hm * hm);
  return Math.round(bmi * 10) / 10;
}

function normalizeSex(sex) {
  const s = String(sex || "").trim().toLowerCase();
  if (s === "f" || s.includes("fem")) return "femenino";
  if (s === "m" || s.includes("masc")) return "masculino";
  return s || "desconocido";
}

// ============================
// Component
// ============================
export default function HistoriaClinicaFormPage() {
  const navigate = useNavigate();
  const location = useLocation();
  const params = useParams();

  const patientId =
    params.patientId ||
    getQueryParam(location.search, "patientId") ||
    getQueryParam(location.search, "id");

  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  const [patient, setPatient] = useState(null);
  const patientSex = useMemo(() => normalizeSex(patient?.sex), [patient?.sex]);
  const isFemale = patientSex === "femenino";

  const [profile, setProfile] = useState(null);

  // Fecha/hora
  const [stamp, setStamp] = useState(() => nowLocalStrings());

  // Form
  const [form, setForm] = useState({
    antecedentesHeredofamiliares: "",
    apnp: {
      lugarNacimiento: "",
      fechaNacimiento: "",
      estadoCivil: "",
      religion: "",
      habitacion: "",
      higienePersonal: "",
      escolaridad: "",
      alimentacion: "",
      ocupacion: "",
      tipoSangre: "",
    },
    ago: {
      menarca: "",
      ritmo: "",
      ivsa: "",
      fur: "",
      fpp: "",
      embarazos: "",
      partos: "",
      cesareas: "",
      abortos: "",
      mpf: "",
      edadPadre: "",
    },
    padecimientoActual: "",
    estudiosLaboratorio: "",
    estudiosImagen: "",
    signosVitales: {
      estaturaCm: "",
      pesoKg: "",
      imc: "",
      temperatura: "",
      presionArterial: "",
      frecuenciaCardiaca: "",
      frecuenciaRespiratoria: "",
    },
    exploracionFisica: {
      inspeccionGeneral: "",
      cabeza: "",
      cuello: "",
      torax: "",
      abdomen: "",
      columnaVertebral: "",
      genitalesExternos: "",
      tactoVaginal: "",
      extremidades: "",
    },

    // ✅ Diagnóstico manual (solo texto)
    diagnostico: "",

    medico: {
      nombre: "",
      cedula: "",
      servicio: "",
    },
  });

  // ============================
  // Load session + profile + patient
  // ============================
  useEffect(() => {
    let cancelled = false;

    async function load() {
      try {
        setLoading(true);

        const { data: sessionData, error: sessionError } =
          await supabase.auth.getSession();
        if (sessionError) throw sessionError;

        const userId = sessionData?.session?.user?.id;
        if (!userId) {
          navigate("/login", { replace: true });
          return;
        }

        const { data: prof, error: profErr } = await supabase
          .from("profiles")
          .select("id, full_name, role, cedula, servicio")
          .eq("id", userId)
          .maybeSingle();

        const safeProfile = profErr ? null : prof;

        if (!patientId) throw new Error("No se encontró patientId.");

        const { data: pat, error: patErr } = await supabase
          .from("patients")
          .select("id, full_name, age, birth_date, curp, sex, location, phone")
          .eq("id", patientId)
          .single();

        if (patErr) throw patErr;
        if (cancelled) return;

        setProfile(safeProfile);
        setPatient(pat);

        setForm((prev) => ({
          ...prev,
          apnp: {
            ...prev.apnp,
            fechaNacimiento: pat?.birth_date ? String(pat.birth_date) : prev.apnp.fechaNacimiento,
          },
          medico: {
            ...prev.medico,
            nombre: safeProfile?.full_name || prev.medico.nombre,
            cedula: safeProfile?.cedula || prev.medico.cedula,
            servicio: safeProfile?.servicio || prev.medico.servicio,
          },
        }));
      } catch (e) {
        console.error(e);
        alert(`Error cargando datos: ${e.message || e}`);
      } finally {
        if (!cancelled) setLoading(false);
      }
    }

    load();
    return () => {
      cancelled = true;
    };
  }, [navigate, patientId]);

  // Actualiza fecha/hora cada 1s
  useEffect(() => {
    const t = setInterval(() => setStamp(nowLocalStrings()), 1000);
    return () => clearInterval(t);
  }, []);

  // IMC automático
  useEffect(() => {
    const imc = calcBMI(form.signosVitales.estaturaCm, form.signosVitales.pesoKg);
    setForm((prev) => ({
      ...prev,
      signosVitales: {
        ...prev.signosVitales,
        imc: imc === "" ? "" : String(imc),
      },
    }));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [form.signosVitales.estaturaCm, form.signosVitales.pesoKg]);

  function setField(path, value) {
    setForm((prev) => {
      const next = structuredClone(prev);
      const parts = path.split(".");
      let cur = next;
      for (let i = 0; i < parts.length - 1; i++) cur = cur[parts[i]];
      cur[parts[parts.length - 1]] = value;
      return next;
    });
  }

  // ============================
  // Save
  // ============================
  async function handleSave() {
    try {
      setSaving(true);

      const { data: sessionData, error: sessionError } =
        await supabase.auth.getSession();
      if (sessionError) throw sessionError;

      const userId = sessionData?.session?.user?.id;
      if (!userId) {
        navigate("/login", { replace: true });
        return;
      }
      if (!patient?.id) throw new Error("Paciente no cargado.");

      const payload = {
        meta: {
          version: "HC_001",
          created_at_iso: stamp.iso,
          created_at_date: stamp.date,
          created_at_time: stamp.time,
          created_by: userId,
          medico: {
            nombre: form.medico.nombre || profile?.full_name || "",
            cedula: form.medico.cedula || "",
            servicio: form.medico.servicio || "",
          },
          paciente: {
            id: patient.id,
            full_name: patient.full_name,
            age: patient.age,
            sex: patient.sex,
            birth_date: patient.birth_date,
            curp: patient.curp,
          },
        },
        historia_clinica: {
          antecedentes_heredofamiliares: form.antecedentesHeredofamiliares,
          antecedentes_personales_no_patologicos: form.apnp,
          antecedentes_ginecoobstetricos: isFemale ? form.ago : null,
          padecimiento_actual: form.padecimientoActual,
          estudios: {
            laboratorio: form.estudiosLaboratorio,
            imagen: form.estudiosImagen,
          },
          signos_vitales: {
            ...form.signosVitales,
            estaturaCm: toNumberOrEmpty(form.signosVitales.estaturaCm),
            pesoKg: toNumberOrEmpty(form.signosVitales.pesoKg),
            imc: toNumberOrEmpty(form.signosVitales.imc),
          },
          exploracion_fisica: {
            ...form.exploracionFisica,
            tactoVaginal: isFemale ? form.exploracionFisica.tactoVaginal : "",
          },

          // ✅ Diagnóstico manual (texto)
          diagnostico: form.diagnostico || "",
        },
      };

      const insertRow = {
        patient_id: patient.id,
        created_by: userId,
        created_at: stamp.iso,
        data: payload,
      };

      const { error: saveErr } = await supabase
        .from("clinical_histories")
        .insert(insertRow);

      if (saveErr) throw saveErr;

      alert("Historia clínica guardada correctamente ✅");
      navigate(`/expediente/${patient.id}`, { replace: true });
    } catch (e) {
      console.error(e);
      alert(`No se pudo guardar: ${e.message || e}`);
    } finally {
      setSaving(false);
    }
  }

  if (loading) {
    return (
      <div style={styles.page}>
        <div style={styles.card}>
          <h2 style={styles.h2}>Cargando historia clínica…</h2>
          <p style={styles.p}>Preparando paciente y sesión.</p>
        </div>
      </div>
    );
  }

  return (
    <div style={styles.page}>
      <div style={styles.headerRow}>
        <button style={styles.btnGhost} onClick={() => navigate(-1)}>
          ← Volver
        </button>

        <div style={{ textAlign: "right" }}>
          <div style={styles.smallMuted}>Fecha: {stamp.date}</div>
          <div style={styles.smallMuted}>Hora: {stamp.time}</div>
        </div>
      </div>

      <div style={styles.card}>
        <h2 style={styles.h2}>Historia Clínica</h2>
        <div style={styles.patientBar}>
          <div>
            <div style={styles.label}>Paciente</div>
            <div style={styles.value}>{patient?.full_name}</div>
            <div style={styles.smallMuted}>
              CURP: {patient?.curp || "—"} · Sexo: {patientSex} · Edad: {patient?.age ?? "—"}
            </div>
          </div>
          <div>
            <div style={styles.label}>Médico que elabora</div>
            <div style={styles.grid2}>
              <input
                style={styles.input}
                value={form.medico.nombre}
                onChange={(e) => setField("medico.nombre", e.target.value)}
                placeholder="Nombre del médico"
              />
              <input
                style={styles.input}
                value={form.medico.cedula}
                onChange={(e) => setField("medico.cedula", e.target.value)}
                placeholder="Cédula"
              />
              <input
                style={{ ...styles.input, gridColumn: "1 / span 2" }}
                value={form.medico.servicio}
                onChange={(e) => setField("medico.servicio", e.target.value)}
                placeholder="Servicio"
              />
            </div>
          </div>
        </div>
      </div>

      {/* 1 */}
      <Section title="1) Antecedentes hereditarios y familiares">
        <textarea
          style={styles.textarea}
          value={form.antecedentesHeredofamiliares}
          onChange={(e) => setForm((p) => ({ ...p, antecedentesHeredofamiliares: e.target.value }))}
        />
      </Section>

      {/* 2 APNP */}
      <Section title="2) Antecedentes personales no patológicos">
        <div style={styles.grid2}>
          <Field label="a) Lugar de nacimiento">
            <input
              style={styles.input}
              value={form.apnp.lugarNacimiento}
              onChange={(e) => setField("apnp.lugarNacimiento", e.target.value)}
            />
          </Field>
          <Field label="b) Fecha de nacimiento">
            <input
              style={styles.input}
              type="date"
              value={form.apnp.fechaNacimiento || ""}
              onChange={(e) => setField("apnp.fechaNacimiento", e.target.value)}
            />
          </Field>
          <Field label="c) Estado civil">
            <input
              style={styles.input}
              value={form.apnp.estadoCivil}
              onChange={(e) => setField("apnp.estadoCivil", e.target.value)}
            />
          </Field>
          <Field label="d) Religión">
            <input
              style={styles.input}
              value={form.apnp.religion}
              onChange={(e) => setField("apnp.religion", e.target.value)}
            />
          </Field>
          <Field label="e) Habitación">
            <input
              style={styles.input}
              value={form.apnp.habitacion}
              onChange={(e) => setField("apnp.habitacion", e.target.value)}
            />
          </Field>
          <Field label="f) Higiene personal">
            <input
              style={styles.input}
              value={form.apnp.higienePersonal}
              onChange={(e) => setField("apnp.higienePersonal", e.target.value)}
            />
          </Field>
          <Field label="g) Escolaridad">
            <input
              style={styles.input}
              value={form.apnp.escolaridad}
              onChange={(e) => setField("apnp.escolaridad", e.target.value)}
            />
          </Field>
          <Field label="h) Alimentación">
            <input
              style={styles.input}
              value={form.apnp.alimentacion}
              onChange={(e) => setField("apnp.alimentacion", e.target.value)}
            />
          </Field>
          <Field label="i) Ocupación">
            <input
              style={styles.input}
              value={form.apnp.ocupacion}
              onChange={(e) => setField("apnp.ocupacion", e.target.value)}
            />
          </Field>
          <Field label="j) Tipo de sangre">
            <input
              style={styles.input}
              value={form.apnp.tipoSangre}
              onChange={(e) => setField("apnp.tipoSangre", e.target.value)}
            />
          </Field>
        </div>
      </Section>

      {/* 3 AGO */}
      {isFemale && (
        <Section title="3) Antecedentes ginecoobstétricos (solo femenino)">
          <div style={styles.grid2}>
            <Field label="Menarca">
              <input style={styles.input} value={form.ago.menarca} onChange={(e) => setField("ago.menarca", e.target.value)} />
            </Field>
            <Field label="Ritmo">
              <input style={styles.input} value={form.ago.ritmo} onChange={(e) => setField("ago.ritmo", e.target.value)} />
            </Field>
            <Field label="IVSA">
              <input style={styles.input} value={form.ago.ivsa} onChange={(e) => setField("ago.ivsa", e.target.value)} />
            </Field>
            <Field label="FUR">
              <input style={styles.input} type="date" value={form.ago.fur || ""} onChange={(e) => setField("ago.fur", e.target.value)} />
            </Field>
            <Field label="FPP">
              <input style={styles.input} type="date" value={form.ago.fpp || ""} onChange={(e) => setField("ago.fpp", e.target.value)} />
            </Field>
            <Field label="Embarazos">
              <input style={styles.input} value={form.ago.embarazos} onChange={(e) => setField("ago.embarazos", e.target.value)} />
            </Field>
            <Field label="Partos">
              <input style={styles.input} value={form.ago.partos} onChange={(e) => setField("ago.partos", e.target.value)} />
            </Field>
            <Field label="Cesáreas">
              <input style={styles.input} value={form.ago.cesareas} onChange={(e) => setField("ago.cesareas", e.target.value)} />
            </Field>
            <Field label="Abortos">
              <input style={styles.input} value={form.ago.abortos} onChange={(e) => setField("ago.abortos", e.target.value)} />
            </Field>
            <Field label="MPF">
              <input style={styles.input} value={form.ago.mpf} onChange={(e) => setField("ago.mpf", e.target.value)} />
            </Field>
            <Field label="Edad del padre">
              <input style={styles.input} value={form.ago.edadPadre} onChange={(e) => setField("ago.edadPadre", e.target.value)} />
            </Field>
          </div>
        </Section>
      )}

      {/* 4 */}
      <Section title="4) Padecimiento actual">
        <textarea
          style={styles.textarea}
          value={form.padecimientoActual}
          onChange={(e) => setForm((p) => ({ ...p, padecimientoActual: e.target.value }))}
        />
      </Section>

      {/* 5 */}
      <Section title="5) Estudios de laboratorio">
        <textarea
          style={styles.textarea}
          value={form.estudiosLaboratorio}
          onChange={(e) => setForm((p) => ({ ...p, estudiosLaboratorio: e.target.value }))}
        />
      </Section>

      {/* 6 */}
      <Section title="6) Estudios de imagen">
        <textarea
          style={styles.textarea}
          value={form.estudiosImagen}
          onChange={(e) => setForm((p) => ({ ...p, estudiosImagen: e.target.value }))}
        />
      </Section>

      {/* Signos */}
      <Section title="Módulo de signos vitales">
        <div style={styles.grid3}>
          <Field label="Estatura (cm)">
            <input style={styles.input} value={form.signosVitales.estaturaCm} onChange={(e) => setField("signosVitales.estaturaCm", e.target.value)} />
          </Field>
          <Field label="Peso (kg)">
            <input style={styles.input} value={form.signosVitales.pesoKg} onChange={(e) => setField("signosVitales.pesoKg", e.target.value)} />
          </Field>
          <Field label="IMC (auto)">
            <input style={{ ...styles.input, background: "#f6f6f6" }} value={form.signosVitales.imc} readOnly />
          </Field>
          <Field label="Temperatura">
            <input style={styles.input} value={form.signosVitales.temperatura} onChange={(e) => setField("signosVitales.temperatura", e.target.value)} />
          </Field>
          <Field label="Presión arterial">
            <input style={styles.input} value={form.signosVitales.presionArterial} onChange={(e) => setField("signosVitales.presionArterial", e.target.value)} />
          </Field>
          <Field label="Frecuencia cardiaca">
            <input style={styles.input} value={form.signosVitales.frecuenciaCardiaca} onChange={(e) => setField("signosVitales.frecuenciaCardiaca", e.target.value)} />
          </Field>
          <Field label="Frecuencia respiratoria">
            <input style={styles.input} value={form.signosVitales.frecuenciaRespiratoria} onChange={(e) => setField("signosVitales.frecuenciaRespiratoria", e.target.value)} />
          </Field>
        </div>
      </Section>

      {/* 7 */}
      <Section title="7) Exploración física">
        <div style={styles.grid2}>
          <Field label="a) Inspección general">
            <textarea style={styles.textareaSm} value={form.exploracionFisica.inspeccionGeneral} onChange={(e) => setField("exploracionFisica.inspeccionGeneral", e.target.value)} />
          </Field>
          <Field label="b) Cabeza">
            <textarea style={styles.textareaSm} value={form.exploracionFisica.cabeza} onChange={(e) => setField("exploracionFisica.cabeza", e.target.value)} />
          </Field>
          <Field label="c) Cuello">
            <textarea style={styles.textareaSm} value={form.exploracionFisica.cuello} onChange={(e) => setField("exploracionFisica.cuello", e.target.value)} />
          </Field>
          <Field label="d) Tórax">
            <textarea style={styles.textareaSm} value={form.exploracionFisica.torax} onChange={(e) => setField("exploracionFisica.torax", e.target.value)} />
          </Field>
          <Field label="e) Abdomen">
            <textarea style={styles.textareaSm} value={form.exploracionFisica.abdomen} onChange={(e) => setField("exploracionFisica.abdomen", e.target.value)} />
          </Field>
          <Field label="f) Columna vertebral">
            <textarea style={styles.textareaSm} value={form.exploracionFisica.columnaVertebral} onChange={(e) => setField("exploracionFisica.columnaVertebral", e.target.value)} />
          </Field>
          <Field label="g) Genitales externos">
            <textarea style={styles.textareaSm} value={form.exploracionFisica.genitalesExternos} onChange={(e) => setField("exploracionFisica.genitalesExternos", e.target.value)} />
          </Field>
          {isFemale && (
            <Field label="h) Tacto vaginal">
              <textarea style={styles.textareaSm} value={form.exploracionFisica.tactoVaginal} onChange={(e) => setField("exploracionFisica.tactoVaginal", e.target.value)} />
            </Field>
          )}
          <Field label="i) Extremidades">
            <textarea style={styles.textareaSm} value={form.exploracionFisica.extremidades} onChange={(e) => setField("exploracionFisica.extremidades", e.target.value)} />
          </Field>
        </div>
      </Section>

      {/* 8 Diagnóstico manual */}
      <Section title="8) Diagnóstico (manual)">
        <textarea
          style={styles.textarea}
          value={form.diagnostico}
          onChange={(e) => setForm((p) => ({ ...p, diagnostico: e.target.value }))}
          placeholder="Escribe el diagnóstico aquí…"
        />
      </Section>

      <div style={styles.footer}>
        <button style={styles.btnGhost} onClick={() => navigate(-1)} disabled={saving}>
          Cancelar
        </button>
        <button style={styles.btnPrimary} onClick={handleSave} disabled={saving}>
          {saving ? "Guardando…" : "Guardar Historia Clínica"}
        </button>
      </div>

      <div style={{ height: 24 }} />
    </div>
  );
}

function Section({ title, children }) {
  return (
    <div style={styles.card}>
      <h3 style={styles.h3}>{title}</h3>
      {children}
    </div>
  );
}

function Field({ label, children }) {
  return (
    <div style={{ display: "flex", flexDirection: "column", gap: 6 }}>
      <div style={styles.label}>{label}</div>
      {children}
    </div>
  );
}

const styles = {
  page: {
    maxWidth: 1100,
    margin: "0 auto",
    padding: "22px 16px 60px",
    fontFamily: "system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif",
  },
  headerRow: {
    display: "flex",
    justifyContent: "space-between",
    alignItems: "center",
    marginBottom: 12,
  },
  card: {
    background: "white",
    border: "1px solid #e8e8e8",
    borderRadius: 14,
    padding: 16,
    marginTop: 12,
    boxShadow: "0 2px 10px rgba(0,0,0,0.03)",
  },
  patientBar: {
    display: "grid",
    gridTemplateColumns: "1fr 1fr",
    gap: 14,
    marginTop: 10,
  },
  h2: { margin: 0, fontSize: 20 },
  h3: { margin: 0, fontSize: 16 },
  p: { margin: "8px 0 0", color: "#333" },
  label: { fontSize: 12, color: "#444", fontWeight: 600 },
  value: { fontSize: 16, fontWeight: 700, marginTop: 2 },
  smallMuted: { fontSize: 12, color: "#666", marginTop: 4 },
  input: {
    height: 38,
    borderRadius: 10,
    border: "1px solid #ddd",
    padding: "0 12px",
    outline: "none",
  },
  textarea: {
    width: "100%",
    minHeight: 110,
    borderRadius: 10,
    border: "1px solid #ddd",
    padding: 12,
    outline: "none",
    resize: "vertical",
  },
  textareaSm: {
    width: "100%",
    minHeight: 84,
    borderRadius: 10,
    border: "1px solid #ddd",
    padding: 12,
    outline: "none",
    resize: "vertical",
  },
  grid2: {
    display: "grid",
    gridTemplateColumns: "1fr 1fr",
    gap: 12,
  },
  grid3: {
    display: "grid",
    gridTemplateColumns: "1fr 1fr 1fr",
    gap: 12,
  },
  btnGhost: {
    height: 38,
    padding: "0 12px",
    borderRadius: 10,
    border: "1px solid #ddd",
    background: "white",
    cursor: "pointer",
    fontWeight: 600,
  },
  btnPrimary: {
    height: 40,
    padding: "0 14px",
    borderRadius: 10,
    border: "1px solid #111",
    background: "#111",
    color: "white",
    cursor: "pointer",
    fontWeight: 700,
  },
  footer: {
    display: "flex",
    justifyContent: "flex-end",
    gap: 10,
    marginTop: 14,
  },
};
