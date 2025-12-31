<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';

$registro_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$paciente_id = isset($_GET['pac']) ? (int)$_GET['pac'] : 0;

if ($registro_id <= 0) {
    die('Registro no especificado.');
}

// Historia clínica
$stmt = $pdo->prepare("SELECT * FROM historia_clinica WHERE id = :id");
$stmt->execute(array(':id' => $registro_id));
$h = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$h) {
    die('Registro de historia clinica no encontrado.');
}

// Datos del paciente
$pac = null;
if ($paciente_id > 0) {
    $s2 = $pdo->prepare("SELECT * FROM pacientes WHERE id = :id");
    $s2->execute(array(':id' => $paciente_id));
    $pac = $s2->fetch(PDO::FETCH_ASSOC);
}

function parseFieldGroup($text)
{
    $result = array();
    $parts = explode('|', (string)$text);
    foreach ($parts as $part) {
        $part = trim($part);
        if ($part === '') {
            continue;
        }
        $pos = strpos($part, ':');
        if ($pos === false) {
            continue;
        }
        $label = trim(substr($part, 0, $pos));
        $value = trim(substr($part, $pos + 1));
        $result[$label] = $value;
    }
    return $result;
}

$apnp   = parseFieldGroup($h['antecedentes_no_patologicos']);
$gineco = parseFieldGroup($h['antecedentes_ginecoobstetricos']);
$expl   = parseFieldGroup($h['exploracion_fisica']);
$diag   = parseFieldGroup($h['diagnosticos']);

$fecha_str = date('d/m/Y H:i', strtotime($h['fecha']));

$nombre_paciente = $pac ? trim($pac['nombre'] . ' ' . $pac['apellido']) : '';
$edad_paciente   = '';
$sexo_paciente   = $pac ? $pac['genero'] : '';
$fecha_nac       = $pac ? $pac['fecha_nacimiento'] : '';

