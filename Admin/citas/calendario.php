<?php
session_start();

require_once __DIR__ . '/_agenda.php';

$servicios = cargar_servicios_citas();
$serviciosIndex = indexar_servicios_por_id($servicios);
$citas = cargar_citas($serviciosIndex);

$view = trim($_GET['view'] ?? 'day');
if (!in_array($view, ['day', 'week', 'month'], true)) {
    $view = 'day';
}

$fechaBase = trim($_GET['date'] ?? date('Y-m-d'));
if (!fecha_valida_ymd($fechaBase)) {
    $fechaBase = date('Y-m-d');
}

$dtBase = new DateTimeImmutable($fechaBase);

include(__DIR__ . '/../Includes/header.php');
?>

<div class="admin-layout">
    <?php include(__DIR__ . '/../Includes/sidebar.php'); ?>

    <main class="admin-main">
        <?php include(__DIR__ . '/../Includes/topbar.php'); ?>

        <section class="admin-content">
            <section class="admin-panel">
                <h2>Calendario de citas</h2>

                <div class="admin-toolbar" style="grid-template-columns: auto auto auto 1fr auto;">
                    <a href="calendario.php?view=day&date=<?php echo urlencode($fechaBase); ?>" class="admin-action">Dia</a>
                    <a href="calendario.php?view=week&date=<?php echo urlencode($fechaBase); ?>" class="admin-action">Semana</a>
                    <a href="calendario.php?view=month&date=<?php echo urlencode($fechaBase); ?>" class="admin-action">Mes</a>
                    <input type="date" class="admin-input" value="<?php echo htmlspecialchars($fechaBase, ENT_QUOTES, 'UTF-8'); ?>" onchange="location.href='calendario.php?view=<?php echo htmlspecialchars($view, ENT_QUOTES, 'UTF-8'); ?>&date=' + this.value;">
                    <a href="listar.php" class="admin-btn">Volver a listado</a>
                </div>

                <?php if ($view === 'day'): ?>
                    <?php $horas = generar_horarios_del_dia($fechaBase); ?>
                    <h3 style="margin-top: 8px; margin-bottom: 10px;">Vista diaria: <?php echo htmlspecialchars($fechaBase, ENT_QUOTES, 'UTF-8'); ?></h3>
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Hora</th>
                                    <th>Estado</th>
                                    <th>Detalle</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$horas): ?>
                                    <tr><td colspan="3">Salon cerrado para esta fecha.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($horas as $hora): ?>
                                        <?php
                                        $citaActiva = null;
                                        foreach ($citas as $cita) {
                                            if (($cita['fecha'] ?? '') !== $fechaBase || !cita_bloquea_espacio($cita)) {
                                                continue;
                                            }
                                            if (cita_se_traslapa($fechaBase, $hora, 30, $cita)) {
                                                $citaActiva = $cita;
                                                break;
                                            }
                                        }
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($hora, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo $citaActiva ? '🔴 Reservado' : '🟢 Libre'; ?></td>
                                            <td>
                                                <?php if ($citaActiva): ?>
                                                    <?php echo htmlspecialchars((string)$citaActiva['cliente'], ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars((string)$citaActiva['servicio'], ENT_QUOTES, 'UTF-8'); ?>
                                                <?php else: ?>
                                                    Disponible
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if ($view === 'week'): ?>
                    <?php
                    $inicioSemana = $dtBase->modify('monday this week');
                    $dias = [];
                    for ($i = 0; $i < 7; $i++) {
                        $dias[] = $inicioSemana->modify('+' . $i . ' day');
                    }
                    ?>
                    <h3 style="margin-top: 8px; margin-bottom: 10px;">Vista semanal</h3>
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Dia</th>
                                    <th>Fecha</th>
                                    <th>Total citas</th>
                                    <th>Pendientes</th>
                                    <th>Confirmadas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dias as $dia): ?>
                                    <?php $resumen = resumen_dia($dia->format('Y-m-d'), $citas); ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($dia->format('l'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($dia->format('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo (int)$resumen['total']; ?></td>
                                        <td><?php echo (int)$resumen['pendientes']; ?></td>
                                        <td><?php echo (int)$resumen['confirmadas']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if ($view === 'month'): ?>
                    <?php
                    $inicioMes = new DateTimeImmutable($dtBase->format('Y-m-01'));
                    $finMes = $inicioMes->modify('last day of this month');
                    $inicioGrid = $inicioMes->modify('monday this week');
                    $finGrid = $finMes->modify('sunday this week');
                    ?>
                    <h3 style="margin-top: 8px; margin-bottom: 10px;">Vista mensual: <?php echo htmlspecialchars($dtBase->format('F Y'), ENT_QUOTES, 'UTF-8'); ?></h3>
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Lunes</th>
                                    <th>Martes</th>
                                    <th>Miercoles</th>
                                    <th>Jueves</th>
                                    <th>Viernes</th>
                                    <th>Sabado</th>
                                    <th>Domingo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $cursor = $inicioGrid;
                                while ($cursor <= $finGrid):
                                ?>
                                    <tr>
                                        <?php for ($i = 0; $i < 7; $i++): ?>
                                            <?php
                                            $fechaDia = $cursor->format('Y-m-d');
                                            $resumen = resumen_dia($fechaDia, $citas);
                                            $esOtroMes = $cursor->format('m') !== $dtBase->format('m');
                                            ?>
                                            <td style="vertical-align: top; <?php echo $esOtroMes ? 'opacity:.55;' : ''; ?>">
                                                <div style="font-weight: 700;"><?php echo htmlspecialchars($cursor->format('d'), ENT_QUOTES, 'UTF-8'); ?></div>
                                                <div style="margin-top: 4px; font-size: 12px;">Citas: <?php echo (int)$resumen['total']; ?></div>
                                                <div style="font-size: 12px; color: #9fd5ac;">Pend: <?php echo (int)$resumen['pendientes']; ?></div>
                                                <div style="font-size: 12px; color: #9bc8ff;">Conf: <?php echo (int)$resumen['confirmadas']; ?></div>
                                            </td>
                                            <?php $cursor = $cursor->modify('+1 day'); ?>
                                        <?php endfor; ?>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        </section>
    </main>
</div>

<?php include(__DIR__ . '/../Includes/footer.php'); ?>
