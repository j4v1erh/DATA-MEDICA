import { env } from "../../config/env.js";
import { authApiEnv } from "./auth.Api.env.js";
import { authApiSupabase } from "./authApi.supabase.js";

export const authApi =
  env.authProvider === "supabase" ? authApiSupabase : authApiEnv;
