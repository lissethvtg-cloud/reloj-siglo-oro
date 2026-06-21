<?php
// C:\laragon\www\reloj\Dashboard.php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: index.php");
    exit();
}
include('config/db.php');

$action = $_GET['action'] ?? '';

if (($action == 'add' || $action == 'update') && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $n = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $c = mysqli_real_escape_string($conexion, $_POST['categoria']);
    $p = $_POST['precio'];
    $s = $_POST['stock'];
    $i = mysqli_real_escape_string($conexion, $_POST['archivo_img']);

    if ($action == 'add') {
        $query = "INSERT INTO productos (nombre, categoria, precio, stock, archivo_img) VALUES ('$n', '$c', '$p', '$s', '$i')";
    } else {
        $id = $_POST['id'];
        $query = "UPDATE productos SET nombre='$n', categoria='$c', precio='$p', stock='$s', archivo_img='$i' WHERE id='$id'";
    }
    
    mysqli_query($conexion, $query);
    header("Location: Dashboard.php");
    exit();
}

if ($action == 'delete') {
    $id = $_GET['id'];
    mysqli_query($conexion, "DELETE FROM productos WHERE id = '$id'");
    header("Location: Dashboard.php");
    exit();
}

$res_bitacora = mysqli_query($conexion, "SELECT * FROM bitacora ORDER BY id DESC LIMIT 10");
$res_productos = mysqli_query($conexion, "SELECT * FROM productos ORDER BY id DESC");

