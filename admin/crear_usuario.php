<?php
require_once __DIR__ . '/../session_path.php';
session_start();
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';

// SOLO "Administración" puede entrar
if (!isset($_SESSION['servicio']) || $_SESSION['servicio'] !== 'Administración') {
    die("Acceso denegado.");
}

$mensaje = "";

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuario          = trim($_POST['usuario'] ?? '');
    $password         = (string)($_POST['password'] ?? '');

    $nombre_completo  = trim($_POST['nombre_completo'] ?? '');
    $rfc              = trim($_POST['rfc'] ?? '');
    $cedula           = trim($_POST['cedula'] ?? '');
    $universidad      = trim($_POST['universidad'] ?? '');
    $servicio         = trim($_POST['servicio'] ?? '');
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null; // yyyy-mm-dd
    $lugar_nacimiento = trim($_POST['lugar_nacimiento'] ?? '');

    // Validaciones mínimas
    if ($usuario === "" || $password === "" || $nombre_completo === "" || $servicio === "") {
        $mensaje = "Campos obligatorios: Nombre completo, Servicio, Usuario y Contraseña.";
    } elseif (!in_array($servicio, $SERVICIOS, true)) {
        $mensaje = "Servicio inválido.";
    } elseif ($lugar_nacimiento !== "" && !in_array($lugar_nacimiento, $ESTADOS_MX, true)) {
        $mensaje = "Lugar de nacimiento inválido.";
    } else {

        // Verificar usuario duplicado
        $ver = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = :u LIMIT 1");
        $ver->execute([':u' => $usuario]);
        if ($ver->fetch()) {
            $mensaje = "Ese usuario ya existe. Intenta con otro.";
        } else {

            // Hash seguro
            $hash = password_hash($password, PASSWORD_BCRYPT);

            // Insertar
            $stmt = $pdo->prepare("
                INSERT INTO usuarios (
                    usuario, pass_hash,
                    nombre_completo, rfc, cedula, universidad,
                    servicio, fecha_nacimiento, lugar_nacimiento,
                    creado_en
                )
                VALUES (
                    :usuario, :pass_hash,
                    :nombre_completo, :rfc, :cedula, :universidad,
                    :servicio, :fecha_nacimiento, :lugar_nacimiento,
                    NOW()
                )
            ");

            $ok = $stmt->execute([
                ':usuario'          => $usuario,
                ':pass_hash'        => $hash,
                ':nombre_completo'  => $nombre_completo,
                ':rfc'              => ($rfc !== "" ? $rfc : null),
                ':cedula'           => ($cedula !== "" ? $cedula : null),
                ':universidad'      => ($universidad !== "" ? $universidad : null),
                ':servicio'         => $servicio,
                ':fecha_nacimiento' => ($fecha_nacimiento !== "" ? $fecha_nacimiento : null),
                ':lugar_nacimiento' => ($lugar_nacimiento !== "" ? $lugar_nacimiento : null),
            ]);

            if ($ok) {
                header("Location: usuarios.php?ok=1");
                exit;
            } else {
                $mensaje = "Error al guardar en la base de datos.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear usuario</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4 bg-light">

<div class="container" style="max-width: 720px;">
    <h2 class="fw-bold">Crear Usuario</h2>
    <hr>

    <?php if ($mensaje): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="POST" class="card p-4 shadow-sm bg-white mt-3">

        <div class="row g-3">
            <div class="col-md-12">
                <label class="form-label">Nombre completo *</label>
                <input type="text" name="nombre_completo" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">RFC</label>
                <input type="text" name="rfc" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label">Cédula</label>
                <input type="text" name="cedula" class="form-control">
            </div>

            <div class="col-md-12">
                <label class="form-label">Universidad</label>
                <input type="text" name="universidad" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label">Servicio *</label>
                <select name="servicio" class="form-select" required>
                    <option value="">Seleccione...</option>
                    <?php foreach ($SERVICIOS as $s): ?>
                        <option value="<?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Fecha de nacimiento</label>
                <input type="date" name="fecha_nacimiento" class="form-control">
            </div>

            <div class="col-md-12">
                <label class="form-label">Lugar de nacimiento</label>
                <select name="lugar_nacimiento" class="form-select">
                    <option value="">Seleccione un estado...</option>
                    <?php foreach ($ESTADOS_MX as $e): ?>
                        <option value="<?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Usuario *</label>
                <input type="text" name="usuario" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Contraseña *</label>
                <input type="password" name="password" class="form-control" required>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary">Crear Usuario</button>
            <a href="usuarios.php" class="btn btn-secondary">Volver</a>
        </div>

    </form>
</div>

</body>
</html>
