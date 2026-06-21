<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro | Siglo & Oro</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
    <style>
        body { background: #121212; color: white; display: flex; justify-content: center; align-items: center; height: 100vh; font-family: sans-serif; }
        .reg-box { background: #1e1e1e; padding: 30px; border-radius: 10px; border: 1px solid #d4af37; width: 300px; }
        input, select { width: 100%; padding: 10px; margin: 10px 0; background: #000; border: 1px solid #333; color: white; box-sizing: border-box; }
        .btn-reg { background: #d4af37; color: black; border: none; padding: 10px; width: 100%; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
    <div class="reg-box">
        <h2 style="color:#d4af37; text-align:center;">Crear Cuenta</h2>
        <form action="modules/auth.php?action=register" method="POST">
            <input type="text" name="usuario" placeholder="Usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <select name="rol">
                <option value="cliente">Cliente</option>
                <option value="admin">Administrador</option>
            </select>
            <button type="submit" class="btn-reg">REGISTRARSE</button>
        </form>
        <p style="font-size: 0.8rem; text-align:center;">¿Ya tienes cuenta? <a href="index.php" style="color:#d4af37;">Logueate</a></p>
    </div>
</body>
</html>