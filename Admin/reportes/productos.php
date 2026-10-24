<?php
require_once __DIR__ . '/_reporte.php';

$reporte = reporte_construir('productos');
$queryBase = ['reporte' => 'productos'];
$e = static fn($texto) => htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');

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
                    <a class="admin-btn" href="exportar_pdf.php?<?php echo $e(http_build_query($queryBase)); ?>">📄 Exportar a PDF</a>
                    <a class="admin-btn" href="exportar_excel.php?<?php echo $e(http_build_query($queryBase)); ?>">📊 Exportar a Excel</a>
                    <a class="admin-btn" href="imprimir.php?<?php echo $e(http_build_query($queryBase)); ?>">🖨 Imprimir</a>
                </div>

                <div class="admin-cards" style="margin-bottom:14px;">
                    <?php foreach ($reporte['resumen'] as [$etiqueta, $valor, $tipo]): ?>
                        <article class="admin-card"><h3><?php echo $e($etiqueta); ?></h3><p><?php echo $e(reporte_formatear($valor, $tipo)); ?></p></article>
                    <?php endforeach; ?>
                </div>

                <?php foreach ($reporte['secciones'] as $seccion): ?>
                    <h3 style="margin:8px 0;"><?php echo $e($seccion['titulo']); ?></h3>
                    <div class="admin-table-wrap" style="margin-bottom:14px;">
                        <table class="admin-table" style="min-width:760px;">
                            <thead>
                                <tr>
                                    <?php foreach ($seccion['columnas'] as [$nombre]): ?>
                                        <th><?php echo $e($nombre); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$seccion['filas']): ?>
                                    <tr><td colspan="<?php echo count($seccion['columnas']); ?>"><?php echo $e($seccion['vacio']); ?></td></tr>
                                <?php endif; ?>
                                <?php foreach ($seccion['filas'] as $fila): ?>
                                    <tr>
                                        <?php foreach ($seccion['columnas'] as $i => [, $tipo]): ?>
                                            <td><?php echo $e(reporte_formatear($fila[$i] ?? '', $tipo)); ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            </section>
        </section>
    </main>
</div>

<?php include(__DIR__ . '/../Includes/footer.php'); ?>
