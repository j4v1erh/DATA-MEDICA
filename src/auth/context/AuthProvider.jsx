import React, { createContext, useEffect, useMemo, useState } from "react";
import { authApi } from "../api/authApi.js";
import { supabase } from "../../lib/supabaseClient.js";

export const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [session, setSession] = useState(null);
  const [role, setRole] = useState(null); // ✅ NUEVO
  const [isLoading, setIsLoading] = useState(true);

  console.log("🔐 AuthProvider render", { session, role, isLoading });

  // =========================
  // Cargar sesión + rol
  // =========================
  useEffect(() => {
    console.log("🔄 AuthProvider useEffect: getSession()");

    (async () => {
      try {
        const s = await authApi.getSession();
        console.log("✅ getSession resultado:", s);
        setSession(s);

        // 👉 Si hay sesión, cargar rol
        if (s?.user?.id) {
          console.log("🔎 Cargando rol para user:", s.user.id);

          const { data, error } = await supabase
            .from("profiles")
            .select("role")
            .eq("id", s.user.id)
            .single();

          if (error) {
            console.warn("⚠️ No se pudo cargar role:", error.message);
            setRole(null);
          } else {
            console.log("✅ Role cargado:", data?.role);
            setRole(data?.role ?? null);
          }
        } else {
          setRole(null);
        }
      } catch (e) {
        console.error("❌ getSession error:", e);
        setSession(null);
        setRole(null);
      } finally {
        console.log("⏹️ AuthProvider loading false");
        setIsLoading(false);
      }
    })();
  }, []);

  const value = useMemo(() => {
    return {
      session,
      role,        // ✅ EXPUESTO AL CONTEXTO
      isLoading,

      async login(payload) {
        console.log("🔑 login llamado", payload);
        const result = await authApi.login(payload);
        console.log("✅ login ok", result);

        localStorage.setItem("session", JSON.stringify(result));
        setSession(result);

        // 👉 cargar role después del login
        if (result?.user?.id) {
          const { data, error } = await supabase
            .from("profiles")
            .select("role")
            .eq("id", result.user.id)
            .single();

          if (!error) {
            setRole(data?.role ?? null);
          } else {
            console.warn("⚠️ Role no encontrado tras login");
            setRole(null);
          }
        }
      },

      async logout() {
        console.log("🚪 logout");
        await authApi.logout();
        localStorage.removeItem("session");
        setSession(null);
        setRole(null); // ✅ limpiar role
      },
    };
  }, [session, role, isLoading]);

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
}
