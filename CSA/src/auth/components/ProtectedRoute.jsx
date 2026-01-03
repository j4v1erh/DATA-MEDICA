import React from "react";
import { Navigate } from "react-router-dom";
import { useAuth } from "../hooks/useAuth.js";

export function ProtectedRoute({ children }) {
  const { session, isLoading } = useAuth();

  console.log("🛡️ ProtectedRoute", { session, isLoading });

  if (isLoading) {
    return <div>Cargando autenticación…</div>;
  }

  if (!session) {
    console.log("➡️ No sesión, redirigiendo a /login");
    return <Navigate to="/login" replace />;
  }

  return children;
}
