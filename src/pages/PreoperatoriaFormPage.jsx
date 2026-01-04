import React from "react";
import { useNavigate, useParams } from "react-router-dom";

export default function PreoperatoriaFormPage() {
  const { patientId } = useParams();
  const nav = useNavigate();

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-slate-100">
      <div className="mx-auto max-w-3xl px-4 py-10">
        <div className="rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/40 backdrop-blur">
          <button
            onClick={() => nav(`/expediente/${patientId}`)}
            className="text-sm text-slate-300 hover:text-slate-100"
          >
            ← Volver al panel del paciente
          </button>

          <h1 className="mt-4 text-2xl font-semibold tracking-tight">
            Nota preoperatoria (Nuevo)
          </h1>

          <p className="mt-2 text-sm text-slate-300">
            Placeholder: aquí irá el formulario de preoperatoria.
          </p>
        </div>
      </div>
    </div>
  );
}
