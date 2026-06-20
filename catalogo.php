<?php
// C:\laragon\www\reloj\Catalogo.php
session_start();
if (!isset($_SESSION['usuario'])) { 
    header("Location: index.php"); 
    exit(); 
}

require_once('config/db.php');

// Obtener productos con stock disponible primero
$sql = "SELECT * FROM productos ORDER BY stock DESC, id DESC";
$resultado = mysqli_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo Luxury | Siglo & Oro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --dorado: #d4af37;
            --dorado-oscuro: #b8860b;
            --oscuro-puro: #0a0a0a;
            --gris-lujo: #141414;
            --blanco-suave: #f4f4f4;
        }

        body.luxury-bg {
            background-color: var(--oscuro-puro);
            color: var(--blanco-suave);
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
        }

        /* HEADER */
        .luxury-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 5%;
            background: rgba(0,0,0,0.9);
            border-bottom: 1px solid #222;
        }

        .logo { font-size: 1.8rem; font-weight: bold; letter-spacing: 3px; }
        .logo span { color: var(--dorado); }

        /* CONTENEDOR PRINCIPAL */
        .content-grid {
            display: flex;
            gap: 40px;
            padding: 40px 5%;
            align-items: flex-start;
        }

        /* SECCIÓN DE PRODUCTOS */
        .products-section { flex: 3; }

        .section-title {
            color: var(--dorado);
            text-transform: uppercase;
            letter-spacing: 4px;
            margin-bottom: 30px;
            border-left: 4px solid var(--dorado);
            padding-left: 15px;
        }

        .products-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }

        /* TARJETA DE PRODUCTO */
        .product-card {
            background: var(--gris-lujo);
            border: 1px solid #222;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            transition: 0.4s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .product-card:hover {
            transform: translateY(-10px);
            border-color: var(--dorado);
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.1);
        }

        .product-card img {
            width: 100%;
            height: 220px;
            object-fit: contain;
            margin-bottom: 20px;
        }

        .product-card h3 { font-size: 1.1rem; margin: 10px 0; }
        .price { color: var(--dorado); font-size: 1.5rem; font-weight: bold; margin-bottom: 5px; }
        .stock-info { font-size: 0.85rem; color: #666; margin-bottom: 10px; }

        /* ESTRELLAS EN PRODUCTOS */
        .product-stars {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            gap: 5px;
        }

        .btn-star {
            background: none;
            border: none;
            color: #444;
            font-size: 1.2rem;
            cursor: pointer;
            transition: transform 0.2s, color 0.2s;
            padding: 0;
        }

        .btn-star:hover {
            transform: scale(1.2);
            color: var(--dorado);
        }

        .btn-add {
            background: transparent;
            color: var(--dorado);
            border: 1.5px solid var(--dorado);
            padding: 12px;
            border-radius: 50px;
            font-weight: bold;
            text-transform: uppercase;
            cursor: pointer;
            transition: 0.3s;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-add:hover {
            background: var(--dorado);
            color: #000;
        }

        /* BARRA LATERAL BOLSA */
        .cart-sidebar {
            flex: 1;
            background: var(--gris-lujo);
            border: 1px solid #333;
            padding: 25px;
            border-radius: 15px;
            position: sticky;
            top: 20px;
            min-width: 320px;
        }

        .cart-sidebar h3 {
            color: var(--dorado);
            border-bottom: 1px solid #333;
            padding-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #222;
            font-size: 0.9rem;
        }

        .cart-total {
            margin: 20px 0;
            font-size: 1.3rem;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            color: var(--dorado);
        }

        /* FORMULARIO DE ENVÍO EN SIDEBAR */
        .checkout-form {
            margin-top: 20px;
            border-top: 1px solid #333;
            padding-top: 20px;
        }

        .checkout-form label {
            display: block;
            font-size: 0.8rem;
            color: var(--dorado);
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .input-email {
            width: 100%;
            padding: 12px;
            background: #000;
            border: 1px solid #333;
            color: white;
            border-radius: 5px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }

        .input-email:focus {
            border-color: var(--dorado);
            outline: none;
        }

        .btn-checkout {
            display: block;
            width: 100%;
            background: var(--dorado);
            color: #000;
            text-align: center;
            padding: 15px;
            border-radius: 8px;
            border: none;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-checkout:hover {
            background: var(--dorado-oscuro);
        }

        .btn-empty {
            display: block;
            text-align: center;
            color: #e74c3c;
            text-decoration: none;
            font-size: 0.8rem;
            margin-top: 15px;
        }

        /* OVERLAY DE ÉXITO */
        .success-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.9);
            display: flex; justify-content: center; align-items: center;
            z-index: 9999;
        }
        .success-card {
            background: #1a1a1a;
            border: 2px solid var(--dorado);
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            max-width: 400px;
        }
        .success-card i { font-size: 4rem; color: var(--dorado); margin-bottom: 20px; }
        .btn-continue {
            background: var(--dorado);
            color: #000;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            margin-top: 20px;
        }

        /* PIE DE PÁGINA: MÓDULO DE CALIFICACIÓN GENERAL CON COMENTARIOS */
        .luxury-footer {
            background: var(--gris-lujo);
            border-top: 1px solid #222;
            padding: 40px 20px;
            text-align: center;
            margin-top: 50px;
        }

        .luxury-footer h3 {
            color: var(--dorado);
            margin: 0 0 10px 0;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-size: 1.3rem;
        }

        .luxury-footer p {
            color: #888;
            font-size: 0.9rem;
            margin: 0;
        }

        .stars-container {
            margin: 20px 0;
            display: inline-flex;
            gap: 12px;
        }

        .star-control {
            font-size: 2.3rem;
            color: #333; /* Estrella vacía */
            cursor: pointer;
            transition: transform 0.2s, color 0.2s;
        }

        .star-control:hover {
            transform: scale(1.15);
        }

        /* Clase interactiva para pintar las estrellas en dorado */
        .star-control.active {
            color: var(--dorado);
            text-shadow: 0 0 12px rgba(212, 175, 55, 0.3);
        }

        .feedback-alert {
            background: rgba(46, 204, 113, 0.1);
            border: 1px solid #2ecc71;
            color: #2ecc71;
            padding: 12px 25px;
            border-radius: 30px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: bold;
            margin-top: 15px;
            font-size: 0.95rem;
            animation: slideUp 0.4s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 1024px) {
            .content-grid { flex-direction: column; }
            .cart-sidebar { width: 100%; position: static; min-width: auto; }
        }
    </style>
</head>
<body class="luxury-bg">

    <?php if (isset($_GET['success'])): ?>
    <div class="success-overlay">
        <div class="success-card">
            <i class="fas fa-check-circle"></i>
            <h2>COMPRA FINALIZADA</h2>
            <p>Gracias por confiar en el lujo de Siglo & Oro. El ticket ha sido enviado a su correo.</p>
            <a href="Catalogo.php" class="btn-continue">CONTINUAR</a>
        </div>
    </div>
    <?php endif; ?>

    <header class="luxury-header">
        <div class="logo">SIGLO <span>&</span> ORO</div>
        <nav style="display:flex; align-items:center; gap:20px;">
            <span><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
            <a href="modules/auth.php?action=logout" style="color:white;"><i class="fas fa-sign-out-alt"></i></a>
        </nav>
    </header>

    <main class="content-grid">
        <section class="products-section">
            <h2 class="section-title">Colección Exclusiva</h2>
            <div class="products-container">
                <?php while($p = mysqli_fetch_assoc($resultado)): ?>
                    <div class="product-card">
                        <img src="img/<?php echo $p['archivo_img']; ?>" alt="Reloj" onerror="this.src='https://via.placeholder.com/250x250/000000/ffffff?text=Reloj'">
                        <h3><?php echo $p['nombre']; ?></h3>
                        <p class="price">$<?php echo number_format($p['precio'], 2); ?></p>
                        <p class="stock-info">Disponibles: <?php echo $p['stock']; ?></p>
                        
                        <div class="product-stars">
                            <button type="button" class="btn-star" onclick="calificarProducto(<?php echo $p['id']; ?>, 1)"><i class="far fa-star"></i></button>
                            <button type="button" class="btn-star" onclick="calificarProducto(<?php echo $p['id']; ?>, 2)"><i class="far fa-star"></i></button>
                            <button type="button" class="btn-star" onclick="calificarProducto(<?php echo $p['id']; ?>, 3)"><i class="far fa-star"></i></button>
                            <button type="button" class="btn-star" onclick="calificarProducto(<?php echo $p['id']; ?>, 4)"><i class="far fa-star"></i></button>
                            <button type="button" class="btn-star" onclick="calificarProducto(<?php echo $p['id']; ?>, 5)"><i class="far fa-star"></i></button>
                        </div>
                        
                        <?php if($p['stock'] > 0): ?>
                            <form action="modules/carrito.php?action=add" method="POST">
                                <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                <input type="hidden" name="nombre" value="<?php echo $p['nombre']; ?>">
                                <input type="hidden" name="precio" value="<?php echo $p['precio']; ?>">
                                <button type="submit" class="btn-add">
                                    <i class="fas fa-shopping-bag"></i> AÑADIR A LA BOLSA
                                </button>
                            </form>
                        <?php else: ?>
                            <button class="btn-add" style="opacity:0.5; cursor:not-allowed;" disabled>AGOTADO</button>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>

        <aside class="cart-sidebar">
            <h3><i class="fas fa-shopping-bag"></i> MI BOLSA</h3>
            <div class="cart-items">
                <?php if(!empty($_SESSION['carrito'])): 
                    $total = 0;
                    foreach($_SESSION['carrito'] as $item): 
                        $subtotal = $item['precio'] * $item['cantidad'];
                        $total += $subtotal;
                ?>
                    <div class="cart-item">
                        <span><?php echo $item['cantidad']; ?>x <?php echo $item['nombre']; ?></span>
                        <span style="color:var(--dorado);">$<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                <?php endforeach; ?>

                <div class="cart-total">
                    <span>TOTAL</span>
                    <span>$<?php echo number_format($total, 2); ?></span>
                </div>

                <div class="checkout-form">
                    <form action="modules/carrito.php?action=finalizar" method="POST">
                        <label for="email_cliente">Enviar ticket a:</label>
                        <input type="email" name="email_cliente" id="email_cliente" 
                               class="input-email" placeholder="ejemplo@correo.com" required>
                        
                        <button type="submit" class="btn-checkout">
                            FINALIZAR COMPRA
                        </button>
                    </form>
                    <a href="modules/carrito.php?action=empty" class="btn-empty">Vaciar Carrito</a>
                </div>

                <?php else: ?>
                    <p style="color:#555; text-align:center; padding:20px;">Tu bolsa está vacía.</p>
                <?php endif; ?>
            </div>
        </aside>
    </main>

    <footer class="luxury-footer">
        <h3>¿Qué te pareció nuestro sistema?</h3>
        <p>Tu opinión nos ayuda a mantener los estándares de excelencia de Siglo & Oro</p>
        
        <div class="stars-container">
            <i class="far fa-star star-control" data-value="1"></i>
            <i class="far fa-star star-control" data-value="2"></i>
            <i class="far fa-star star-control" data-value="3"></i>
            <i class="far fa-star star-control" data-value="4"></i>
            <i class="far fa-star star-control" data-value="5"></i>
        </div>

        <div id="review-form-wrapper" style="display: none; max-width: 500px; margin: 20px auto; animation: slideUp 0.4s ease-out;">
            <textarea id="comentario-sistema" class="input-email" rows="3" 
                      placeholder="Escribe tu experiencia con el sistema... (Opcional)" 
                      style="resize: none; border-radius: 8px; padding: 15px;"></textarea>
            
            <button type="button" id="btn-enviar-opinion" class="btn-checkout" style="padding: 12px; font-size: 0.9rem;">
                ENVIAR VALORACIÓN
            </button>
        </div>

        <div id="alert-box" style="display: none;">
            <div class="feedback-alert">
                <i class="fas fa-check-circle"></i> ¡Muchas gracias! Tu opinión y las <span id="stars-count">0</span> estrellas han sido registradas.
            </div>
        </div>
    </footer>

    <script>
        // --- NUEVA FUNCIÓN: ENVIAR CALIFICACIÓN EXCLUSIVA DE UN RELOJ ---
        function calificarProducto(idProducto, estrellas) {
            let datos = new FormData();
            datos.append('producto_id', idProducto);
            datos.append('calificacion', estrellas);

            fetch('modules/procesar_calificacion.php', { // Apunta a tu archivo de productos
                method: 'POST',
                body: datos
            })
            .then(response => response.json())
            .then(res => {
                if (res.status === "success") {
                    alert("¡Gracias! Has calificado este reloj con " + estrellas + " estrellas.");
                } else {
                    alert("Error: " + res.message);
                }
            })
            .catch(err => {
                console.error("Error:", err);
                alert("No se pudo registrar la calificación del reloj.");
            });
        }

        // --- LÓGICA ANTERIOR: CALIFICACIÓN GENERAL DEL FOOTER ---
        const listaEstrellas = document.querySelectorAll('.star-control');
        const reviewWrapper = document.getElementById('review-form-wrapper');
        const alertBox = document.getElementById('alert-box');
        const starsCount = document.getElementById('stars-count');
        const btnEnviar = document.getElementById('btn-enviar-opinion');
        let calificacionActual = 0;

        listaEstrellas.forEach(star => {
            star.addEventListener('mouseover', function() {
                const valorHover = parseInt(this.getAttribute('data-value'));
                renderizarEstrellas(valorHover);
            });

            star.addEventListener('mouseout', function() {
                renderizarEstrellas(calificacionActual);
            });

            star.addEventListener('click', function() {
                calificacionActual = parseInt(this.getAttribute('data-value'));
                renderizarEstrellas(calificacionActual);
                alertBox.style.display = 'none'; 
                reviewWrapper.style.display = 'block';
            });
        });

        btnEnviar.addEventListener('click', function() {
            const comentario = document.getElementById('comentario-sistema').value;
            
            let datos = new FormData();
            datos.append('calificacion', calificacionActual);
            datos.append('comentario', comentario);

            fetch('modules/procesar_calificacion.php', {
                method: 'POST',
                body: datos
            })
            .then(response => response.json())
            .then(res => {
                if (res.status === "success") {
                    reviewWrapper.style.display = 'none';
                    starsCount.innerText = calificacionActual;
                    alertBox.style.display = 'block';
                    document.getElementById('comentario-sistema').value = '';
                } else {
                    alert("Error del servidor: " + res.message);
                }
            })
            .catch(err => {
                console.error("Error en la petición Fetch:", err);
                alert("Hubo un problema de conexión al enviar tu valoración.");
            });
        });

        function renderizarEstrellas(limite) {
            listaEstrellas.forEach(star => {
                const valorEstrella = parseInt(star.getAttribute('data-value'));
                if (valorEstrella <= limite) {
                    star.classList.remove('far');
                    star.classList.add('fas', 'active');
                } else {
                    star.classList.remove('fas', 'active');
                    star.classList.add('far');
                }
            });
        }
    </script>
</body>
</html>