import React, { createContext, useEffect, useMemo, useState } from "react";
import { authApi } from "../api/authApi.js";
import { supabase } from "../../lib/supabaseClient.js";

export const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [session, setSession] = useState(null);
  const [role, setRole] = useState("user"); // ✅ default seguro
  const [profile, setProfile] = useState(null); // opcional (por si quieres nombre)
  const [isLoading, setIsLoading] = useState(true);

  // =========================
  // Helper: asegurar profile
  // =========================
  async function ensureProfile(user) {
    if (!user?.id) return { profile: null, role: "user" };

    // 1) intentar leer
    const read = await supabase
      .from("profiles")
      .select("id, full_name, role")
      .eq("id", user.id)
      .maybeSingle(); // ✅ no truena si no existe

    if (!read.error && read.data) {
      return { profile: read.data, role: read.data.role || "user" };
    }

    // Si hay error por "tabla no existe" o RLS fuerte, no rompas la app.
    // (puedes ver el error en consola)
    if (read.error && read.error.code !== "PGRST116") {
      console.warn("⚠️ profiles read error:", read.error.message);
      return { profile: null, role: "user" };
    }

    // 2) No existe el profile -> crear
    // Nota: si tienes RLS en profiles, necesitas policy de INSERT propia.
    const insert = await supabase.from("profiles").insert({
      id: user.id,
      full_name: user.user_metadata?.full_name || user.email || null,
      role: "user",
    });

    if (insert.error) {
      console.warn("⚠️ profiles insert error:", insert.error.message);
      return { profile: null, role: "user" };
    }

    // 3) Leer otra vez
    const read2 = await supabase
      .from("profiles")
      .select("id, full_name, role")
      .eq("id", user.id)
      .maybeSingle();

    if (read2.error) {
      console.warn("⚠️ profiles read2 error:", read2.error.message);
      return { profile: null, role: "user" };
    }

    return { profile: read2.data, role: read2.data?.role || "user" };
  }

  // =========================
  // Cargar sesión + rol
  // =========================
  useEffect(() => {
    (async () => {
      try {
        const s = await authApi.getSession();
        setSession(s);

        if (s?.user?.id) {
          const res = await ensureProfile(s.user);
          setProfile(res.profile);
          setRole(res.role || "user");
        } else {
          setProfile(null);
          setRole("user");
        }
      } catch (e) {
        console.error("❌ getSession error:", e);
        setSession(null);
        setProfile(null);
        setRole("user");
      } finally {
        setIsLoading(false);
      }
    })();
  }, []);

  const value = useMemo(() => {
    return {
      session,
      role,
      profile, // ✅ por si quieres mostrar full_name en UI
      isLoading,

      async login(payload) {
        const result = await authApi.login(payload);

        localStorage.setItem("session", JSON.stringify(result));
        setSession(result);

        if (result?.user?.id) {
          const res = await ensureProfile(result.user);
          setProfile(res.profile);
          setRole(res.role || "user");
        } else {
          setProfile(null);
          setRole("user");
        }
      },

      async logout() {
        await authApi.logout();
        localStorage.removeItem("session");
        setSession(null);
        setProfile(null);
        setRole("user");
      },
    };
  }, [session, role, profile, isLoading]);

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}
