<?php
require_once __DIR__ . '/db.php';

$paciente_id = $_GET['id'] ?? 0;
$descripcion = trim($_POST['descripcion'] ?? '');
$notas = trim($_POST['notas'] ?? '');

if ($descripcion !== "") {
    $stmt = $pdo->prepare("
        INSERT INTO historia_clinica (paciente_id, descripcion, notas)
        VALUES (:paciente_id, :descripcion, :notas)
    ");

    $stmt->execute([
        ':paciente_id' => $paciente_id,
        ':descripcion' => $descripcion,
        ':notas'       => $notas
    ]);
}

header("Location: panel_paciente.php?id=$paciente_id&sec=historia");
exit;
