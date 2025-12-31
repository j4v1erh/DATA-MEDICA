<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';

/**
 * Modo:
 * - EDITAR: ?id=REGISTRO_ID&pac=PACIENTE_ID
 * - NUEVO:  ?pac=PACIENTE_ID
 */
$registro_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$paciente_id = isset($_GET['pac']) ? (int)$_GET['pac'] : (isset($_GET['id_paciente']) ? (int)$_GET['id_paciente'] : 0);

$modo_editar = $registro_id > 0;

function buildFieldGroup($pairs)
{
    $segments = array();
    foreach ($pairs as $label => $value) {
        $value = trim((string)$value);
        if ($value !== '') {
            $segments[] = $label . ': ' . $value;
        }
    }
    return implode(' | ', $segments);
}

function parseFieldGroup($text)
{
    $result = array();
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

$errores = '';
$h = null;

// ===== CARGAR REGISTRO SI ES EDITAR =====
$apnp = $gineco = $expl = $diag = [];
$padecimiento = $ant_patologicos = $ant_heredo = $pronostico = '';

if ($modo_editar) {
    $stmt = $pdo->prepare("SELECT * FROM historia_clinica WHERE id = :id");
    $stmt->execute([':id' => $registro_id]);
    $h = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$h) {
        die('Registro de historia clínica no encontrado.');
    }

    // parsear grupos para prellenar
    $apnp   = parseFieldGroup($h['antecedentes_no_patologicos'] ?? '');
    $gineco = parseFieldGroup($h['antecedentes_ginecoobstetricos'] ?? '');
    $expl   = parseFieldGroup($h['exploracion_fisica'] ?? '');
    $diag   = parseFieldGroup($h['diagnosticos'] ?? '');

    $padecimiento    = $h['padecimiento_actual'] ?? '';
    $ant_patologicos = $h['antecedentes_patologicos'] ?? '';
    $ant_heredo      = $h['antecedentes_heredofamiliares'] ?? '';
    $pronostico      = $h['tratamientos'] ?? '';

    // Si no viene pac en URL, intenta tomarlo del registro
    if ($paciente_id <= 0 && isset($h['paciente_id'])) {
        $paciente_id = (int)$h['paciente_id'];
    }
} else {
    if ($paciente_id <= 0) {
        die('Paciente no especificado.');
    }
}

// ===== POST: INSERT o UPDATE =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // textos simples
    $padecimiento    = trim($_POST['padecimiento_actual'] ?? '');
    $ant_patologicos = trim($_POST['antecedentes_patologicos'] ?? '');
    $ant_heredo      = trim($_POST['antecedentes_heredofamiliares'] ?? '');
    $pronostico      = trim($_POST['pronostico'] ?? '');

    // grupos
    $ant_no_patologicos = buildFieldGroup(array(
        'Lugar y fecha de nacimiento' => $_POST['lugar_fecha_nacimiento'] ?? '',
        'Estado civil'                => $_POST['estado_civil'] ?? '',
        'Religion'                    => $_POST['religion'] ?? '',
        'Habitacion'                  => $_POST['habitacion'] ?? '',
        'Higiene personal'            => $_POST['higiene_personal'] ?? '',
        'Escolaridad'                 => $_POST['escolaridad'] ?? '',
        'Alimentacion'                => $_POST['alimentacion'] ?? '',
        'Ocupacion'                   => $_POST['ocupacion'] ?? '',
        'Tipo de sangre'              => $_POST['tipo_sangre'] ?? '',
    ));

    $ant_gineco = buildFieldGroup(array(
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
    ));

    $exploracion = buildFieldGroup(array(
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
    ));

    $diagnosticos = buildFieldGroup(array(
        'Laboratorios'       => $_POST['laboratorios'] ?? '',
        'Estudios de imagen' => $_POST['estudios_imagen'] ?? '',
        'Diagnostico'        => $_POST['diagnostico'] ?? '',
    ));

    try {
        if ($modo_editar) {
            $sql = "UPDATE historia_clinica
                    SET padecimiento_actual = :padecimiento,
                        antecedentes_no_patologicos = :ant_no_pat,
                        antecedentes_patologicos = :ant_pat,
                        antecedentes_heredofamiliares = :ant_heredo,
                        antecedentes_ginecoobstetricos = :ant_gineco,
                        exploracion_fisica = :exploracion,
                        diagnosticos = :diagnosticos,
                        tratamientos = :tratamientos
                    WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(
                ':padecimiento' => $padecimiento,
                ':ant_no_pat'   => $ant_no_patologicos,
                ':ant_pat'      => $ant_patologicos,
                ':ant_heredo'   => $ant_heredo,
                ':ant_gineco'   => $ant_gineco,
                ':exploracion'  => $exploracion,
                ':diagnosticos' => $diagnosticos,
                ':tratamientos' => $pronostico,
                ':id'           => $registro_id,
            ));
        } else {
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
            $stmt->execute(array(
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
            ));
        }

        header('Location: ../panel_paciente.php?id=' . $paciente_id . '&sec=historia');
        exit;

    } catch (Exception $e) {
        $errores = 'Error al guardar historia clínica: ' . $e->getMessage();
    }
}

