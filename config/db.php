<?php
// Configuración directa para evitar errores de variables no definidas
$host = 'mysql-2b6cef58-lissethvtg-7b3f.aivencloud.com';
$user = 'avnadmin';
$pass = 'AVNS_ihzjatSKhF24ksM343R'; 
$db   = 'defaultdb';
$port = 20544;

// Intentamos la conexión
$conexion = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conexion) {
    // Si falla, mostramos el error específico
    die("Fallo de conexión: " . mysqli_connect_error());
}
?>