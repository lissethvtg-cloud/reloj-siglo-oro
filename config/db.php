<?php
// Usamos las variables de entorno configuradas en Render
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$db   = getenv('DB_NAME');
$port = getenv('DB_PORT');

// Inicializamos la conexión
$conexion = mysqli_init();

// Aquí le decimos a mysqli que use el certificado ca.pem que subiste
// Asegúrate de que el archivo ca.pem esté en la misma carpeta 'config'
mysqli_ssl_set($conexion, NULL, NULL, __DIR__ . '/ca.pem', NULL, NULL);

// Conectamos
if (!mysqli_real_connect($conexion, $host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Error de conexión con SSL: " . mysqli_connect_error());
}
?>