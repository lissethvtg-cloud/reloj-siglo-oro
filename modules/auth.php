<?php
session_start();
// Forzar visualización de errores para evitar pantallas blancas durante el desarrollo
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('../config/db.php');
date_default_timezone_set('America/Mexico_City'); // Ajusta a tu zona horaria

$action = $_GET['action'] ?? '';

// --- 1. FUNCIÓN DE REGISTRO (Crear Cuenta) ---
if ($action == 'register') {
    $user = mysqli_real_escape_string($conexion, $_POST['usuario']);
    $pass = mysqli_real_escape_string($conexion, $_POST['password']);
    $rol  = $_POST['rol'];

    $sql = "INSERT INTO usuarios (username, password, rol) VALUES ('$user', '$pass', '$rol')";
    
    if (mysqli_query($conexion, $sql)) {
        header("Location: ../index.php?msg=cuenta_creada");
    } else {
        echo "Error al crear cuenta: " . mysqli_error($conexion);
    }
    exit();
}

// --- 2. FUNCIÓN DE LOGIN (Con Bitácora) ---
if ($action == 'login') {
    $user = mysqli_real_escape_string($conexion, $_POST['usuario']);
    $pass = mysqli_real_escape_string($conexion, $_POST['password']);
    $rol_form = $_POST['rol'];

    // Datos para la bitácora
    $fecha = date('Y-m-d');
    $hora = date('H:i:s');

    $sql = "SELECT * FROM usuarios WHERE username = '$user' AND password = '$pass'";
    $resultado = mysqli_query($conexion, $sql);

    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $datos = mysqli_fetch_assoc($resultado);

        // Validar si es admin intentando entrar como tal
        if ($rol_form == 'admin' && $datos['rol'] != 'admin') {
            $estado = "Fallido (Sin permisos de Admin)";
            mysqli_query($conexion, "INSERT INTO bitacora (usuario, fecha, hora, estado) VALUES ('$user', '$fecha', '$hora', '$estado')");
            header("Location: ../index.php?error=permisos");
            exit();
        }

        // Login Exitoso
        $_SESSION['usuario'] = $datos['username'];
        $_SESSION['rol'] = $datos['rol'];
        $estado = "Exitoso";
        
        mysqli_query($conexion, "INSERT INTO bitacora (usuario, fecha, hora, estado) VALUES ('$user', '$fecha', '$hora', '$estado')");

        header($_SESSION['rol'] == 'admin' ? "Location: ../Dashboard.php" : "Location: ../Catalogo.php");
    } else {
        // Login Fallido
        $estado = "Fallido (Datos incorrectos)";
        mysqli_query($conexion, "INSERT INTO bitacora (usuario, fecha, hora, estado) VALUES ('$user', '$fecha', '$hora', '$estado')");
        header("Location: ../index.php?error=datos");
    }
    exit();
}

// --- 3. CERRAR SESIÓN ---
if ($action == 'logout') {
    session_destroy();
    header("Location: ../index.php");
    exit();
}
?>