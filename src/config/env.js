export const env = {
  authProvider: import.meta.env.VITE_AUTH_PROVIDER || "env",
  devUserEmail: import.meta.env.VITE_DEV_USER_EMAIL || "",
  devUserPassword: import.meta.env.VITE_DEV_USER_PASSWORD || "",
  supabaseUrl: import.meta.env.VITE_SUPABASE_URL || "",
  supabaseAnonKey: import.meta.env.VITE_SUPABASE_ANON_KEY || "",
};
