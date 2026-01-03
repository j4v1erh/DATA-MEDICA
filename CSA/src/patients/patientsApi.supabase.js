import { supabase } from "../lib/supabaseClient.js";

function explainSupabaseError(err) {
  return {
    message: err?.message,
    code: err?.code,
    details: err?.details,
    hint: err?.hint,
  };
}

export async function debugSupabaseSession(tag = "") {
  if (!supabase) {
    console.warn(`⚠️ [patientsApi] supabase=null ${tag}`);
    return { userId: null };
  }

  const { data, error } = await supabase.auth.getSession();
  const userId = data?.session?.user?.id ?? null;

  console.log(`🧪 [patientsApi] getSession ${tag}:`, {
    userId,
    error: error ? explainSupabaseError(error) : null,
  });

  return { userId, raw: data, error };
}

export async function supabaseInsertPatient(payload) {
  console.log("🧾 [patientsApi] insert payload:", payload);

  if (!supabase) {
    throw new Error("Supabase no está configurado (supabaseClient.js es null).");
  }

  const { data, error } = await supabase
    .from("patients")
    .insert(payload)
    .select("*")
    .single();

  console.log("✅ [patientsApi] insert result:", {
    data,
    error: error ? explainSupabaseError(error) : null,
  });

  if (error) throw error;
  return data;
}

export async function supabaseFetchPatients() {
  console.log("📥 [patientsApi] fetch start");

  if (!supabase) {
    throw new Error("Supabase no está configurado (supabaseClient.js es null).");
  }

  const { data, error } = await supabase
    .from("patients")
    .select("*")
    .order("created_at", { ascending: false });

  console.log("📥 [patientsApi] fetch result:", {
    count: data?.length ?? 0,
    error: error ? explainSupabaseError(error) : null,
  });

  if (error) throw error;
  return data ?? [];
}
