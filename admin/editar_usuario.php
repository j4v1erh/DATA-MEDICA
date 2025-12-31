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

// Listas
$SERVICIOS = [
    "Medicina General",
    "Enfermería",
    "Laboratorio",
    "Recepción",
    "Administración",
    "Traumatología y Ortopedia",
    "Ginecología y Obstetricia",
    "Medicina Interna",
    "Cirugía General",
    "Pediatría",
    "Urología",
    "Almacén",
    "Sucursal"
];

$ESTADOS_MX = [
    "Aguascalientes","Baja California","Baja California Sur","Campeche","Chiapas","Chihuahua",
    "Ciudad de México","Coahuila","Colima","Durango","Estado de México","Guanajuato","Guerrero",
    "Hidalgo","Jalisco","Michoacán","Morelos","Nayarit","Nuevo León","Oaxaca","Puebla","Querétaro",
    "Quintana Roo","San Luis Potosí","Sinaloa","Sonora","Tabasco","Tamaulipas","Tlaxcala",
    "Veracruz","Yucatán","Zacatecas"
];

// Obtener usuario
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    die("Usuario no encontrado.");
}

$mensaje = "";

// Helper
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nuevo_usuario     = trim($_POST['usuario'] ?? '');
    $nombre_completo   = trim($_POST['nombre_completo'] ?? '');
    $rfc               = trim($_POST['rfc'] ?? '');
    $cedula            = trim($_POST['cedula'] ?? '');
    $universidad       = trim($_POST['universidad'] ?? '');
    $servicio          = trim($_POST['servicio'] ?? '');
    $fecha_nacimiento  = $_POST['fecha_nacimiento'] ?? null;
    $lugar_nacimiento  = trim($_POST['lugar_nacimiento'] ?? '');

    $password          = (string)($_POST['password'] ?? '');

    // Validaciones
    if ($nuevo_usuario === "" || $nombre_completo === "" || $servicio === "") {
        $mensaje = "Campos obligatorios: Nombre completo, Servicio y Usuario.";
    } elseif (!in_array($servicio, $SERVICIOS, true)) {
        $mensaje = "Servicio inválido.";
    } elseif ($lugar_nacimiento !== "" && !in_array($lugar_nacimiento, $ESTADOS_MX, true)) {
        $mensaje = "Lugar de nacimiento inválido.";
    } else {

        // Evitar duplicado de usuario (excepto el mismo id)
        $ver = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = :u AND id <> :id LIMIT 1");
        $ver->execute([':u' => $nuevo_usuario, ':id' => $id]);
        if ($ver->fetch()) {
            $mensaje = "Ese usuario ya existe. Intenta con otro.";
        } else {

            $setPass = "";
            $params = [
                ':usuario'          => $nuevo_usuario,
                ':nombre_completo'  => $nombre_completo,
                ':rfc'              => ($rfc !== "" ? $rfc : null),
                ':cedula'           => ($cedula !== "" ? $cedula : null),
                ':universidad'      => ($universidad !== "" ? $universidad : null),
                ':servicio'         => $servicio,
                ':fecha_nacimiento' => ($fecha_nacimiento !== "" ? $fecha_nacimiento : null),
                ':lugar_nacimiento' => ($lugar_nacimiento !== "" ? $lugar_nacimiento : null),
                ':id'               => $id
            ];

            // Si se quiere cambiar contraseña
            if ($password !== "") {
                $pass_hash = password_hash($password, PASSWORD_BCRYPT);
                $setPass = ", pass_hash = :pass_hash";
                $params[':pass_hash'] = $pass_hash;
            }

            $sql = "
                UPDATE usuarios
                SET usuario = :usuario,
                    nombre_completo = :nombre_completo,
                    rfc = :rfc,
                    cedula = :cedula,
                    universidad = :universidad,
                    servicio = :servicio,
                    fecha_nacimiento = :fecha_nacimiento,
                    lugar_nacimiento = :lugar_nacimiento
                    $setPass
                WHERE id = :id
            ";

            $stmt2 = $pdo->prepare($sql);
            $stmt2->execute($params);

            header("Location: usuarios.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Usuario</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-4" style="max-width: 820px;">

    <h2 class="fw-bold mb-4">Editar Usuario</h2>

    <?php if ($mensaje): ?>
        <div class="alert alert-danger"><?= h($mensaje) ?></div>
    <?php endif; ?>

    <form method="post" class="card p-4 shadow-sm">

        <div class="row g-3">
            <div class="col-md-12">
                <label class="form-label">Nombre completo *</label>
                <input type="text" name="nombre_completo" class="form-control"
                       value="<?= h($usuario['nombre_completo'] ?? '') ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">RFC</label>
                <input type="text" name="rfc" class="form-control"
                       value="<?= h($usuario['rfc'] ?? '') ?>">
            </div>

            <div class="col-md-6">
                <label class="form-label">Cédula</label>
                <input type="text" name="cedula" class="form-control"
                       value="<?= h($usuario['cedula'] ?? '') ?>">
            </div>

            <div class="col-md-12">
                <label class="form-label">Universidad</label>
                <input type="text" name="universidad" class="form-control"
                       value="<?= h($usuario['universidad'] ?? '') ?>">
            </div>

            <div class="col-md-6">
                <label class="form-label">Servicio *</label>
                <select name="servicio" class="form-select" required>
                    <option value="">Seleccione...</option>
                    <?php foreach ($SERVICIOS as $s): ?>
                        <option value="<?= h($s) ?>" <?= (($usuario['servicio'] ?? '') === $s) ? 'selected' : '' ?>>
                            <?= h($s) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Fecha de nacimiento</label>
                <input type="date" name="fecha_nacimiento" class="form-control"
                       value="<?= h($usuario['fecha_nacimiento'] ?? '') ?>">
            </div>

            <div class="col-md-12">
                <label class="form-label">Lugar de nacimiento</label>
                <select name="lugar_nacimiento" class="form-select">
                    <option value="">Seleccione un estado...</option>
                    <?php foreach ($ESTADOS_MX as $e): ?>
                        <option value="<?= h($e) ?>" <?= (($usuario['lugar_nacimiento'] ?? '') === $e) ? 'selected' : '' ?>>
                            <?= h($e) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Usuario *</label>
                <input type="text" name="usuario" class="form-control"
                       value="<?= h($usuario['usuario'] ?? '') ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Cambiar contraseña (opcional)</label>
                <input type="password" name="password" class="form-control"
                       placeholder="Dejar en blanco para no cambiar">
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary">Guardar cambios</button>
            <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>

</div>

</body>
</html>
