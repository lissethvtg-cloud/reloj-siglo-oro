<?php
$host = "localhost";
$user = "root";
$pass = "21122406"; 
$db   = "tienda_relojes";

$conexion = mysqli_connect($host, $user, $pass, $db);

if (!$conexion) {
    die("Error crítico de conexión: " . mysqli_connect_error());
}
?>