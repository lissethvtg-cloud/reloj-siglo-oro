<?php
// Obtenemos la URL de conexión desde las variables de entorno de Render
$dbUrl = getenv('DATABASE_URL');

if (!$dbUrl) {
    die("Error: No se encontró la variable DATABASE_URL.");
}

$dbParts = parse_url($dbUrl);

$host = $dbParts['host'];
$port = isset($dbParts['port']) ? $dbParts['port'] : 5432;
$db   = ltrim($dbParts['path'], '/');
$user = $dbParts['user'];
$pass = $dbParts['pass'];

try {
    // Conexión usando PDO (la forma correcta para PostgreSQL)
    $dsn = "pgsql:host=$host;port=$port;dbname=$db;";
    $conexion = new PDO($dsn, $user, $pass);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
