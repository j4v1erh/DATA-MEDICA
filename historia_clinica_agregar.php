<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$paciente_id = $_GET['id'] ?? 0;

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $sql = "INSERT INTO historia_clinica (
        paciente_id,
        apnp_vivienda, apnp_alimentacion, apnp_higiene, apnp_tabaquismo, apnp_alcoholismo,
        apnp_toxicomanias, apnp_act_fisica, apnp_inmunizaciones, apnp_otros,
        app_enfermedades, app_cirugias, app_fracturas, app_alergias, app_hospitalizaciones,
        app_transfusiones,
        ahf_madre, ahf_padre, ahf_hermanos, ahf_abuelos,
        ef_talla, ef_peso, ef_temperatura, ef_fc, ef_fr, ef_ta, ef_notas,
        motivo_consulta, diagnostico, tratamiento, observaciones
    ) VALUES (
        :paciente_id,
        :apnp_vivienda, :apnp_alimentacion, :apnp_higiene, :apnp_tabaquismo, :apnp_alcoholismo,
        :apnp_toxicomanias, :apnp_act_fisica, :apnp_inmunizaciones, :apnp_otros,
        :app_enfermedades, :app_cirugias, :app_fracturas, :app_alergias, :app_hospitalizaciones,
        :app_transfusiones,
        :ahf_madre, :ahf_padre, :ahf_hermanos, :ahf_abuelos,
        :ef_talla, :ef_peso, :ef_temperatura, :ef_fc, :ef_fr, :ef_ta, :ef_notas,
        :motivo_consulta, :diagnostico, :tratamiento, :observaciones
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':paciente_id' => $paciente_id,

        // APNP
        ':apnp_vivienda' => $_POST['apnp_vivienda'],
        ':apnp_alimentacion' => $_POST['apnp_alimentacion'],
        ':apnp_higiene' => $_POST['apnp_higiene'],
        ':apnp_tabaquismo' => $_POST['apnp_tabaquismo'],
        ':apnp_alcoholismo' => $_POST['apnp_alcoholismo'],
        ':apnp_toxicomanias' => $_POST['apnp_toxicomanias'],
        ':apnp_act_fisica' => $_POST['apnp_act_fisica'],
        ':apnp_inmunizaciones' => $_POST['apnp_inmunizaciones'],
        ':apnp_otros' => $_POST['apnp_otros'],

        // APP
        ':app_enfermedades' => $_POST['app_enfermedades'],
        ':app_cirugias' => $_POST['app_cirugias'],
        ':app_fracturas' => $_POST['app_fracturas'],
        ':app_alergias' => $_POST['app_alergias'],
        ':app_hospitalizaciones' => $_POST['app_hospitalizaciones'],
        ':app_transfusiones' => $_POST['app_transfusiones'],

        // AHF
        ':ahf_madre' => $_POST['ahf_madre'],
        ':ahf_padre' => $_POST['ahf_padre'],
        ':ahf_hermanos' => $_POST['ahf_hermanos'],
        ':ahf_abuelos' => $_POST['ahf_abuelos'],

        // Exploración física
        ':ef_talla' => $_POST['ef_talla'],
        ':ef_peso' => $_POST['ef_peso'],
        ':ef_temperatura' => $_POST['ef_temperatura'],
        ':ef_fc' => $_POST['ef_fc'],
        ':ef_fr' => $_POST['ef_fr'],
        ':ef_ta' => $_POST['ef_ta'],
        ':ef_notas' => $_POST['ef_notas'],

        // Consulta
        ':motivo_consulta' => $_POST['motivo_consulta'],
        ':diagnostico' => $_POST['diagnostico'],
        ':tratamiento' => $_POST['tratamiento'],
        ':observaciones' => $_POST['observaciones']
    ]);

    header("Location: panel_paciente.php?id=" . $paciente_id . "&sec=historia");
    exit;
}

?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Nueva Historia Clínica</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
textarea {
    resize: vertical;
}
</style>

</head>
<body class="bg-light">

