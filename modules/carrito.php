<?php
// C:\laragon\www\reloj\modules\carrito.php
session_start();
require_once('../config/db.php'); 
require_once __DIR__ . '/_lib/Exception.php';
require_once __DIR__ . '/_lib/PHPMailer.php';
require_once __DIR__ . '/_lib/SMTP.php';
require_once __DIR__ . '/dompdf/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;
use Dompdf\Options;

$action = $_GET['action'] ?? '';

// --- AGREGAR PRODUCTO ---
if ($action == 'add') {
    $id = $_POST['id'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $precio = (float)($_POST['precio'] ?? 0);
    
    $res = mysqli_query($conexion, "SELECT archivo_img FROM productos WHERE id = '$id'");
    $reg = mysqli_fetch_assoc($res);
    $imagen = $reg['archivo_img'] ?? ''; 

    if (!empty($id)) {
        if (!isset($_SESSION['carrito'])) { $_SESSION['carrito'] = array(); }

        if (isset($_SESSION['carrito'][$id])) {
            $_SESSION['carrito'][$id]['cantidad']++;
        } else {
            $_SESSION['carrito'][$id] = array(
                'nombre' => $nombre,
                'precio' => $precio,
                'imagen' => $imagen, 
                'cantidad' => 1
            );
        }
    }
    header("Location: ../Catalogo.php");
    exit();
}

// --- VACIAR CARRITO ---
if ($action == 'empty') {
    unset($_SESSION['carrito']);
    header("Location: ../Catalogo.php");
    exit();
}

// --- FINALIZAR COMPRA (ACTUALIZA STOCK + PDF + CORREO) ---
if ($action == 'finalizar') {
    $email_usuario = $_POST['email_cliente'] ?? '';

    if (empty($email_usuario)) {
        die("Error: No se recibió la dirección de correo electrónico.");
    }

    if (!empty($_SESSION['carrito'])) {
        $mail = new PHPMailer(true);

        try {
            // Configuración SMTP de Gmail
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'sigloyoro@gmail.com'; 
            $mail->Password   = 'hwwp epwg bnjk qldf'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
            $mail->Port       = 465;

            $mail->setFrom('ventas@sigloyoro.mx', 'Siglo & Oro Luxury');
            $mail->addAddress($email_usuario);
            $mail->isHTML(true);
            $mail->Subject = 'Su Ticket de Compra - Siglo & Oro';

            // 1. PROCESAR CARRITO (STOCK Y CONTENIDO)
            $total_compra = 0;
            $items_html = "";
            $raiz_proyecto = dirname(__DIR__); 

            foreach ($_SESSION['carrito'] as $id => $item) {
                $subtotal = $item['precio'] * $item['cantidad'];
                $total_compra += $subtotal;
                $cantidad_comprada = $item['cantidad'];

                // --- ACTUALIZACIÓN DE STOCK EN BD ---
                $sql_stock = "UPDATE productos SET stock = stock - $cantidad_comprada WHERE id = '$id'";
                mysqli_query($conexion, $sql_stock);

                // --- PREPARACIÓN DE IMAGEN PARA PDF ---
                $ruta_img = $raiz_proyecto . DIRECTORY_SEPARATOR . "img" . DIRECTORY_SEPARATOR . $item['imagen'];

                if (!empty($item['imagen']) && file_exists($ruta_img)) {
                    $img_tag = "<img src='" . $ruta_img . "' width='60' height='60' style='object-fit: contain;'>";
                } else {
                    $img_tag = "<span style='font-size:8px; color:gray;'>Sin imagen</span>";
                }

                $items_html .= "
                <tr>
                    <td style='border-bottom: 1px solid #eee; padding: 10px;'>$img_tag</td>
                    <td style='border-bottom: 1px solid #eee; padding: 10px;'>
                        <b style='color:#d4af37;'>{$item['nombre']}</b><br>
                        Cantidad: {$item['cantidad']}
                    </td>
                    <td align='right' style='border-bottom: 1px solid #eee; padding: 10px;'>
                        <b>$" . number_format($subtotal, 2) . "</b>
                    </td>
                </tr>";
            }

            // 2. HTML PARA EL PDF
            $html_ticket = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; color: #333; }
                    .gold-border { border: 2px solid #d4af37; padding: 20px; }
                    .header { text-align: center; margin-bottom: 20px; }
                    .title { color: #d4af37; font-size: 24px; margin-bottom: 0; }
                    table { width: 100%; border-collapse: collapse; }
                    th { color: #d4af37; border-bottom: 2px solid #d4af37; text-align: left; padding: 10px; }
                    .total { text-align: right; color: #d4af37; font-size: 20px; }
                </style>
            </head>
            <body>
                <div class='gold-border'>
                    <div class='header'>
                        <h1 class='title'>SIGLO & ORO</h1>
                        <p>Luxury Timepieces</p>
                    </div>
                    <p><b>Comprobante para:</b> $email_usuario</p>
                    <table>
                        <thead>
                            <tr>
                                <th>Imagen</th>
                                <th>Producto</th>
                                <th align='right'>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            $items_html
                        </tbody>
                    </table>
                    <h2 class='total'>Total: $" . number_format($total_compra, 2) . "</h2>
                </div>
            </body>
            </html>";

            // 3. GENERAR PDF
            $options = new Options();
            $options->set('isRemoteEnabled', true); 
            $options->set('chroot', $raiz_proyecto); 

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html_ticket);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $pdf_content = $dompdf->output();

            // 4. ENVIAR CORREO
            $mail->Body = "Estimado cliente, adjuntamos su ticket de compra de Siglo & Oro Luxury. El stock de su pedido ha sido reservado.";
            $mail->addStringAttachment($pdf_content, 'Ticket_Siglo_Oro.pdf');

            $mail->send();

            // Limpiar sesión y finalizar
            unset($_SESSION['carrito']);
            header("Location: ../Catalogo.php?success=true");

        } catch (Exception $e) {
            echo "Error: {$mail->ErrorInfo}";
        }
    } else {
        header("Location: ../Catalogo.php");
    }
}
?>