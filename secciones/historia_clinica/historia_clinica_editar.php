<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../db.php';
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$id  = isset($_GET['id'])  ? (int)$_GET['id'] : 0;
$pac = isset($_GET['pac']) ? (int)$_GET['pac'] : 0;
if ($id<=0 || $pac<=0) die("Parámetros inválidos.");

$stmt = $pdo->prepare("SELECT * FROM historia_clinica WHERE id=:id AND paciente_id=:pac LIMIT 1");
$stmt->execute([':id'=>$id, ':pac'=>$pac]);
$r = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$r) die("Registro no encontrado.");

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $notaMedica        = trim($_POST['notaMedica'] ?? '');
  $exploracionFisica = trim($_POST['exploracionFisica'] ?? '');
  $diagnostico       = trim($_POST['diagnostico'] ?? '');
  $plan              = trim($_POST['plan'] ?? '');
  $analisis          = trim($_POST['analisis'] ?? '');
  $pronostico        = trim($_POST['pronostico'] ?? '');

  if ($notaMedica === "" && $diagnostico === "") {
    $mensaje = "Captura al menos Nota Médica o Diagnóstico.";
  } else {
    $descripcion = ($notaMedica !== "") ? $notaMedica : $diagnostico;

    $notas = trim(
      ($analisis !== "" ? "ANÁLISIS:\n$analisis\n\n" : "") .
      ($pronostico !== "" ? "PRONÓSTICO:\n$pronostico" : "")
    );
    if ($notas === "") $notas = null;

    $up = $pdo->prepare("
      UPDATE historia_clinica
      SET motivo_consulta   = :motivo,
          exploracion_fisica= :expl,
          diagnostico_text  = :diag,
          tratamientos      = :trat,
          descripcion       = :desc,
          notas             = :notas
      WHERE id = :id AND paciente_id = :pac
    ");
    $up->execute([
      ':motivo' => ($notaMedica !== "" ? $notaMedica : null),
      ':expl'   => ($exploracionFisica !== "" ? $exploracionFisica : null),
      ':diag'   => ($diagnostico !== "" ? $diagnostico : null),
      ':trat'   => ($plan !== "" ? $plan : null),
      ':desc'   => $descripcion,
      ':notas'  => $notas,
      ':id'     => $id,
      ':pac'    => $pac
    ]);

    header("Location: ../../panel_paciente.php?id=".$pac."&sec=historia");
    exit;
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Historia Clínica - Editar</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    :root{--primary:#079FCF;--primary-dark:#0277A1;--primary-deep:#014F75;--neutral-bg:#F4F8FA;--neutral-gray:#DDE5EA;--neutral-text:#2F3A45;}
    body{background:var(--neutral-bg);}
    .card{background:white;border:2px solid var(--neutral-gray);border-radius:8px;box-shadow:0 2px 5px rgba(0,0,0,.05);}
    h3{color:var(--primary-deep);}
    #tituloNota{background:var(--primary-dark);color:white;}
  </style>
</head>
<body>
<div class="max-w-[1200px] mx-auto p-6">

  <?php if ($mensaje): ?>
    <div class="card p-4 mb-6" style="border-color:#f5c2c7;">
      <div style="color:#b02a37;font-weight:600;"><?= h($mensaje) ?></div>
    </div>
  <?php endif; ?>

  <form method="POST">
    <div class="card p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-start">
        <div>
          <label class="block text-[var(--neutral-text)] mb-1">Médico que Elabora</label>
          <input class="w-full h-9 px-3 border border-[var(--neutral-gray)] rounded text-[var(--neutral-text)]"
                 value="<?= h($r['medico_elabora']) ?>" readonly>
        </div>
        <div>
          <label class="block text-[var(--neutral-text)] mb-1">Cédula Profesional</label>
          <input class="w-full h-9 px-3 border border-[var(--neutral-gray)] rounded text-[var(--neutral-text)]"
                 value="<?= h($r['medico_cedula']) ?>" readonly>
        </div>
        <div>
          <label class="block text-[var(--neutral-text)] mb-1">Servicio Médico</label>
          <input class="w-full h-9 px-3 border border-[var(--neutral-gray)] rounded text-[var(--neutral-text)]"
                 value="<?= h($r['medico_servicio']) ?>" readonly>
        </div>
        <div>
          <label class="block text-[var(--neutral-text)] mb-1">Fecha</label>
          <div class="w-full h-9 px-3 border border-[var(--neutral-gray)] rounded bg-[var(--neutral-gray)] flex items-center text-[var(--neutral-text)]">
            <?= h($r['fecha']) ?>
          </div>
        </div>
        <div class="flex items-end">
          <a class="px-6 py-2 rounded flex items-center gap-2 transition-colors"
             style="background: var(--primary-deep); color: white;"
             href="../../panel_paciente.php?id=<?= (int)$pac ?>&sec=historia">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Regresar
          </a>
        </div>
      </div>
    </div>

    <div id="tituloNota" class="rounded-lg p-3 mb-6 text-center">
      <h2 class="uppercase tracking-wide">Editar Historia Clínica</h2>
    </div>

    <div id="contenedorTextAreas"></div>

    <div class="flex justify-end gap-4 mb-6">
      <a href="../../panel_paciente.php?id=<?= (int)$pac ?>&sec=historia"
         class="px-8 py-3 border-2 rounded"
         style="background:white;border-color:var(--neutral-gray);color:var(--neutral-text);text-decoration:none;">
        Cancelar
      </a>
      <button type="submit"
        class="px-8 py-3 rounded transition-colors"
        style="background: var(--primary); color: white;">
        Guardar cambios
      </button>
    </div>
  </form>
</div>

<script>
lucide.createIcons();

const initial = {
  notaMedica: <?= json_encode((string)($r['descripcion'] ?? '')) ?>,
  exploracionFisica: <?= json_encode((string)($r['exploracion_fisica'] ?? '')) ?>,
  diagnostico: <?= json_encode((string)($r['diagnostico_text'] ?? '')) ?>,
  plan: <?= json_encode((string)($r['tratamientos'] ?? '')) ?>,
  analisis: <?= json_encode("") ?>,
  pronostico: <?= json_encode("") ?>
};

const textAreas = [
  { id: "notaMedica", label: "Nota Médica" },
  { id: "exploracionFisica", label: "Exploración Física" },
  { id: "diagnostico", label: "Diagnóstico" },
  { id: "plan", label: "Plan / Tratamiento" },
  { id: "analisis", label: "Análisis" },
  { id: "pronostico", label: "Pronóstico" }
];

const cont = document.getElementById("contenedorTextAreas");
textAreas.forEach(area => {
  cont.innerHTML += `
    <div class="card p-4 mb-6">
      <h3 class="mb-4 pb-2 border-b-2 border-[var(--neutral-gray)] uppercase">${area.label}</h3>
      <textarea name="${area.id}" id="${area.id}" rows="5"
        class="w-full px-3 py-2 border border-[var(--neutral-gray)] rounded text-[var(--neutral-text)] resize-none placeholder-[#999]"></textarea>
    </div>`;
  document.getElementById(area.id).value = initial[area.id] || "";
});
</script>
</body>
</html>
