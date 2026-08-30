<?php
session_start();

require_once __DIR__ . '/_data.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pedidos = pedidos_cargar();
$pedido = pedidos_buscar_por_id($pedidos, $id);

if (!$pedido) {
    $_SESSION['error_pedido'] = 'Pedido no encontrado.';
    header('Location: listar.php');
    exit;
}

include(__DIR__ . '/../Includes/header.php');
?>

<div class="admin-layout">
    <?php include(__DIR__ . '/../Includes/sidebar.php'); ?>

    <main class="admin-main">
        <?php include(__DIR__ . '/../Includes/topbar.php'); ?>

        <section class="admin-content">
            <section class="admin-panel">
                <h2>Detalle de pedido</h2>

                <div class="admin-form-grid">
                    <div class="admin-field"><label># Pedido</label><p><?php echo htmlspecialchars((string)($pedido['numero_pedido'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p></div>
                    <div class="admin-field"><label>Fecha</label><p><?php echo htmlspecialchars((string)($pedido['fecha'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p></div>
                    <div class="admin-field"><label>Cliente</label><p><?php echo htmlspecialchars((string)($pedido['cliente'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p></div>
                    <div class="admin-field"><label>Telefono</label><p><?php echo htmlspecialchars((string)($pedido['telefono'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p></div>
                    <div class="admin-field"><label>Correo</label><p><?php echo htmlspecialchars((string)($pedido['correo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p></div>
                    <div class="admin-field"><label>Metodo de pago</label><p><?php echo htmlspecialchars((string)($pedido['metodo_pago'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p></div>
                    <div class="admin-field"><label>Estado</label><p><?php echo htmlspecialchars((string)($pedido['estado'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p></div>
                    <div class="admin-field"><label>Total</label><p>₡<?php echo number_format((float)($pedido['total'] ?? 0), 0, ',', '.'); ?></p></div>
                    <div class="admin-field" style="grid-column: 1 / -1;"><label>Direccion</label><p><?php echo nl2br(htmlspecialchars((string)($pedido['direccion'] ?? ''), ENT_QUOTES, 'UTF-8')); ?></p></div>
                    <div class="admin-field" style="grid-column: 1 / -1;"><label>Observaciones</label><p><?php echo nl2br(htmlspecialchars((string)($pedido['observaciones'] ?? ''), ENT_QUOTES, 'UTF-8')); ?></p></div>
                </div>

                <h3 style="margin-top:14px; margin-bottom:8px;">Productos del pedido</h3>
                <div class="admin-table-wrap">
                    <table class="admin-table">
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
                                $subtotal = $cantidad * $precio;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars((string)($item['producto'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo $cantidad; ?></td>
                                    <td>₡<?php echo number_format($precio, 0, ',', '.'); ?></td>
                                    <td>₡<?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="admin-actions-row" style="margin-top: 14px;">
                    <a href="editar.php?id=<?php echo (int)$id; ?>" class="admin-btn">Editar pedido</a>
                    <a href="factura.php?id=<?php echo (int)$id; ?>" class="admin-action">Ver factura</a>
                    <a href="listar.php" class="admin-action">Volver</a>
                </div>
            </section>
        </section>
    </main>
</div>

<?php include(__DIR__ . '/../Includes/footer.php'); ?>
