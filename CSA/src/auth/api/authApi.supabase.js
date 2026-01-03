import { supabase } from "../../lib/supabaseClient.js";

export const authApiSupabase = {
  async login({ email, password }) {
    console.log("🔐 [authApiSupabase] login start:", { email });

    if (!supabase) {
      throw new Error(
        "Supabase no está configurado (.env). Define VITE_SUPABASE_URL y VITE_SUPABASE_ANON_KEY."
      );
    }

    const { data, error } = await supabase.auth.signInWithPassword({
      email,
      password,
    });

    console.log("🔐 [authApiSupabase] login result:", { data, error });

    if (error) throw error;

    return {
      user: data.user,
      token: data.session?.access_token,
    };
  },

  async logout() {
    console.log("🔐 [authApiSupabase] logout");

    if (!supabase) return true;

    const { error } = await supabase.auth.signOut();
    if (error) throw error;

    return true;
  },

  async getSession() {
    if (!supabase) return null;

    const { data, error } = await supabase.auth.getSession();
    console.log("🔐 [authApiSupabase] getSession:", { data, error });

    if (error) return null;
    return data.session ?? null;
  },
};
