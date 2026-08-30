<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$esLocal = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
if (!$esLocal) {
    http_response_code(403);
    echo 'Acceso permitido solo en localhost.';
    exit;
}

$archivoHash = __DIR__ . '/hash.json';
$usuario = trim($_POST['usuario'] ?? 'admin');
$mensaje = '';
$error = '';
$sqlUpdate = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $passwordNueva = (string)($_POST['password_nueva'] ?? '');

    if ($usuario === '' || $passwordNueva === '') {
        $error = 'Usuario y nueva contrasena son obligatorios.';
    } elseif (strlen($passwordNueva) < 8) {
        $error = 'La nueva contrasena debe tener al menos 8 caracteres.';
    } else {
        $hash = password_hash($passwordNueva, PASSWORD_DEFAULT);
        $contenido = [
            'usuario' => $usuario,
            'password_hash' => $hash,
            'updated_at' => date('c'),
            'note' => 'Generado desde generar_hash.php',
        ];

        file_put_contents($archivoHash, json_encode($contenido, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $mensaje = 'hash.json actualizado correctamente.';
        $sqlUpdate = "UPDATE administrador SET password='" . addslashes($hash) . "' WHERE usuario='" . addslashes($usuario) . "';";
    }
}

include(__DIR__ . '/../Includes/header.php');
?>

<main style="min-height:100vh;display:grid;place-items:center;padding:24px;">
    <section class="admin-panel" style="width:min(560px,100%);">
        <h2>Generar Hash de Contrasena</h2>
        <p style="margin-bottom:14px;">Actualiza hash.json y copia el SQL para tu base de datos.</p>

        <?php if ($mensaje !== ''): ?>
            <div class="admin-alert success"><p><?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></p></div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="admin-alert error"><p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p></div>
        <?php endif; ?>

        <form method="post" class="admin-form">
            <div class="admin-field">
                <label for="usuario">Usuario</label>
                <input id="usuario" name="usuario" type="text" value="<?php echo htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="admin-field">
                <label for="password_nueva">Nueva contrasena</label>
                <input id="password_nueva" name="password_nueva" type="password" required>
            </div>

            <button class="admin-btn" type="submit">Generar y guardar hash</button>
        </form>

        <?php if ($sqlUpdate !== ''): ?>
            <div class="admin-field" style="margin-top:14px;">
                <label>SQL para actualizar la base de datos</label>
                <textarea readonly><?php echo htmlspecialchars($sqlUpdate, ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
        <?php endif; ?>

        <p style="margin-top:14px;font-size:13px;color:#bcc5da;">Despues de actualizar el hash, inicia sesion en <a href="/Admin/login/login.php" style="color:#ffd34f;">/Admin/login/login.php</a>.</p>
    </section>
</main>

<?php include(__DIR__ . '/../Includes/footer.php'); ?>
