<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../../Includes/conexion.php');

$autoloadPath = __DIR__ . '/../../vendor/autoload.php';
$phpMailerDisponible = false;
if (file_exists($autoloadPath)) {
    require_once($autoloadPath);
    $phpMailerDisponible = class_exists('PHPMailer\\PHPMailer\\PHPMailer');
}

$mensaje = '';
$error = '';
$debugLink = '';

function construirResetUrl(string $token): string
{
    $esHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    $protocolo = $esHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';

    return $protocolo . '://' . $host . '/Admin/login/reset_password.php?token=' . urlencode($token);
}

function enviarCorreoResetSmtp(string $correo, string $nombre, string $urlReset): bool
{
    if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        return false;
    }

    $smtpUser = trim((string)(getenv('SMTP_USER') ?: ''));
    $smtpPass = trim((string)(getenv('SMTP_PASS') ?: ''));
    if ($smtpUser === '' || $smtpPass === '') {
        return false;
    }

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = (string)(getenv('SMTP_HOST') ?: 'smtp.gmail.com');
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUser;
        $mail->Password = $smtpPass;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int)(getenv('SMTP_PORT') ?: 587);
        $mail->CharSet = 'UTF-8';

        $from = (string)(getenv('SMTP_FROM') ?: $smtpUser);
        $fromName = (string)(getenv('SMTP_FROM_NAME') ?: 'Alfredo Salon Estudio CR');

        $mail->setFrom($from, $fromName);
        $mail->addAddress($correo, $nombre);
        $mail->isHTML(true);
        $mail->Subject = 'Recuperacion de contrasena - Alfredo Salon Estudio CR';
        $mail->Body = '<p>Hola ' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . ',</p>'
            . '<p>Recibimos una solicitud para restablecer tu contrasena.</p>'
            . '<p>Usa este enlace (valido por 30 minutos):<br><a href="' . htmlspecialchars($urlReset, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($urlReset, ENT_QUOTES, 'UTF-8') . '</a></p>'
            . '<p>Si no solicitaste este cambio, ignora este correo.</p>';
        $mail->AltBody = "Hola {$nombre},\n\n"
            . "Recibimos una solicitud para restablecer tu contrasena.\n"
            . "Usa este enlace (valido por 30 minutos):\n{$urlReset}\n\n"
            . "Si no solicitaste este cambio, ignora este correo.";

        return $mail->send();
    } catch (Throwable $e) {
        return false;
    }
}

function enviarCorreoResetMail(string $correo, string $nombre, string $urlReset): bool
{
    $asunto = 'Recuperacion de contrasena - Alfredo Salon Estudio CR';
    $cuerpo = "Hola {$nombre},\n\n";
    $cuerpo .= "Recibimos una solicitud para restablecer tu contrasena.\n";
    $cuerpo .= "Usa este enlace (valido por 30 minutos):\n{$urlReset}\n\n";
    $cuerpo .= "Si no solicitaste este cambio, ignora este correo.\n";

    $headers = "From: no-reply@alfredosaloncr.local\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    return @mail($correo, $asunto, $cuerpo, $headers);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $correo = trim((string)($_POST['correo'] ?? ''));

    if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'Debes ingresar un correo valido.';
    } else {
        $pdo = obtenerConexionSalon();
        if (!$pdo) {
            $error = 'No fue posible conectar con la base de datos.';
        } else {
            $stmt = $pdo->prepare('SELECT id_admin, nombre, correo FROM administrador WHERE correo = :correo LIMIT 1');
            $stmt->execute(['correo' => $correo]);
            $admin = $stmt->fetch();

            if ($admin) {
                $tokenPlano = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $tokenPlano);
                $expira = (new DateTimeImmutable('+30 minutes'))->format('Y-m-d H:i:s');

                $update = $pdo->prepare('UPDATE administrador SET reset_token = :token, reset_expires_at = :expira WHERE id_admin = :id');
                $update->execute([
                    'token' => $tokenHash,
                    'expira' => $expira,
                    'id' => (int)$admin['id_admin'],
                ]);

                $urlReset = construirResetUrl($tokenPlano);
                $nombre = (string)($admin['nombre'] ?? 'Administrador');

                $enviado = false;
                if ($phpMailerDisponible) {
                    $enviado = enviarCorreoResetSmtp($correo, $nombre, $urlReset);
                }

                if (!$enviado) {
                    $enviado = enviarCorreoResetMail($correo, $nombre, $urlReset);
                }

                // En localhost normalmente mail() no esta configurado; mostramos el enlace para pruebas.
                if (!$enviado && in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
                    $debugLink = $urlReset;
                }
            }

            $mensaje = 'Si el correo existe, te enviamos un enlace para restablecer la contrasena.';
        }
    }
}

include(__DIR__ . '/../Includes/header.php');
?>

<main style="min-height:100vh;display:grid;place-items:center;padding:24px;">
    <section class="admin-panel" style="width:min(520px,100%);">
        <h2 style="text-align:center;margin-bottom:8px;">Recuperar contrasena</h2>
        <p style="text-align:center;color:#bcc5da;margin-bottom:14px;">Ingresa tu correo de administrador.</p>

        <?php if ($error !== ''): ?>
            <div class="admin-alert error"><p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p></div>
        <?php endif; ?>

        <?php if ($mensaje !== ''): ?>
            <div class="admin-alert success"><p><?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></p></div>
        <?php endif; ?>

        <form method="post" class="admin-form">
            <div class="admin-field">
                <label for="correo">Correo</label>
                <input id="correo" name="correo" type="email" required>
            </div>
            <button class="admin-btn" type="submit">Enviar enlace</button>
        </form>

        <?php if ($debugLink !== ''): ?>
            <div class="admin-alert success" style="margin-top:14px;">
                <p>Modo local: copia este enlace para continuar el flujo.</p>
                <p><a style="color:#ffd34f;word-break:break-all;" href="<?php echo htmlspecialchars($debugLink, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($debugLink, ENT_QUOTES, 'UTF-8'); ?></a></p>
            </div>
        <?php endif; ?>

		<div class="admin-alert" style="margin-top:14px;background:#142235;border:1px solid #2b4a75;color:#c7dcff;">
			<p>SMTP Gmail opcional (variables de entorno): SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS, SMTP_FROM, SMTP_FROM_NAME.</p>
		</div>

        <p style="margin-top:14px;text-align:center;"><a href="/Admin/login/login.php" style="color:#ffd34f;">Volver al login</a></p>
    </section>
</main>

<?php include(__DIR__ . '/../Includes/footer.php'); ?>
