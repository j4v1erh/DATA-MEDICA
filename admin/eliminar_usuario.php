<?php
require_once __DIR__ . '/../session_path.php';
session_start();
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';

// SOLO "Administración"
if (!isset($_SESSION['servicio']) || $_SESSION['servicio'] !== 'Administración') {
    header("Location: ../index.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("ID inválido.");
}

// Prevenir eliminarse a sí mismo
if (isset($_SESSION['usuario_id']) && $id === (int)$_SESSION['usuario_id']) {
    die("⚠ No puedes eliminar tu propia cuenta.");
}

// Confirmación vía GET (si no confirmas, muestra una pantalla)
if (!isset($_GET['confirm']) || $_GET['confirm'] !== '1') {
    // Obtener usuario para mostrarlo
    $stmt = $pdo->prepare("SELECT id, usuario, nombre_completo, servicio FROM usuarios WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$u) die("Usuario no encontrado.");

    ?>
    <!doctype html>
    <html lang="es">
    <head>
      <meta charset="utf-8">
      <title>Eliminar usuario</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
      <div class="container py-5" style="max-width:700px;">
        <div class="card p-4 shadow-sm">
          <h4 class="text-danger fw-bold">Confirmar eliminación</h4>
          <p class="mb-2">Vas a eliminar este usuario:</p>
          <ul>
            <li><b>ID:</b> <?= (int)$u['id'] ?></li>
            <li><b>Nombre:</b> <?= htmlspecialchars($u['nombre_completo'] ?? '', ENT_QUOTES, 'UTF-8') ?></li>
            <li><b>Usuario:</b> <?= htmlspecialchars($u['usuario'] ?? '', ENT_QUOTES, 'UTF-8') ?></li>
            <li><b>Servicio:</b> <?= htmlspecialchars($u['servicio'] ?? '', ENT_QUOTES, 'UTF-8') ?></li>
          </ul>

          <div class="d-flex gap-2 mt-3">
            <a class="btn btn-danger"
               href="eliminar_usuario.php?id=<?= (int)$u['id'] ?>&confirm=1"
               onclick="return confirm('¿Seguro? Esta acción no se puede deshacer.')">
              Sí, eliminar
            </a>
            <a class="btn btn-secondary" href="usuarios.php">Cancelar</a>
          </div>
        </div>
      </div>
    </body>
    </html>
    <?php
    exit;
}

// Eliminar usuario
$stmtDel = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
$stmtDel->execute([':id' => $id]);

header("Location: usuarios.php");
exit;
