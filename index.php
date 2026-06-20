<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Siglo & Oro | Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/estilos.css?v=<?php echo time(); ?>">
</head>
<body class="login-page">
    <div class="card-login">
        <div class="login-header">
            <h1>SIGLO <span>&</span> ORO</h1>
            <p>CRONOMETRANDO TU LEGADO</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div style="background: rgba(231, 76, 60, 0.2); border: 1px solid #e74c3c; color: #e74c3c; padding: 10px; border-radius: 10px; margin-bottom: 20px; font-size: 0.8rem; text-align: center;">
                <?php 
                    if ($_GET['error'] == 'permisos') echo "Acceso denegado: No tienes permisos de administrador.";
                    if ($_GET['error'] == 'datos') echo "Usuario o contraseña incorrectos.";
                ?>
            </div>
        <?php endif; ?>

        <form action="modules/auth.php?action=login" method="POST">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="usuario" placeholder="Usuario" required>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Contraseña" required>
            </div>
            
            <div class="input-group">
                <i class="fas fa-user-tag"></i>
                <select name="rol" required style="width: 100%; padding: 12px 15px 12px 50px; background: rgba(255,255,255,0.05); border: 1px solid #333; color: white; border-radius: 30px; outline: none; appearance: none;">
                    <option value="usuario">Usuario General</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>

            <button type="submit" class="btn-oro">ENTRAR</button>
        </form>
        <div class="login-footer"><a href="registro.php">¿No tienes cuenta? Regístrate aquí</a></div>
    </div>
</body>
</html>