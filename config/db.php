<?php
// Definimos las variables de conexión asegurándonos que coincidan exactamente
$host = "mysql-2b6cef58-lissethvtg-7b3f.aivencloud.com";
$user = "avnadmin";
$pass = "AVNS_ihzjatSKhF24ksM343R"; 
$db   = "defaultdb";
$port = 20544;

// Conexión estándar sin forzar SSL
$conexion = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>