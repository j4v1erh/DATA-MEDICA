<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../db.php';

$serv = (string)($_SESSION['servicio'] ?? '');
if ($serv !== 'Administración') {
  http_response_code(403);
  die("Acceso denegado: solo Administración puede eliminar registros.");
}

$pac = isset($_GET['pac']) ? (int)$_GET['pac'] : 0;
$ids = isset($_GET['ids']) ? trim($_GET['ids']) : '';

if ($pac <= 0 || $ids === '') die("Parámetros inválidos.");

$lista = array_filter(array_map('intval', explode(',', $ids)));
$lista = array_values(array_unique($lista));
if (empty($lista)) die("No hay IDs válidos.");

$placeholders = implode(',', array_fill(0, count($lista), '?'));

$stmt = $pdo->prepare("DELETE FROM historia_clinica WHERE paciente_id = ? AND id IN ($placeholders)");
$params = array_merge([$pac], $lista);
$stmt->execute($params);

header("Location: ../../panel_paciente.php?id=".$pac."&sec=historia");
exit;