<div class="container py-4">
    <a href="panel_paciente.php?id=<?= $paciente_id ?>&sec=historia" class="btn btn-link">&larr; Volver</a>
    <h2 class="fw-bold">Nueva Historia Clínica</h2>

    <form method="post" class="mt-3">

        <!-- APNP -->
        <h4 class="mt-4">Antecedentes Personales No Patológicos</h4>
        <div class="row g-3 mt-1">
            <div class="col-md-6"><label>Vivienda</label><textarea class="form-control" name="apnp_vivienda"></textarea></div>
            <div class="col-md-6"><label>Alimentación</label><textarea class="form-control" name="apnp_alimentacion"></textarea></div>
            <div class="col-md-6"><label>Higiene</label><textarea class="form-control" name="apnp_higiene"></textarea></div>
            <div class="col-md-6"><label>Tabaquismo</label><textarea class="form-control" name="apnp_tabaquismo"></textarea></div>
            <div class="col-md-6"><label>Alcoholismo</label><textarea class="form-control" name="apnp_alcoholismo"></textarea></div>
            <div class="col-md-6"><label>Toxicomanías</label><textarea class="form-control" name="apnp_toxicomanias"></textarea></div>
            <div class="col-md-6"><label>Actividad física</label><textarea class="form-control" name="apnp_act_fisica"></textarea></div>
            <div class="col-md-6"><label>Inmunizaciones</label><textarea class="form-control" name="apnp_inmunizaciones"></textarea></div>
            <div class="col-md-12"><label>Otros</label><textarea class="form-control" name="apnp_otros"></textarea></div>
        </div>

        <!-- APP -->
        <h4 class="mt-4">Antecedentes Personales Patológicos</h4>
        <div class="row g-3">
            <div class="col-md-6"><label>Enfermedades</label><textarea class="form-control" name="app_enfermedades"></textarea></div>
            <div class="col-md-6"><label>Cirugías</label><textarea class="form-control" name="app_cirugias"></textarea></div>
            <div class="col-md-6"><label>Fracturas</label><textarea class="form-control" name="app_fracturas"></textarea></div>
            <div class="col-md-6"><label>Alergias</label><textarea class="form-control" name="app_alergias"></textarea></div>
            <div class="col-md-6"><label>Hospitalizaciones</label><textarea class="form-control" name="app_hospitalizaciones"></textarea></div>
            <div class="col-md-6"><label>Transfusiones</label><textarea class="form-control" name="app_transfusiones"></textarea></div>
        </div>

        <!-- AHF -->
        <h4 class="mt-4">Antecedentes Heredofamiliares</h4>
        <div class="row g-3">
            <div class="col-md-6"><label>Madre</label><textarea class="form-control" name="ahf_madre"></textarea></div>
            <div class="col-md-6"><label>Padre</label><textarea class="form-control" name="ahf_padre"></textarea></div>
            <div class="col-md-6"><label>Hermanos</label><textarea class="form-control" name="ahf_hermanos"></textarea></div>
            <div class="col-md-6"><label>Abuelos</label><textarea class="form-control" name="ahf_abuelos"></textarea></div>
        </div>

        <!-- Exploración física -->
        <h4 class="mt-4">Exploración Física</h4>
        <div class="row g-3">
            <div class="col-md-2"><label>Talla</label><input type="text" name="ef_talla" class="form-control"></div>
            <div class="col-md-2"><label>Peso</label><input type="text" name="ef_peso" class="form-control"></div>
            <div class="col-md-2"><label>Temperatura</label><input type="text" name="ef_temperatura" class="form-control"></div>
            <div class="col-md-2"><label>FC</label><input type="text" name="ef_fc" class="form-control"></div>
            <div class="col-md-2"><label>FR</label><input type="text" name="ef_fr" class="form-control"></div>
            <div class="col-md-2"><label>TA</label><input type="text" name="ef_ta" class="form-control"></div>
            <div class="col-md-12"><label>Notas</label><textarea class="form-control" name="ef_notas"></textarea></div>
        </div>

        <!-- Consulta -->
        <h4 class="mt-4">Motivo de consulta</h4>
        <textarea class="form-control" name="motivo_consulta"></textarea>

        <h4 class="mt-4">Diagnóstico</h4>
        <textarea class="form-control" name="diagnostico"></textarea>

        <h4 class="mt-4">Tratamiento</h4>
        <textarea class="form-control" name="tratamiento"></textarea>

        <h4 class="mt-4">Observaciones</h4>
        <textarea class="form-control" name="observaciones"></textarea>

        <div class="mt-4">
            <button class="btn btn-primary">Guardar Historia Clínica</button>
        </div>

    </form>
</div>

</body>
</html>