// helpers de prefill (modo editar usa parseados; si falla post usa POST)
function v($postKey, $fallback = '')
{
    return htmlspecialchars($_POST[$postKey] ?? $fallback ?? '', ENT_QUOTES, 'UTF-8');
}
function vg($arr, $label, $fallback = '')
{
    $val = $arr[$label] ?? $fallback ?? '';
    // si hubo POST, respeta POST
    return htmlspecialchars($_POST[$_POST['_map'][$label] ?? '___'] ?? $val, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $modo_editar ? 'Editar Historia Clínica' : 'Nueva Historia Clínica' ?></title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>

  <!-- 🎨 PALETA / ESTILO = DISEÑO 1 -->
  <style>
    :root {
      --primary: #079FCF;
      --primary-dark: #0277A1;
      --primary-deep: #014F75;

      --accent-light: #5FD5F4;
      --accent: #36BCE1;
      --accent-dark: #01678C;

      --neutral-bg: #F4F8FA;
      --neutral-gray: #DDE5EA;
      --neutral-text: #2F3A45;
    }

    body { background-color: var(--neutral-bg); }

    .input_base {
      width: 100%;
      height: 40px;
      padding: 0 12px;
      border: 1px solid var(--neutral-gray);
      border-radius: 8px;
      color: var(--neutral-text);
      outline: none;
      transition: box-shadow .15s, border-color .15s;
      background: #fff;
    }
    .input_base:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 4px rgba(7, 159, 207, 0.18);
    }

    .textarea_base {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid var(--neutral-gray);
      border-radius: 10px;
      color: var(--neutral-text);
      outline: none;
      transition: box-shadow .15s, border-color .15s;
      background: #fff;
      resize: vertical;
      min-height: 90px;
    }
    .textarea_base:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 4px rgba(7, 159, 207, 0.18);
    }

    .card {
      background: white;
      border: 2px solid var(--neutral-gray);
      border-radius: 10px;
      box-shadow: 0px 2px 8px rgba(0,0,0,0.05);
    }

    h3 { color: var(--primary-deep); }
    #tituloBarra { background: var(--primary-dark); color: white; }
  </style>
</head>

