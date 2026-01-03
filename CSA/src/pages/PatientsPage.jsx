import React, { useEffect, useMemo, useState } from "react";
import { useNavigate } from "react-router-dom";
import { deletePatientById, getPatients } from "../patients/patientsStore.js";
import { supabaseFetchPatients, debugSupabaseSession } from "../patients/patientsApi.supabase.js";


function PatientRow({ patient, onDelete }) {
  return (
    <div className="flex flex-col gap-3 rounded-3xl border border-white/10 bg-white/5 p-5 backdrop-blur transition hover:border-white/20 hover:bg-white/10 sm:flex-row sm:items-center sm:justify-between">
      <div className="min-w-0">
        <div className="flex items-center gap-2">
          <div className="truncate text-sm font-semibold text-slate-100">
            {patient.fullName}
          </div>
          <span className="rounded-full border border-white/10 bg-slate-950/40 px-2 py-0.5 text-xs text-slate-300">
            {patient.sex}
          </span>
        </div>

        <div className="mt-2 grid grid-cols-1 gap-2 text-xs text-slate-300 sm:grid-cols-2">
          <div>
            <span className="text-slate-400">Edad:</span> {patient.age}
          </div>
          <div className="truncate">
            <span className="text-slate-400">CURP:</span> {patient.curp}
          </div>
          <div className="truncate">
            <span className="text-slate-400">Teléfono:</span> {patient.phone}
          </div>
          <div className="truncate">
            <span className="text-slate-400">Ubicación:</span> {patient.location}
          </div>
        </div>
      </div>

      <div className="flex items-center justify-end gap-2">
        <button
          onClick={() => onDelete(patient.id)}
          className="rounded-2xl border border-rose-500/20 bg-rose-500/10 px-4 py-2 text-sm text-rose-200 transition hover:bg-rose-500/15"
        >
          Eliminar
        </button>
      </div>
    </div>
  );
}

export default function PatientsPage() {
  const navigate = useNavigate();
  const [patients, setPatients] = useState([]);

  useEffect(() => {
  (async () => {
    console.log("🧪 [PatientsPage] mounted");

    const { userId } = await debugSupabaseSession("from PatientsPage");
    if (!userId) {
      console.warn("⚠️ [PatientsPage] sin sesión supabase, usando cache local");
      setPatients(getPatients());
      return;
    }

    try {
      const rows = await supabaseFetchPatients();
      setPatients(rows);
    } catch (e) {
      console.error("❌ [PatientsPage] fetch error, fallback cache:", e);
      setPatients(getPatients());
    }
  })();
}, []);


  const count = patients.length;

  const onDelete = (id) => {
    const next = deletePatientById(id);
    setPatients(next);
  };

  const empty = useMemo(() => count === 0, [count]);

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-slate-100">
      <div className="mx-auto max-w-6xl px-4 py-10">
        <div className="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <div className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-slate-300">
              <span className="h-2 w-2 rounded-full bg-sky-400" />
              Módulo Pacientes
            </div>
            <h1 className="mt-4 text-2xl font-semibold tracking-tight">
              Pacientes
            </h1>
            <p className="mt-1 text-sm text-slate-300">
              Registro y consulta. Por ahora se guarda en cache local.
            </p>
          </div>

          <div className="flex items-center gap-2">
            <button
              onClick={() => navigate("/")}
              className="rounded-2xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200 transition hover:border-white/20 hover:bg-white/10"
            >
              Volver
            </button>
            <button
              onClick={() => navigate("/patients/new")}
              className="rounded-2xl bg-white px-4 py-2 text-sm font-semibold text-slate-950 transition hover:opacity-90"
            >
              Dar de alta
            </button>
          </div>
        </div>

        <div className="mt-10">
          <div className="mb-4 text-sm text-slate-300">
            Total: <span className="font-semibold text-slate-100">{count}</span>
          </div>

          {empty ? (
            <div className="rounded-3xl border border-white/10 bg-white/5 p-10 text-center text-slate-300">
              <div className="text-base font-semibold text-slate-100">
                Sin pacientes registrados
              </div>
              <p className="mt-2 text-sm">
                Da de alta tu primer paciente para comenzar.
              </p>
              <button
                onClick={() => navigate("/patients/new")}
                className="mt-6 rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-slate-950 transition hover:opacity-90"
              >
                Dar de alta
              </button>
            </div>
          ) : (
            <div className="space-y-4">
              {patients.map((p) => (
                <PatientRow key={p.id} patient={p} onDelete={onDelete} />
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
