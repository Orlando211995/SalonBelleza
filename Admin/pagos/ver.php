<?php
session_start();

require_once __DIR__ . '/_data.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$printMode = isset($_GET['print']) && $_GET['print'] === '1';

$pagos = pagos_cargar();
$pago = pagos_buscar_por_id($pagos, $id);

if (!$pago) {
    $_SESSION['error_pago'] = 'Pago no encontrado.';
    header('Location: listar.php');
    exit;
}

if ($printMode) {
    ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pago <?php echo htmlspecialchars((string)($pago['numero_pago'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        body { font-family: Arial, sans-serif; color: #111; margin: 0; padding: 20px; }
        .card { max-width: 760px; margin: 0 auto; border: 1px solid #ddd; border-radius: 10px; padding: 18px; }
        h1 { margin: 0 0 12px; }
        p { margin: 6px 0; line-height: 1.6; }
        @media print { .acciones { display: none; } }
    </style>
</head>
<body>
    <div class="card">
        <h1>Pago #<?php echo htmlspecialchars((string)($pago['numero_pago'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></h1>
        <p><strong>Cliente:</strong> <?php echo htmlspecialchars((string)($pago['cliente'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
        <p><strong>Tipo:</strong> <?php echo htmlspecialchars((string)($pago['tipo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
        <p><strong>Numero:</strong> <?php echo htmlspecialchars((string)($pago['numero'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
        <p><strong>Metodo:</strong> <?php echo htmlspecialchars((string)($pago['metodo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
        <p><strong>Monto:</strong> ₡<?php echo number_format((float)($pago['monto'] ?? 0), 0, ',', '.'); ?></p>
        <p><strong>Fecha:</strong> <?php echo htmlspecialchars((string)($pago['fecha'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
        <p><strong>Estado:</strong> <?php echo htmlspecialchars((string)($pago['estado'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
        <p><strong>Comprobante:</strong> <?php echo !empty($pago['comprobante']) ? htmlspecialchars((string)$pago['comprobante'], ENT_QUOTES, 'UTF-8') : 'No adjunto'; ?></p>
        <p><strong>Observaciones:</strong> <?php echo nl2br(htmlspecialchars((string)($pago['observaciones'] ?? ''), ENT_QUOTES, 'UTF-8')); ?></p>

        <div class="acciones" style="margin-top: 14px;">
            <a href="ver.php?id=<?php echo (int)$id; ?>">Volver</a>
        </div>
    </div>
    <script>window.print();</script>
</body>
</html>
    <?php
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
                <h2>Ver pago</h2>

                <div class="admin-form-grid">
                    <div class="admin-field"><label>Pago #</label><p><?php echo htmlspecialchars((string)($pago['numero_pago'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p></div>
                    <div class="admin-field"><label>Fecha</label><p><?php echo htmlspecialchars((string)($pago['fecha'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p></div>
                    <div class="admin-field"><label>Cliente</label><p><?php echo htmlspecialchars((string)($pago['cliente'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p></div>
                    <div class="admin-field"><label>Telefono</label><p><?php echo htmlspecialchars((string)($pago['telefono'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p></div>
                    <div class="admin-field"><label>Correo</label><p><?php echo htmlspecialchars((string)($pago['correo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p></div>
                    <div class="admin-field"><label>Tipo</label><p><?php echo htmlspecialchars((string)($pago['tipo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p></div>
                    <div class="admin-field"><label>Numero</label><p><?php echo htmlspecialchars((string)($pago['numero'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p></div>
                    <div class="admin-field"><label>Metodo</label><p><?php echo htmlspecialchars((string)($pago['metodo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p></div>
                    <div class="admin-field"><label>Monto</label><p>₡<?php echo number_format((float)($pago['monto'] ?? 0), 0, ',', '.'); ?></p></div>
                    <div class="admin-field"><label>Estado</label><p><?php echo htmlspecialchars((string)($pago['estado'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p></div>
                    <div class="admin-field" style="grid-column: 1 / -1;"><label>Comprobante</label><p><?php if (!empty($pago['comprobante'])): ?><a href="comprobante.php?id=<?php echo (int)$id; ?>" class="admin-action">📄 Ver imagen</a><?php else: ?>No adjunto<?php endif; ?></p></div>
                    <div class="admin-field" style="grid-column: 1 / -1;"><label>Observaciones</label><p><?php echo nl2br(htmlspecialchars((string)($pago['observaciones'] ?? ''), ENT_QUOTES, 'UTF-8')); ?></p></div>
                </div>

                <div class="admin-actions-row" style="margin-top: 14px;">
                    <a href="editar.php?id=<?php echo (int)$id; ?>" class="admin-btn">Editar pago</a>
                    <a href="imprimir.php?id=<?php echo (int)$id; ?>" class="admin-action">Imprimir</a>
                    <a href="listar.php" class="admin-action">Volver</a>
                </div>
            </section>
        </section>
    </main>
</div>

<?php include(__DIR__ . '/../Includes/footer.php'); ?>
