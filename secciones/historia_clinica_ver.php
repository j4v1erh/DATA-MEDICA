<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';

$registro_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$paciente_id = isset($_GET['pac']) ? (int)$_GET['pac'] : 0;

$stmt = $pdo->prepare("SELECT * FROM historia_clinica WHERE id = :id");
$stmt->execute(array(':id' => $registro_id));
$h = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$h) {
    die('Registro de historia clinica no encontrado.');
}

$fecha_str = date('d/m/Y H:i', strtotime($h['fecha']));
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ver Historia Clinica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f6f6f6; }
        .card-details { background: #ffffff; }
        .label { font-weight: 600; color: #0d6efd; }
        .value { white-space: pre-wrap; }
    </style>
</head>
<body>
<div class="container py-4">
    <a href="../panel_paciente.php?id=<?= $paciente_id ?>&sec=historia" class="btn btn-link mb-3">
        &larr; Volver a historia clinica
    </a>

    <h2 class="fw-bold mb-3">Detalle de Historia Clinica</h2>
    <p class="text-muted mb-4">Registro del <?= $fecha_str ?></p>

    <div class="card card-details shadow-sm">
        <div class="card-body">
            <p><span class="label">Motivo:</span> <span class="value"><?= nl2br(htmlspecialchars($h['motivo_consulta'], ENT_QUOTES, 'UTF-8')) ?></span></p>
            <p><span class="label">Padecimiento actual:</span> <span class="value"><?= nl2br(htmlspecialchars($h['padecimiento_actual'], ENT_QUOTES, 'UTF-8')) ?></span></p>
            <hr>
            <p><span class="label">Antecedentes no patologicos:</span><br><span class="value"><?= nl2br(htmlspecialchars($h['antecedentes_no_patologicos'], ENT_QUOTES, 'UTF-8')) ?></span></p>
            <p><span class="label">Antecedentes patologicos:</span><br><span class="value"><?= nl2br(htmlspecialchars($h['antecedentes_patologicos'], ENT_QUOTES, 'UTF-8')) ?></span></p>
            <p><span class="label">Antecedentes heredofamiliares:</span><br><span class="value"><?= nl2br(htmlspecialchars($h['antecedentes_heredofamiliares'], ENT_QUOTES, 'UTF-8')) ?></span></p>
            <p><span class="label">Antecedentes ginecoobstetricos:</span><br><span class="value"><?= nl2br(htmlspecialchars($h['antecedentes_ginecoobstetricos'], ENT_QUOTES, 'UTF-8')) ?></span></p>
            <hr>
            <p><span class="label">Exploracion fisica:</span><br><span class="value"><?= nl2br(htmlspecialchars($h['exploracion_fisica'], ENT_QUOTES, 'UTF-8')) ?></span></p>
            <p><span class="label">Diagnosticos:</span><br><span class="value"><?= nl2br(htmlspecialchars($h['diagnosticos'], ENT_QUOTES, 'UTF-8')) ?></span></p>
            <p><span class="label">Pronostico:</span><br><span class="value"><?= nl2br(htmlspecialchars($h['tratamientos'], ENT_QUOTES, 'UTF-8')) ?></span></p>
        </div>
    </div>
</div>
</body>
</html>

