<?php
require_once __DIR__ . '/db.php';

$paciente_id = $_GET['id'] ?? 0;

// Obtener registros existentes
$stmt = $pdo->prepare("SELECT * FROM historia_clinica WHERE paciente_id = :id ORDER BY fecha DESC");
$stmt->execute([':id' => $paciente_id]);
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Detectar modo agregar
$mood = $_GET['mood'] ?? '';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold">Historia Clínica</h3>

    <div>
        <a href="?id=<?= $paciente_id ?>&sec=historia&mood=add" class="btn btn-primary btn-sm">➕ Agregar</a>
        <a href="?id=<?= $paciente_id ?>&sec=historia&mood=delete" class="btn btn-danger btn-sm">🗑 Eliminar</a>
    </div>
</div>

<hr>

<?php if ($mood === 'add'): ?>

    <!-- FORMULARIO PARA AGREGAR HISTORIA CLÍNICA -->
    <form method="post" action="historia_clinica_guardar.php?id=<?= $paciente_id ?>" class="p-3 bg-white rounded shadow-sm">

        <div class="mb-3">
            <label class="form-label fw-bold">Descripción del evento clínico</label>
            <textarea name="descripcion" class="form-control" rows="3" required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Notas adicionales</label>
            <textarea name="notas" class="form-control" rows="3"></textarea>
        </div>

        <button class="btn btn-success">Guardar registro</button>
        <a href="panel_paciente.php?id=<?= $paciente_id ?>&sec=historia" class="btn btn-secondary">Cancelar</a>
    </form>

<?php else: ?>

    <!-- LISTA DE HISTORIAL -->
    <?php if (empty($registros)): ?>
        <p class="text-muted">No hay registros clínicos aún.</p>
    <?php else: ?>
        <?php foreach ($registros as $r): ?>
            <div class="p-3 mb-3 border rounded bg-white shadow-sm">
                <p class="text-secondary"><strong>Fecha:</strong> <?= $r['fecha'] ?></p>
                <p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($r['descripcion'])) ?></p>

                <?php if ($r['notas']): ?>
                    <p><strong>Notas:</strong> <?= nl2br(htmlspecialchars($r['notas'])) ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

<?php endif; ?>
