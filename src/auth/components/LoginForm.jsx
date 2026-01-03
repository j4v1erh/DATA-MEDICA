import React, { useMemo, useState } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "../hooks/useAuth.js";

export function LoginForm() {
  const { login } = useAuth();
  const navigate = useNavigate();

  const [email, setEmail] = useState("admin@demo.com");
  const [password, setPassword] = useState("123456");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  const canSubmit = useMemo(() => {
    return email.trim().length > 0 && password.trim().length > 0 && !loading;
  }, [email, password, loading]);

  const onSubmit = async (e) => {
    e.preventDefault();
    setError("");
    setLoading(true);

    try {
      await login({ email, password });
      navigate("/", { replace: true }); // ✅ redirección a pantalla principal
    } catch (err) {
      setError(err?.message ?? "No fue posible iniciar sesión.");
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={onSubmit} className="space-y-4">
      <div className="space-y-2">
        <label className="text-sm text-slate-200">Correo</label>
        <input
          className="w-full rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3 text-sm text-slate-100 outline-none ring-0 placeholder:text-slate-500 focus:border-white/20 focus:bg-slate-950/55 focus:ring-4 focus:ring-white/10"
          type="email"
          autoComplete="email"
          placeholder="tu-correo@hospital.com"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
        />
      </div>

      <div className="space-y-2">
        <label className="text-sm text-slate-200">Contraseña</label>
        <input
          className="w-full rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3 text-sm text-slate-100 outline-none ring-0 placeholder:text-slate-500 focus:border-white/20 focus:bg-slate-950/55 focus:ring-4 focus:ring-white/10"
          type="password"
          autoComplete="current-password"
          placeholder="••••••••"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
        />
      </div>

      {error ? (
        <div className="rounded-2xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
          <div className="font-medium">No se pudo iniciar sesión</div>
          <div className="mt-1 text-rose-200/90">{error}</div>
        </div>
      ) : null}

      <button
        type="submit"
        disabled={!canSubmit}
        className="group relative w-full overflow-hidden rounded-2xl bg-white px-4 py-3 text-sm font-semibold text-slate-950 transition disabled:cursor-not-allowed disabled:opacity-60"
      >
        <span className="relative z-10">
          {loading ? "Entrando..." : "Entrar"}
        </span>
        <span className="absolute inset-0 -translate-x-full bg-gradient-to-r from-sky-200 via-white to-sky-200 transition group-hover:translate-x-0" />
      </button>

      <div className="flex items-center justify-between pt-1 text-xs text-slate-400">
        <span>Proveedor actual: .env</span>
        <span className="font-mono">VITE_AUTH_PROVIDER=env</span>
      </div>
    </form>
  );
}
