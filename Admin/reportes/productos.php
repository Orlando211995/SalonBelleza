<?php
require_once __DIR__ . '/../pedidos/_data.php';
require_once __DIR__ . '/../productos/_data.php';

$pedidos = pedidos_cargar();
$productos = productos_cargar();

$conteoVentas = [];
foreach ($pedidos as $pedido) {
    $items = $pedido['items'] ?? [];
    foreach ($items as $item) {
        $nombre = trim((string)($item['producto'] ?? ''));
        $cantidad = (int)($item['cantidad'] ?? 0);
        if ($nombre === '') {
            continue;
        }

        if (!isset($conteoVentas[$nombre])) {
            $conteoVentas[$nombre] = 0;
        }
        $conteoVentas[$nombre] += $cantidad;
    }
}

arsort($conteoVentas);
$masVendidos = array_slice($conteoVentas, 0, 5, true);

$menosVendidos = $conteoVentas;
asort($menosVendidos);
$menosVendidos = array_slice($menosVendidos, 0, 5, true);

$sinExistencias = [];
$inventarioBajo = [];

foreach ($productos as $producto) {
    $stock = (int)($producto['stock'] ?? 0);
    if ($stock <= 0) {
        $sinExistencias[] = $producto;
    }
    if ($stock > 0 && $stock <= 10) {
        $inventarioBajo[] = $producto;
    }
}

$queryBase = ['reporte' => 'productos'];

include(__DIR__ . '/../Includes/header.php');
?>

<div class="admin-layout">
    <?php include(__DIR__ . '/../Includes/sidebar.php'); ?>

    <main class="admin-main">
        <?php include(__DIR__ . '/../Includes/topbar.php'); ?>

        <section class="admin-content">
            <section class="admin-panel">
                <h2>Reporte de Productos</h2>
                <p>Productos mas vendidos, menos vendidos y control de inventario.</p>

                <div class="admin-actions-row" style="margin:12px 0 14px; flex-wrap: wrap;">
                    <a class="admin-btn" href="exportar_pdf.php?<?php echo htmlspecialchars(http_build_query($queryBase), ENT_QUOTES, 'UTF-8'); ?>">📄 Exportar a PDF</a>
                    <a class="admin-btn" href="exportar_excel.php?<?php echo htmlspecialchars(http_build_query($queryBase), ENT_QUOTES, 'UTF-8'); ?>">📊 Exportar a Excel</a>
                    <a class="admin-btn" href="imprimir.php?<?php echo htmlspecialchars(http_build_query($queryBase), ENT_QUOTES, 'UTF-8'); ?>">🖨 Imprimir</a>
                </div>

                <div class="admin-cards" style="margin-bottom:14px;">
                    <article class="admin-card"><h3>Productos con baja rotacion</h3><p><?php echo count($menosVendidos); ?></p></article>
                    <article class="admin-card"><h3>Productos sin stock</h3><p><?php echo count($sinExistencias); ?></p></article>
                    <article class="admin-card"><h3>Inventario bajo</h3><p><?php echo count($inventarioBajo); ?></p></article>
                </div>

                <h3 style="margin:8px 0;">Productos mas vendidos</h3>
                <div class="admin-table-wrap" style="margin-bottom:14px;">
                    <table class="admin-table" style="min-width:640px;">
                        <thead><tr><th>Producto</th><th>Cantidad vendida</th></tr></thead>
                        <tbody>
                            <?php if (!$masVendidos): ?>
                                <tr><td colspan="2">Sin ventas registradas.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($masVendidos as $producto => $cantidad): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($producto, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo (int)$cantidad; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <h3 style="margin:8px 0;">Productos menos vendidos</h3>
                <div class="admin-table-wrap" style="margin-bottom:14px;">
                    <table class="admin-table" style="min-width:640px;">
                        <thead><tr><th>Producto</th><th>Cantidad vendida</th></tr></thead>
                        <tbody>
                            <?php if (!$menosVendidos): ?>
                                <tr><td colspan="2">Sin ventas registradas.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($menosVendidos as $producto => $cantidad): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($producto, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo (int)$cantidad; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <h3 style="margin:8px 0;">Productos sin existencias</h3>
                <div class="admin-table-wrap" style="margin-bottom:14px;">
                    <table class="admin-table" style="min-width:640px;">
                        <thead><tr><th>Producto</th><th>Categoria</th><th>Stock</th></tr></thead>
                        <tbody>
                            <?php if (!$sinExistencias): ?>
                                <tr><td colspan="3">No hay productos agotados.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($sinExistencias as $producto): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($producto['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($producto['categoria'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo (int)($producto['stock'] ?? 0); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <h3 style="margin:8px 0;">Productos con inventario bajo</h3>
                <div class="admin-table-wrap">
                    <table class="admin-table" style="min-width:640px;">
                        <thead><tr><th>Producto</th><th>Categoria</th><th>Stock</th></tr></thead>
                        <tbody>
                            <?php if (!$inventarioBajo): ?>
                                <tr><td colspan="3">No hay productos con inventario bajo.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($inventarioBajo as $producto): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($producto['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($producto['categoria'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo (int)($producto['stock'] ?? 0); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </section>
    </main>
</div>

<?php include(__DIR__ . '/../Includes/footer.php'); ?>
