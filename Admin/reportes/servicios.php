<?php
require_once __DIR__ . '/../servicios/_data.php';
require_once __DIR__ . '/../citas/_agenda.php';

$servicios = servicios_cargar();
$serviciosCitas = cargar_servicios_citas();
$serviciosIndexCitas = indexar_servicios_por_id($serviciosCitas);
$citas = cargar_citas($serviciosIndexCitas);

$porServicio = [];
$precios = [];
foreach ($servicios as $servicio) {
    $nombre = trim((string)($servicio['nombre'] ?? ''));
    if ($nombre === '') {
        continue;
    }
    $porServicio[$nombre] = 0;
    $precios[$nombre] = (float)($servicio['precio'] ?? 0);
}

foreach ($citas as $cita) {
    $nombre = trim((string)($cita['servicio'] ?? ''));
    if ($nombre === '') {
        continue;
    }

    if (!isset($porServicio[$nombre])) {
        $porServicio[$nombre] = 0;
    }
    $porServicio[$nombre]++;
}

arsort($porServicio);
$totalCitasServicios = array_sum($porServicio);
$masSolicitado = $totalCitasServicios > 0 ? array_key_first($porServicio) : 'Sin datos';

$ingresoEstimado = 0.0;
foreach ($porServicio as $nombre => $cantidad) {
    $precio = (float)($precios[$nombre] ?? 0);
    $ingresoEstimado += $precio * (int)$cantidad;
}

$queryBase = ['reporte' => 'servicios'];

include(__DIR__ . '/../Includes/header.php');
?>

<div class="admin-layout">
    <?php include(__DIR__ . '/../Includes/sidebar.php'); ?>

    <main class="admin-main">
        <?php include(__DIR__ . '/../Includes/topbar.php'); ?>

        <section class="admin-content">
            <section class="admin-panel">
                <h2>Reporte de Servicios</h2>
                <p>Comportamiento de citas por servicio y estimacion de ingresos.</p>

                <div class="admin-actions-row" style="margin:12px 0 14px; flex-wrap: wrap;">
                    <a class="admin-btn" href="exportar_pdf.php?<?php echo htmlspecialchars(http_build_query($queryBase), ENT_QUOTES, 'UTF-8'); ?>">📄 Exportar a PDF</a>
                    <a class="admin-btn" href="exportar_excel.php?<?php echo htmlspecialchars(http_build_query($queryBase), ENT_QUOTES, 'UTF-8'); ?>">📊 Exportar a Excel</a>
                    <a class="admin-btn" href="imprimir.php?<?php echo htmlspecialchars(http_build_query($queryBase), ENT_QUOTES, 'UTF-8'); ?>">🖨 Imprimir</a>
                </div>

                <div class="admin-cards" style="margin-bottom:14px;">
                    <article class="admin-card"><h3>Servicios activos</h3><p><?php echo count($servicios); ?></p></article>
                    <article class="admin-card"><h3>Servicio mas solicitado</h3><p style="font-size:18px;"><?php echo htmlspecialchars($masSolicitado, ENT_QUOTES, 'UTF-8'); ?></p></article>
                    <article class="admin-card"><h3>Total de citas en servicios</h3><p><?php echo (int)$totalCitasServicios; ?></p></article>
                    <article class="admin-card"><h3>Ingreso estimado</h3><p><?php echo '₡' . number_format($ingresoEstimado, 0, ',', '.'); ?></p></article>
                </div>

                <div class="admin-table-wrap">
                    <table class="admin-table" style="min-width:720px;">
                        <thead>
                            <tr>
                                <th>Servicio</th>
                                <th>Citas</th>
                                <th>Precio referencia</th>
                                <th>Ingreso estimado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$porServicio): ?>
                                <tr><td colspan="4">No hay datos de servicios.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($porServicio as $nombre => $cantidad): ?>
                                <?php $precio = (float)($precios[$nombre] ?? 0); ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo (int)$cantidad; ?></td>
                                    <td><?php echo '₡' . number_format($precio, 0, ',', '.'); ?></td>
                                    <td><?php echo '₡' . number_format($precio * (int)$cantidad, 0, ',', '.'); ?></td>
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
