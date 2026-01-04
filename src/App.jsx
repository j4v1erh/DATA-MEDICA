import React from "react";
import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";

import LoginPage from "./pages/LoginPage.jsx";
import { ProtectedRoute } from "./auth/components/ProtectedRoute.jsx";

import HomePage from "./pages/HomePage.jsx";
import PatientsPage from "./pages/PatientsPage.jsx";
import PatientCreatePage from "./pages/PatientCreatePage.jsx";

// ✅ EXPEDIENTE CLÍNICO
import ClinicalRecordSelectPage from "./pages/ClinicalRecordSelectPage.jsx";
import PatientRecordPanelPage from "./pages/PatientRecordPanelPage.jsx";

// ✅ FORMULARIOS
import HistoriaClinicaFormPage from "./pages/HistoriaClinicaFormPage.jsx";
import NotaMedicaFormPage from "./pages/NotaMedicaFormPage.jsx";
import PreoperatoriaFormPage from "./pages/PreoperatoriaFormPage.jsx";
import TransoperatoriaFormPage from "./pages/TransoperatoriaFormPage.jsx";
import PostoperatoriaFormPage from "./pages/PostoperatoriaFormPage.jsx";
import IndicacionesIngresoFormPage from "./pages/IndicacionesIngresoFormPage.jsx";
import LaboratoriosImagenFormPage from "./pages/LaboratoriosImagenFormPage.jsx";

// ✅ VER HISTORIAL (NUEVO)
import HistoriaClinicaViewPage from "./pages/HistoriaClinicaViewPage.jsx";

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/login" element={<LoginPage />} />

        <Route
          path="/"
          element={
            <ProtectedRoute>
              <HomePage />
            </ProtectedRoute>
          }
        />

        <Route
          path="/patients"
          element={
            <ProtectedRoute>
              <PatientsPage />
            </ProtectedRoute>
          }
        />

        <Route
          path="/patients/new"
          element={
            <ProtectedRoute>
              <PatientCreatePage />
            </ProtectedRoute>
          }
        />

        {/* =========================
            ✅ EXPEDIENTE CLÍNICO
        ========================= */}
        <Route
          path="/expediente"
          element={
            <ProtectedRoute>
              <ClinicalRecordSelectPage />
            </ProtectedRoute>
          }
        />

        <Route
          path="/expediente/:patientId"
          element={
            <ProtectedRoute>
              <PatientRecordPanelPage />
            </ProtectedRoute>
          }
        />

        {/* =========================
            ✅ HISTORIA CLÍNICA (NUEVA + VER)
        ========================= */}
        <Route
          path="/expediente/:patientId/historia-clinica/nueva"
          element={
            <ProtectedRoute>
              <HistoriaClinicaFormPage />
            </ProtectedRoute>
          }
        />

        {/* ✅ VER HISTORIAL DE HISTORIAS CLÍNICAS (NUEVO) */}
        <Route
          path="/expediente/:patientId/historia-clinica/ver"
          element={
            <ProtectedRoute>
              <HistoriaClinicaViewPage />
            </ProtectedRoute>
          }
        />

        {/* =========================
            ✅ OTROS FORMULARIOS
        ========================= */}
        <Route
          path="/expediente/:patientId/nota-medica/nueva"
          element={
            <ProtectedRoute>
              <NotaMedicaFormPage />
            </ProtectedRoute>
          }
        />

        <Route
          path="/expediente/:patientId/preoperatoria/nueva"
          element={
            <ProtectedRoute>
              <PreoperatoriaFormPage />
            </ProtectedRoute>
          }
        />

        <Route
          path="/expediente/:patientId/transoperatoria/nueva"
          element={
            <ProtectedRoute>
              <TransoperatoriaFormPage />
            </ProtectedRoute>
          }
        />

        <Route
          path="/expediente/:patientId/postoperatoria/nueva"
          element={
            <ProtectedRoute>
              <PostoperatoriaFormPage />
            </ProtectedRoute>
          }
        />

        <Route
          path="/expediente/:patientId/indicaciones-ingreso/nueva"
          element={
            <ProtectedRoute>
              <IndicacionesIngresoFormPage />
            </ProtectedRoute>
          }
        />

        <Route
          path="/expediente/:patientId/laboratorios/imagen/nueva"
          element={
            <ProtectedRoute>
              <LaboratoriosImagenFormPage />
            </ProtectedRoute>
          }
        />

        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </BrowserRouter>
  );
}
