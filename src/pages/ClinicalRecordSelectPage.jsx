import React, { useEffect, useMemo, useState } from "react";
import { useNavigate } from "react-router-dom";
import { supabaseFetchPatients } from "../patients/patientsApi.supabase.js";

function normalize(s) {
  return (s || "").toString().trim().toLowerCase();
}

function inDateRange(createdAt, from, to) {
  if (!createdAt) return true;
  const d = new Date(createdAt);

  if (from) {
    const f = new Date(`${from}T00:00:00`);
    if (d < f) return false;
  }
  if (to) {
    const t = new Date(`${to}T23:59:59`);
    if (d > t) return false;
  }
  return true;
}

function PatientRow({ p, active, onSelect }) {
  const fullName = (p.full_name || "").trim() || "Sin nombre";

  return (
    <button
      onClick={onSelect}
      className={`group w-full rounded-2xl border px-5 py-4 text-left shadow-2xl shadow-black/30 backdrop-blur transition
        ${
          active
            ? "border-emerald-400/50 bg-emerald-400/10"
            : "border-white/10 bg-white/5 hover:border-white/20 hover:bg-white/10"
        }
      `}
    >
      <div className="flex items-center justify-between gap-4">
        <div className="min-w-0">
          <div className="flex flex-wrap items-center gap-x-3 gap-y-1">
            <h3 className="truncate text-sm font-semibold tracking-tight text-slate-100">
              {fullName}
            </h3>

            <span className="rounded-full border border-white/10 bg-slate-950/40 px-3 py-1 text-xs text-slate-300">
              CURP: <span className="text-slate-100">{p.curp || "—"}</span>
            </span>

            <span className="rounded-full border border-white/10 bg-slate-950/40 px-3 py-1 text-xs text-slate-300">
              Tel: <span className="text-slate-100">{p.phone || "—"}</span>
            </span>
          </div>

          <div className="mt-2 text-xs text-slate-400">
            Registro:{" "}
            <span className="text-slate-300">
              {p.created_at ? new Date(p.created_at).toLocaleString() : "—"}
            </span>
          </div>
        </div>

        <div className="flex items-center gap-3">
          <div
            className={`rounded-2xl border px-3 py-2 text-xs transition
              ${
                active
                  ? "border-emerald-400/40 bg-emerald-950/40 text-emerald-200"
                  : "border-white/10 bg-slate-950/40 text-slate-300"
              }
            `}
          >
            {active ? "Seleccionado" : "Seleccionar"}
          </div>

          <div className="text-sm font-semibold text-slate-100 transition group-hover:translate-x-1">
            →
          </div>
        </div>
      </div>
    </button>
  );
}

