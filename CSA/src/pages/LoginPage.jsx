import React from "react";
import { LoginForm } from "../auth/components/LoginForm.jsx";

export default function LoginPage() {
  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-slate-100">
      <div className="mx-auto flex min-h-screen max-w-6xl items-center justify-center px-4">
        <div className="grid w-full grid-cols-1 gap-8 lg:grid-cols-2 lg:gap-10">
          {/* Panel izquierdo: branding */}
          <div className="hidden lg:flex lg:flex-col lg:justify-center">
            <div className="rounded-3xl border border-white/10 bg-white/5 p-10 shadow-2xl shadow-black/40 backdrop-blur">
              <div className="flex items-center gap-3">
                <div className="grid h-12 w-12 place-items-center rounded-2xl bg-white/10 ring-1 ring-white/15">
                  <span className="text-xl font-semibold">DM</span>
                </div>
                <div>
                  <h1 className="text-2xl font-semibold tracking-tight">
                    DATA MÉDICA
                  </h1>
                  <p className="text-sm text-slate-300">
                    Expediente Clínico Electrónico / HIS
                  </p>
                </div>
              </div>

              <div className="mt-8 space-y-4 text-sm text-slate-300">
                <p className="leading-relaxed">
                  Acceso seguro para personal clínico y administrativo.
                  Estructura lista para integrar Supabase (Auth + Roles + RLS).
                </p>

                <div className="grid grid-cols-2 gap-3">
                  <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <div className="text-xs text-slate-400">Módulo</div>
                    <div className="mt-1 font-medium text-slate-100">
                      Pacientes
                    </div>
                  </div>
                  <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <div className="text-xs text-slate-400">Módulo</div>
                    <div className="mt-1 font-medium text-slate-100">
                      Citas
                    </div>
                  </div>
                  <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <div className="text-xs text-slate-400">Módulo</div>
                    <div className="mt-1 font-medium text-slate-100">
                      Evolución
                    </div>
                  </div>
                  <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <div className="text-xs text-slate-400">Módulo</div>
                    <div className="mt-1 font-medium text-slate-100">
                      Recetas
                    </div>
                  </div>
                </div>

                <p className="pt-2 text-xs text-slate-400">
                  Recomendación: al pasar a Supabase, se implementarán roles
                  (admin/doctor/enfermería/recepción) con políticas por fila.
                </p>
              </div>
            </div>
          </div>

          {/* Panel derecho: login */}
          <div className="flex items-center justify-center">
            <div className="w-full max-w-md rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/40 backdrop-blur sm:p-8">
              <div className="mb-6">
                <h2 className="text-xl font-semibold tracking-tight">
                  Iniciar sesión
                </h2>
                <p className="mt-1 text-sm text-slate-300">
                  Ingresa con tus credenciales provisionales (.env).
                </p>
              </div>

              <LoginForm />

              <div className="mt-6 rounded-2xl border border-white/10 bg-white/5 p-4 text-xs text-slate-300">
                <div className="font-medium text-slate-100">Modo prueba</div>
                <p className="mt-1">
                  Actualmente el login valida contra variables del archivo{" "}
                  <span className="font-mono text-slate-100">.env</span>.
                  Cuando migremos a Supabase, el UI se mantiene y solo cambia el
                  provider.
                </p>
              </div>

              <p className="mt-6 text-center text-xs text-slate-400">
                © {new Date().getFullYear()} Clinica San Agustin - Cardenas, Tab. Acceso controlado.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