if ($pac && !empty($pac['fecha_nacimiento'])) {
    $nac = new DateTime($pac['fecha_nacimiento']);
    $hoy = new DateTime();
    $edad_paciente = $hoy->diff($nac)->y . ' años';
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Historia Clinica Medica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #dfe3eb;
            font-family: "Aptos", Arial, sans-serif;
            font-size: 8pt;
        }
        .page {
            background: #ffffff;
            margin: 20px auto;
            padding: 30px 40px;
            max-width: 900px;
            border: 1px solid #ccc;
        }
        .header-clinic {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .header-logo {
            width: 80px;
            text-align: left;
        }
        .header-logo img {
            max-height: 70px;
        }
        .header-text {
            flex-grow: 1;
            text-align: center;
            color: #003366;
        }
        .header-text .clinic-name {
            font-size: 12pt;
            font-weight: 700;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .header-text .clinic-address,
        .header-text .clinic-city {
            font-size: 8pt;
        }
        .header-text .clinic-separator {
            border-bottom: 1px dotted #003366;
            margin: 4px auto 0;
            width: 70%;
        }
        .historia-title {
            text-align: center;
            font-weight: 700;
            margin: 12px 0 8px;
        }
        .box {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 8pt;
            margin-bottom: 8px;
        }
        .box-title {
            font-weight: 600;
            margin-bottom: 4px;
        }
        .section-title {
            font-weight: 700;
            background: #f7f7f7;
            padding: 4px 6px;
            border-bottom: 1px solid #000;
            font-size: 8pt;
        }
        .highlight {
            background: #77C5EB;
            font-weight: 700;
        }
        .label-inline {
            font-weight: 700;
        }
        .value-block {
            white-space: pre-wrap;
            font-size: 8pt;
        }
        .explora-row label {
            font-weight: 700;
            font-size: 8pt;
        }
        .explora-row span {
            border-bottom: 1px solid #000;
            min-width: 40px;
            display: inline-block;
            padding: 0 4px;
            font-size: 8pt;
        }
        .page-break {
            page-break-before: always;
            break-before: page;
            margin-top: 20px;
        }
        @media print {
            body { background: #ffffff; }
            .no-print { display: none !important; }
            .page { margin: 0; border: none; }
        }
    </style>
</head>
<body onload="window.print()">
<div class="page">
    <div class="no-print mb-3 text-end">
        <a href="../panel_paciente.php?id=<?= $paciente_id ?>&sec=historia" class="btn btn-secondary btn-sm">
            &larr; Volver
        </a>
    </div>

    <div class="header-clinic">
        <div class="header-logo">
            <img src="../img/logo_clinica.png" alt="Clínica San Agustín">
        </div>
        <div class="header-text">
            <div class="clinic-name">CLÍNICA SAN AGUSTIN</div>
            <div class="clinic-address">Prolongación Ramón López Velarde N°30  Col. El Toloque.</div>
            <div class="clinic-city">Cárdenas Tabasco</div>
            <div class="clinic-separator"></div>
        </div>
    </div>
    <h2 class="historia-title">HISTORIA CLINICA MEDICA</h2>

    <div class="box">
        <div class="row">
            <div class="col-8">
                <div class="box-title">EXPEDIDA POR:</div>
                <div>CLÍNICA SAN AGUSTÍN COL. EL TOLOQUE. N.30</div>
                <div>SERVICIO: ___________________________________</div>
                <div>MEDICO: ____________________________________</div>
            </div>
            <div class="col-4">
                <div class="box-title text-center">FIRMA DEL MEDICO RESPONSABLE</div>
                <div style="height:55px;"></div>
                <div>Elaboró: ________________________</div>
            </div>
        </div>
    </div>

    <div class="box">
        <div class="box-title">PACIENTE:</div>
        <div class="mb-1">
            <span class="label-inline">NOMBRE:</span>
            <span><?= htmlspecialchars($nombre_paciente, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <div class="row">
            <div class="col-3">
                <span class="label-inline">EDAD:</span>
                <span><?= htmlspecialchars($edad_paciente, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="col-5">
                <span class="label-inline">FECHA DE NACIMIENTO:</span>
                <span><?= htmlspecialchars($fecha_nac, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="col-4">
                <span class="label-inline">SEXO:</span>
                <span><?= htmlspecialchars($sexo_paciente, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>
    </div>

    <div class="box">
        <div class="section-title">
            HISTORIA CLINICA MEDICA
        </div>

        <p class="mt-2 mb-1 highlight">ANTECEDENTES HEREDITARIOS Y FAMILIARES</p>
        <div class="value-block mb-2"><?= nl2br(htmlspecialchars($h['antecedentes_heredofamiliares'], ENT_QUOTES, 'UTF-8')) ?></div>

        <p class="mb-1 highlight">ANTECEDENTES PERSONALES NO PATOLOGICOS</p>
        <div class="value-block mb-2"><?= nl2br(htmlspecialchars($h['antecedentes_no_patologicos'], ENT_QUOTES, 'UTF-8')) ?></div>

        <p class="mb-1 highlight">ANTECEDENTES PERSONALES PATOLOGICOS</p>
        <div class="value-block mb-2"><?= nl2br(htmlspecialchars($h['antecedentes_patologicos'], ENT_QUOTES, 'UTF-8')) ?></div>

        <p class="mb-1 highlight">ANTECEDENTES GINECOBSTETRICOS</p>
        <div class="value-block mb-2"><?= nl2br(htmlspecialchars($h['antecedentes_ginecoobstetricos'], ENT_QUOTES, 'UTF-8')) ?></div>

        <p class="mb-1 highlight">PADECIMIENTO ACTUAL</p>
        <div class="value-block mb-2"><?= nl2br(htmlspecialchars($h['padecimiento_actual'], ENT_QUOTES, 'UTF-8')) ?></div>

        <p class="mb-1">LABORATORIO Y GABINETE</p>
        <div class="value-block mb-3"><?= nl2br(htmlspecialchars(isset($diag['Laboratorios']) ? $diag['Laboratorios'] : '', ENT_QUOTES, 'UTF-8')) ?></div>
    </div>

    <!-- Segunda página: Exploración física, diagnóstico y pronóstico -->
    <div class="box page-break">
        <p class="mb-1 highlight">EXPLORACION FISICA</p>

        <div class="explora-row mb-2">
            <div class="d-flex flex-wrap gap-4">
                <div><label>ESTATURA</label> <span><?= htmlspecialchars(isset($expl['Estatura']) ? $expl['Estatura'] : '', ENT_QUOTES, 'UTF-8') ?></span></div>
                <div><label>PESO</label> <span><?= htmlspecialchars(isset($expl['Peso']) ? $expl['Peso'] : '', ENT_QUOTES, 'UTF-8') ?></span></div>
                <div><label>I.M.C</label> <span><?= htmlspecialchars(isset($expl['IMC']) ? $expl['IMC'] : '', ENT_QUOTES, 'UTF-8') ?></span></div>
                <div><label>TEMPERATURA</label> <span><?= htmlspecialchars(isset($expl['Temperatura']) ? $expl['Temperatura'] : '', ENT_QUOTES, 'UTF-8') ?></span></div>
                <div><label>PRESION ARTERIAL</label> <span><?= htmlspecialchars(isset($expl['Presion arterial']) ? $expl['Presion arterial'] : '', ENT_QUOTES, 'UTF-8') ?></span></div>
                <div><label>FRECUENCIA CARDIACA</label> <span><?= htmlspecialchars(isset($expl['Frecuencia cardiaca']) ? $expl['Frecuencia cardiaca'] : '', ENT_QUOTES, 'UTF-8') ?></span></div>
                <div><label>FRECUENCIA RESPIRATORIA</label> <span><?= htmlspecialchars(isset($expl['Frecuencia respiratoria']) ? $expl['Frecuencia respiratoria'] : '', ENT_QUOTES, 'UTF-8') ?></span></div>
            </div>
        </div>

        <p class="mb-1 highlight">FRECUENCIA RESPIRATORIA</p>
        <div class="value-block mb-2"><?= htmlspecialchars(isset($expl['Frecuencia respiratoria']) ? $expl['Frecuencia respiratoria'] : '', ENT_QUOTES, 'UTF-8') ?></div>

        <p class="mb-1 highlight">INSPECCION GENERAL</p>
        <div class="value-block mb-2"><?= nl2br(htmlspecialchars(isset($expl['Inspeccion general']) ? $expl['Inspeccion general'] : '', ENT_QUOTES, 'UTF-8')) ?></div>

        <p class="mb-1 highlight">CABEZA</p>
        <div class="value-block mb-2"><?= nl2br(htmlspecialchars(isset($expl['Cabeza']) ? $expl['Cabeza'] : '', ENT_QUOTES, 'UTF-8')) ?></div>

        <p class="mb-1 highlight">CUELLO</p>
        <div class="value-block mb-2"><?= nl2br(htmlspecialchars(isset($expl['Cuello']) ? $expl['Cuello'] : '', ENT_QUOTES, 'UTF-8')) ?></div>

        <p class="mb-1 highlight">TORAX</p>
        <div class="value-block mb-2"><?= nl2br(htmlspecialchars(isset($expl['Torax']) ? $expl['Torax'] : '', ENT_QUOTES, 'UTF-8')) ?></div>

        <p class="mb-1 highlight">ABDOMEN</p>
        <div class="value-block mb-2"><?= nl2br(htmlspecialchars(isset($expl['Abdomen']) ? $expl['Abdomen'] : '', ENT_QUOTES, 'UTF-8')) ?></div>

        <p class="mb-1 highlight">COLUMNA VERTEBRAL</p>
        <div class="value-block mb-2"><?= nl2br(htmlspecialchars(isset($expl['Columna vertebral']) ? $expl['Columna vertebral'] : '', ENT_QUOTES, 'UTF-8')) ?></div>

        <p class="mb-1 highlight">GENITALES EXTERNOS</p>
        <div class="value-block mb-2"><?= nl2br(htmlspecialchars(isset($expl['Genitales']) ? $expl['Genitales'] : '', ENT_QUOTES, 'UTF-8')) ?></div>

        <p class="mb-1 highlight">TACTO VAGINAL</p>
        <div class="value-block mb-2"><?= nl2br(htmlspecialchars(isset($expl['Tacto vaginal']) ? $expl['Tacto vaginal'] : '', ENT_QUOTES, 'UTF-8')) ?></div>

        <p class="mb-1 highlight">EXTREMIDADES</p>
        <div class="value-block mb-3"><?= nl2br(htmlspecialchars(isset($expl['Extremidades']) ? $expl['Extremidades'] : '', ENT_QUOTES, 'UTF-8')) ?></div>

        <p class="mb-1 highlight">DIAGNOSTICO</p>
        <div class="value-block mb-2"><?= nl2br(htmlspecialchars(isset($diag['Diagnostico']) ? $diag['Diagnostico'] : $h['diagnosticos'], ENT_QUOTES, 'UTF-8')) ?></div>

        <p class="mb-1 highlight">PRONOSTICO</p>
        <div class="value-block mb-4"><?= nl2br(htmlspecialchars($h['tratamientos'], ENT_QUOTES, 'UTF-8')) ?></div>

        <p class="mb-1">TRATAMIENTO</p>
        <div class="value-block mb-4">______________________________________________</div>

        <p class="mb-1">PLAN</p>
        <div class="value-block mb-4">______________________________________________</div>

        <div class="mt-5 d-flex justify-content-between">
            <div>FIRMA DEL MEDICO RESPONSABLE</div>
            <div>
                DR. _______________________________<br>
                CED. PROF: ________________________
            </div>
        </div>
    </div>
</div>
</body>
</html>