export default function ClinicalRecordSelectPage() {
  const navigate = useNavigate();

  const [q, setQ] = useState("");
  const [from, setFrom] = useState("");
  const [to, setTo] = useState("");

  const [loading, setLoading] = useState(false);
  const [patients, setPatients] = useState([]);
  const [selected, setSelected] = useState(null);
  const [error, setError] = useState("");

  const canConfirm = useMemo(() => !!selected?.id, [selected]);

  const runSearch = async () => {
    setError("");
    setLoading(true);
    try {
      const all = await supabaseFetchPatients();

      const text = normalize(q);

      const filtered = (all || []).filter((p) => {
        const full = normalize(p.full_name);
        const curp = normalize(p.curp);

        const matchesText = !text || full.includes(text) || curp.includes(text);
        const matchesDate = inDateRange(p.created_at, from, to);

        return matchesText && matchesDate;
      });

      setPatients(filtered);
      setSelected(null);
    } catch (e) {
      setError(e?.message || "Error buscando pacientes");
      setPatients([]);
      setSelected(null);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    runSearch();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const onConfirm = () => {
    if (!selected?.id) return;
    navigate(`/expediente/${selected.id}`);
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-slate-100">
      <div className="mx-auto max-w-6xl px-4 py-10">
        {/* Header */}
        <div className="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <div className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-slate-300">
              <span className="h-2 w-2 rounded-full bg-emerald-400" />
              Expediente clínico
            </div>
            <h1 className="mt-4 text-2xl font-semibold tracking-tight">
              Seleccionar paciente
            </h1>
            <p className="mt-1 text-sm text-slate-300">
              Busca por <span className="font-medium text-slate-100">nombre</span>,{" "}
              <span className="font-medium text-slate-100">CURP</span> y/o rango de fechas.
            </p>
          </div>

          <div className="flex flex-wrap gap-2">
            <button
              onClick={() => navigate("/")}
              className="rounded-2xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200 transition hover:border-white/20 hover:bg-white/10"
            >
              Volver al panel
            </button>

            <button
              onClick={onConfirm}
              disabled={!canConfirm}
              className={`rounded-2xl border px-4 py-2 text-sm transition
                ${
                  canConfirm
                    ? "border-emerald-400/40 bg-emerald-500/10 text-emerald-200 hover:bg-emerald-500/15"
                    : "border-white/10 bg-white/5 text-slate-500 cursor-not-allowed"
                }`}
            >
              Confirmar paciente
            </button>
          </div>
        </div>

        {/* Filters */}
        <div className="mt-10 rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/40 backdrop-blur">
          <div className="grid grid-cols-1 gap-3 md:grid-cols-4">
            <input
              className="md:col-span-2 rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3 text-sm text-slate-100 outline-none placeholder:text-slate-500 focus:border-white/20"
              placeholder="Buscar por nombre o CURP"
              value={q}
              onChange={(e) => setQ(e.target.value)}
            />
            <input
              type="date"
              className="rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3 text-sm text-slate-100 outline-none focus:border-white/20"
              value={from}
              onChange={(e) => setFrom(e.target.value)}
              title="Desde"
            />
            <input
              type="date"
              className="rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3 text-sm text-slate-100 outline-none focus:border-white/20"
              value={to}
              onChange={(e) => setTo(e.target.value)}
              title="Hasta"
            />
          </div>

          <div className="mt-4 flex flex-wrap gap-2">
            <button
              onClick={runSearch}
              className="rounded-2xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200 transition hover:border-white/20 hover:bg-white/10"
            >
              {loading ? "Buscando..." : "Buscar"}
            </button>

            <button
              onClick={() => {
                setQ("");
                setFrom("");
                setTo("");
                setSelected(null);
                runSearch();
              }}
              className="rounded-2xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-200 transition hover:border-white/20 hover:bg-white/10"
            >
              Limpiar filtros
            </button>

            {selected?.id && (
              <div className="ml-auto inline-flex items-center gap-2 rounded-2xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-2 text-sm text-emerald-200">
                <span className="h-2 w-2 rounded-full bg-emerald-400" />
                Paciente seleccionado:{" "}
                <span className="font-medium">{(selected.full_name || "").trim()}</span>
              </div>
            )}
          </div>

          {error && (
            <div className="mt-4 rounded-2xl border border-red-400/20 bg-red-500/10 p-4 text-sm text-red-200">
              {error}
            </div>
          )}
        </div>

        {/* List */}
        <div className="mt-6 space-y-3">
          {patients.map((p) => (
            <PatientRow
              key={p.id}
              p={p}
              active={selected?.id === p.id}
              onSelect={() => setSelected(p)}
            />
          ))}
        </div>

        {!loading && patients.length === 0 && !error && (
          <div className="mt-10 rounded-3xl border border-white/10 bg-white/5 p-6 text-sm text-slate-300">
            <div className="font-medium text-slate-100">Sin resultados</div>
            <p className="mt-2">
              Ajusta la búsqueda (nombre/CURP) o el rango de fechas y vuelve a intentar.
            </p>
          </div>
        )}
      </div>
    </div>
  );
}
