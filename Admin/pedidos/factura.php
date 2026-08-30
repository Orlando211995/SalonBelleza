<?php
session_start();

require_once __DIR__ . '/_data.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$printMode = isset($_GET['print']) && $_GET['print'] === '1';

$pedidos = pedidos_cargar();
$pedido = pedidos_buscar_por_id($pedidos, $id);

if (!$pedido) {
    $_SESSION['error_pedido'] = 'Pedido no encontrado.';
    header('Location: listar.php');
    exit;
}

$subtotal = 0;
foreach (($pedido['items'] ?? []) as $item) {
    $subtotal += ((int)($item['cantidad'] ?? 0)) * ((float)($item['precio'] ?? 0));
}

$envio = (float)($pedido['costo_envio'] ?? 0);
$tipoEntrega = (string)($pedido['tipo_entrega'] ?? 'envio');
$total = (float)($pedido['total'] ?? ($subtotal + $envio));

?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Factura <?php echo htmlspecialchars((string)($pedido['numero_pedido'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        body { font-family: Arial, sans-serif; color: #111; margin: 0; padding: 24px; background: #f5f5f5; }
        .card { max-width: 900px; margin: 0 auto; background: #fff; border: 1px solid #ddd; border-radius: 10px; padding: 20px; }
        .head { display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
        h1 { margin: 0 0 6px; font-size: 26px; }
        p { margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        th, td { border-bottom: 1px solid #e8e8e8; padding: 10px; text-align: left; }
        th { background: #f7f7f7; }
        .totales { margin-top: 14px; margin-left: auto; max-width: 280px; }
        .totales div { display: flex; justify-content: space-between; padding: 6px 0; }
        .total-final { font-weight: 700; font-size: 18px; border-top: 1px solid #ddd; margin-top: 6px; }
        .acciones { margin-top: 18px; display: flex; gap: 8px; }
        .btn { display: inline-block; padding: 10px 14px; border-radius: 8px; text-decoration: none; border: 1px solid #ccc; color: #111; }
        .btn-primary { background: #111; color: #fff; border-color: #111; }
        @media print {
            body { background: #fff; padding: 0; }
            .card { border: none; border-radius: 0; }
            .acciones { display: none; }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="head">
            <div>
                <h1>Factura</h1>
                <p><strong>Alfredo Salon Estudio CR</strong></p>
                <p># Pedido: <?php echo htmlspecialchars((string)($pedido['numero_pedido'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                <p>Fecha: <?php echo htmlspecialchars((string)($pedido['fecha'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div>
                <p><strong>Cliente:</strong> <?php echo htmlspecialchars((string)($pedido['cliente'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>Telefono:</strong> <?php echo htmlspecialchars((string)($pedido['telefono'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>Correo:</strong> <?php echo htmlspecialchars((string)($pedido['correo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>Pago:</strong> <?php echo htmlspecialchars((string)($pedido['metodo_pago'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>Estado:</strong> <?php echo htmlspecialchars((string)($pedido['estado'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>Entrega:</strong> <?php echo $tipoEntrega === 'retiro' ? 'Retiro en salon' : 'Envio por Correos'; ?></p>
            </div>
        </div>

        <p><strong>Direccion:</strong> <?php echo htmlspecialchars((string)($pedido['direccion'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>

        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio unitario</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($pedido['items'] ?? []) as $item): ?>
                    <?php
                    $cantidad = (int)($item['cantidad'] ?? 0);
                    $precio = (float)($item['precio'] ?? 0);
                    $linea = $cantidad * $precio;
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars((string)($item['producto'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo $cantidad; ?></td>
                        <td>₡<?php echo number_format($precio, 0, ',', '.'); ?></td>
                        <td>₡<?php echo number_format($linea, 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totales">
            <div><span>Subtotal</span><strong>₡<?php echo number_format($subtotal, 0, ',', '.'); ?></strong></div>
            <div><span>Envio</span><strong>₡<?php echo number_format($envio, 0, ',', '.'); ?></strong></div>
            <div class="total-final"><span>Total</span><strong>₡<?php echo number_format($total, 0, ',', '.'); ?></strong></div>
        </div>

        <p><strong>Observaciones:</strong> <?php echo htmlspecialchars((string)($pedido['observaciones'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>

        <div class="acciones">
            <a class="btn" href="ver.php?id=<?php echo (int)$id; ?>">Volver</a>
            <a class="btn btn-primary" href="imprimir.php?id=<?php echo (int)$id; ?>">Imprimir</a>
        </div>
    </div>

    <?php if ($printMode): ?>
        <script>window.print();</script>
    <?php endif; ?>
</body>
</html>
