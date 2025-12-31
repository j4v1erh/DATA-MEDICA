<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';

/**
 * Acepta paciente por:
 *  - ?pac=123  (recomendado)
 *  - o si viene incluido desde panel_paciente.php que usa ?id=123
 */
$paciente_id = isset($_GET['pac']) ? (int)$_GET['pac'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
if ($paciente_id <= 0) {
    die('Paciente no especificado.');
}

$accion = isset($_GET['accion']) ? $_GET['accion'] : 'list';
$registro_id = isset($_GET['reg']) ? (int)$_GET['reg'] : 0;

function buildFieldGroup($pairs) {
    $segments = [];
    foreach ($pairs as $label => $value) {
        $value = trim((string)$value);
        if ($value !== '') $segments[] = $label . ': ' . $value;
    }
    return implode(' | ', $segments);
}

function parseFieldGroup($text) {
    $result = [];
    $parts = explode('|', (string)$text);
    foreach ($parts as $part) {
        $part = trim($part);
        if ($part === '') continue;
        $pos = strpos($part, ':');
        if ($pos === false) continue;
        $label = trim(substr($part, 0, $pos));
        $value = trim(substr($part, $pos + 1));
        $result[$label] = $value;
    }
    return $result;
}

function h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function irPanelHistoria($paciente_id) {
    header('Location: ../panel_paciente.php?id=' . (int)$paciente_id . '&sec=historia');
    exit;
}

// ====== ACCIÓN: DELETE ======
if ($accion === 'delete') {
    // ids puede venir como "1,2,3"
    $ids_str = $_GET['ids'] ?? '';
    $ids = array_filter(array_map('intval', explode(',', $ids_str)));

    if (empty($ids)) {
        irPanelHistoria($paciente_id);
    }

    // seguridad: solo borrar registros que sean de este paciente
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "DELETE FROM historia_clinica WHERE paciente_id = ? AND id IN ($placeholders)";
    $params = array_merge([$paciente_id], $ids);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    irPanelHistoria($paciente_id);
}

// ====== CARGA DE REGISTRO (para view/print/form editar) ======
$hcl = null;
$apnp = $gineco = $expl = $diag = [];
$padecimiento = $ant_patologicos = $ant_heredo = $pronostico = '';

if (in_array($accion, ['view','print','form'], true) && $registro_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM historia_clinica WHERE id = :id AND paciente_id = :pac");
    $stmt->execute([':id' => $registro_id, ':pac' => $paciente_id]);
    $hcl = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$hcl) die('Registro no encontrado.');

    $apnp   = parseFieldGroup($hcl['antecedentes_no_patologicos'] ?? '');
    $gineco = parseFieldGroup($hcl['antecedentes_ginecoobstetricos'] ?? '');
    $expl   = parseFieldGroup($hcl['exploracion_fisica'] ?? '');
    $diag   = parseFieldGroup($hcl['diagnosticos'] ?? '');

    $padecimiento    = $hcl['padecimiento_actual'] ?? '';
    $ant_patologicos = $hcl['antecedentes_patologicos'] ?? '';
    $ant_heredo      = $hcl['antecedentes_heredofamiliares'] ?? '';
    $pronostico      = $hcl['tratamientos'] ?? '';
}

