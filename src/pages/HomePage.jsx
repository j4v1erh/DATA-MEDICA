import React from "react";
import { useAuth } from "../auth/hooks/useAuth.js";
import { useNavigate } from "react-router-dom";




function ActionCard({ title, description, meta, onClick }) {
  return (
    <button
      onClick={onClick}
      className="group w-full rounded-3xl border border-white/10 bg-white/5 p-6 text-left shadow-2xl shadow-black/40 backdrop-blur transition hover:border-white/20 hover:bg-white/10"
    >
      <div className="flex items-start justify-between gap-4">
        <div>
          <h3 className="text-base font-semibold tracking-tight text-slate-100">
            {title}
          </h3>
          <p className="mt-2 text-sm leading-relaxed text-slate-300">
            {description}
          </p>
        </div>

        <div className="rounded-2xl border border-white/10 bg-slate-950/40 px-3 py-2 text-xs text-slate-300">
          {meta}
        </div>
      </div>

      <div className="mt-5 flex items-center justify-between">
        <div className="text-xs text-slate-400">
          Acceder al módulo
        </div>
        <div className="text-sm font-semibold text-slate-100 transition group-hover:translate-x-1">
          →
        </div>
      </div>
    </button>
  );
}

export default function HomePage() {
  const { session, logout } = useAuth();
  const email = session?.user?.email ?? "usuario";
const navigate = useNavigate();
const go = (path) => navigate(path);

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-slate-100">
      <div className="mx-auto max-w-6xl px-4 py-10">
        {/* Header */}
        <div className="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <div className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-slate-300">
              <span className="h-2 w-2 rounded-full bg-emerald-400" />
              Sesión activa
            </div>
            <h1 className="mt-4 text-2xl font-semibold tracking-tight">
              Panel principal
            </h1>
            <p className="mt-1 text-sm text-slate-300">
              Bienvenido, <span className="font-medium text-slate-100">{email}</span>. Selecciona un módulo para continuar.
            </p>
          </div>

          <button
            onClick={logout}
            className="rounded-2xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200 transition hover:border-white/20 hover:bg-white/10"
          >
            Cerrar sesión
          </button>
        </div>

        {/* Cards */}
        <div className="mt-10 grid grid-cols-1 gap-5 md:grid-cols-2">
          <ActionCard
            title="Pacientes"
            description="Alta, búsqueda y gestión del expediente del paciente. Datos generales y contacto."
            meta="ADM / CLÍNICO"
            onClick={() => go("/patients")}
          />
          <ActionCard
            title="Citas"
            description="Agenda clínica: programación, confirmación, reprogramación y estado de atención."
            meta="AGENDA"
            onClick={() => go("/appointments")}
          />
          <ActionCard
            title="Expediente clínico"
            description="Notas médicas, evolución, diagnósticos, antecedentes y exploración física."
            meta="EHR"
            onClick={() => go("/ehr")}
          />
          <ActionCard
            title="Recetas y órdenes"
            description="Recetas, indicaciones, órdenes de laboratorio y estudios. Historial por paciente."
            meta="RX / LAB"
            onClick={() => go("/orders")}
          />
          <ActionCard
            title="Facturación"
            description="Servicios, cargos, pagos y estados de cuenta. Preparado para integración posterior."
            meta="FIN"
            onClick={() => go("/billing")}
          />
          <ActionCard
            title="Configuración"
            description="Roles, permisos, catálogos y parámetros del sistema. Acceso restringido."
            meta="ADMIN"
            onClick={() => go("/settings")}
          />
        </div>

        {/* Footer hint */}
        <div className="mt-10 rounded-3xl border border-white/10 bg-white/5 p-6 text-sm text-slate-300">
          <div className="font-medium text-slate-100">Siguiente paso</div>
          <p className="mt-2">
            Después armamos el <span className="font-medium text-slate-100">navbar</span> y conectamos cada card a sus rutas reales.
            También definimos roles (admin/doctor/enfermería/recepción) para mostrar módulos por permisos.
          </p>
        </div>
      </div>
    </div>
  );
}
