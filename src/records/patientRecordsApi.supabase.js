import { supabase } from "../lib/supabaseClient.js";

// Busca pacientes por texto (full_name/curp) + rango de fechas
export async function searchPatients({ q = "", from = "", to = "" }) {
  let query = supabase
    .from("patients")
    .select("id, full_name, age, sex, phone, curp, created_at")
    .order("created_at", { ascending: false })
    .limit(50);

  const text = (q || "").trim();
  if (text) {
    // busca por full_name o curp
    query = query.or(
      `full_name.ilike.%${text}%,curp.ilike.%${text}%`
    );
  }

  if (from) query = query.gte("created_at", `${from}T00:00:00`);
  if (to) query = query.lte("created_at", `${to}T23:59:59`);

  const { data, error } = await query;
  if (error) throw error;

  return data || [];
}

// Obtener 1 paciente por ID (para el panel: sexo, edad, etc.)
export async function getPatientById(patientId) {
  const { data, error } = await supabase
    .from("patients")
    .select("*")
    .eq("id", patientId)
    .single();

  if (error) throw error;
  return data;
}
