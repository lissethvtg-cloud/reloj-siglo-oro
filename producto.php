<?php
// C:\laragon\www\reloj\producto.php
include('config/db.php');

// Verificar que venga el ID en el QR
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<div style='color:#fff; background:#0a0a0a; height:100vh; display:flex; justify-content:center; align-items:center; font-family:sans-serif;'><h2>Producto no especificado.</h2></div>";
    exit();
}

$id_producto = mysqli_real_escape_string($conexion, $_GET['id']);

// Buscar los datos del reloj y simular o promediar su calificación interna
$resultado = mysqli_query($conexion, "SELECT * FROM productos WHERE id = '$id_producto'");
$producto = mysqli_fetch_assoc($resultado);

if (!$producto) {
    echo "<div style='color:#fff; background:#0a0a0a; height:100vh; display:flex; justify-content:center; align-items:center; font-family:sans-serif;'><h2>El producto no existe en el inventario.</h2></div>";
    exit();
}

// Calificación dinámica: Excelente puntuación (4 o 5 estrellas) basada en su ID
$estrellas_fijas = ($producto['id'] % 2 == 0) ? 5 : 4;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($producto['nombre']); ?> | Siglo & Oro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --dorado: #d4af37;
            --dorado-brillante: #f3e5ab;
            --oscuro-puro: #090909;
            --tarjeta-bg: #121212;
            --borde: #262626;
            --texto-gris: #b3b3b3;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--oscuro-puro);
            color: #ffffff;
            font-family: 'Montserrat', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 15px;
        }

        /* Contenedor adaptado perfectamente a cualquier pantalla móvil */
        .wrapper-premium {
            width: 100%;
            max-width: 420px;
            background: var(--tarjeta-bg);
            border: 1px solid var(--borde);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.8);
            display: flex;
            flex-direction: column;
        }

        /* Encabezado elegante de la marca */
        .brand-header {
            background: #000000;
            padding: 18px;
            text-align: center;
            border-bottom: 1px solid var(--borde);
        }

        .brand-header h1 {
            font-family: 'Cinzel', serif;
            font-size: 1.25rem;
            letter-spacing: 3px;
            margin: 0;
            font-weight: 700;
        }

        .brand-header h1 span {
            color: var(--dorado);
            text-shadow: 0 0 10px rgba(212, 175, 55, 0.2);
        }

        /* Contenedor de Imagen de Producto con Aspect Ratio perfecto */
        .gallery-container {
            width: 100%;
            height: 340px;
            background: #000000;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .gallery-container:hover .product-image {
            transform: scale(1.04);
        }

        /* Insignia de categoría flotante sobre la foto */
        .badge-category {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(0, 0, 0, 0.75);
            border: 1px solid var(--dorado);
            color: var(--dorado);
            padding: 5px 12px;
            font-size: 0.7rem;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 1px;
            border-radius: 4px;
        }

        /* Contenido y detalles técnicos */
        .content-details {
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .title-area h2 {
            font-family: 'Cinzel', serif;
            font-size: 1.6rem;
            font-weight: 500;
            line-height: 1.3;
            color: #ffffff;
            margin-bottom: 6px;
        }

        /* Sistema de Calificación por Estrellas */
        .rating-box {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .stars {
            color: var(--dorado);
            display: flex;
            gap: 2px;
        }

        .rating-value {
            color: var(--texto-gris);
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* Precio del Reloj */
        .price-tag {
            font-size: 2rem;
            font-weight: 300;
            color: var(--dorado-brillante);
            letter-spacing: 1px;
            margin: 4px 0;
        }

        /* Estado del Stock */
        .stock-indicator {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px;
            background: #171717;
            border: 1px solid #222;
            border-radius: 8px;
            font-size: 0.85rem;
            color: #ffffff;
        }

        .stock-indicator i {
            color: var(--dorado);
            font-size: 1.1rem;
        }

        .stock-count {
            font-weight: 600;
            color: #2ecc71;
        }
        
        .stock-low {
            color: #e74c3c;
        }

        /* Botones de Acción */
        .actions-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 10px;
        }

        .btn-action {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 15px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* El botón de volver pasa a ser el principal destacado */
        .btn-main-back {
            background: transparent;
            border: 1px solid var(--dorado);
            color: var(--dorado);
        }

        .btn-main-back:hover {
            background: var(--dorado);
            color: #000000;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.2);
        }
    </style>
</head>
<body>

    <div class="wrapper-premium">
        <div class="brand-header">
            <h1>Siglo & <span>Oro</span></h1>
        </div>
        
        <div class="gallery-container">
            <span class="badge-category"><?php echo htmlspecialchars($producto['categoria']); ?></span>
            <img src="img/<?php echo $producto['archivo_img']; ?>" class="product-image" alt="<?php echo htmlspecialchars($producto['nombre']); ?>" onerror="this.src='https://via.placeholder.com/450?text=Siglo+and+Oro'">
        </div>
        
        <div class="content-details">
            <div class="title-area">
                <h2><?php echo htmlspecialchars($producto['nombre']); ?></h2>
                
                <div class="rating-box">
                    <div class="stars">
                        <?php 
                        for ($m = 1; $m <= 5; $m++) {
                            if ($m <= $estrellas_fijas) {
                                echo '<i class="fas fa-star"></i>';
                            } else {
                                echo '<i class="far fa-star"></i>';
                            }
                        }
                        ?>
                    </div>
                    <span class="rating-value">(<?php echo $estrellas_fijas; ?>.0 Excelencia)</span>
                </div>
            </div>
            
            <div class="price-tag">
                $<?php echo number_format($producto['precio'], 2); ?> <span style="font-size: 0.9rem; color: var(--texto-gris);">MXN</span>
            </div>
            
            <div class="stock-indicator">
                <i class="fas fa-store"></i>
                <div>
                    <span>Disponibilidad en boutique: </span>
                    <span class="stock-count <?php echo ($producto['stock'] <= 3) ? 'stock-low' : ''; ?>">
                        <strong><?php echo $producto['stock']; ?> piezas</strong>
                    </span>
                </div>
            </div>
            
            <div class="actions-group">
                <a href="catalogo.php" class="btn-main-back btn-action">
                    <i class="fas fa-chevron-left"></i> Explorar Colección
                </a>
            </div>
        </div>
    </div>

</body>
</html>