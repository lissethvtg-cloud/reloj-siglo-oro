<?php
// C:\laragon\www\reloj\modules\procesar_calificacion_reloj.php
session_start();
header('Content-Type: application/json');

// Si no hay sesión iniciada, rechazar
if (!isset($_SESSION['usuario'])) {
    echo json_encode(["status" => "error", "message" => "Sesión no válida."]);
    exit();
}

// Validar que lleguen los datos obligatorios
if (!isset($_POST['producto_id']) || !isset($_POST['calificacion'])) {
    echo json_encode(["status" => "error", "message" => "Datos incompletos."]);
    exit();
}

// Incluir la conexión a la base de datos cambiando la ruta relativa
require_once('../config/db.php');

$producto_id = intval($_POST['producto_id']);
$calificacion = intval($_POST['calificacion']);
$usuario = $_SESSION['usuario']; // Por si quieres registrar quién votó

// Insertar en tu tabla de calificaciones de productos
// Asegúrate de cambiar 'calificaciones_productos' por el nombre real de tu tabla si es distinto
$sql = "INSERT INTO calificaciones (producto_id, calificacion, fecha) 
        VALUES ($producto_id, $calificacion, NOW())";

if (mysqli_query($conexion, $sql)) {
    echo json_encode(["status" => "success", "message" => "Calificación guardada con éxito."]);
} else {
    echo json_encode(["status" => "error", "message" => "Error al insertar en la base de datos: " . mysqli_error($conexion)]);
}

mysqli_close($conexion);