$res_cats = mysqli_query($conexion, "SELECT DISTINCT categoria FROM productos WHERE categoria != ''");
$categorias = [];
while($cat = mysqli_fetch_assoc($res_cats)) { $categorias[] = $cat['categoria']; }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Administrativo | Siglo & Oro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        :root { --dorado: #d4af37; --oscuro: #0a0a0a; --gris-panel: #161616; --exito: #2ecc71; --error: #e74c3c; --azul: #3498db; --morado: #9b59b6; --naranja: #e67e22; }
        body { background: var(--oscuro); color: #fff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; display: flex; }
        
        .sidebar { width: 260px; background: #000; height: 100vh; position: fixed; top: 0; left: 0; border-right: 2px solid var(--dorado); display: flex; flex-direction: column; justify-content: space-between; z-index: 1000; }
        .sidebar-brand { padding: 30px 20px; text-align: center; border-bottom: 1px solid #222; }
        .sidebar-brand h1 { font-size: 1.3rem; margin: 0; color: #fff; font-weight: bold; text-transform: uppercase; }
        .sidebar-brand h1 span { color: var(--dorado); }
        .sidebar-menu { list-style: none; padding: 20px 0; margin: 0; flex-grow: 1; }
        .sidebar-item { padding: 14px 25px; display: flex; align-items: center; gap: 15px; color: #aaa; text-decoration: none; font-weight: 600; cursor: pointer; transition: 0.3s; font-size: 0.95rem; }
        .sidebar-item:hover { background: #111; color: var(--dorado); }
        .sidebar-item.active { background: rgba(212, 175, 55, 0.1); color: var(--dorado); border-left: 4px solid var(--dorado); padding-left: 21px; }
        .sidebar-footer { padding: 20px; display: flex; flex-direction: column; gap: 10px; border-top: 1px solid #222; }
        
        .main-content { margin-left: 260px; flex-grow: 1; padding: 40px; min-height: 100vh; box-sizing: border-box; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        .card { background: var(--gris-panel); padding: 25px; border-radius: 12px; border: 1px solid #333; margin-bottom: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h2 { color: var(--dorado); margin-top: 0; border-left: 4px solid var(--dorado); padding-left: 15px; margin-bottom: 25px; }
        .cards-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .kpi-card { background: var(--gris-panel); padding: 20px; border-radius: 8px; border: 1px solid #333; display: flex; align-items: center; justify-content: space-between; border-left: 4px solid var(--dorado); }
        .kpi-info h3 { font-size: 24px; color: #fff; margin: 0 0 5px 0; }
        .kpi-info p { font-size: 14px; color: #aaa; margin: 0; }
        .kpi-icon { font-size: 32px; color: var(--dorado); opacity: 0.7; }
        
        #contenedor-reporte-graficas { display: block; }
        .charts-multigrid { display: grid; grid-template-columns: repeat(auto-fit, minmax(48%, 1fr)); gap: 25px; margin-bottom: 40px; }
        .live-indicator { font-size: 12px; color: var(--exito); font-weight: bold; display: flex; align-items: center; gap: 5px; }
        
        .filter-bar { background: #000; border: 1px solid #333; border-radius: 8px; padding: 15px; margin-bottom: 25px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap; }
        .filter-bar .btn-pdf { margin-left: auto; }
        .filter-group { display: flex; align-items: center; gap: 10px; }
        .filter-group label { color: #aaa; font-size: 0.9rem; }
        .input-filter { background: var(--gris-panel); border: 1px solid #444; color: #fff; padding: 8px 12px; border-radius: 5px; outline: none; }
        
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        .input-group label { display: block; margin-bottom: 8px; color: #aaa; font-size: 0.9rem; }
        .input-style { background: #000; border: 1px solid #444; color: #fff; padding: 12px; border-radius: 6px; width: 100%; box-sizing: border-box; outline: none; }
        .btn-main { background: var(--dorado); border: none; padding: 15px; border-radius: 6px; color: #000; font-weight: bold; cursor: pointer; transition: 0.3s; margin-top: 25px; }
        .btn-filter { background: var(--dorado); color: #000; border: none; padding: 8px 15px; border-radius: 5px; font-weight: bold; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-pdf { background: #e74c3c; color: #fff; }

        .card-header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-download-pdf { background: rgba(231, 76, 60, 0.1); border: 1px solid var(--error); color: var(--error); padding: 6px 12px; border-radius: 4px; font-size: 0.8rem; cursor: pointer; font-weight: bold; display: inline-flex; align-items: center; gap: 6px; }
        .btn-download-pdf:hover { background: var(--error); color: #fff; }

        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { padding: 15px; border-bottom: 2px solid #333; color: var(--dorado); text-transform: uppercase; font-size: 0.85rem; }
        td { padding: 15px; border-bottom: 1px solid #222; vertical-align: middle; }
        .img-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; }
        .btn-catalog { background: rgba(212, 175, 55, 0.1); border: 1px solid var(--dorado); color: var(--dorado); padding: 8px 15px; border-radius: 5px; text-decoration: none; font-weight: bold; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-logout { background: transparent; border: 1px solid var(--error); color: var(--error); padding: 8px 15px; border-radius: 5px; text-decoration: none; font-weight: bold; text-align: center; }
        .status-badge { padding: 6px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; display: inline-flex; align-items: center; gap: 6px; }
        .status-exito { background: rgba(46, 204, 113, 0.15); color: var(--exito); border: 1px solid var(--exito); }
        .status-error { background: rgba(231, 76, 60, 0.15); color: var(--error); border: 1px solid var(--error); }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-brand">
            <h1>Siglo & <span>Oro</span></h1>
            <small style="opacity: 0.5; font-size: 0.75rem; letter-spacing: 1px;">ADMIN PANEL</small>
        </div>
        <div class="sidebar-menu">
            <div class="sidebar-item active" id="menu-stats" onclick="cambiarVista('vista-stats', this)"><i class="fas fa-chart-pie"></i> Estadísticas</div>
            <div class="sidebar-item" id="menu-registro" onclick="cambiarVista('vista-registro', this)"><i class="fas fa-plus-circle"></i> Registrar Producto</div>
            <div class="sidebar-item" onclick="cambiarVista('vista-inventario', this)"><i class="fas fa-boxes"></i> Inventario</div>
            <div class="sidebar-item" onclick="cambiarVista('vista-bitacora', this)"><i class="fas fa-history"></i> Bitácora</div>
        </div>
        <div class="sidebar-footer">
            <a href="Catalogo.php" class="btn-catalog"><i class="fas fa-store"></i> VER STORE</a>
            <a href="modules/auth.php?action=logout" class="btn-logout"><i class="fas fa-sign-out-alt"></i> SALIR</a>
        </div>
    </div>

    <div class="main-content">
        <div id="vista-stats" class="tab-content active">
            <div class="filter-bar">
                <div class="filter-group">
                    <i class="fas fa-calendar-alt" style="color: var(--dorado);"></i>
                    <label>Desde:</label>
                    <input type="date" id="fecha-inicio" class="input-filter">
                </div>
                <div class="filter-group">
                    <label>Hasta:</label>
                    <input type="date" id="fecha-fin" class="input-filter">
                </div>
                <button type="button" class="btn-filter" onclick="sincronizarServidor()"><i class="fas fa-filter"></i> Filtrar</button>
                <button type="button" class="btn-filter" style="background: #444; color: #fff;" onclick="limpiarFiltros()"><i class="fas fa-sync"></i> Reset</button>
                <button type="button" class="btn-filter btn-pdf" onclick="exportarGraficasPDF()"><i class="fas fa-file-pdf"></i> Descargar Todo (PDF)</button>
            </div>

            <div class="cards-grid">
                <div class="kpi-card">
                    <div class="kpi-info">
                        <h3 id="total-productos">--</h3>
                        <p>Modelos en Sistema</p>
                    </div>
                    <div class="kpi-icon"><i class="fas fa-clock"></i></div>
                </div>
                <div class="kpi-card" style="border-left-color: var(--error);">
                    <div class="kpi-info">
                        <h3 id="alerta-stock" style="color: var(--error);">--</h3>
                        <p>Bajo Inventario (&lt; 3 u.)</p>
                    </div>
                    <div class="kpi-icon" style="color: var(--error);"><i class="fas fa-exclamation-triangle"></i></div>
                </div>
            </div>

            <div id="contenedor-reporte-graficas">
                <div class="card" id="card-graficoStock">
                    <div class="card-header-flex">
                        <h2 style="margin: 0; border: none; padding: 0;"><i class="fas fa-chart-bar"></i> Monitoreo de Stock General por Producto</h2>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <span class="live-indicator" id="txt-vivo"><i class="fas fa-circle" style="animation: pulse 1.5s infinite;"></i> VIVO (3s)</span>
                            <button class="btn-download-pdf" onclick="descargarGraficaPDF('graficoStock', 'Monitoreo de Stock General', 'Stock_General')">
                                <i class="fas fa-file-pdf"></i> Exportar PDF
                            </button>
                        </div>
                    </div>
                    <div style="position: relative; width: 100%; height: 260px;">
                        <canvas id="graficoStock"></canvas>
                    </div>
                </div>

                <div class="charts-multigrid">
                    <div class="card" id="card-chartCategorias">
                        <div class="card-header-flex">
                            <h3 style="margin:0;"><i class="fas fa-pie-chart"></i> Stock por Categoría</h3>
                            <button class="btn-download-pdf" onclick="descargarGraficaPDF('chartCategorias', 'Distribución de Stock por Categoría', 'Stock_Categorias')">
                                <i class="fas fa-file-pdf"></i> Exportar PDF
                            </button>
                        </div>
                        <div style="position: relative; width: 100%; height: 220px;">
                            <canvas id="chartCategorias"></canvas>
                        </div>
                    </div>

                    <div class="card" id="card-chartFinanzas">
                        <div class="card-header-flex">
                            <h3 style="margin:0;"><i class="fas fa-wallet"></i> Valor de Inventario ($ MXN)</h3>
                            <button class="btn-download-pdf" onclick="descargarGraficaPDF('chartFinanzas', 'Valor Financiero del Inventario', 'Valor_Inventario')">
                                <i class="fas fa-file-pdf"></i> Exportar PDF
                            </button>
                        </div>
                        <div style="position: relative; width: 100%; height: 220px;">
                            <canvas id="chartFinanzas"></canvas>
                        </div>
                    </div>

                    <div class="card" id="card-chartBitacora">
                        <div class="card-header-flex">
                            <h3 style="margin:0;"><i class="fas fa-calendar-day"></i> Historial de Accesos</h3>
                            <button class="btn-download-pdf" onclick="descargarGraficaPDF('chartBitacora', 'Historial Crítico de Accesos', 'Historial_Accesos')">
                                <i class="fas fa-file-pdf"></i> Exportar PDF
                            </button>
                        </div>
                        <div style="position: relative; width: 100%; height: 220px;">
                            <canvas id="chartBitacora"></canvas>
                        </div>
                    </div>

                    <div class="card" id="card-chartExclusivos">
                        <div class="card-header-flex">
                            <h3 style="margin:0;"><i class="fas fa-crown"></i> Top 5 Piezas de Mayor Valor</h3>
                            <button class="btn-download-pdf" onclick="descargarGraficaPDF('chartExclusivos', 'Top 5 Piezas de Mayor Valor', 'Top_Piezas_Valor')">
                                <i class="fas fa-file-pdf"></i> Exportar PDF
                            </button>
                        </div>
                        <div style="position: relative; width: 100%; height: 220px;">
                            <canvas id="chartExclusivos"></canvas>
                        </div>
                    </div>

                    <div class="card" id="card-chartCalificaciones">
                        <div class="card-header-flex">
                            <h3 style="margin:0;"><i class="fas fa-star" style="color: var(--naranja);"></i> Satisfacción del Catálogo (Reseñas de Productos)</h3>
                            <button class="btn-download-pdf" onclick="descargarGraficaPDF('chartCalificaciones', 'Métricas de Satisfacción de Productos', 'Calificaciones_Productos')">
                                <i class="fas fa-file-pdf"></i> Exportar PDF
                            </button>
                        </div>
                        <div style="position: relative; width: 100%; height: 220px;">
                            <canvas id="chartCalificaciones"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="vista-registro" class="tab-content">
            <div class="card">
                <h2 id="form-title"><i class="fas fa-plus-circle"></i> Gestión de Productos</h2>
                <form id="main-form" action="Dashboard.php?action=add" method="POST">
                    <input type="hidden" name="id" id="prod-id">
                    <div class="form-grid">
                        <div class="input-group">
                            <label>Nombre del Reloj</label>
                            <input type="text" name="nombre" id="prod-nombre" class="input-style" required>
                        </div>
                        <div class="input-group">
                            <label>Categoría</label>
                            <input type="text" name="categoria" id="prod-categoria" class="input-style" list="list-categorias">
                            <datalist id="list-categorias">
                                <?php foreach($categorias as $c): ?><option value="<?php echo $c; ?>"><?php endforeach; ?></option>
                            </datalist>
                        </div>
                        <div class="input-group">
                            <label>Precio (MXN)</label>
                            <input type="number" step="0.01" name="precio" id="prod-precio" class="input-style" required>
                        </div>
                        <div class="input-group">
                            <label>Stock Disponible</label>
                            <input type="number" name="stock" id="prod-stock" class="input-style" required>
                        </div>
                        <div class="input-group">
                            <label>Archivo Imagen (.png)</label>
                            <input type="text" name="archivo_img" id="prod-img" class="input-style" placeholder="ejemplo.png">
                        </div>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" id="btn-submit" class="btn-main" style="flex: 2;">GUARDAR PRODUCTO EN INVENTARIO</button>
                        <button type="button" id="btn-cancel" onclick="resetForm()" class="btn-main" style="display:none; background:#444; color:#fff; flex: 1;">CANCELAR</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="vista-inventario" class="tab-content">
            <div class="card">
                <h2><i class="fas fa-boxes"></i> Inventario Completo</h2>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Imagen</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th style="text-align: center;">Código QR</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($p = mysqli_fetch_assoc($res_productos)): ?>
                            <tr>
                                <td><img src="img/<?php echo $p['archivo_img']; ?>" class="img-thumb" onerror="this.src='https://via.placeholder.com/50'"></td>
                                <td><strong><?php echo $p['nombre']; ?></strong></td>
                                <td><span style="color: #aaa;"><?php echo $p['categoria']; ?></span></td>
                                <td style="color: var(--dorado); font-weight: bold;">$<?php echo number_format($p['precio'], 2); ?></td>
                                <td><?php echo $p['stock']; ?> piezas</td>
                                
                                <td style="text-align: center;">
                                    <div style="display: flex; justify-content: center; align-items: center;">
                                        <div class="contenedor-qr" 
                                             data-url="http://192.168.0.232/reloj/producto.php?id=<?php echo $p['id']; ?>"
                                             style="background: #fff; padding: 8px; border-radius: 6px; display: inline-block; border: 1px solid #ccc; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <a href="javascript:void(0)" onclick="prepararEdicion(<?php echo htmlspecialchars(json_encode($p)); ?>)" style="color: #3498db; margin-right: 15px;"><i class="fas fa-pen"></i></a>
                                    <a href="Dashboard.php?action=delete&id=<?php echo $p['id']; ?>" style="color: var(--error);" onclick="return confirm('¿Seguro que desea eliminar?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="vista-bitacora" class="tab-content">
            <div class="card">
                <h2><i class="fas fa-history"></i> Últimos Registros de Acceso (Bitácora)</h2>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Fecha de Movimiento</th>
                                <th>Estado / Resultado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($res_bitacora) > 0): ?>
                                <?php while($b = mysqli_fetch_assoc($res_bitacora)): ?>
                                <tr>
                                    <td style="color: #666;">#<?php echo $b['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($b['usuario']); ?></strong></td>
                                    <td><i class="far fa-calendar-alt" style="color: var(--dorado); margin-right: 6px;"></i><?php echo date('d/m/Y', strtotime($b['fecha'])); ?></td>
                                    <td>
                                        <?php 
                                        $est = $b['estado'];
                                        echo (strpos($est, 'Exitoso') !== false) ? 
                                            '<span class="status-badge status-exito"><i class="fas fa-check-circle"></i> ' . htmlspecialchars($est) . '</span>' : 
                                            '<span class="status-badge status-error"><i class="fas fa-times-circle"></i> ' . htmlspecialchars($est) . '</span>';
                                        ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    function cambiarVista(idVista, elementoMenu) {
        document.querySelectorAll('.tab-content').forEach(v => v.classList.remove('active'));
        document.querySelectorAll('.sidebar-item').forEach(i => i.classList.remove('active'));
        document.getElementById(idVista).classList.add('active');
        elementoMenu.classList.add('active');

        if (idVista === 'vista-stats') {
            ['graficoStock', 'chartCategorias', 'chartFinanzas', 'chartBitacora', 'chartExclusivos', 'chartCalificaciones'].forEach(id => {
                let c = Chart.getChart(id); if(c) { c.resize(); c.update(); }
            });
        }
    }

    Chart.defaults.color = '#aaa';
    let temporalizadorRealtime;

    function obtenerFechaActual() {
        const ahora = new Date();
        return ahora.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' ' + ahora.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
    }

    const miGrafico = new Chart(document.getElementById('graficoStock').getContext('2d'), {
        type: 'bar',
        data: { labels: [], datasets: [{ label: 'Unidades', data: [], backgroundColor: 'rgba(212, 175, 55, 0.4)', borderColor: 'rgba(212, 175, 55, 1)', borderWidth: 1.5 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { subtitle: { display: true, text: 'Estado actual al: ' + obtenerFechaActual() } } }
    });

    const chartCat = new Chart(document.getElementById('chartCategorias').getContext('2d'), {
        type: 'doughnut',
        data: { labels: [], datasets: [{ data: [], backgroundColor: ['#d4af37', '#3498db', '#9b59b6', '#e74c3c', '#2ecc71'] }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
    });

    const chartFin = new Chart(document.getElementById('chartFinanzas').getContext('2d'), {
        type: 'bar',
        data: { labels: [], datasets: [{ label: 'Valor ($)', data: [], backgroundColor: 'rgba(52, 152, 219, 0.5)', borderColor: '#3498db', borderWidth: 1 }] },
        options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false }
    });

    const chartBit = new Chart(document.getElementById('chartBitacora').getContext('2d'), {
        type: 'bar',
        data: { labels: [], datasets: [{ label: 'Exitosos', data: [], backgroundColor: 'rgba(46, 204, 113, 0.6)' }, { label: 'Fallidos', data: [], backgroundColor: 'rgba(231, 76, 60, 0.6)' }] },
        options: { responsive: true, maintainAspectRatio: false }
    });

    const chartExc = new Chart(document.getElementById('chartExclusivos').getContext('2d'), {
        type: 'polarArea',
        data: { labels: [], datasets: [{ data: [], backgroundColor: 'rgba(155, 89, 182, 0.4)' }] },
        options: { responsive: true, maintainAspectRatio: false }
    });

    const chartCal = new Chart(document.getElementById('chartCalificaciones').getContext('2d'), {
        type: 'line',
        data: { 
            labels: ['1 ★', '2 ★', '3 ★', '4 ★', '5 ★'], 
            datasets: [{ label: 'Reseñas', data: [0, 0, 0, 0, 0], backgroundColor: 'rgba(230, 126, 34, 0.2)', borderColor: '#e67e22', borderWidth: 2, tension: 0.3, fill: true }] 
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });

    function descargarGraficaPDF(idCanvas, titulo, archivo) {
        const c = Chart.getChart(idCanvas); if (!c) return;
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'mm', 'a4');
        pdf.setFillColor(0, 0, 0); pdf.rect(0, 0, pdf.internal.pageSize.getWidth(), 25, 'F');
        pdf.setTextColor(212, 175, 55); pdf.setFontSize(16); pdf.text("SIGLO & ORO", 12, 15);
        pdf.setTextColor(255, 255, 255); pdf.setFontSize(10); pdf.text(titulo.toUpperCase(), 12, 21);
        pdf.addImage(c.toBase64Image(), 'PNG', 15, 45, pdf.internal.pageSize.getWidth() - 30, 100);
        pdf.save(`Siglo_Oro_${archivo}.pdf`);
    }

    function sincronizarServidor() {
        const inicio = document.getElementById('fecha-inicio').value; 
        const fin = document.getElementById('fecha-fin').value;       

        if(inicio || fin) {
            document.getElementById('txt-vivo').innerHTML = '<i class="fas fa-filter" style="color:var(--azul);"></i> Filtro Activo';
            clearInterval(temporalizadorRealtime); 
        }

        fetch(`modules/datos_stock.php?inicio=${inicio}&fin=${fin}`)
            .then(r => r.json())
            .then(data => {
                if(!data || data.error) return;
                const prods = data.productos || [];

                document.getElementById('total-productos').innerText = data.productos_total;
                document.getElementById('alerta-stock').innerText = data.productos_bajos;

                miGrafico.data.labels = prods.map(i => i.nombre);
                miGrafico.data.datasets[0].data = prods.map(i => i.stock);
                miGrafico.update();

                chartCat.data.labels = data.categorias.map(i => i.categoria);
                chartCat.data.datasets[0].data = data.categorias.map(i => i.total);
                chartCat.update();

                chartFin.data.labels = prods.map(i => i.nombre);
                chartFin.data.datasets[0].data = prods.map(i => i.stock * i.precio);
                chartFin.update();

                chartBit.data.labels = data.cronologia_bitacora.map(i => i.fecha); 
                chartBit.data.datasets[0].data = data.cronologia_bitacora.map(i => i.exitosos);
                chartBit.data.datasets[1].data = data.cronologia_bitacora.map(i => i.fallidos);
                chartBit.update();

                chartExc.data.labels = data.exclusivos.map(i => i.nombre);
                chartExc.data.datasets[0].data = data.exclusivos.map(i => i.precio);
                chartExc.update();

                chartCal.data.datasets[0].data = data.calificaciones;
                chartCal.update();
            });
    }

    function limpiarFiltros() {
        document.getElementById('fecha-inicio').value = '';
        document.getElementById('fecha-fin').value = '';
        document.getElementById('txt-vivo').innerHTML = '<i class="fas fa-circle"></i> VIVO (3s)';
        sincronizarServidor();
        clearInterval(temporalizadorRealtime);
        temporalizadorRealtime = setInterval(sincronizarServidor, 3000);
    }

    function exportarGraficasPDF() {
        const { jsPDF } = window.jspdf;
        html2canvas(document.getElementById('contenedor-reporte-graficas'), { backgroundColor: '#0a0a0a', scale: 2 }).then(canvas => {
            const pdf = new jsPDF('p', 'mm', 'a4');
            pdf.setFillColor(0, 0, 0); pdf.rect(0, 0, pdf.internal.pageSize.getWidth(), 25, 'F');
            pdf.setTextColor(212, 175, 55); pdf.text("SIGLO & ORO - REPORTE COMPLETO", 12, 15);
            pdf.addImage(canvas.toDataURL('image/png'), 'PNG', 10, 35, pdf.internal.pageSize.getWidth() - 20, 200);
            pdf.save("Reporte_Siglo_Oro.pdf");
        });
    }

    sincronizarServidor();
    temporalizadorRealtime = setInterval(sincronizarServidor, 3000);

    function prepararEdicion(producto) {
        cambiarVista('vista-registro', document.getElementById('menu-registro'));
        document.getElementById('form-title').innerHTML = '<i class="fas fa-edit"></i> Editar Producto: ' + producto.nombre;
        document.getElementById('main-form').action = 'Dashboard.php?action=update';
        document.getElementById('prod-id').value = producto.id;
        document.getElementById('prod-nombre').value = producto.nombre;
        document.getElementById('prod-categoria').value = producto.categoria;
        document.getElementById('prod-precio').value = producto.precio;
        document.getElementById('prod-stock').value = producto.stock;
        document.getElementById('prod-img').value = producto.archivo_img;
        document.getElementById('btn-submit').innerText = 'ACTUALIZAR PRODUCTO';
        document.getElementById('btn-cancel').style.display = 'block';
    }

    function resetForm() {
        document.getElementById('form-title').innerHTML = '<i class="fas fa-plus-circle"></i> Gestión de Productos';
        document.getElementById('main-form').action = 'Dashboard.php?action=add';
        document.getElementById('main-form').reset();
        document.getElementById('btn-submit').innerText = 'GUARDAR PRODUCTO EN INVENTARIO';
        document.getElementById('btn-cancel').style.display = 'none';
    }

    // SCRIPT DE ACTIVACIÓN OPTIMIZADO PARA ESCANEO RÁPIDO (90x90px)
    document.querySelectorAll('.contenedor-qr').forEach(contenedor => {
        const urlProducto = contenedor.getAttribute('data-url');
        contenedor.innerHTML = ""; // Limpieza de seguridad
        new QRCode(contenedor, {
            text: urlProducto,
            width: 90,
            height: 90,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.M
        });
    });
    </script>
</body>
</html>