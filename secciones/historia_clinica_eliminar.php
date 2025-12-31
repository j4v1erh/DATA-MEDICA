<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';

$paciente_id = isset($_GET['pac']) ? (int)$_GET['pac'] : 0;
$ids_param   = isset($_GET['ids']) ? $_GET['ids'] : '';

if ($paciente_id <= 0 || $ids_param === '') {
    header('Location: ../panel_paciente.php?id=' . $paciente_id . '&sec=historia');
    exit;
}

$ids_raw = explode(',', $ids_param);
$ids = array();
foreach ($ids_raw as $v) {
    $v = (int)$v;
    if ($v > 0) {
        $ids[] = $v;
    }
}

if (empty($ids)) {
    header('Location: ../panel_paciente.php?id=' . $paciente_id . '&sec=historia');
    exit;
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$sql = "DELETE FROM historia_clinica WHERE paciente_id = ? AND id IN ($placeholders)";

$stmt = $pdo->prepare($sql);
array_unshift($ids, $paciente_id);
$stmt->execute($ids);

header('Location: ../panel_paciente.php?id=' . $paciente_id . '&sec=historia');
exit;

