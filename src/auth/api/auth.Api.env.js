import { env } from "../../config/env.js";

export const authApiEnv = {
  async login({ email, password }) {
    if (email === env.devUserEmail && password === env.devUserPassword) {
      return {
        user: { id: "dev-user", email, role: "admin" },
        token: "dev-token",
      };
    }
    throw new Error("Credenciales inválidas");
  },

  async logout() {
    return true;
  },

  async getSession() {
    const raw = localStorage.getItem("session");
    return raw ? JSON.parse(raw) : null;
  },
};
