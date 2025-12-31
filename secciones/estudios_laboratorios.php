<?php
$id = $_GET['id'] ?? 0;
?>

<div class="d-flex justify-content-end mb-3">
    <a href="estudios_agregar.php?id=<?= $id ?>" class="btn btn-success btn-sm me-2">Agregar</a>
    <a href="estudios_eliminar.php?id=<?= $id ?>" class="btn btn-danger btn-sm">Eliminar</a>
</div>

<h4 class="fw-bold mb-3">Estudios y Laboratorios</h4>
<p class="text-muted">Aquí se mostrarán los estudios realizados al paciente.</p>
