import { supabase } from "../lib/supabaseClient.js";

// Busca pacientes por texto (nombre/apellido/curp) + rango de fechas
export async function searchPatients({ q = "", from = "", to = "" }) {
  let query = supabase
    .from("patients")
    .select("id, nombre, apellido, curp, created_at")
    .order("created_at", { ascending: false })
    .limit(50);

  const text = (q || "").trim();
  if (text) {
    // busca por nombre, apellido o curp
    query = query.or(
      `nombre.ilike.%${text}%,apellido.ilike.%${text}%,curp.ilike.%${text}%`
    );
  }

  if (from) query = query.gte("created_at", `${from}T00:00:00`);
  if (to) query = query.lte("created_at", `${to}T23:59:59`);

  const { data, error } = await query;
  if (error) throw error;

  return data || [];
}
