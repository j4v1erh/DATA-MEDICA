const KEY = "patients_cache_v1";

export function getPatients() {
  try {
    const raw = localStorage.getItem(KEY);
    return raw ? JSON.parse(raw) : [];
  } catch {
    return [];
  }
}

export function savePatients(patients) {
  localStorage.setItem(KEY, JSON.stringify(patients));
}

export function addPatient(patient) {
  const patients = getPatients();
  const next = [patient, ...patients];
  savePatients(next);
  return next;
}

export function deletePatientById(id) {
  const patients = getPatients();
  const next = patients.filter((p) => p.id !== id);
  savePatients(next);
  return next;
}
