import React from "react";
import { useNavigate } from "react-router-dom";

export default function RecordMenuModal({ open, onClose, patientId }) {
  const navigate = useNavigate();
  if (!open) return null;

  const go = (path) => {
    onClose?.();
    navigate(path);
  };

  return (
    <div className="fixed inset-0 z-50">
      {/* overlay */}
      <div className="absolute inset-0 bg-black/50" onClick={onClose} />

      {/* modal */}
      <div className="absolute left-1/2 top-1/2 w-[92%] max-w-xl -translate-x-1/2 -translate-y-1/2 rounded-3xl border border-white/10 bg-slate-950/60 p-6 text-slate-100 shadow-2xl shadow-black/60 backdrop-blur">
        <div className="flex items-start justify-between gap-3">
          <div>
            <h3 className="text-lg font-semibold tracking-tight">
              Formatos del expediente
            </h3>
            <p className="mt-1 text-sm text-slate-300">
              Selecciona el tipo de registro a capturar.
            </p>
          </div>

          <button
            onClick={onClose}
            className="rounded-2xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-200 transition hover:border-white/20 hover:bg-white/10"
          >
            Cerrar
          </button>
        </div>

        <div className="mt-5 grid grid-cols-1 gap-3 md:grid-cols-2">
          <button
            onClick={() => go(`/expediente/${patientId}/historia-clinica/nueva`)}
            className="rounded-3xl border border-white/10 bg-white/5 p-5 text-left transition hover:border-white/20 hover:bg-white/10"
          >
            <div className="font-semibold">Historia clínica</div>
            <div className="mt-1 text-sm text-slate-300">
              Antecedentes, padecimiento, exploración…
            </div>
          </button>

          <button
            onClick={() => go(`/expediente/${patientId}/nota-medica/nueva`)}
            className="rounded-3xl border border-white/10 bg-white/5 p-5 text-left transition hover:border-white/20 hover:bg-white/10"
          >
            <div className="font-semibold">Nota médica</div>
            <div className="mt-1 text-sm text-slate-300">
              Evolución, diagnóstico, plan…
            </div>
          </button>

          <button
            onClick={() => go(`/expediente/${patientId}/preoperatoria/nueva`)}
            className="rounded-3xl border border-white/10 bg-white/5 p-5 text-left transition hover:border-white/20 hover:bg-white/10"
          >
            <div className="font-semibold">Preoperatoria</div>
            <div className="mt-1 text-sm text-slate-300">
              Valoración previa a cirugía.
            </div>
          </button>

          <button
            onClick={() => go(`/expediente/${patientId}/transoperatoria/nueva`)}
            className="rounded-3xl border border-white/10 bg-white/5 p-5 text-left transition hover:border-white/20 hover:bg-white/10"
          >
            <div className="font-semibold">Transoperatoria</div>
            <div className="mt-1 text-sm text-slate-300">
              Evento quirúrgico y hallazgos.
            </div>
          </button>

          <button
            onClick={() => go(`/expediente/${patientId}/postoperatoria/nueva`)}
            className="rounded-3xl border border-white/10 bg-white/5 p-5 text-left transition hover:border-white/20 hover:bg-white/10"
          >
            <div className="font-semibold">Postoperatoria</div>
            <div className="mt-1 text-sm text-slate-300">
              Evolución posterior al procedimiento.
            </div>
          </button>

          <button
            onClick={() => go(`/expediente/${patientId}/indicaciones-ingreso/nueva`)}
            className="rounded-3xl border border-white/10 bg-white/5 p-5 text-left transition hover:border-white/20 hover:bg-white/10"
          >
            <div className="font-semibold">Indicaciones de ingreso</div>
            <div className="mt-1 text-sm text-slate-300">
              Dieta, soluciones, medicamentos…
            </div>
          </button>

          <button
            onClick={() => go(`/expediente/${patientId}/laboratorios/imagen/nueva`)}
            className="md:col-span-2 rounded-3xl border border-emerald-400/30 bg-emerald-500/10 p-5 text-left text-emerald-200 transition hover:bg-emerald-500/15"
          >
            <div className="font-semibold">Laboratorios (imagen)</div>
            <div className="mt-1 text-sm text-emerald-100/80">
              Subir y guardar imagen en el expediente.
            </div>
          </button>
        </div>
      </div>
    </div>
  );
}