<body>
<div class="max-w-[1200px] mx-auto p-6">

  <?php if (!empty($errores)): ?>
    <div class="card p-4 mb-6 border-red-300">
      <p class="text-red-700 font-semibold"><?= htmlspecialchars($errores, ENT_QUOTES, 'UTF-8') ?></p>
    </div>
  <?php endif; ?>

  <!-- ======= HEADER ======= -->
  <div class="card p-4 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-start">
      <div>
        <label class="block text-[var(--neutral-text)] mb-1">Médico que Elabora</label>
        <input type="text" class="input_base" value="Dr. Carlos Alberto Méndez Ruiz">
      </div>
      <div>
        <label class="block text-[var(--neutral-text)] mb-1">Cédula Profesional</label>
        <input type="text" class="input_base" value="7654321">
      </div>
      <div>
        <label class="block text-[var(--neutral-text)] mb-1">Servicio Médico</label>
        <input type="text" class="input_base" value="Medicina General">
      </div>
      <div>
        <label class="block text-[var(--neutral-text)] mb-1">Fecha</label>
        <div id="fecha"
          class="w-full h-10 px-3 border border-[var(--neutral-gray)] rounded-lg bg-[var(--neutral-gray)] flex items-center text-[var(--neutral-text)]"></div>
      </div>
      <div>
        <label class="block text-[var(--neutral-text)] mb-1">Hora</label>
        <div id="hora"
          class="w-full h-10 px-3 border border-[var(--neutral-gray)] rounded-lg bg-[var(--neutral-gray)] flex items-center text-[var(--neutral-text)]"></div>
      </div>
    </div>

    <div class="mt-4 flex justify-end">
      <a href="../panel_paciente.php?id=<?= (int)$paciente_id ?>&sec=historia"
         class="px-6 py-2 rounded-lg flex items-center gap-2 transition-colors"
         style="background: var(--primary-deep); color: white;">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        Regresar
      </a>
    </div>
  </div>

  <!-- TÍTULO -->
  <div id="tituloBarra" class="rounded-lg p-3 mb-6 text-center">
    <h2 class="uppercase tracking-wide font-semibold">
      <?= $modo_editar ? 'Editar Historia Clínica' : 'Nueva Historia Clínica' ?>
    </h2>
  </div>

  <!-- FORM -->
  <form method="POST" class="space-y-6">
    <input type="hidden" name="motivo_consulta" value="Historia clinica">

    <!-- HEREDO -->
    <div class="card p-4">
      <h3 class="mb-4 pb-2 border-b-2 border-[var(--neutral-gray)] uppercase font-semibold">
        Antecedentes Hereditarios y Familiares
      </h3>
      <textarea name="antecedentes_heredofamiliares" class="textarea_base"><?= v('antecedentes_heredofamiliares', $ant_heredo) ?></textarea>
    </div>

    <!-- NO PATOLÓGICOS -->
    <div class="card p-4">
      <h3 class="mb-4 pb-2 border-b-2 border-[var(--neutral-gray)] uppercase font-semibold">
        Antecedentes Personales No Patológicos
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2">
          <label class="block text-sm text-[var(--neutral-text)] mb-1">Lugar y Fecha de Nacimiento</label>
          <input name="lugar_fecha_nacimiento" class="input_base"
                 value="<?= v('lugar_fecha_nacimiento', $apnp['Lugar y fecha de nacimiento'] ?? '') ?>">
        </div>

        <div>
          <label class="block text-sm text-[var(--neutral-text)] mb-1">Escolaridad</label>
          <input name="escolaridad" class="input_base"
                 value="<?= v('escolaridad', $apnp['Escolaridad'] ?? '') ?>">
        </div>

        <div>
          <label class="block text-sm text-[var(--neutral-text)] mb-1">Estado Civil</label>
          <input name="estado_civil" class="input_base"
                 value="<?= v('estado_civil', $apnp['Estado civil'] ?? '') ?>">
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm text-[var(--neutral-text)] mb-1">Alimentación</label>
          <input name="alimentacion" class="input_base"
                 value="<?= v('alimentacion', $apnp['Alimentacion'] ?? '') ?>">
        </div>

        <div>
          <label class="block text-sm text-[var(--neutral-text)] mb-1">Religión</label>
          <input name="religion" class="input_base"
                 value="<?= v('religion', $apnp['Religion'] ?? '') ?>">
        </div>

        <div>
          <label class="block text-sm text-[var(--neutral-text)] mb-1">Habitación</label>
          <input name="habitacion" class="input_base"
                 value="<?= v('habitacion', $apnp['Habitacion'] ?? '') ?>">
        </div>

        <div>
          <label class="block text-sm text-[var(--neutral-text)] mb-1">Ocupación</label>
          <input name="ocupacion" class="input_base"
                 value="<?= v('ocupacion', $apnp['Ocupacion'] ?? '') ?>">
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm text-[var(--neutral-text)] mb-1">Higiene Personal</label>
          <input name="higiene_personal" class="input_base"
                 value="<?= v('higiene_personal', $apnp['Higiene personal'] ?? '') ?>">
        </div>

        <div>
          <label class="block text-sm text-[var(--neutral-text)] mb-1">Tipo de Sangre</label>
          <input name="tipo_sangre" class="input_base"
                 value="<?= v('tipo_sangre', $apnp['Tipo de sangre'] ?? '') ?>">
        </div>
      </div>
    </div>

    <!-- PATOLÓGICOS -->
    <div class="card p-4">
      <h3 class="mb-4 pb-2 border-b-2 border-[var(--neutral-gray)] uppercase font-semibold">
        Antecedentes Personales Patológicos
      </h3>
      <textarea name="antecedentes_patologicos" class="textarea_base"><?= v('antecedentes_patologicos', $ant_patologicos) ?></textarea>
    </div>

    <!-- GINECO -->
    <div class="card p-4">
      <h3 class="mb-4 pb-2 border-b-2 border-[var(--neutral-gray)] uppercase font-semibold">
        Antecedentes Ginecoobstétricos
      </h3>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php
          $g = [
            ['Menarca','menarca'],
            ['Ritmo','ritmo'],
            ['IVSA','ivsa'],
            ['FUR','fur'],
            ['FFP','ffp'],
            ['Embarazos','embarazos'],
            ['Partos','partos'],
            ['Cesareas','cesareas'],
            ['Abortos','abortos'],
            ['MFP','mfp'],
            ['Edad del padre','edad_padre'],
            ['Hijos con bajo peso','hijos_bajo_peso'],
            ['Hijos macrosomicos','hijos_macrosomicos'],
            ['Edad de hijos vivos','edad_hijos_vivos'],
            ['Climaterio','climaterio'],
            ['Tiempo de uso del MFP','tiempo_uso_mfp'],
          ];
          foreach($g as $item):
            [$label, $name] = $item;
        ?>
          <div>
            <label class="block text-sm text-[var(--neutral-text)] mb-1"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></label>
            <input name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" class="input_base"
              value="<?= v($name, $gineco[$label] ?? '') ?>">
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- PADECIMIENTO + LAB/IMG -->
    <div class="card p-4">
      <h3 class="mb-4 pb-2 border-b-2 border-[var(--neutral-gray)] uppercase font-semibold">
        Padecimiento Actual
      </h3>

      <textarea name="padecimiento_actual" class="textarea_base"><?= v('padecimiento_actual', $padecimiento) ?></textarea>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        <div>
          <label class="block text-sm text-[var(--neutral-text)] mb-1">Laboratorios</label>
          <input name="laboratorios" class="input_base"
                 value="<?= v('laboratorios', $diag['Laboratorios'] ?? '') ?>">
        </div>
        <div>
          <label class="block text-sm text-[var(--neutral-text)] mb-1">Estudios de Imagen</label>
          <input name="estudios_imagen" class="input_base"
                 value="<?= v('estudios_imagen', $diag['Estudios de imagen'] ?? '') ?>">
        </div>
      </div>
    </div>

    <!-- EXPLORACIÓN -->
    <div class="card p-4">
      <h3 class="mb-4 pb-2 border-b-2 border-[var(--neutral-gray)] uppercase font-semibold">
        Exploración Física
      </h3>

      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-4">
        <?php
          $sv = [
            ['Estatura (cm)','estatura','Estatura'],
            ['Peso (kg)','peso','Peso'],
            ['IMC','imc','IMC'],
            ['Temperatura (°C)','temperatura','Temperatura'],
            ['Presión arterial (mmHg)','presion_arterial','T/A'],
            ['Frecuencia cardiaca','frecuencia_cardiaca','FC'],
            ['Frecuencia respiratoria','frecuencia_respiratoria','FR'],
          ];
          foreach($sv as $item):
            [$label, $name, $key] = $item;
        ?>
          <div>
            <label class="block text-sm text-[var(--neutral-text)] mb-1"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></label>
            <input name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" class="input_base"
              value="<?= v($name, $expl[$key] ?? '') ?>">
          </div>
        <?php endforeach; ?>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        <?php
          $ex = [
            ['Inspección general','inspeccion_general','Inspeccion general'],
            ['Cabeza','cabeza','Cabeza'],
            ['Cuello','cuello','Cuello'],
            ['Tórax','torax','Torax'],
            ['Abdomen','abdomen','Abdomen'],
            ['Columna vertebral','columna_vertebral','Columna vertebral'],
            ['Genitales','genitales','Genitales'],
            ['Tacto vaginal','tacto_vaginal','Tacto vaginal'],
            ['Extremidades','extremidades','Extremidades'],
          ];
          foreach($ex as $item):
            [$label, $name, $key] = $item;
        ?>
          <div>
            <label class="block text-sm text-[var(--neutral-text)] mb-1"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></label>
            <input name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" class="input_base"
              value="<?= v($name, $expl[$key] ?? '') ?>">
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- DIAGNÓSTICO -->
    <div class="card p-4">
      <h3 class="mb-4 pb-2 border-b-2 border-[var(--neutral-gray)] uppercase font-semibold">
        Diagnóstico
      </h3>
      <textarea name="diagnostico" class="textarea_base"><?= v('diagnostico', $diag['Diagnostico'] ?? '') ?></textarea>
    </div>

    <!-- PRONÓSTICO -->
    <div class="card p-4">
      <h3 class="mb-4 pb-2 border-b-2 border-[var(--neutral-gray)] uppercase font-semibold">
        Pronóstico / Tratamientos
      </h3>
      <textarea name="pronostico" class="textarea_base"><?= v('pronostico', $pronostico) ?></textarea>
    </div>

    <!-- NORMA -->
    <div class="card p-4">
      <p class="text-sm font-semibold text-[var(--primary-deep)]">
        DICHA ATENCIÓN BASADA EN LA NORMA OFICIAL MEXICANA NOM-004-SSA3-2012, DEL EXPEDIENTE CLÍNICO,
        Y NORMA OFICIAL MEXICANA NOM-007-SSA2-2016.
      </p>
    </div>

    <!-- BOTONES -->
    <div class="flex justify-end gap-4 mb-6">
      <button id="btnCancelar" type="button"
        class="px-8 py-3 border-2 rounded-lg transition-colors hover:opacity-90"
        style="background: white; border-color: var(--neutral-gray); color: var(--neutral-text);">
        Cancelar
      </button>

      <button id="btnGuardar" type="submit"
        class="px-8 py-3 rounded-lg transition-colors hover:opacity-90"
        style="background: var(--primary); color: white;">
        <?= $modo_editar ? 'Guardar cambios' : 'Guardar historia clínica' ?>
      </button>
    </div>

  </form>
</div>

<script>
  lucide.createIcons();

  function actualizarFechaHora() {
    const now = new Date();
    const fecha = now.toLocaleDateString("es-MX", { day: "2-digit", month: "2-digit", year: "numeric" });
    const hora = now.toLocaleTimeString("es-MX", { hour: "2-digit", minute: "2-digit", second: "2-digit", hour12: false });
    document.getElementById("fecha").textContent = fecha;
    document.getElementById("hora").textContent = hora;
  }
  actualizarFechaHora();
  setInterval(actualizarFechaHora, 1000);

  document.getElementById("btnCancelar").onclick = () => {
    if (confirm("¿Deseas cancelar? Se perderán los cambios no guardados.")) {
      document.querySelectorAll("input, textarea").forEach(el => {
        if (el.type !== "hidden" && el.type !== "radio") el.value = "";
      });
    }
  };
</script>
</body>
</html>
