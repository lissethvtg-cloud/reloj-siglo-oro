<?php
// Lee la variable que pusiste en Render
$dbUrl = getenv('DATABASE_URL');
$dbParts = parse_url($dbUrl);

$host = $dbParts['host'];
$port = $dbParts['port'];
$db   = ltrim($dbParts['path'], '/');
$user = $dbParts['user'];
$pass = $dbParts['pass'];

$dsn = "pgsql:host=$host;port=$port;dbname=$db;";

try {
    // Conexión usando PDO
    $conexion = new PDO($dsn, $user, $pass);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>