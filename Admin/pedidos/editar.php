<?php
session_start();

require_once __DIR__ . '/_data.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pedidos = pedidos_cargar();
$pedido = pedidos_buscar_por_id($pedidos, $id);

if (!$pedido) {
    $_SESSION['error_pedido'] = 'Pedido no encontrado.';
    header('Location: listar.php');
    exit;
}

$errores = $_SESSION['errores_pedido_editar'] ?? [];
$old = $_SESSION['old_pedido_editar'] ?? [];
unset($_SESSION['errores_pedido_editar'], $_SESSION['old_pedido_editar']);

include(__DIR__ . '/../Includes/header.php');
?>

<div class="admin-layout">
    <?php include(__DIR__ . '/../Includes/sidebar.php'); ?>

    <main class="admin-main">
        <?php include(__DIR__ . '/../Includes/topbar.php'); ?>

        <section class="admin-content">
            <section class="admin-panel">
                <h2>Editar pedido</h2>

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
                            <label for="numero_pedido">Numero de pedido</label>
                            <input type="text" id="numero_pedido" name="numero_pedido" maxlength="40" required value="<?php echo htmlspecialchars((string)($old['numero_pedido'] ?? ($pedido['numero_pedido'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="admin-field">
                            <label for="estado">Estado</label>
                            <?php $estado = (string)($old['estado'] ?? ($pedido['estado'] ?? 'Pendiente')); ?>
                            <select id="estado" name="estado" required>
                                <?php foreach (pedidos_estados_validos() as $op): ?>
                                    <option value="<?php echo htmlspecialchars($op, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $estado === $op ? 'selected' : ''; ?>><?php echo htmlspecialchars($op, ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="admin-field">
                            <label for="metodo_pago">Metodo de pago</label>
                            <input type="text" id="metodo_pago" value="SINPE" readonly>
                            <input type="hidden" name="metodo_pago" value="SINPE">
                        </div>

                        <div class="admin-field">
                            <label for="total">Total</label>
                            <input type="number" id="total" name="total" min="0" step="1" required value="<?php echo htmlspecialchars((string)($old['total'] ?? ($pedido['total'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="admin-field" style="grid-column: 1 / -1;">
                            <label for="direccion">Direccion de entrega</label>
                            <textarea id="direccion" name="direccion" maxlength="700" required><?php echo htmlspecialchars((string)($old['direccion'] ?? ($pedido['direccion'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>

                        <div class="admin-field" style="grid-column: 1 / -1;">
                            <label for="observaciones">Observaciones</label>
                            <textarea id="observaciones" name="observaciones" maxlength="1200"><?php echo htmlspecialchars((string)($old['observaciones'] ?? ($pedido['observaciones'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
                    </div>

                    <div class="admin-actions-row">
                        <button type="submit" class="admin-btn">Actualizar pedido</button>
                        <a href="listar.php" class="admin-action">Cancelar</a>
                    </div>
                </form>
            </section>
        </section>
    </main>
</div>

<?php include(__DIR__ . '/../Includes/footer.php'); ?>
