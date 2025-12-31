<?php
// secciones/historia_clinica/historia_clinica.php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../db.php';

$paciente_id = isset($id) ? (int)$id : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
if ($paciente_id <= 0) {
  echo "<div class='alert alert-danger'>Paciente no especificado.</div>";
  return;
}

$stmt = $pdo->prepare("
  SELECT id, fecha, diagnostico_text, medico_elabora
  FROM historia_clinica
  WHERE paciente_id = :pac
  ORDER BY fecha DESC, id DESC
");
$stmt->execute([':pac' => $paciente_id]);
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>

<div class="d-flex justify-content-end gap-2 mb-3">
  <a href="secciones/historia_clinica/historia_clinica_agregar.php?id=<?= (int)$paciente_id ?>"
     class="btn btn-primary btn-sm">Agregar</a>

  <button type="button" class="btn btn-secondary btn-sm" onclick="hcVer(<?= (int)$paciente_id ?>)">Ver</button>
  <button type="button" class="btn btn-warning btn-sm" onclick="hcEditar(<?= (int)$paciente_id ?>)">Editar</button>
  <button type="button" class="btn btn-danger btn-sm" onclick="hcEliminar(<?= (int)$paciente_id ?>)">Eliminar</button>
  <button type="button" class="btn btn-outline-dark btn-sm" disabled title="Imprimir pendiente">Imprimir</button>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <h5 class="fw-bold mb-3">Historia clínica</h5>

    <?php if (empty($registros)): ?>
      <p class="text-muted mb-0">Aún no hay registros de historia clínica para este paciente.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead>
            <tr>
              <th style="width:60px;">Sel.</th>
              <th>Fecha y hora</th>
              <th>Diagnóstico</th>
              <th>Médico que elabora</th>
              <th style="width:90px;">ID</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($registros as $r): ?>
              <tr>
                <td>
                  <input type="checkbox" class="form-check-input historia-check" value="<?= (int)$r['id'] ?>">
                </td>
                <td><?= h($r['fecha']) ?></td>
                <td><?= h($r['diagnostico_text'] ?: '—') ?></td>
                <td><?= h($r['medico_elabora'] ?: '—') ?></td>
                <td>#<?= (int)$r['id'] ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
function hcSeleccion() {
  const checks = document.querySelectorAll('.historia-check:checked');
  return Array.from(checks).map(c => c.value);
}

function hcVer(pacId) {
  const ids = hcSeleccion();
  if (ids.length === 0) return alert('Selecciona un registro para ver.');
  if (ids.length > 1) return alert('Selecciona solo un registro para ver.');
  window.location.href = 'secciones/historia_clinica/historia_clinica_ver.php?id=' + ids[0] + '&pac=' + pacId;
}

function hcEditar(pacId) {
  const ids = hcSeleccion();
  if (ids.length === 0) return alert('Selecciona un registro para editar.');
  if (ids.length > 1) return alert('Selecciona solo un registro para editar.');
  window.location.href = 'secciones/historia_clinica/historia_clinica_editar.php?id=' + ids[0] + '&pac=' + pacId;
}

function hcEliminar(pacId) {
  const ids = hcSeleccion();
  if (ids.length === 0) return alert('Selecciona al menos un registro para eliminar.');
  if (!confirm('¿Eliminar los registros seleccionados? Esta acción no se puede deshacer.')) return;
  window.location.href = 'secciones/historia_clinica/historia_clinica_eliminar.php?pac=' + pacId + '&ids=' + ids.join(',');
}
</script>
