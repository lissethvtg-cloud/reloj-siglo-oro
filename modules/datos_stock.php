<?php
// C:\laragon\www\reloj\modules\datos_stock.php
error_reporting(0);
header('Content-Type: application/json');
require_once('../config/db.php');

if (!$conexion) {
    echo json_encode(["error" => "Error de conexión en base de datos"]);
    exit();
}

// Capturar parámetros de rango de fechas desde el Dashboard (Formato: YYYY-MM-DD)
$inicioInput = $_GET['inicio'] ?? ''; 
$finInput = $_GET['fin'] ?? '';    

$whereBitacora = " WHERE 1=1 ";
$whereCalificaciones = " WHERE 1=1 ";

// Filtro exacto por rango de fechas
if (!empty($inicioInput)) {
    $whereBitacora .= " AND fecha >= '{$inicioInput}' ";
    $whereCalificaciones .= " AND fecha >= '{$inicioInput}' ";
}
if (!empty($finInput)) {
    $whereBitacora .= " AND fecha <= '{$finInput}' ";
    $whereCalificaciones .= " AND fecha <= '{$finInput}' ";
}

// 1. Consulta de productos generales (Inventario actual vivo)
$queryProds = "SELECT nombre, categoria, precio, stock FROM productos ORDER BY id DESC";
$resProds = mysqli_query($conexion, $queryProds);
$productos = [];
$total_productos = 0;
$bajos_stock = 0;

if ($resProds) {
    while ($f = mysqli_fetch_assoc($resProds)) {
        $productos[] = [
            'nombre'    => $f['nombre'],
            'categoria' => $f['categoria'] ? $f['categoria'] : 'Sin Categoría',
            'precio'    => (float)$f['precio'],
            'stock'     => (int)$f['stock']
        ];
        $total_productos++;
        if ((int)$f['stock'] < 3) {
            $bajos_stock++;
        }
    }
}

// 2. Consulta de Bitácora agrupada por Día y Mes (%d/%m)
$queryBitacora = "SELECT DATE_FORMAT(fecha, '%d/%m') as dia_mes, 
            SUM(CASE WHEN estado LIKE '%Exitoso%' THEN 1 ELSE 0 END) as ok,
            SUM(CASE WHEN estado LIKE '%Fallido%' OR estado LIKE '%Error%' THEN 1 ELSE 0 END) as fail
            FROM bitacora 
            $whereBitacora 
            GROUP BY fecha, dia_mes 
            ORDER BY fecha ASC";

$resBitacora = mysqli_query($conexion, $queryBitacora);
$cronologia_bitacora = [];

if ($resBitacora) {
    while ($b = mysqli_fetch_assoc($resBitacora)) {
        $cronologia_bitacora[] = [
            'fecha'    => $b['dia_mes'],
            'exitosos' => (int)$b['ok'],
            'fallidos' => (int)$b['fail']
        ];
    }
}

// 3. Agrupación de Stock por Categorías
$queryCat = "SELECT categoria, SUM(stock) as total_stock FROM productos GROUP BY categoria";
$resCat = mysqli_query($conexion, $queryCat);
$categorias = [];
if ($resCat) {
    while ($c = mysqli_fetch_assoc($resCat)) {
        $categorias[] = [
            'categoria' => $c['categoria'] ? $c['categoria'] : 'Sin Categoría',
            'total'     => (int)$c['total_stock']
        ];
    }
}

// 4. Obtención del Top 5 piezas de mayor valor
$queryTop = "SELECT nombre, precio FROM productos ORDER BY precio DESC LIMIT 5";
$resTop = mysqli_query($conexion, $queryTop);
$exclusivos = [];
if ($resTop) {
    while ($t = mysqli_fetch_assoc($resTop)) {
        $exclusivos[] = [
            'nombre' => $t['nombre'],
            'precio' => (float)$t['precio']
        ];
    }
}

// 5. PROTECCIÓN INTEGRADA: Conteo de Calificaciones del Sistema (1 a 5 estrellas)
$calificaciones = [0, 0, 0, 0, 0]; // Valores base seguros

$queryCalificaciones = "SELECT 
            SUM(CASE WHEN calificacion = 1 THEN 1 ELSE 0 END) as estrella1,
            SUM(CASE WHEN calificacion = 2 THEN 1 ELSE 0 END) as estrella2,
            SUM(CASE WHEN calificacion = 3 THEN 1 ELSE 0 END) as estrella3,
            SUM(CASE WHEN calificacion = 4 THEN 1 ELSE 0 END) as estrella4,
            SUM(CASE WHEN calificacion = 5 THEN 1 ELSE 0 END) as estrella5
            FROM calificaciones 
            $whereCalificaciones";

// El @ previene que la página falle si la tabla aún no existe en MySQL
$resCalificaciones = @mysqli_query($conexion, $queryCalificaciones);

if ($resCalificaciones) {
    $cal = mysqli_fetch_assoc($resCalificaciones);
    $calificaciones = [
        (int)($cal['estrella1'] ?? 0),
        (int)($cal['estrella2'] ?? 0),
        (int)($cal['estrella3'] ?? 0),
        (int)($cal['estrella4'] ?? 0),
        (int)($cal['estrella5'] ?? 0)
    ];
}

// Envío limpio en formato JSON al JavaScript
echo json_encode([
    "productos_total"     => $total_productos,
    "productos_bajos"     => $bajos_stock,
    "productos"           => $productos,
    "cronologia_bitacora" => $cronologia_bitacora,
    "categorias"          => $categorias,
    "exclusivos"          => $exclusivos,
    "calificaciones"      => $calificaciones 
]);

exit();
?>