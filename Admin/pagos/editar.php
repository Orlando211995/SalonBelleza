<?php
session_start();

require_once __DIR__ . '/_data.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pagos = pagos_cargar();
$pago = pagos_buscar_por_id($pagos, $id);

if (!$pago) {
    $_SESSION['error_pago'] = 'Pago no encontrado.';
    header('Location: listar.php');
    exit;
}

$errores = $_SESSION['errores_pago_editar'] ?? [];
$old = $_SESSION['old_pago_editar'] ?? [];
unset($_SESSION['errores_pago_editar'], $_SESSION['old_pago_editar']);

include(__DIR__ . '/../Includes/header.php');
?>

<div class="admin-layout">
    <?php include(__DIR__ . '/../Includes/sidebar.php'); ?>

    <main class="admin-main">
        <?php include(__DIR__ . '/../Includes/topbar.php'); ?>

        <section class="admin-content">
            <section class="admin-panel">
                <h2>Editar pago</h2>

                <?php if ($errores): ?>
                    <div class="admin-alert error">
                        <?php foreach ($errores as $error): ?>
                            <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form action="actualizar.php" method="post" class="admin-form">
                    <input type="hidden" name="id" value="<?php echo (int)$id; ?>">

                    <div class="admin-form-grid">
                        <div class="admin-field">
                            <label for="numero_pago">Numero de pago</label>
                            <input type="text" id="numero_pago" name="numero_pago" maxlength="30" required value="<?php echo htmlspecialchars((string)($old['numero_pago'] ?? ($pago['numero_pago'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="admin-field">
                            <label for="metodo">Metodo</label>
                            <?php $metodo = (string)($old['metodo'] ?? ($pago['metodo'] ?? 'SINPE Movil')); ?>
                            <select id="metodo" name="metodo" required>
                                <?php foreach (pagos_metodos_validos() as $op): ?>
                                    <option value="<?php echo htmlspecialchars($op, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $metodo === $op ? 'selected' : ''; ?>><?php echo htmlspecialchars($op, ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="admin-field">
                            <label for="estado">Estado</label>
                            <?php $estado = (string)($old['estado'] ?? ($pago['estado'] ?? 'Pendiente')); ?>
                            <select id="estado" name="estado" required>
                                <?php foreach (pagos_estados_validos() as $op): ?>
                                    <option value="<?php echo htmlspecialchars($op, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $estado === $op ? 'selected' : ''; ?>><?php echo htmlspecialchars($op, ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="admin-field">
                            <label for="monto">Monto</label>
                            <input type="number" id="monto" name="monto" min="0" step="1" required value="<?php echo htmlspecialchars((string)($old['monto'] ?? ($pago['monto'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="admin-field" style="grid-column: 1 / -1;">
                            <label for="observaciones">Observaciones</label>
                            <textarea id="observaciones" name="observaciones" maxlength="1200"><?php echo htmlspecialchars((string)($old['observaciones'] ?? ($pago['observaciones'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
                    </div>

                    <div class="admin-actions-row">
                        <button type="submit" class="admin-btn">Actualizar pago</button>
                        <a href="listar.php" class="admin-action">Cancelar</a>
                    </div>
                </form>
            </section>
        </section>
    </main>
</div>

<?php include(__DIR__ . '/../Includes/footer.php'); ?>
