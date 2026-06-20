<?php
$host = "mysql-2b6cef58-lissethvtg-7b3f.aivencloud.com";
$user = "avnadmin";
$pass = "AVNS_oxWuaB71vtR8XESkjNh"; 
$db   = "defaultdb";
$port = 20544; 

$conexion = mysqli_connect($host, $user, $password, $database, $port);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>
