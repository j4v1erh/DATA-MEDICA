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
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Historia Clínica - Ver</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4" style="max-width:900px;">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold mb-0">Historia Clínica (Ver)</h3>
    <a class="btn btn-secondary" href="../../panel_paciente.php?id=<?= (int)$pac ?>&sec=historia">Volver</a>
  </div>

  <div class="card p-4 shadow-sm">
    <div class="row g-3">
      <div class="col-md-4"><b>Fecha:</b> <?= h($r['fecha']) ?></div>
      <div class="col-md-8"><b>Médico:</b> <?= h($r['medico_elabora']) ?> (<?= h($r['medico_cedula'] ?: 'Sin cédula') ?>) — <?= h($r['medico_servicio'] ?: 'Sin servicio') ?></div>

      <hr class="my-2">

      <div class="col-12">
        <h6 class="fw-bold">Nota Médica</h6>
        <pre class="mb-0" style="white-space:pre-wrap;"><?= h($r['descripcion']) ?></pre>
      </div>

      <div class="col-12">
        <h6 class="fw-bold">Exploración Física</h6>
        <pre class="mb-0" style="white-space:pre-wrap;"><?= h($r['exploracion_fisica']) ?></pre>
      </div>

      <div class="col-12">
        <h6 class="fw-bold">Diagnóstico</h6>
        <pre class="mb-0" style="white-space:pre-wrap;"><?= h($r['diagnostico_text']) ?></pre>
      </div>

      <div class="col-12">
        <h6 class="fw-bold">Plan / Tratamiento</h6>
        <pre class="mb-0" style="white-space:pre-wrap;"><?= h($r['tratamientos']) ?></pre>
      </div>

      <div class="col-12">
        <h6 class="fw-bold">Notas</h6>
        <pre class="mb-0" style="white-space:pre-wrap;"><?= h($r['notas']) ?></pre>
      </div>
    </div>
  </div>

</div>
</body>
</html>
