<?php
include('../config/db.php'); // Verifica que esta ruta sea correcta
$action = $_GET['action'] ?? '';

if ($action == 'add') {
    $n = $_POST['nombre'];
    $c = $_POST['categoria'];
    $s = $_POST['stock'];
    // Si no usas imagen en la BD, quita '$i' de la consulta
    $i = $_POST['archivo_img'] ?? 'default.png'; 

    // IMPORTANTE: Los nombres de las columnas deben ser IGUALES a tu BD
    $query = "INSERT INTO productos (nombre, categoria, stock, archivo_img) VALUES ('$n', '$c', '$s', '$i')";
    
    if(mysqli_query($conexion, $query)){
        header("Location: ../Dashboard.php");
    } else {
        // Esto te dirá exactamente QUÉ error tiene la base de datos
        echo "Error: " . mysqli_error($conexion);
    }
}