<?php
require_once __DIR__ . '/../pagos/_data.php';

$pagos = pagos_cargar();
$metodoFiltro = trim($_GET['metodo'] ?? '');
$estadoFiltro = trim($_GET['estado'] ?? '');

$conteo = [
    'sinpe' => 0,
    'efectivo' => 0,
    'tarjeta' => 0,
    'pendiente' => 0,
    'rechazado' => 0,
];

$filtrados = [];
foreach ($pagos as $pago) {
    $metodo = strtolower((string)($pago['metodo'] ?? ''));
    $estado = strtolower((string)($pago['estado'] ?? ''));

    if (strpos($metodo, 'sinpe') !== false) {
        $conteo['sinpe']++;
    }
    if (strpos($metodo, 'efectivo') !== false) {
        $conteo['efectivo']++;
    }
    if (strpos($metodo, 'tarjeta') !== false) {
        $conteo['tarjeta']++;
    }
    if (strpos($estado, 'pendiente') !== false) {
        $conteo['pendiente']++;
    }
    if (strpos($estado, 'rechaz') !== false) {
        $conteo['rechazado']++;
    }

    if ($metodoFiltro !== '' && stripos($metodo, $metodoFiltro) === false) {
        continue;
    }
    if ($estadoFiltro !== '' && stripos($estado, $estadoFiltro) === false) {
        continue;
    }

    $filtrados[] = $pago;
}

$queryBase = [
    'reporte' => 'pagos',
    'metodo' => $metodoFiltro,
    'estado' => $estadoFiltro,
];

include(__DIR__ . '/../Includes/header.php');
?>

<div class="admin-layout">
    <?php include(__DIR__ . '/../Includes/sidebar.php'); ?>

    <main class="admin-main">
        <?php include(__DIR__ . '/../Includes/topbar.php'); ?>

        <section class="admin-content">
            <section class="admin-panel">
                <h2>Reporte de Pagos</h2>
                <p>Consulta pagos por metodo y estado.</p>

                <form class="admin-toolbar" method="get" action="pagos.php" style="grid-template-columns:1fr 1fr auto;">
                    <select name="metodo" class="admin-select">
                        <option value="">Todos los metodos</option>
                        <option value="sinpe" <?php echo $metodoFiltro === 'sinpe' ? 'selected' : ''; ?>>SINPE</option>
                        <option value="efectivo" <?php echo $metodoFiltro === 'efectivo' ? 'selected' : ''; ?>>Efectivo</option>
                        <option value="tarjeta" <?php echo $metodoFiltro === 'tarjeta' ? 'selected' : ''; ?>>Tarjeta</option>
                    </select>

                    <select name="estado" class="admin-select">
                        <option value="">Todos los estados</option>
                        <option value="pendiente" <?php echo $estadoFiltro === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="rechaz" <?php echo $estadoFiltro === 'rechaz' ? 'selected' : ''; ?>>Rechazado</option>
                        <option value="aprob" <?php echo $estadoFiltro === 'aprob' ? 'selected' : ''; ?>>Aprobado</option>
                        <option value="revision" <?php echo $estadoFiltro === 'revision' ? 'selected' : ''; ?>>En revision</option>
                    </select>

                    <button class="admin-btn" type="submit">Filtrar</button>
                </form>

                <div class="admin-actions-row" style="margin:12px 0 14px; flex-wrap: wrap;">
                    <a class="admin-btn" href="exportar_pdf.php?<?php echo htmlspecialchars(http_build_query($queryBase), ENT_QUOTES, 'UTF-8'); ?>">📄 Exportar a PDF</a>
                    <a class="admin-btn" href="exportar_excel.php?<?php echo htmlspecialchars(http_build_query($queryBase), ENT_QUOTES, 'UTF-8'); ?>">📊 Exportar a Excel</a>
                    <a class="admin-btn" href="imprimir.php?<?php echo htmlspecialchars(http_build_query($queryBase), ENT_QUOTES, 'UTF-8'); ?>">🖨 Imprimir</a>
                </div>

                <div class="admin-cards" style="margin-bottom:14px;">
                    <article class="admin-card"><h3>Pagos por SINPE</h3><p><?php echo $conteo['sinpe']; ?></p></article>
                    <article class="admin-card"><h3>Pagos en efectivo</h3><p><?php echo $conteo['efectivo']; ?></p></article>
                    <article class="admin-card"><h3>Pagos con tarjeta</h3><p><?php echo $conteo['tarjeta']; ?></p></article>
                    <article class="admin-card"><h3>Pagos pendientes</h3><p><?php echo $conteo['pendiente']; ?></p></article>
                    <article class="admin-card"><h3>Pagos rechazados</h3><p><?php echo $conteo['rechazado']; ?></p></article>
                </div>

                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>N pago</th>
                                <th>Cliente</th>
                                <th>Metodo</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$filtrados): ?>
                                <tr><td colspan="6">No hay pagos para el filtro actual.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($filtrados as $pago): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($pago['numero_pago'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($pago['cliente'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($pago['metodo'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($pago['estado'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($pago['fecha'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo '₡' . number_format((float)($pago['monto'] ?? 0), 0, ',', '.'); ?></td>
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
