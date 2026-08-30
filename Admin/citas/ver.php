<?php
session_start();

require_once __DIR__ . '/_agenda.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$servicios = cargar_servicios_citas();
$serviciosIndex = indexar_servicios_por_id($servicios);
$citas = cargar_citas($serviciosIndex);

$cita = null;
foreach ($citas as $item) {
    if ((int)($item['id'] ?? 0) === $id) {
        $cita = $item;
        break;
    }
}

if (!$cita) {
    $_SESSION['error_cita'] = 'Cita no encontrada.';
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
                <h2>Detalle de cita</h2>

                <div class="admin-form-grid">
                    <div class="admin-field"><label>Cliente</label><p><?php echo htmlspecialchars((string)($cita['cliente'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p></div>
                    <div class="admin-field"><label>Telefono</label><p><?php echo htmlspecialchars((string)($cita['telefono'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p></div>
                    <div class="admin-field"><label>Correo</label><p><?php echo htmlspecialchars((string)($cita['correo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p></div>
                    <div class="admin-field"><label>Servicio</label><p><?php echo htmlspecialchars((string)($cita['servicio'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p></div>
                    <div class="admin-field"><label>Duracion</label><p><?php echo (int)($cita['duracion'] ?? 0); ?> min</p></div>
                    <div class="admin-field"><label>Empleado</label><p><?php echo htmlspecialchars((string)($cita['empleado'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p></div>
                    <div class="admin-field"><label>Fecha</label><p><?php echo htmlspecialchars((string)($cita['fecha'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p></div>
                    <div class="admin-field"><label>Hora</label><p><?php echo htmlspecialchars((string)($cita['hora'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p></div>
                    <div class="admin-field"><label>Estado</label><p><?php echo htmlspecialchars((string)($cita['estado'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p></div>
                    <div class="admin-field"><label>Pago</label><p><?php echo htmlspecialchars((string)($cita['pago'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p></div>
                    <div class="admin-field" style="grid-column: 1 / -1;"><label>Observaciones</label><p><?php echo nl2br(htmlspecialchars((string)($cita['observaciones'] ?? ''), ENT_QUOTES, 'UTF-8')); ?></p></div>
                </div>

                <div class="admin-actions-row" style="margin-top: 14px;">
                    <a href="editar.php?id=<?php echo (int)($cita['id'] ?? 0); ?>" class="admin-btn">Editar cita</a>
                    <a href="listar.php" class="admin-action">Volver</a>
                </div>
            </section>
        </section>
    </main>
</div>

<?php include(__DIR__ . '/../Includes/footer.php'); ?>
