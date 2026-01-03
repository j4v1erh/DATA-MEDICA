import React, { createContext, useEffect, useMemo, useState } from "react";
import { authApi } from "../api/authApi.js";

export const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [session, setSession] = useState(null);
  const [isLoading, setIsLoading] = useState(true);

  console.log("🔐 AuthProvider render", { session, isLoading });

  useEffect(() => {
    console.log("🔄 AuthProvider useEffect: getSession()");

    (async () => {
      try {
        const s = await authApi.getSession();
        console.log("✅ getSession resultado:", s);
        setSession(s);
      } catch (e) {
        console.error("❌ getSession error:", e);
        setSession(null);
      } finally {
        console.log("⏹️ AuthProvider loading false");
        setIsLoading(false);
      }
    })();
  }, []);

  const value = useMemo(() => {
    return {
      session,
      isLoading,
      async login(payload) {
        console.log("🔑 login llamado", payload);
        const result = await authApi.login(payload);
        console.log("✅ login ok", result);
        localStorage.setItem("session", JSON.stringify(result));
        setSession(result);
      },
      async logout() {
        console.log("🚪 logout");
        await authApi.logout();
        localStorage.removeItem("session");
        setSession(null);
      },
    };
  }, [session, isLoading]);

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}
