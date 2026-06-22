<?php
session_start();
// Forzar visualización de errores durante el desarrollo
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('../config/db.php'); // Asegúrate de que este archivo use PDO como te indiqué antes
date_default_timezone_set('America/Mexico_City');

$action = $_GET['action'] ?? '';

// --- 1. FUNCIÓN DE REGISTRO ---
if ($action == 'register') {
    $user = $_POST['usuario'];
    $pass = $_POST['password']; // Considera usar password_hash() en el futuro
    $rol  = $_POST['rol'];

    $sql = "INSERT INTO usuarios (username, password, rol) VALUES (?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    
    if ($stmt->execute([$user, $pass, $rol])) {
        header("Location: ../index.php?msg=cuenta_creada");
    } else {
        echo "Error al crear cuenta.";
    }
    exit();
}

// --- 2. FUNCIÓN DE LOGIN ---
if ($action == 'login') {
    $user = $_POST['usuario'];
    $pass = $_POST['password'];
    $rol_form = $_POST['rol'];

    $fecha = date('Y-m-d');
    $hora = date('H:i:s');

    // Consulta usando sentencia preparada
    $sql = "SELECT * FROM usuarios WHERE username = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$user]);
    $datos = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar si el usuario existe y la contraseña coincide
    if ($datos && $pass === $datos['password']) {

        // Validar si es admin intentando entrar como tal
        if ($rol_form == 'admin' && $datos['rol'] != 'admin') {
            $estado = "Fallido (Sin permisos de Admin)";
            $stmtBitacora = $conexion->prepare("INSERT INTO bitacora (usuario, fecha, hora, estado) VALUES (?, ?, ?, ?)");
            $stmtBitacora->execute([$user, $fecha, $hora, $estado]);
            header("Location: ../index.php?error=permisos");
            exit();
        }

        // Login Exitoso
        $_SESSION['usuario'] = $datos['username'];
        $_SESSION['rol'] = $datos['rol'];
        $estado = "Exitoso";
        
        $stmtBitacora = $conexion->prepare("INSERT INTO bitacora (usuario, fecha, hora, estado) VALUES (?, ?, ?, ?)");
        $stmtBitacora->execute([$user, $fecha, $hora, $estado]);

        header($_SESSION['rol'] == 'admin' ? "Location: ../Dashboard.php" : "Location: ../Catalogo.php");
    } else {
        // Login Fallido
        $estado = "Fallido (Datos incorrectos)";
        $stmtBitacora = $conexion->prepare("INSERT INTO bitacora (usuario, fecha, hora, estado) VALUES (?, ?, ?, ?)");
        $stmtBitacora->execute([$user, $fecha, $hora, $estado]);
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
