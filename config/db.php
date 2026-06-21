<?php
// Definimos las variables de conexión asegurándonos que coincidan exactamente
$host = "mysql-2b6cef58-lissethvtg-7b3f.aivencloud.com";
$user = "avnadmin";
$pass = "AVNS_ihzjatSKhF24ksM343R"; 
$db   = "defaultdb";
$port = 20544;

// Creamos la conexión de forma segura
$conexion = mysqli_init();

if (!$conexion) {
    die("Error al inicializar MySQLi");
}

// Activamos SSL para que Aiven acepte la conexión desde Render
mysqli_ssl_set($conexion, NULL, NULL, NULL, NULL, NULL);

// Conectamos
$conectar = mysqli_real_connect($conexion, $host, $user, $pass, $db, $port);

if (!$conectar) {
    die("Error crítico de conexión: " . mysqli_connect_error());
}
?>