// ====== POST: GUARDAR (INSERT/UPDATE) ======
$errores = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'form') {

    $padecimiento    = trim($_POST['padecimiento_actual'] ?? '');
    $ant_patologicos = trim($_POST['antecedentes_patologicos'] ?? '');
    $ant_heredo      = trim($_POST['antecedentes_heredofamiliares'] ?? '');
    $pronostico      = trim($_POST['pronostico'] ?? '');

    $ant_no_patologicos = buildFieldGroup([
        'Lugar y fecha de nacimiento' => $_POST['lugar_fecha_nacimiento'] ?? '',
        'Estado civil'                => $_POST['estado_civil'] ?? '',
        'Religion'                    => $_POST['religion'] ?? '',
        'Habitacion'                  => $_POST['habitacion'] ?? '',
        'Higiene personal'            => $_POST['higiene_personal'] ?? '',
        'Escolaridad'                 => $_POST['escolaridad'] ?? '',
        'Alimentacion'                => $_POST['alimentacion'] ?? '',
        'Ocupacion'                   => $_POST['ocupacion'] ?? '',
        'Tipo de sangre'              => $_POST['tipo_sangre'] ?? '',
    ]);

    $ant_gineco = buildFieldGroup([
        'Menarca'               => $_POST['menarca'] ?? '',
        'Ritmo'                 => $_POST['ritmo'] ?? '',
        'IVSA'                  => $_POST['ivsa'] ?? '',
        'FUR'                   => $_POST['fur'] ?? '',
        'FFP'                   => $_POST['ffp'] ?? '',
        'Embarazos'             => $_POST['embarazos'] ?? '',
        'Partos'                => $_POST['partos'] ?? '',
        'Cesareas'              => $_POST['cesareas'] ?? '',
        'Abortos'               => $_POST['abortos'] ?? '',
        'MFP'                   => $_POST['mfp'] ?? '',
        'Edad del padre'        => $_POST['edad_padre'] ?? '',
        'Hijos con bajo peso'   => $_POST['hijos_bajo_peso'] ?? '',
        'Hijos macrosomicos'    => $_POST['hijos_macrosomicos'] ?? '',
        'Edad de hijos vivos'   => $_POST['edad_hijos_vivos'] ?? '',
        'Climaterio'            => $_POST['climaterio'] ?? '',
        'Tiempo de uso del MFP' => $_POST['tiempo_uso_mfp'] ?? '',
    ]);

    $exploracion = buildFieldGroup([
        'Estatura'                => $_POST['estatura'] ?? '',
        'Peso'                    => $_POST['peso'] ?? '',
        'IMC'                     => $_POST['imc'] ?? '',
        'Temperatura'             => $_POST['temperatura'] ?? '',
        'Presion arterial'        => $_POST['presion_arterial'] ?? '',
        'Frecuencia cardiaca'     => $_POST['frecuencia_cardiaca'] ?? '',
        'Frecuencia respiratoria' => $_POST['frecuencia_respiratoria'] ?? '',
        'Inspeccion general'      => $_POST['inspeccion_general'] ?? '',
        'Cabeza'                  => $_POST['cabeza'] ?? '',
        'Cuello'                  => $_POST['cuello'] ?? '',
        'Torax'                   => $_POST['torax'] ?? '',
        'Abdomen'                 => $_POST['abdomen'] ?? '',
        'Columna vertebral'       => $_POST['columna_vertebral'] ?? '',
        'Genitales'               => $_POST['genitales'] ?? '',
        'Tacto vaginal'           => $_POST['tacto_vaginal'] ?? '',
        'Extremidades'            => $_POST['extremidades'] ?? '',
    ]);

    $diagnosticos = buildFieldGroup([
        'Laboratorios'       => $_POST['laboratorios'] ?? '',
        'Estudios de imagen' => $_POST['estudios_imagen'] ?? '',
        'Diagnostico'        => $_POST['diagnostico'] ?? '',
    ]);

    try {
        if ($registro_id > 0) {
            // UPDATE
            $sql = "UPDATE historia_clinica
                    SET padecimiento_actual = :padecimiento,
                        antecedentes_no_patologicos = :ant_no_pat,
                        antecedentes_patologicos = :ant_pat,
                        antecedentes_heredofamiliares = :ant_heredo,
                        antecedentes_ginecoobstetricos = :ant_gineco,
                        exploracion_fisica = :exploracion,
                        diagnosticos = :diagnosticos,
                        tratamientos = :tratamientos
                    WHERE id = :id AND paciente_id = :pac";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':padecimiento' => $padecimiento,
                ':ant_no_pat'   => $ant_no_patologicos,
                ':ant_pat'      => $ant_patologicos,
                ':ant_heredo'   => $ant_heredo,
                ':ant_gineco'   => $ant_gineco,
                ':exploracion'  => $exploracion,
                ':diagnosticos' => $diagnosticos,
                ':tratamientos' => $pronostico,
                ':id'           => $registro_id,
                ':pac'          => $paciente_id,
            ]);
        } else {
            // INSERT
            $motivo = trim($_POST['motivo_consulta'] ?? 'Historia clinica');

            $sql = "INSERT INTO historia_clinica
                   (paciente_id, fecha, motivo_consulta, padecimiento_actual,
                    antecedentes_no_patologicos, antecedentes_patologicos,
                    antecedentes_heredofamiliares, antecedentes_ginecoobstetricos,
                    exploracion_fisica, diagnosticos, tratamientos)
                    VALUES
                   (:paciente_id, NOW(), :motivo, :padecimiento,
                    :ant_no_pat, :ant_pat, :ant_heredo, :ant_gin,
                    :exploracion, :diagnosticos, :tratamientos)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':paciente_id'  => $paciente_id,
                ':motivo'       => $motivo,
                ':padecimiento' => $padecimiento,
                ':ant_no_pat'   => $ant_no_patologicos,
                ':ant_pat'      => $ant_patologicos,
                ':ant_heredo'   => $ant_heredo,
                ':ant_gin'      => $ant_gineco,
                ':exploracion'  => $exploracion,
                ':diagnosticos' => $diagnosticos,
                ':tratamientos' => $pronostico
            ]);
        }

        irPanelHistoria($paciente_id);

    } catch (Exception $e) {
        $errores = 'Error al guardar: ' . $e->getMessage();
    }
}

