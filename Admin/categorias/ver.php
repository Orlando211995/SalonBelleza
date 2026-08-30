<?php
session_start();

require_once __DIR__ . '/_data.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$categoria = categorias_buscar_por_id($id);

if (!$categoria) {
    $_SESSION['error_categoria'] = 'Categoria no encontrada.';
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
                <h2>Detalle de categoria</h2>

                <div class="admin-form-grid">
                    <div class="admin-field">
                        <label>Nombre</label>
                        <p><?php echo htmlspecialchars($categoria['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>

                    <div class="admin-field">
                        <label>Tipo</label>
                        <p><?php echo htmlspecialchars($categoria['tipo'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>

                    <div class="admin-field">
                        <label>Estado</label>
                        <p><?php echo htmlspecialchars($categoria['estado'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>

                    <div class="admin-field" style="grid-column: 1 / -1;">
                        <label>Descripcion</label>
                        <p><?php echo nl2br(htmlspecialchars($categoria['descripcion'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
                    </div>
                </div>

                <div class="admin-actions-row" style="margin-top: 14px;">
                    <a href="editar.php?id=<?php echo (int)($categoria['id'] ?? 0); ?>" class="admin-btn">Editar categoria</a>
                    <a href="listar.php" class="admin-action">Volver</a>
                </div>
            </section>
        </section>
    </main>
</div>

<?php include(__DIR__ . '/../Includes/footer.php'); ?>
