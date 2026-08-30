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

$errores = $_SESSION['errores_categoria_editar'] ?? [];
$old = $_SESSION['old_categoria_editar'] ?? [];
unset($_SESSION['errores_categoria_editar'], $_SESSION['old_categoria_editar']);

include(__DIR__ . '/../Includes/header.php');
?>

<div class="admin-layout">
    <?php include(__DIR__ . '/../Includes/sidebar.php'); ?>

    <main class="admin-main">
        <?php include(__DIR__ . '/../Includes/topbar.php'); ?>

        <section class="admin-content">
            <section class="admin-panel">
                <h2>Editar categoria</h2>

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
                            <label for="nombre">Nombre de categoria</label>
                            <input type="text" id="nombre" name="nombre" maxlength="100" required value="<?php echo htmlspecialchars($old['nombre'] ?? ($categoria['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="admin-field">
                            <label for="tipo">Tipo</label>
                            <?php $tipo = $old['tipo'] ?? ($categoria['tipo'] ?? ''); ?>
                            <select id="tipo" name="tipo" required>
                                <option value="">Seleccionar tipo</option>
                                <option value="Producto" <?php echo $tipo === 'Producto' ? 'selected' : ''; ?>>Producto</option>
                                <option value="Servicio" <?php echo $tipo === 'Servicio' ? 'selected' : ''; ?>>Servicio</option>
                                <option value="Ambos" <?php echo $tipo === 'Ambos' ? 'selected' : ''; ?>>Ambos</option>
                            </select>
                        </div>

                        <div class="admin-field" style="grid-column: 1 / -1;">
                            <label for="descripcion">Descripcion</label>
                            <textarea id="descripcion" name="descripcion" maxlength="600" required><?php echo htmlspecialchars($old['descripcion'] ?? ($categoria['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>

                        <div class="admin-field">
                            <label>Estado</label>
                            <?php $estado = $old['estado'] ?? ($categoria['estado'] ?? 'Activo'); ?>
                            <div class="admin-radios">
                                <label><input type="radio" name="estado" value="Activo" <?php echo $estado === 'Activo' ? 'checked' : ''; ?>> Activo</label>
                                <label><input type="radio" name="estado" value="Inactivo" <?php echo $estado === 'Inactivo' ? 'checked' : ''; ?>> Inactivo</label>
                            </div>
                        </div>
                    </div>

                    <div class="admin-actions-row">
                        <button type="submit" class="admin-btn">Actualizar categoria</button>
                        <a href="listar.php" class="admin-action">Cancelar</a>
                    </div>
                </form>
            </section>
        </section>
    </main>
</div>

<?php include(__DIR__ . '/../Includes/footer.php'); ?>
