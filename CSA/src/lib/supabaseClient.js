import { createClient } from "@supabase/supabase-js";
import { env } from "../config/env.js";

console.log("🧩 supabaseClient env:", {
  authProvider: env.authProvider,
  supabaseUrlPresent: Boolean(env.supabaseUrl),
  supabaseAnonKeyPresent: Boolean(env.supabaseAnonKey),
});

if (!env.supabaseUrl || !env.supabaseAnonKey) {
  console.warn(
    "⚠️ Supabase no configurado: falta VITE_SUPABASE_URL o VITE_SUPABASE_ANON_KEY"
  );
}

export const supabase =
  env.supabaseUrl && env.supabaseAnonKey
    ? createClient(env.supabaseUrl, env.supabaseAnonKey)
    : null;
