<?php
require_once __DIR__ . '/../productos/_data.php';

$productos = productos_cargar();

$totalProductos = count($productos);
$sinStock = 0;
$stockBajo = 0;
$valorInventario = 0.0;

foreach ($productos as $producto) {
    $stock = (int)($producto['stock'] ?? 0);
    $precio = (float)($producto['precio'] ?? 0);

    if ($stock <= 0) {
        $sinStock++;
    }
    if ($stock > 0 && $stock <= 10) {
        $stockBajo++;
    }

    $valorInventario += $stock * $precio;
}

$queryBase = ['reporte' => 'inventario'];

include(__DIR__ . '/../Includes/header.php');
?>

<div class="admin-layout">
    <?php include(__DIR__ . '/../Includes/sidebar.php'); ?>

    <main class="admin-main">
        <?php include(__DIR__ . '/../Includes/topbar.php'); ?>

        <section class="admin-content">
            <section class="admin-panel">
                <h2>Reporte de Inventario</h2>
                <p>Control de stock disponible y alertas de reposicion.</p>

                <div class="admin-actions-row" style="margin:12px 0 14px; flex-wrap: wrap;">
                    <a class="admin-btn" href="exportar_pdf.php?<?php echo htmlspecialchars(http_build_query($queryBase), ENT_QUOTES, 'UTF-8'); ?>">📄 Exportar a PDF</a>
                    <a class="admin-btn" href="exportar_excel.php?<?php echo htmlspecialchars(http_build_query($queryBase), ENT_QUOTES, 'UTF-8'); ?>">📊 Exportar a Excel</a>
                    <a class="admin-btn" href="imprimir.php?<?php echo htmlspecialchars(http_build_query($queryBase), ENT_QUOTES, 'UTF-8'); ?>">🖨 Imprimir</a>
                </div>

                <div class="admin-cards" style="margin-bottom:14px;">
                    <article class="admin-card"><h3>Total de productos</h3><p><?php echo $totalProductos; ?></p></article>
                    <article class="admin-card"><h3>Sin existencias</h3><p><?php echo $sinStock; ?></p></article>
                    <article class="admin-card"><h3>Inventario bajo</h3><p><?php echo $stockBajo; ?></p></article>
                    <article class="admin-card"><h3>Valor total inventario</h3><p><?php echo '₡' . number_format($valorInventario, 0, ',', '.'); ?></p></article>
                </div>

                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Categoria</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$productos): ?>
                                <tr><td colspan="5">No hay productos registrados.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($productos as $producto): ?>
                                <?php
                                $precio = (float)($producto['precio'] ?? 0);
                                $stock = (int)($producto['stock'] ?? 0);
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($producto['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($producto['categoria'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo '₡' . number_format($precio, 0, ',', '.'); ?></td>
                                    <td><?php echo $stock; ?></td>
                                    <td><?php echo '₡' . number_format($precio * $stock, 0, ',', '.'); ?></td>
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
