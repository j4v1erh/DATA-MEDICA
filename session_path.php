<?php
// Define carpeta segura para sesiones
ini_set('session.save_path', __DIR__ . '/sessions');

// Crear carpeta si no existe
if (!file_exists(__DIR__ . '/sessions')) {
    mkdir(__DIR__ . '/sessions', 0777, true);
}
