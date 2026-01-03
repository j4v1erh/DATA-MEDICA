import React, { useMemo, useState } from "react";
import { useNavigate } from "react-router-dom";
import { addPatient } from "../patients/patientsStore.js";
import { debugSupabaseSession, supabaseInsertPatient } from "../patients/patientsApi.supabase.js";


function Field({ label, children, hint }) {
  return (
    <div className="space-y-2">
      <div className="flex items-end justify-between">
        <label className="text-sm text-slate-200">{label}</label>
        {hint ? <span className="text-xs text-slate-500">{hint}</span> : null}
      </div>
      {children}
    </div>
  );
}

const inputClass =
  "w-full rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3 text-sm text-slate-100 outline-none placeholder:text-slate-500 focus:border-white/20 focus:bg-slate-950/55 focus:ring-4 focus:ring-white/10";

export default function PatientCreatePage() {
  const navigate = useNavigate();

  const [fullName, setFullName] = useState("");
  const [age, setAge] = useState("");
  const [birthDate, setBirthDate] = useState(""); // FN
  const [curp, setCurp] = useState("");
  const [sex, setSex] = useState("No especificado");
  const [location, setLocation] = useState("");
  const [phone, setPhone] = useState("");
  const [emergencyPhone, setEmergencyPhone] = useState("");
  const [error, setError] = useState("");

  const canSubmit = useMemo(() => {
    return (
      fullName.trim() &&
      age.toString().trim() &&
      birthDate.trim() &&
      curp.trim() &&
      sex.trim() &&
      location.trim() &&
      phone.trim() &&
      emergencyPhone.trim()
    );
  }, [fullName, age, birthDate, curp, sex, location, phone, emergencyPhone]);

  const normalizeCurp = (v) => v.toUpperCase().replace(/\s+/g, "");

  const onSubmit = async (e) => {
  e.preventDefault();
  setError("");

  console.log("🧪 [PatientCreatePage] submit");

  const ageNum = Number(age);
  if (!Number.isFinite(ageNum) || ageNum < 0 || ageNum > 130) {
    setError("Edad inválida.");
    return;
  }

  const curpNorm = curp.toUpperCase().replace(/\s+/g, "");
  if (curpNorm.length !== 18) {
    setError("CURP inválida: debe tener 18 caracteres.");
    return;
  }

  // Confirmar sesión supabase
  const { userId } = await debugSupabaseSession("from PatientCreatePage");
  if (!userId) {
    setError(
      "No hay sesión de Supabase. Asegúrate de VITE_AUTH_PROVIDER=supabase y de iniciar sesión con un usuario de Supabase Auth."
    );
    return;
  }

  const payload = {
    full_name: fullName.trim(),
    age: ageNum,
    birth_date: birthDate,
    curp: curpNorm,
    sex,
    location: location.trim(),
    phone: phone.trim(),
    emergency_phone: emergencyPhone.trim(),
    created_by: userId,
  };

  try {
    const inserted = await supabaseInsertPatient(payload);
    console.log("🎉 [PatientCreatePage] inserted:", inserted);
    navigate("/patients", { replace: true });
  } catch (err) {
    console.error("❌ [PatientCreatePage] insert error:", err);
    setError(err?.message ?? "Error al guardar en Supabase.");
  }
};


  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-slate-100">
      <div className="mx-auto max-w-6xl px-4 py-10">
        <div className="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <div className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-slate-300">
              <span className="h-2 w-2 rounded-full bg-emerald-400" />
              Alta de paciente
            </div>
            <h1 className="mt-4 text-2xl font-semibold tracking-tight">
              Registrar paciente
            </h1>
            <p className="mt-1 text-sm text-slate-300">
              Por el momento se guarda en cache local (localStorage).
            </p>
          </div>

          <div className="flex items-center gap-2">
            <button
              onClick={() => navigate("/patients")}
              className="rounded-2xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200 transition hover:border-white/20 hover:bg-white/10"
            >
              Cancelar
            </button>
            <button
              form="patientForm"
              type="submit"
              disabled={!canSubmit}
              className="rounded-2xl bg-white px-4 py-2 text-sm font-semibold text-slate-950 transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60"
            >
              Guardar
            </button>
          </div>
        </div>

        <div className="mt-10 grid grid-cols-1 gap-6 lg:grid-cols-3">
          {/* Form */}
          <div className="lg:col-span-2">
            <div className="rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/40 backdrop-blur sm:p-8">
              <form id="patientForm" onSubmit={onSubmit} className="space-y-5">
                <Field label="Nombre completo" hint="Obligatorio">
                  <input
                    className={inputClass}
                    value={fullName}
                    onChange={(e) => setFullName(e.target.value)}
                    placeholder="Ej. Juan Pérez López"
                  />
                </Field>

                <div className="grid grid-cols-1 gap-5 sm:grid-cols-2">
                  <Field label="Edad" hint="Obligatorio">
                    <input
                      className={inputClass}
                      value={age}
                      onChange={(e) => setAge(e.target.value)}
                      placeholder="Ej. 28"
                      inputMode="numeric"
                    />
                  </Field>

                  <Field label="FN (Fecha de nacimiento)" hint="Obligatorio">
                    <input
                      className={inputClass}
                      type="date"
                      value={birthDate}
                      onChange={(e) => setBirthDate(e.target.value)}
                    />
                  </Field>
                </div>

                <div className="grid grid-cols-1 gap-5 sm:grid-cols-2">
                  <Field label="CURP" hint="18 caracteres">
                    <input
                      className={inputClass}
                      value={curp}
                      onChange={(e) => setCurp(e.target.value)}
                      placeholder="Ej. ABCD001231HDFXXX09"
                    />
                  </Field>

                  <Field label="Sexo" hint="Obligatorio">
                    <select
                      className={inputClass}
                      value={sex}
                      onChange={(e) => setSex(e.target.value)}
                    >
                      <option>No especificado</option>
                      <option>Masculino</option>
                      <option>Femenino</option>
                      <option>Otro</option>
                    </select>
                  </Field>
                </div>

                <Field label="Ubicación" hint="Obligatorio">
                  <input
                    className={inputClass}
                    value={location}
                    onChange={(e) => setLocation(e.target.value)}
                    placeholder="Ej. CDMX, Alcaldía Benito Juárez"
                  />
                </Field>

                <div className="grid grid-cols-1 gap-5 sm:grid-cols-2">
                  <Field label="Teléfono" hint="Obligatorio">
                    <input
                      className={inputClass}
                      value={phone}
                      onChange={(e) => setPhone(e.target.value)}
                      placeholder="Ej. 5512345678"
                      inputMode="tel"
                    />
                  </Field>

                  <Field
                    label="Teléfono de contacto / responsable"
                    hint="Obligatorio"
                  >
                    <input
                      className={inputClass}
                      value={emergencyPhone}
                      onChange={(e) => setEmergencyPhone(e.target.value)}
                      placeholder="Ej. 5587654321"
                      inputMode="tel"
                    />
                  </Field>
                </div>

                {error ? (
                  <div className="rounded-2xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                    <div className="font-medium">Revisa el formulario</div>
                    <div className="mt-1 text-rose-200/90">{error}</div>
                  </div>
                ) : null}
              </form>
            </div>
          </div>

          {/* Side info */}
          <div className="space-y-6">
            <div className="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur">
              <div className="text-sm font-semibold text-slate-100">
                Nota de prototipo
              </div>
              <p className="mt-2 text-sm text-slate-300">
                El registro se guarda localmente. Más adelante se conectará a
                Supabase con tablas y políticas (RLS) para control por roles.
              </p>
            </div>

            <div className="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur">
              <div className="text-sm font-semibold text-slate-100">
                Reglas iniciales
              </div>
              <ul className="mt-2 space-y-2 text-sm text-slate-300">
                <li>• CURP: 18 caracteres (validación básica por longitud).</li>
                <li>• Edad: 0 a 130.</li>
                <li>• Todos los campos actuales son obligatorios.</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
