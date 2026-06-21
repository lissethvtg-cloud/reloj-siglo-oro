<?php
// Usaremos variables simples sin funciones extra que puedan fallar
$host = 'mysql-2b6cef58-lissethvtg-7b3f.aivencloud.com';
$user = 'avnadmin';
$pass = 'AVNS_ihzjatSKhF24ksM343R'; 
$db   = 'defaultdb';
$port = 20544;

$conexion = mysqli_init();
// Esto ayuda a que no intente buscar certificados SSL que no tenemos configurados
mysqli_options($conexion, MYSQLI_OPT_SSL_MODE, MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT);

if (!mysqli_real_connect($conexion, $host, $user, $pass, $db, $port)) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>