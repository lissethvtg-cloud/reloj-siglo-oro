<?php
// Usamos las variables que configuraste en el panel de Render
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$db   = getenv('DB_NAME');
$port = getenv('DB_PORT');

// Verificamos si las variables fueron cargadas
if (!$host || !$user || !$pass || !$db) {
    die("Error: Las variables de entorno no están configuradas correctamente en Render.");
}

// Conexión
$conexion = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conexion) {
    die("Error de conexión a la base de datos: " . mysqli_connect_error());
}
?>