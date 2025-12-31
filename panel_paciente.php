<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Obtener datos del paciente
$stmt = $pdo->prepare("SELECT * FROM pacientes WHERE id = :id");
$stmt->execute([':id' => $id]);
$pac = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pac) {
    die("Paciente no encontrado.");
}

// Calcular edad
$edad = "No registrada";
if (!empty($pac['fecha_nacimiento'])) {
    $nac = new DateTime($pac['fecha_nacimiento']);
    $hoy = new DateTime();
    $edad = $hoy->diff($nac)->y . " años";
}

// Determinar sección actual
$sec = isset($_GET['sec']) ? $_GET['sec'] : '';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Panel del Paciente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background: #f5f7fa; }
        .side-menu {
            background: #ffffff;
            border-right: 1px solid #ddd;
            height: 100vh;
            position: fixed;
            width: 270px;
            padding-top: 20px;
        }
        .side-menu a {
            display: block;
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
        }
        .side-menu a:hover, .active-menu {
            background: #e9ecef;
        }
        .content-area {
            margin-left: 290px;
            padding: 20px;
        }
        .header-box {
            background: #e9f1ff;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 30px;
            position: relative;
        }
        .header-box img {
            width: 140px;
            height: 140px;
            border-radius: 100px;
            background: #ddd;
            object-fit: cover;
        }
        .edit-buttons {
            position: absolute;
            top: 20px;
            right: 20px;
        }
    </style>
</head>
<body>

<!-- MENÚ LATERAL -->
<div class="side-menu">

    <h4 class="text-center fw-bold mb-4">MENÚ</h4>

    <a href="?id=<?= $id ?>&sec=hoja" class="<?= $sec=='hoja'?'active-menu':'' ?>">Hoja Frontal</a>
    <a href="?id=<?= $id ?>&sec=historia" class="<?= $sec=='historia'?'active-menu':'' ?>">Historia clínica</a>
    <a href="?id=<?= $id ?>&sec=nota" class="<?= $sec=='nota'?'active-menu':'' ?>">Nota Médica</a>
    <a href="?id=<?= $id ?>&sec=estudios" class="<?= $sec=='estudios'?'active-menu':'' ?>">Estudios y Laboratorios</a>
    <a href="?id=<?= $id ?>&sec=recetas" class="<?= $sec=='recetas'?'active-menu':'' ?>">Recetas</a>
    <a href="?id=<?= $id ?>&sec=enfermeria" class="<?= $sec=='enfermeria'?'active-menu':'' ?>">Indicación Enfermería</a>
    <a href="?id=<?= $id ?>&sec=consentimiento" class="<?= $sec=='consentimiento'?'active-menu':'' ?>">Consentimiento informado</a>
    <a href="?id=<?= $id ?>&sec=banco" class="<?= $sec=='banco'?'active-menu':'' ?>">Banco de sangre</a>
    <a href="?id=<?= $id ?>&sec=certificados" class="<?= $sec=='certificados'?'active-menu':'' ?>">Certificados</a>

    <a href="index.php" class="text-danger mt-5">&larr; Volver</a>
</div>

<!-- ÁREA PRINCIPAL -->
<div class="content-area">

    <!-- ENCABEZADO DEL PACIENTE -->
    <div class="header-box shadow-sm">

        <img src="https://cdn-icons-png.flaticon.com/512/847/847969.png" alt="Foto paciente">

        <div style="flex-grow:1;">
            <h2 class="fw-bold mb-3"><?= strtoupper($pac['nombre'] . " " . $pac['apellido']) ?></h2>

            <div class="row">
                <div class="col-md-4">
                    <p><strong>Edad:</strong> <?= $edad ?></p>
                    <p><strong>Género:</strong> <?= $pac['genero'] ?: 'No registrado' ?></p>
                    <p><strong>CURP:</strong> <?= $pac['curp'] ?: 'No registrada' ?></p>
                </div>

                <div class="col-md-4">
                    <p><strong>Estado civil:</strong> <?= $pac['estado_civil'] ?: 'No registrado' ?></p>
                    <p><strong>Teléfono:</strong> <?= $pac['telefono'] ?: 'No registrado' ?></p>
                    <p><strong>Email:</strong> <?= $pac['email'] ?: 'No registrado' ?></p>
                </div>

                <div class="col-md-4">
                    <p><strong>Dirección:</strong><br>
                        <?= $pac['calle'] ?> <?= $pac['no_ext'] ?> <?= $pac['no_int'] ?><br>
                        Col. <?= $pac['colonia'] ?><br>
                        CP <?= $pac['cp'] ?>, <?= $pac['municipio'] ?>, <?= $pac['estado'] ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- BOTONES EDITAR / ELIMINAR PACIENTE -->
        <div class="edit-buttons">
            <a href="editar_paciente.php?id=<?= $id ?>" class="btn btn-warning btn-sm">Editar</a>
            <a href="eliminar_paciente.php?id=<?= $id ?>" class="btn btn-danger btn-sm"
               onclick="return confirm('¿Seguro que deseas eliminar este paciente?')">Eliminar</a>
        </div>

    </div>

    <!-- CARGA DE SECCIONES (cada sección maneja sus propios botones + JS) -->
    <?php
        if ($sec === 'historia') {
            include __DIR__ . '/secciones/historia_clinica/historia_clinica.php';

        } elseif ($sec === 'hoja') {
            include __DIR__ . '/secciones/hoja_frontal/hoja_frontal.php';

        } elseif ($sec === 'nota') {
            include __DIR__ . '/secciones/nota_medica/nota_medica.php';

        } elseif ($sec === 'estudios') {
            include __DIR__ . '/secciones/estudios_laboratorios/estudios_laboratorios.php';

        } elseif ($sec === 'recetas') {
            include __DIR__ . '/secciones/recetas/recetas.php';

        } elseif ($sec === 'enfermeria') {
            include __DIR__ . '/secciones/indicacion_enfermeria/indicacion_enfermeria.php';

        } elseif ($sec === 'consentimiento') {
            include __DIR__ . '/secciones/consentimiento_informado/consentimiento_informado.php';

        } elseif ($sec === 'banco') {
            include __DIR__ . '/secciones/banco_sangre/banco_sangre.php';

        } elseif ($sec === 'certificados') {
            include __DIR__ . '/secciones/certificados/certificados.php';

        } else {
            echo "<p class='text-muted'>Selecciona una sección del menú para continuar.</p>";
        }
    ?>

</div>

</body>
</html>
