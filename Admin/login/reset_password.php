<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../../Includes/conexion.php');

$tokenPlano = trim((string)($_GET['token'] ?? $_POST['token'] ?? ''));
$mensaje = '';
$error = '';
$tokenValido = false;
$adminId = 0;

if ($tokenPlano !== '') {
    $pdo = obtenerConexionSalon();

    if ($pdo) {
        $tokenHash = hash('sha256', $tokenPlano);
        $stmt = $pdo->prepare('SELECT id_admin FROM administrador WHERE reset_token = :token AND reset_expires_at IS NOT NULL AND reset_expires_at >= NOW() LIMIT 1');
        $stmt->execute(['token' => $tokenHash]);
        $admin = $stmt->fetch();

        if ($admin) {
            $tokenValido = true;
            $adminId = (int)$admin['id_admin'];
        }
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $nueva = (string)($_POST['password_nueva'] ?? '');
    $confirmar = (string)($_POST['password_confirmar'] ?? '');

    if (!$tokenValido) {
        $error = 'El enlace no es valido o ya vencio.';
    } elseif (strlen($nueva) < 8) {
        $error = 'La nueva contrasena debe tener al menos 8 caracteres.';
    } elseif ($nueva !== $confirmar) {
        $error = 'Las contrasenas no coinciden.';
    } else {
        $hash = password_hash($nueva, PASSWORD_DEFAULT);
        $pdo = obtenerConexionSalon();

        if (!$pdo) {
            $error = 'No fue posible conectar con la base de datos.';
        } else {
            $upd = $pdo->prepare('UPDATE administrador SET password = :password, reset_token = NULL, reset_expires_at = NULL WHERE id_admin = :id');
            $upd->execute([
                'password' => $hash,
                'id' => $adminId,
            ]);

            $mensaje = 'Contrasena actualizada correctamente. Ya puedes iniciar sesion.';
            $tokenValido = false;
        }
    }
}

include(__DIR__ . '/../Includes/header.php');
?>

<main style="min-height:100vh;display:grid;place-items:center;padding:24px;">
    <section class="admin-panel" style="width:min(520px,100%);">
        <h2 style="text-align:center;margin-bottom:8px;">Restablecer contrasena</h2>

        <?php if ($error !== ''): ?>
            <div class="admin-alert error"><p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p></div>
        <?php endif; ?>

        <?php if ($mensaje !== ''): ?>
            <div class="admin-alert success"><p><?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></p></div>
            <p style="margin-top:14px;text-align:center;"><a href="/Admin/login/login.php" style="color:#ffd34f;">Ir al login</a></p>
        <?php elseif ($tokenValido): ?>
            <form method="post" class="admin-form">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($tokenPlano, ENT_QUOTES, 'UTF-8'); ?>">

                <div class="admin-field">
                    <label for="password_nueva">Nueva contrasena</label>
                    <input id="password_nueva" name="password_nueva" type="password" required>
                </div>

                <div class="admin-field">
                    <label for="password_confirmar">Confirmar contrasena</label>
                    <input id="password_confirmar" name="password_confirmar" type="password" required>
                </div>

                <button class="admin-btn" type="submit">Guardar nueva contrasena</button>
            </form>
        <?php else: ?>
            <div class="admin-alert error"><p>El enlace no es valido o ya vencio.</p></div>
            <p style="margin-top:14px;text-align:center;"><a href="/Admin/login/forgot_password.php" style="color:#ffd34f;">Solicitar nuevo enlace</a></p>
        <?php endif; ?>
    </section>
</main>

<?php include(__DIR__ . '/../Includes/footer.php'); ?>
