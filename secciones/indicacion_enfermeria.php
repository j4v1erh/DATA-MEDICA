<?php
$id = $_GET['id'] ?? 0;
?>

<div class="d-flex justify-content-end mb-3">
    <a href="enfermeria_agregar.php?id=<?= $id ?>" class="btn btn-success btn-sm me-2">Agregar</a>
    <a href="enfermeria_eliminar.php?id=<?= $id ?>" class="btn btn-danger btn-sm">Eliminar</a>
</div>

<h4 class="fw-bold mb-3">Indicación de Enfermería</h4>
<p class="text-muted">Aquí se mostrarán las indicaciones de enfermería registradas.</p>
