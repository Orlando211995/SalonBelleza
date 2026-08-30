<?php
require_once __DIR__ . '/../citas/_agenda.php';

$servicios = cargar_servicios_citas();
$serviciosIndex = indexar_servicios_por_id($servicios);
$citas = cargar_citas($serviciosIndex);

$periodo = trim($_GET['periodo'] ?? 'mes');
$estado = trim($_GET['estado'] ?? '');
$vista = trim($_GET['vista'] ?? 'citas');

$hoy = new DateTimeImmutable('today');
$inicioSemana = $hoy->modify('monday this week');
$finSemana = $inicioSemana->modify('+6 days')->setTime(23, 59, 59);
$inicioMes = $hoy->modify('first day of this month');
$finMes = $hoy->modify('last day of this month')->setTime(23, 59, 59);

$filtradas = [];
foreach ($citas as $cita) {
    $fechaTexto = trim((string)($cita['fecha'] ?? ''));
    $fecha = DateTimeImmutable::createFromFormat('Y-m-d', $fechaTexto);
    if (!$fecha) {
        continue;
    }

    $estadoCita = trim((string)($cita['estado'] ?? ''));
    if ($estado !== '' && strcasecmp($estadoCita, $estado) !== 0) {
        continue;
    }

    $incluir = true;
    if ($periodo === 'dia') {
        $incluir = $fecha->format('Y-m-d') === $hoy->format('Y-m-d');
    } elseif ($periodo === 'semana') {
        $incluir = $fecha >= $inicioSemana && $fecha <= $finSemana;
    } elseif ($periodo === 'mes') {
        $incluir = $fecha >= $inicioMes && $fecha <= $finMes;
    }

    if ($incluir) {
        $filtradas[] = $cita;
    }
}

$conteoEstados = [];
$clientes = [];
foreach ($filtradas as $cita) {
    $estadoCita = trim((string)($cita['estado'] ?? 'Sin estado'));
    if (!isset($conteoEstados[$estadoCita])) {
        $conteoEstados[$estadoCita] = 0;
    }
    $conteoEstados[$estadoCita]++;

    $nombreCliente = trim((string)($cita['cliente'] ?? ''));
    if ($nombreCliente !== '') {
        if (!isset($clientes[$nombreCliente])) {
            $clientes[$nombreCliente] = 0;
        }
        $clientes[$nombreCliente]++;
    }
}
arsort($clientes);

$queryBase = [
    'reporte' => 'citas',
    'periodo' => $periodo,
    'estado' => $estado,
    'vista' => $vista,
];

include(__DIR__ . '/../Includes/header.php');
?>

<div class="admin-layout">
    <?php include(__DIR__ . '/../Includes/sidebar.php'); ?>

    <main class="admin-main">
        <?php include(__DIR__ . '/../Includes/topbar.php'); ?>

        <section class="admin-content">
            <section class="admin-panel">
                <h2>Reporte de Citas</h2>
                <p>Filtro por dia, semana, mes y estado.</p>

                <form class="admin-toolbar" method="get" action="citas.php" style="grid-template-columns:1fr 1fr auto;">
                    <select name="periodo" class="admin-select">
                        <option value="dia" <?php echo $periodo === 'dia' ? 'selected' : ''; ?>>Dia</option>
                        <option value="semana" <?php echo $periodo === 'semana' ? 'selected' : ''; ?>>Semana</option>
                        <option value="mes" <?php echo $periodo === 'mes' ? 'selected' : ''; ?>>Mes</option>
                        <option value="todo" <?php echo $periodo === 'todo' ? 'selected' : ''; ?>>Todo</option>
                    </select>

                    <select name="estado" class="admin-select">
                        <option value="">Todos los estados</option>
                        <option value="Pendiente" <?php echo $estado === 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="Confirmada" <?php echo $estado === 'Confirmada' ? 'selected' : ''; ?>>Confirmada</option>
                        <option value="Cancelada" <?php echo $estado === 'Cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                    </select>

                    <button class="admin-btn" type="submit">Aplicar filtro</button>
                </form>

                <div class="admin-actions-row" style="margin:12px 0 14px; flex-wrap: wrap;">
                    <a class="admin-btn" href="exportar_pdf.php?<?php echo htmlspecialchars(http_build_query($queryBase), ENT_QUOTES, 'UTF-8'); ?>">📄 Exportar a PDF</a>
                    <a class="admin-btn" href="exportar_excel.php?<?php echo htmlspecialchars(http_build_query($queryBase), ENT_QUOTES, 'UTF-8'); ?>">📊 Exportar a Excel</a>
                    <a class="admin-btn" href="imprimir.php?<?php echo htmlspecialchars(http_build_query($queryBase), ENT_QUOTES, 'UTF-8'); ?>">🖨 Imprimir</a>
                </div>

                <div class="admin-cards" style="margin-bottom:14px;">
                    <article class="admin-card"><h3>Total citas filtradas</h3><p><?php echo count($filtradas); ?></p></article>
                    <article class="admin-card"><h3>Clientes unicos</h3><p><?php echo count($clientes); ?></p></article>
                </div>

                <?php if ($vista === 'clientes'): ?>
                    <h3 style="margin:8px 0;">Clientes mas frecuentes</h3>
                    <div class="admin-table-wrap" style="margin-bottom:14px;">
                        <table class="admin-table" style="min-width:620px;">
                            <thead><tr><th>Cliente</th><th>Citas</th></tr></thead>
                            <tbody>
                                <?php if (!$clientes): ?>
                                    <tr><td colspan="2">No hay clientes en este filtro.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($clientes as $cliente => $cantidad): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($cliente, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo (int)$cantidad; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <h3 style="margin:8px 0;">Estado de citas</h3>
                <div class="admin-table-wrap" style="margin-bottom:14px;">
                    <table class="admin-table" style="min-width:620px;">
                        <thead><tr><th>Estado</th><th>Cantidad</th></tr></thead>
                        <tbody>
                            <?php if (!$conteoEstados): ?>
                                <tr><td colspan="2">Sin datos para este filtro.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($conteoEstados as $estadoNombre => $cantidad): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($estadoNombre, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo (int)$cantidad; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <h3 style="margin:8px 0;">Detalle de citas</h3>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Servicio</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Estado</th>
                                <th>Pago</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$filtradas): ?>
                                <tr><td colspan="6">No hay citas registradas.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($filtradas as $cita): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cita['cliente'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($cita['servicio'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($cita['fecha'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($cita['hora'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($cita['estado'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($cita['pago'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
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