function val($key, $fallback='') {
    return h($_POST[$key] ?? $fallback);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Historia Clínica</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>

  <style>
    :root{
      --primary:#079FCF; --primary-dark:#0277A1; --primary-deep:#014F75;
      --neutral-bg:#F4F8FA; --neutral-gray:#DDE5EA; --neutral-text:#2F3A45;
    }
    body{background:var(--neutral-bg);}
    .card{background:#fff;border:2px solid var(--neutral-gray);border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,.05);}
    .input_base{width:100%;height:40px;padding:0 12px;border:1px solid var(--neutral-gray);border-radius:8px;color:var(--neutral-text);outline:none;background:#fff;}
    .input_base:focus{border-color:var(--primary);box-shadow:0 0 0 4px rgba(7,159,207,.18);}
    .textarea_base{width:100%;padding:10px 12px;border:1px solid var(--neutral-gray);border-radius:10px;color:var(--neutral-text);outline:none;background:#fff;resize:vertical;min-height:90px;}
    .textarea_base:focus{border-color:var(--primary);box-shadow:0 0 0 4px rgba(7,159,207,.18);}
    h3{color:var(--primary-deep);}
    #tituloBarra{background:var(--primary-dark);color:#fff;}
    input[type="checkbox"]{accent-color: var(--primary-deep);}
  </style>
</head>
<body>
<div class="max-w-[1200px] mx-auto p-6">

  <!-- HEADER -->
  <div class="card p-4 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-start">
      <div>
        <label class="block text-[var(--neutral-text)] mb-1">Médico que Elabora</label>
        <input class="input_base" value="Dr. Carlos Alberto Méndez Ruiz">
      </div>
      <div>
        <label class="block text-[var(--neutral-text)] mb-1">Cédula Profesional</label>
        <input class="input_base" value="7654321">
      </div>
      <div>
        <label class="block text-[var(--neutral-text)] mb-1">Servicio Médico</label>
        <input class="input_base" value="Medicina General">
      </div>
      <div>
        <label class="block text-[var(--neutral-text)] mb-1">Fecha</label>
        <div id="fecha" class="w-full h-10 px-3 border border-[var(--neutral-gray)] rounded-lg bg-[var(--neutral-gray)] flex items-center text-[var(--neutral-text)]"></div>
      </div>
      <div>
        <label class="block text-[var(--neutral-text)] mb-1">Hora</label>
        <div id="hora" class="w-full h-10 px-3 border border-[var(--neutral-gray)] rounded-lg bg-[var(--neutral-gray)] flex items-center text-[var(--neutral-text)]"></div>
      </div>
    </div>

    <div class="mt-4 flex justify-end gap-3">
      <a href="../panel_paciente.php?id=<?= (int)$paciente_id ?>&sec=historia"
         class="px-6 py-2 rounded-lg flex items-center gap-2"
         style="background: var(--primary-deep); color:white;">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Regresar
      </a>
    </div>
  </div>

  <div id="tituloBarra" class="rounded-lg p-3 mb-6 text-center">
    <h2 class="uppercase tracking-wide font-semibold">Historia Clínica</h2>
  </div>

  <?php if ($errores): ?>
    <div class="card p-4 mb-6 border-red-300">
      <p class="text-red-700 font-semibold"><?= h($errores) ?></p>
    </div>
  <?php endif; ?>

  <?php if ($accion === 'list'): ?>

    <!-- BOTONES (ahora viven aquí, 1 solo archivo) -->
    <div class="flex justify-end gap-3 mb-4">
      <a class="px-5 py-2 rounded-lg" style="background:var(--primary);color:white;"
         href="?pac=<?= (int)$paciente_id ?>&accion=form">Agregar</a>

      <button class="px-5 py-2 rounded-lg border" style="border-color:var(--neutral-gray);"
              onclick="historiaVer(<?= (int)$paciente_id ?>)">Ver</button>

      <button class="px-5 py-2 rounded-lg border" style="border-color:var(--neutral-gray);"
              onclick="historiaImprimir(<?= (int)$paciente_id ?>)">Imprimir</button>

      <button class="px-5 py-2 rounded-lg" style="background: #fbbf24; color:#111827;"
              onclick="historiaEditar(<?= (int)$paciente_id ?>)">Editar</button>

      <button class="px-5 py-2 rounded-lg" style="background:#ef4444;color:white;"
              onclick="historiaEliminar(<?= (int)$paciente_id ?>)">Eliminar</button>
    </div>

    <!-- TABLA CON CHECKBOXES -->
    <div class="card p-4">
      <h3 class="mb-4 pb-2 border-b-2 border-[var(--neutral-gray)] uppercase font-semibold">Registros</h3>

      <?php
        $stmt = $pdo->prepare("SELECT id, fecha, motivo_consulta
                               FROM historia_clinica
                               WHERE paciente_id = :pac
                               ORDER BY fecha DESC");
        $stmt->execute([':pac' => $paciente_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      ?>

      <?php if (empty($rows)): ?>
        <p class="text-[var(--neutral-text)]">No hay registros aún.</p>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-left">
                <th class="py-2 pr-4">Sel.</th>
                <th class="py-2 pr-4">Fecha</th>
                <th class="py-2 pr-4">Motivo</th>
                <th class="py-2 pr-4">ID</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $r): ?>
                <tr class="border-t border-[var(--neutral-gray)]">
                  <td class="py-2 pr-4">
                    <input type="checkbox" class="historia-check w-4 h-4" value="<?= (int)$r['id'] ?>">
                  </td>
                  <td class="py-2 pr-4"><?= h($r['fecha']) ?></td>
                  <td class="py-2 pr-4"><?= h($r['motivo_consulta']) ?></td>
                  <td class="py-2 pr-4"><?= (int)$r['id'] ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

  <?php elseif ($accion === 'view' && $registro_id > 0): ?>

    <div class="card p-4">
      <h3 class="mb-4 pb-2 border-b-2 border-[var(--neutral-gray)] uppercase font-semibold">
        Ver Historia Clínica #<?= (int)$registro_id ?>
      </h3>

      <div class="space-y-4 text-[var(--neutral-text)]">
        <div><strong>Fecha:</strong> <?= h($hcl['fecha'] ?? '') ?></div>
        <div><strong>Padecimiento actual:</strong><br><?= nl2br(h($padecimiento)) ?></div>
        <div><strong>Heredofamiliares:</strong><br><?= nl2br(h($ant_heredo)) ?></div>
        <div><strong>Antecedentes patológicos:</strong><br><?= nl2br(h($ant_patologicos)) ?></div>

        <div><strong>No patológicos:</strong><br><?= h($hcl['antecedentes_no_patologicos'] ?? '') ?></div>
        <div><strong>Ginecoobstétricos:</strong><br><?= h($hcl['antecedentes_ginecoobstetricos'] ?? '') ?></div>
        <div><strong>Exploración física:</strong><br><?= h($hcl['exploracion_fisica'] ?? '') ?></div>
        <div><strong>Diagnósticos:</strong><br><?= h($hcl['diagnosticos'] ?? '') ?></div>
        <div><strong>Tratamientos / Pronóstico:</strong><br><?= nl2br(h($pronostico)) ?></div>
      </div>

      <div class="mt-6 flex justify-end gap-3">
        <a class="px-6 py-2 rounded-lg border" style="border-color:var(--neutral-gray);"
           href="?pac=<?= (int)$paciente_id ?>&accion=list">Volver</a>
      </div>
    </div>

  <?php elseif ($accion === 'print' && $registro_id > 0): ?>

    <div class="card p-4">
      <h3 class="mb-4 pb-2 border-b-2 border-[var(--neutral-gray)] uppercase font-semibold">
        Imprimir Historia Clínica #<?= (int)$registro_id ?>
      </h3>

      <div class="space-y-3 text-[var(--neutral-text)] text-sm">
        <div><strong>Fecha:</strong> <?= h($hcl['fecha'] ?? '') ?></div>
        <div><strong>Padecimiento actual:</strong><br><?= nl2br(h($padecimiento)) ?></div>
        <div><strong>Heredofamiliares:</strong><br><?= nl2br(h($ant_heredo)) ?></div>
        <div><strong>Antecedentes patológicos:</strong><br><?= nl2br(h($ant_patologicos)) ?></div>
        <div><strong>No patológicos:</strong><br><?= h($hcl['antecedentes_no_patologicos'] ?? '') ?></div>
        <div><strong>Ginecoobstétricos:</strong><br><?= h($hcl['antecedentes_ginecoobstetricos'] ?? '') ?></div>
        <div><strong>Exploración física:</strong><br><?= h($hcl['exploracion_fisica'] ?? '') ?></div>
        <div><strong>Diagnósticos:</strong><br><?= h($hcl['diagnosticos'] ?? '') ?></div>
        <div><strong>Tratamientos / Pronóstico:</strong><br><?= nl2br(h($pronostico)) ?></div>
      </div>

      <div class="mt-6 flex justify-end gap-3">
        <button class="px-6 py-2 rounded-lg" style="background:var(--primary);color:white;" onclick="window.print()">Imprimir</button>
        <a class="px-6 py-2 rounded-lg border" style="border-color:var(--neutral-gray);"
           href="?pac=<?= (int)$paciente_id ?>&accion=list">Volver</a>
      </div>
    </div>

  <?php else: /* FORM (ALTA/EDITAR) */ ?>

    <div class="card p-4 mb-6">
      <h3 class="mb-4 pb-2 border-b-2 border-[var(--neutral-gray)] uppercase font-semibold">
        <?= $registro_id > 0 ? 'Editar Historia Clínica' : 'Nueva Historia Clínica' ?>
      </h3>

      <form method="POST" action="?pac=<?= (int)$paciente_id ?>&accion=form<?= $registro_id>0 ? '&reg='.(int)$registro_id : '' ?>" class="space-y-6">
        <input type="hidden" name="motivo_consulta" value="Historia clinica">

        <div class="card p-4">
          <h3 class="mb-4 pb-2 border-b-2 border-[var(--neutral-gray)] uppercase font-semibold">Heredofamiliares</h3>
          <textarea name="antecedentes_heredofamiliares" class="textarea_base"><?= val('antecedentes_heredofamiliares', $ant_heredo) ?></textarea>
        </div>

        <div class="card p-4">
          <h3 class="mb-4 pb-2 border-b-2 border-[var(--neutral-gray)] uppercase font-semibold">No patológicos</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
              <label class="block text-sm text-[var(--neutral-text)] mb-1">Lugar y Fecha de Nacimiento</label>
              <input name="lugar_fecha_nacimiento" class="input_base" value="<?= val('lugar_fecha_nacimiento', $apnp['Lugar y fecha de nacimiento'] ?? '') ?>">
            </div>
            <div>
              <label class="block text-sm text-[var(--neutral-text)] mb-1">Escolaridad</label>
              <input name="escolaridad" class="input_base" value="<?= val('escolaridad', $apnp['Escolaridad'] ?? '') ?>">
            </div>
            <div>
              <label class="block text-sm text-[var(--neutral-text)] mb-1">Estado Civil</label>
              <input name="estado_civil" class="input_base" value="<?= val('estado_civil', $apnp['Estado civil'] ?? '') ?>">
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm text-[var(--neutral-text)] mb-1">Alimentación</label>
              <input name="alimentacion" class="input_base" value="<?= val('alimentacion', $apnp['Alimentacion'] ?? '') ?>">
            </div>
            <div>
              <label class="block text-sm text-[var(--neutral-text)] mb-1">Religión</label>
              <input name="religion" class="input_base" value="<?= val('religion', $apnp['Religion'] ?? '') ?>">
            </div>
            <div>
              <label class="block text-sm text-[var(--neutral-text)] mb-1">Habitación</label>
              <input name="habitacion" class="input_base" value="<?= val('habitacion', $apnp['Habitacion'] ?? '') ?>">
            </div>
            <div>
              <label class="block text-sm text-[var(--neutral-text)] mb-1">Ocupación</label>
              <input name="ocupacion" class="input_base" value="<?= val('ocupacion', $apnp['Ocupacion'] ?? '') ?>">
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm text-[var(--neutral-text)] mb-1">Higiene Personal</label>
              <input name="higiene_personal" class="input_base" value="<?= val('higiene_personal', $apnp['Higiene personal'] ?? '') ?>">
            </div>
            <div>
              <label class="block text-sm text-[var(--neutral-text)] mb-1">Tipo de Sangre</label>
              <input name="tipo_sangre" class="input_base" value="<?= val('tipo_sangre', $apnp['Tipo de sangre'] ?? '') ?>">
            </div>
          </div>
        </div>

        <div class="card p-4">
          <h3 class="mb-4 pb-2 border-b-2 border-[var(--neutral-gray)] uppercase font-semibold">Patológicos</h3>
          <textarea name="antecedentes_patologicos" class="textarea_base"><?= val('antecedentes_patologicos', $ant_patologicos) ?></textarea>
        </div>

        <div class="card p-4">
          <h3 class="mb-4 pb-2 border-b-2 border-[var(--neutral-gray)] uppercase font-semibold">Ginecoobstétricos</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php
              $g = [
                ['Menarca','menarca'],['Ritmo','ritmo'],['IVSA','ivsa'],['FUR','fur'],['FFP','ffp'],
                ['Embarazos','embarazos'],['Partos','partos'],['Cesareas','cesareas'],['Abortos','abortos'],
                ['MFP','mfp'],['Edad del padre','edad_padre'],['Hijos con bajo peso','hijos_bajo_peso'],
                ['Hijos macrosomicos','hijos_macrosomicos'],['Edad de hijos vivos','edad_hijos_vivos'],
                ['Climaterio','climaterio'],['Tiempo de uso del MFP','tiempo_uso_mfp'],
              ];
              foreach($g as $it):
                [$label,$name] = $it;
            ?>
              <div>
                <label class="block text-sm text-[var(--neutral-text)] mb-1"><?= h($label) ?></label>
                <input name="<?= h($name) ?>" class="input_base" value="<?= val($name, $gineco[$label] ?? '') ?>">
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="card p-4">
          <h3 class="mb-4 pb-2 border-b-2 border-[var(--neutral-gray)] uppercase font-semibold">Padecimiento Actual</h3>
          <textarea name="padecimiento_actual" class="textarea_base"><?= val('padecimiento_actual', $padecimiento) ?></textarea>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
              <label class="block text-sm text-[var(--neutral-text)] mb-1">Laboratorios</label>
              <input name="laboratorios" class="input_base" value="<?= val('laboratorios', $diag['Laboratorios'] ?? '') ?>">
            </div>
            <div>
              <label class="block text-sm text-[var(--neutral-text)] mb-1">Estudios de Imagen</label>
              <input name="estudios_imagen" class="input_base" value="<?= val('estudios_imagen', $diag['Estudios de imagen'] ?? '') ?>">
            </div>
          </div>
        </div>

        <div class="card p-4">
          <h3 class="mb-4 pb-2 border-b-2 border-[var(--neutral-gray)] uppercase font-semibold">Exploración Física</h3>

          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-4">
            <?php
              $sv = [
                ['Estatura','estatura'],['Peso','peso'],['IMC','imc'],['Temperatura','temperatura'],
                ['Presion arterial','presion_arterial'],['Frecuencia cardiaca','frecuencia_cardiaca'],
                ['Frecuencia respiratoria','frecuencia_respiratoria'],
              ];
              foreach($sv as $it):
                [$key,$name] = $it;
            ?>
              <div>
                <label class="block text-sm text-[var(--neutral-text)] mb-1"><?= h($key) ?></label>
                <input name="<?= h($name) ?>" class="input_base" value="<?= val($name, $expl[$key] ?? '') ?>">
              </div>
            <?php endforeach; ?>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <?php
              $ex = [
                ['Inspeccion general','inspeccion_general'],['Cabeza','cabeza'],['Cuello','cuello'],
                ['Torax','torax'],['Abdomen','abdomen'],['Columna vertebral','columna_vertebral'],
                ['Genitales','genitales'],['Tacto vaginal','tacto_vaginal'],['Extremidades','extremidades'],
              ];
              foreach($ex as $it):
                [$key,$name] = $it;
            ?>
              <div>
                <label class="block text-sm text-[var(--neutral-text)] mb-1"><?= h($key) ?></label>
                <input name="<?= h($name) ?>" class="input_base" value="<?= val($name, $expl[$key] ?? '') ?>">
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="card p-4">
          <h3 class="mb-4 pb-2 border-b-2 border-[var(--neutral-gray)] uppercase font-semibold">Diagnóstico</h3>
          <textarea name="diagnostico" class="textarea_base"><?= val('diagnostico', $diag['Diagnostico'] ?? '') ?></textarea>
        </div>

        <div class="card p-4">
          <h3 class="mb-4 pb-2 border-b-2 border-[var(--neutral-gray)] uppercase font-semibold">Pronóstico</h3>
          <textarea name="pronostico" class="textarea_base"><?= val('pronostico', $pronostico) ?></textarea>
        </div>

        <div class="card p-4">
          <p class="text-sm font-semibold text-[var(--primary-deep)]">
            DICHA ATENCIÓN BASADA EN LA NOM-004-SSA3-2012 (EXPEDIENTE CLÍNICO) Y NOM-007-SSA2-2016.
          </p>
        </div>

        <div class="flex justify-end gap-4">
          <a href="?pac=<?= (int)$paciente_id ?>&accion=list"
             class="px-8 py-3 border-2 rounded-lg"
             style="background:white;border-color:var(--neutral-gray);color:var(--neutral-text);">
            Cancelar
          </a>

          <button type="submit"
            class="px-8 py-3 rounded-lg"
            style="background:var(--primary);color:white;">
            <?= $registro_id > 0 ? 'Guardar cambios' : 'Guardar historia clínica' ?>
          </button>
        </div>

      </form>
    </div>

  <?php endif; ?>

</div>

<script>
  lucide.createIcons();

  function actualizarFechaHora() {
    const now = new Date();
    const fecha = now.toLocaleDateString("es-MX", { day:"2-digit", month:"2-digit", year:"numeric" });
    const hora  = now.toLocaleTimeString("es-MX", { hour:"2-digit", minute:"2-digit", second:"2-digit", hour12:false });
    document.getElementById("fecha").textContent = fecha;
    document.getElementById("hora").textContent = hora;
  }
  actualizarFechaHora();
  setInterval(actualizarFechaHora, 1000);

  function obtenerSeleccionHistoria() {
    const checks = document.querySelectorAll('.historia-check:checked');
    const ids = [];
    checks.forEach(c => ids.push(c.value));
    return ids;
  }

  function historiaVer(pacId) {
    const ids = obtenerSeleccionHistoria();
    if (ids.length === 0) return alert('Selecciona un registro para ver.');
    if (ids.length > 1) return alert('Selecciona solo un registro para ver.');
    window.location.href = '?pac=' + pacId + '&accion=view&reg=' + ids[0];
  }

  function historiaImprimir(pacId) {
    const ids = obtenerSeleccionHistoria();
    if (ids.length === 0) return alert('Selecciona un registro para imprimir.');
    if (ids.length > 1) return alert('Selecciona solo un registro para imprimir.');
    window.location.href = '?pac=' + pacId + '&accion=print&reg=' + ids[0];
  }

  function historiaEditar(pacId) {
    const ids = obtenerSeleccionHistoria();
    if (ids.length === 0) return alert('Selecciona un registro para editar.');
    if (ids.length > 1) return alert('Selecciona solo un registro para editar.');
    window.location.href = '?pac=' + pacId + '&accion=form&reg=' + ids[0];
  }

  function historiaEliminar(pacId) {
    const ids = obtenerSeleccionHistoria();
    if (ids.length === 0) return alert('Selecciona al menos un registro para eliminar.');
    if (!confirm('¿Eliminar los registros seleccionados?')) return;
    window.location.href = '?pac=' + pacId + '&accion=delete&ids=' + ids.join(',');
  }
</script>

</body>
</html>
