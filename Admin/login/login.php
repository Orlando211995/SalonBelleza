<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

require_once(__DIR__ . '/../../Includes/conexion.php');

function cargarCredencialesJson(string $ruta): array
{
	if (!file_exists($ruta)) {
		return [];
	}

	$contenido = file_get_contents($ruta);
	$json = json_decode($contenido ?: '{}', true);

	return is_array($json) ? $json : [];
}

function autenticarDb(string $usuario, string $password): bool
{
	$pdo = obtenerConexionSalon();
	if (!$pdo) {
		return false;
	}

	$stmt = $pdo->prepare('SELECT id_admin, usuario, password FROM administrador WHERE usuario = :usuario LIMIT 1');
	$stmt->execute(['usuario' => $usuario]);
	$admin = $stmt->fetch();

	if (!$admin) {
		return false;
	}

	$hash = (string)($admin['password'] ?? '');
	if ($hash === '' || $hash === '$2y$10$CAMBIAR_HASH') {
		return false;
	}

	return password_verify($password, $hash);
}

function autenticarJson(string $usuario, string $password, string $archivoHash): bool
{
	$credenciales = cargarCredencialesJson($archivoHash);
	$usuarioJson = (string)($credenciales['usuario'] ?? 'admin');
	$hashJson = (string)($credenciales['password_hash'] ?? '');

	if ($hashJson === '' || $usuario !== $usuarioJson) {
		return false;
	}

	return password_verify($password, $hashJson);
}

$archivoHash = __DIR__ . '/hash.json';
$usuario = trim($_POST['usuario'] ?? '');
$error = '';

if (!empty($_SESSION['admin_auth'])) {
	header('Location: /Admin/login/dashboard.php');
	exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
	$password = (string)($_POST['password'] ?? '');

	if ($usuario === '' || $password === '') {
		$error = 'Debes ingresar usuario y contrasena.';
	} else {
		$validoDb = autenticarDb($usuario, $password);
		$validoJson = autenticarJson($usuario, $password, $archivoHash);

		if ($validoDb || $validoJson) {
			session_regenerate_id(true);
			$_SESSION['admin_auth'] = true;
			$_SESSION['admin_usuario'] = $usuario;

			$next = trim($_GET['next'] ?? '');
			if ($next === '' || strpos($next, '/Admin/') !== 0) {
				$next = '/Admin/login/dashboard.php';
			}

			header('Location: ' . $next);
			exit;
		}

		$error = 'Usuario o contrasena incorrectos.';
	}
}

include(__DIR__ . '/../Includes/header.php');
?>

<main style="min-height:100vh;display:grid;place-items:center;padding:24px;">
	<section class="admin-panel" style="width:min(440px,100%);">
		<div style="display:grid;place-items:center;margin-bottom:10px;">
			<img
				src="/Assets/img/logo/logodashboard.jpg"
				alt="Logo Alfredo Salon Estudio CR"
				style="width:120px;height:120px;object-fit:cover;border-radius:50%;border:2px solid #e0bb35;box-shadow:0 10px 25px rgba(0,0,0,.35);">
		</div>

		<h2 style="margin-bottom:6px;text-align:center;">Alfredo Salon Estudio CR</h2>
		<p style="margin-bottom:6px;font-weight:600;color:#e0bb35;text-align:center;">Ingreso Administrador</p>
		<div style="margin-bottom:14px;"></div>

		<?php if ($error !== ''): ?>
			<div class="admin-alert error"><p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p></div>
		<?php endif; ?>

		<form method="post" action="" class="admin-form">
			<div class="admin-field">
				<label for="usuario">Usuario</label>
				<input id="usuario" name="usuario" type="text" value="<?php echo htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8'); ?>" required>
			</div>

			<div class="admin-field">
				<label for="password">Contrasena</label>
				<input id="password" name="password" type="password" required>
			</div>

			<button class="admin-btn" type="submit">Ingresar</button>
		</form>

		<p style="margin-top:10px;font-size:14px;text-align:center;">
			<a href="/Admin/login/forgot_password.php" style="color:#ffd34f;">Olvide mi contrasena</a>
		</p>

		<p style="margin-top:14px;font-size:13px;color:#bcc5da;">
			Para cambiar hash de contrasena usa: <a href="/Admin/login/generar_hash.php" style="color:#ffd34f;">/Admin/login/generar_hash.php</a>
		</p>
	</section>
</main>

<?php include(__DIR__ . '/../Includes/footer.php'); ?>
