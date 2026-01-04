import React, { useEffect, useMemo, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { supabase } from "../lib/supabaseClient.js";
import { useAuth } from "../auth/hooks/useAuth.js";
import RecordMenuModal from "../records/RecordMenuModal.jsx";

function InfoPill({ label, value }) {
  return (
    <div className="rounded-2xl border border-white/10 bg-slate-950/40 px-3 py-2 text-xs text-slate-300">
      <span className="text-slate-400">{label}: </span>
      <span className="text-slate-100">{value || "—"}</span>
    </div>
  );
}

function ActionCard({ title, description, meta, onClick, disabled }) {
  return (
    <button
      onClick={disabled ? undefined : onClick}
      disabled={disabled}
      className={`group w-full rounded-3xl border p-6 text-left shadow-2xl shadow-black/40 backdrop-blur transition
        ${disabled
          ? "border-white/10 bg-white/5 opacity-50 cursor-not-allowed"
          : "border-white/10 bg-white/5 hover:border-white/20 hover:bg-white/10"}
      `}
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
          {disabled ? "Restringido" : "Acceder"}
        </div>
        <div className="text-sm font-semibold text-slate-100 transition group-hover:translate-x-1">
          →
        </div>
      </div>
    </button>
  );
}

export default function PatientRecordPanelPage() {
  const { patientId } = useParams();
  const navigate = useNavigate();
  const { session, role } = useAuth();

  const isAdmin = useMemo(() => role === "admin", [role]);

  const [patient, setPatient] = useState(null);
  const [openMenu, setOpenMenu] = useState(false);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    let alive = true;

    (async () => {
      setError("");
      setLoading(true);

      try {
        const { data, error } = await supabase
          .from("patients")
          .select("*")
          .eq("id", patientId)
          .single();

        if (!alive) return;

        if (error) {
          setError(error.message);
          setPatient(null);
        } else {
          setPatient(data);
        }
      } catch (e) {
        if (!alive) return;
        setError(e?.message || "Error cargando paciente.");
        setPatient(null);
      } finally {
        if (alive) setLoading(false);
      }
    })();

    return () => {
      alive = false;
    };
  }, [patientId]);

  const email = session?.user?.email ?? "usuario";

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-slate-100">
      <div className="mx-auto max-w-6xl px-4 py-10">
        {/* Header */}
        <div className="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <div className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-slate-300">
              <span className="h-2 w-2 rounded-full bg-emerald-400" />
              Panel de expediente
            </div>
            <h1 className="mt-4 text-2xl font-semibold tracking-tight">
              {loading ? "Cargando paciente..." : "Expediente del paciente"}
            </h1>
            <p className="mt-1 text-sm text-slate-300">
              Sesión: <span className="font-medium text-slate-100">{email}</span>
            </p>
          </div>

          <div className="flex flex-wrap gap-2">
            <button
              onClick={() => navigate("/expediente")}
              className="rounded-2xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200 transition hover:border-white/20 hover:bg-white/10"
            >
              Cambiar paciente
            </button>

            <button
              onClick={() => setOpenMenu(true)}
              className="rounded-2xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-2 text-sm text-emerald-200 transition hover:bg-emerald-500/15"
            >
              Abrir formatos
            </button>
          </div>
        </div>

        {/* Patient summary */}
        <div className="mt-10 rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/40 backdrop-blur">
          {error && (
            <div className="rounded-2xl border border-red-400/20 bg-red-500/10 p-4 text-sm text-red-200">
              {error}
            </div>
          )}

          {!error && patient && (
            <>
              <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                  <div className="text-lg font-semibold text-slate-100">
                    {patient.nombre ?? ""} {patient.apellido ?? ""}
                  </div>
                  <div className="mt-1 text-sm text-slate-300">
                    CURP: <span className="text-slate-100">{patient.curp || "—"}</span>
                  </div>
                </div>

                <div className="flex flex-wrap gap-2">
                  <InfoPill label="Tel" value={patient.telefono || patient.phone || "—"} />
                  <InfoPill label="Sexo" value={patient.sexo || "—"} />
                  <InfoPill label="Edad" value={patient.edad || "—"} />
                </div>
              </div>

              <div className="mt-4 text-xs text-slate-400">
                ID interno: <span className="text-slate-300">{patient.id}</span>
              </div>
            </>
          )}

          {!error && !loading && !patient && (
            <div className="text-sm text-slate-300">
              No se encontró el paciente seleccionado.
            </div>
          )}
        </div>

        {/* Actions */}
        <div className="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2">
          <ActionCard
            title="1. Agregar"
            description="Crear un nuevo registro (historia clínica, notas, indicaciones, etc.)."
            meta="NUEVO"
            onClick={() => setOpenMenu(true)}
          />

          <ActionCard
            title="2. Ver"
            description="Consultar registros existentes del paciente (historial y formatos guardados)."
            meta="CONSULTA"
            onClick={() => {
              // Placeholder: aquí luego ponemos /expediente/:patientId/ver
              // Por ahora abrimos menú para capturar rápido.
              setOpenMenu(true);
            }}
          />

          <ActionCard
            title="3. Modificar (Admin)"
            description="Editar registros existentes. Acceso exclusivo para administrador."
            meta="ADMIN"
            disabled={!isAdmin}
            onClick={() => {
              // Placeholder: aquí luego ponemos /expediente/:patientId/editar
              setOpenMenu(true);
            }}
          />

          <ActionCard
            title="4. Eliminar (Admin)"
            description="Eliminar registros. Requiere confirmación adicional."
            meta="ADMIN"
            disabled={!isAdmin}
            onClick={() => {
              // Placeholder: aquí luego ponemos /expediente/:patientId/eliminar
              // (y confirmación doble)
              alert("Eliminar (Admin): pendiente de implementar con confirmación.");
            }}
          />
        </div>

        {/* Footer hint */}
        <div className="mt-10 rounded-3xl border border-white/10 bg-white/5 p-6 text-sm text-slate-300">
          <div className="font-medium text-slate-100">Siguiente paso</div>
          <p className="mt-2">
            Ahora conectamos el módulo <span className="font-medium text-slate-100">Ver</span> para listar registros
            guardados por tipo (historia/nota/pre/post/indicaciones/labs) y aplicar permisos por rol.
          </p>
        </div>
      </div>

      {/* Modal de formatos */}
      <RecordMenuModal
        open={openMenu}
        onClose={() => setOpenMenu(false)}
        patientId={patientId}
      />
    </div>
  );
}
