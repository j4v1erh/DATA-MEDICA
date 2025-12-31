<?php
// secciones/historia_clinica/historia_clinica_agregar.php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../db.php';

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$paciente_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($paciente_id <= 0) { die("Paciente no especificado."); }

// Obtener usuario en sesión (quien registra)
$usuario_id = (int)($_SESSION['usuario_id'] ?? 0);
if ($usuario_id <= 0) { die("Sesión inválida."); }

$stmtU = $pdo->prepare("SELECT nombre_completo, cedula, servicio FROM usuarios WHERE id = :id LIMIT 1");
$stmtU->execute([':id' => $usuario_id]);
$u = $stmtU->fetch(PDO::FETCH_ASSOC);
if (!$u) { die("Usuario no encontrado."); }

$nombre_medico   = (string)($u['nombre_completo'] ?? '');
$cedula_medico   = (string)($u['cedula'] ?? '');
$servicio_medico = (string)($u['servicio'] ?? '');

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // OJO: NO leemos medico/cedula/servicio desde POST
  $notaMedica       = trim($_POST['notaMedica'] ?? '');
  $exploracionFisica= trim($_POST['exploracionFisica'] ?? '');
  $diagnostico      = trim($_POST['diagnostico'] ?? '');
  $plan             = trim($_POST['plan'] ?? '');
  $analisis         = trim($_POST['analisis'] ?? '');
  $pronostico       = trim($_POST['pronostico'] ?? '');

  if ($notaMedica === "" && $diagnostico === "") {
    $mensaje = "Captura al menos Nota Médica o Diagnóstico.";
  } else {

    // descripcion es NOT NULL en tu tabla -> lo llenamos con Nota Médica (o Diagnóstico si nota está vacío)
    $descripcion = ($notaMedica !== "") ? $notaMedica : $diagnostico;

    // notas (opcional)
    $notas = trim(
      ($analisis !== "" ? "ANÁLISIS:\n$analisis\n\n" : "") .
      ($pronostico !== "" ? "PRONÓSTICO:\n$pronostico" : "")
    );
    if ($notas === "") $notas = null;

    $stmt = $pdo->prepare("
      INSERT INTO historia_clinica (
        paciente_id,
        creado_por_usuario_id,
        motivo_consulta,
        exploracion_fisica,
        diagnostico_text,
        tratamientos,
        descripcion,
        notas,
        medico_elabora,
        medico_cedula,
        medico_servicio,
        fecha,
        creado_en
      )
      VALUES (
        :paciente_id,
        :creado_por,
        :motivo,
        :exploracion,
        :diagnostico_text,
        :tratamientos,
        :descripcion,
        :notas,
        :medico_elabora,
        :medico_cedula,
        :medico_servicio,
        NOW(),
        NOW()
      )
    ");

    $ok = $stmt->execute([
      ':paciente_id'     => $paciente_id,
      ':creado_por'      => $usuario_id,
      ':motivo'          => ($notaMedica !== "" ? $notaMedica : null),
      ':exploracion'     => ($exploracionFisica !== "" ? $exploracionFisica : null),
      ':diagnostico_text'=> ($diagnostico !== "" ? $diagnostico : null),
      ':tratamientos'    => ($plan !== "" ? $plan : null),
      ':descripcion'     => $descripcion,
      ':notas'           => $notas,
      ':medico_elabora'  => $nombre_medico,
      ':medico_cedula'   => ($cedula_medico !== "" ? $cedula_medico : null),
      ':medico_servicio' => ($servicio_medico !== "" ? $servicio_medico : null),
    ]);

    if ($ok) {
      header("Location: ../../panel_paciente.php?id=".$paciente_id."&sec=historia");
      exit;
    } else {
      $mensaje = "Error al guardar.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Historia Clínica - Agregar</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>

  <style>
    :root {
      --primary: #079FCF;
      --primary-dark: #0277A1;
      --primary-deep: #014F75;

      --accent-light: #5FD5F4;
      --accent: #36BCE1;
      --accent-dark: #01678C;

      --neutral-bg: #F4F8FA;
      --neutral-gray: #DDE5EA;
      --neutral-text: #2F3A45;
    }
    body { background-color: var(--neutral-bg); }
    .input_sv {
      width: 100%;
      height: 40px;
      padding: 0 12px;
      border: 1px solid var(--neutral-gray);
      border-radius: 6px;
      color: var(--neutral-text);
    }
    .card {
      background: white;
      border: 2px solid var(--neutral-gray);
      border-radius: 8px;
      box-shadow: 0px 2px 5px rgba(0,0,0,0.05);
    }
    h3 { color: var(--primary-deep); }
    #tituloNota { background: var(--primary-dark); color: white; }
  </style>
</head>

<body>
<div class="max-w-[1200px] mx-auto p-6">

  <?php if ($mensaje): ?>
    <div class="card p-4 mb-6" style="border-color:#f5c2c7;">
      <div style="color:#b02a37; font-weight:600;"><?= h($mensaje) ?></div>
    </div>
  <?php endif; ?>

  <form method="POST">

    <!-- HEADER -->
    <div class="card p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-start">

        <div>
          <label class="block text-[var(--neutral-text)] mb-1">Médico que Elabora</label>
          <input id="medico" name="medico_elabora" type="text"
            class="w-full h-9 px-3 border border-[var(--neutral-gray)] rounded text-[var(--neutral-text)]"
            value="<?= h($nombre_medico) ?>" readonly>
        </div>

        <div>
          <label class="block text-[var(--neutral-text)] mb-1">Cédula Profesional</label>
          <input id="cedula" name="cedula_elabora" type="text"
            class="w-full h-9 px-3 border border-[var(--neutral-gray)] rounded text-[var(--neutral-text)]"
            value="<?= h($cedula_medico) ?>" readonly>
        </div>

        <div>
          <label class="block text-[var(--neutral-text)] mb-1">Servicio Médico</label>
          <input id="servicio" name="servicio_elabora" type="text"
            class="w-full h-9 px-3 border border-[var(--neutral-gray)] rounded text-[var(--neutral-text)]"
            value="<?= h($servicio_medico) ?>" readonly>
        </div>

        <div>
          <label class="block text-[var(--neutral-text)] mb-1">Fecha</label>
          <div id="fecha"
            class="w-full h-9 px-3 border border-[var(--neutral-gray)] rounded bg-[var(--neutral-gray)] flex items-center text-[var(--neutral-text)]"></div>
        </div>

        <div>
          <label class="block text-[var(--neutral-text)] mb-1">Hora</label>
          <div id="hora"
            class="w-full h-9 px-3 border border-[var(--neutral-gray)] rounded bg-[var(--neutral-gray)] flex items-center text-[var(--neutral-text)]"></div>
        </div>

      </div>

      <div class="mt-4 flex justify-end">
        <button type="button" id="btnRegresar"
          class="px-6 py-2 rounded flex items-center gap-2 transition-colors"
          style="background: var(--primary-deep); color: white;">
          <i data-lucide="arrow-left" class="w-4 h-4"></i>
          Regresar
        </button>
      </div>
    </div>

    <!-- TÍTULO -->
    <div id="tituloNota" class="rounded-lg p-3 mb-6 text-center">
      <h2 class="uppercase tracking-wide">Historia Clínica</h2>
    </div>

    <!-- TEXTAREAS (solo tus campos) -->
    <script>
      const textAreas = [
        { id: "notaMedica", label: "Nota Médica" },
        { id: "exploracionFisica", label: "Exploración Física" },
        { id: "diagnostico", label: "Diagnóstico" },
        { id: "plan", label: "Plan / Tratamiento" },
        { id: "analisis", label: "Análisis" },
        { id: "pronostico", label: "Pronóstico" }
      ];
    </script>

    <div id="contenedorTextAreas"></div>

    <!-- BOTONES -->
    <div class="flex justify-end gap-4 mb-6">
      <button type="button" id="btnCancelar"
        class="px-8 py-3 border-2 rounded transition-colors"
        style="background: white; border-color: var(--neutral-gray); color: var(--neutral-text);">
        Cancelar
      </button>

      <button type="submit" id="btnGuardar"
        class="px-8 py-3 rounded transition-colors"
        style="background: var(--primary); color: white;">
        Guardar
      </button>
    </div>

  </form>
</div>

<script>
  lucide.createIcons();

  function actualizarFechaHora() {
    const now = new Date();
    const fecha = now.toLocaleDateString("es-MX", { day:"2-digit", month:"2-digit", year:"numeric" });
    const hora  = now.toLocaleTimeString("es-MX", { hour:"2-digit", minute:"2-digit", second:"2-digit", hour12:false });
    document.getElementById("fecha").textContent = fecha;
    document.getElementById("hora").textContent  = hora;
  }
  actualizarFechaHora();
  setInterval(actualizarFechaHora, 1000);

  const cont = document.getElementById("contenedorTextAreas");
  textAreas.forEach(area => {
    cont.innerHTML += `
      <div class="card p-4 mb-6">
        <h3 class="mb-4 pb-2 border-b-2 border-[var(--neutral-gray)] uppercase">${area.label}</h3>
        <textarea name="${area.id}" id="${area.id}" rows="5"
          class="w-full px-3 py-2 border border-[var(--neutral-gray)] rounded text-[var(--neutral-text)] resize-none placeholder-[#999]"></textarea>
      </div>`;
  });

  btnRegresar.onclick = () => {
    if (confirm("¿Deseas regresar? Los cambios no guardados se perderán.")) window.history.back();
  };

  btnCancelar.onclick = () => {
    if (confirm("¿Deseas cancelar? Se perderán todos los datos.")) {
      document.querySelectorAll("textarea").forEach(el => el.value = "");
    }
  };
</script>

</body>
</html>
