<?php
$host = "mysql-2b6cef58-lissethvtg-7b3f.aivencloud.com";
$user = "avnadmin";
$pass = "AVNS_ihzjatSKhF24ksM343R"; 
$db   = "defaultdb";
$port = 20544;

// Inicializamos mysqli para poder configurar el SSL
$conexion = mysqli_init();

if (!$conexion) {
    die("Error al inicializar MySQLi");
}

// Activamos el SSL obligatorio para Aiven (sin necesidad de un archivo físico)
mysqli_ssl_set($conexion, NULL, NULL, NULL, NULL, NULL);

// Realizamos la conexión real incluyendo el puerto de Aiven
$conectar = mysqli_real_connect($conexion, $host, $user, $pass, $db, $port);

if (!$conectar) {
    die("Error crítico de conexión a la nube: " . mysqli_connect_error());
}
?>