<?php
session_start();

require_once __DIR__ . '/_agenda.php';

$servicios = cargar_servicios_citas();
$serviciosIndex = indexar_servicios_por_id($servicios);
$citas = cargar_citas($serviciosIndex);

$errores = $_SESSION['errores_cita'] ?? [];
$old = $_SESSION['old_cita'] ?? [];
unset($_SESSION['errores_cita'], $_SESSION['old_cita']);

$servicioId = (int)($old['servicio_id'] ?? ($_GET['servicio_id'] ?? ($servicios[0]['id'] ?? 0)));
$fecha = (string)($old['fecha'] ?? ($_GET['fecha'] ?? date('Y-m-d')));

$servicio = servicio_por_id($servicioId, $serviciosIndex);
$duracion = (int)($servicio['duracion'] ?? 30);
$horasDisponibles = fecha_valida_ymd($fecha) ? generar_horas_disponibles($fecha, $duracion, $citas) : [];

include(__DIR__ . '/../Includes/header.php');
?>

<div class="admin-layout">
    <?php include(__DIR__ . '/../Includes/sidebar.php'); ?>

    <main class="admin-main">
        <?php include(__DIR__ . '/../Includes/topbar.php'); ?>

        <section class="admin-content">
            <section class="admin-panel">
                <h2>Nueva cita</h2>

                <?php if ($errores): ?>
                    <div class="admin-alert error">
                        <?php foreach ($errores as $error): ?>
                            <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form action="agregar.php" method="get" class="admin-form" style="margin-bottom: 8px;">
                    <div class="admin-form-grid">
                        <div class="admin-field">
                            <label for="servicio_id_filtro">Servicio para disponibilidad</label>
                            <select id="servicio_id_filtro" name="servicio_id" required>
                                <?php foreach ($servicios as $item): ?>
                                    <option value="<?php echo (int)$item['id']; ?>" <?php echo $servicioId === (int)$item['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars((string)$item['nombre'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo (int)($item['duracion'] ?? 0); ?> min)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="admin-field">
                            <label for="fecha_filtro">Fecha</label>
                            <input type="date" id="fecha_filtro" name="fecha" value="<?php echo htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                    </div>
                    <div class="admin-actions-row">
                        <button type="submit" class="admin-action">Ver horas disponibles</button>
                    </div>
                </form>

                <?php if ($servicio): ?>
                    <p style="margin-bottom: 10px;">Duracion automatica del servicio: <strong><?php echo (int)$duracion; ?> min</strong></p>
                <?php endif; ?>

                <div class="admin-table-wrap" style="margin-bottom: 14px;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$horasDisponibles): ?>
                                <tr><td colspan="2">No hay horarios disponibles para la combinacion actual.</td></tr>
                            <?php else: ?>
                                <?php foreach ($horasDisponibles as $hora): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($hora, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>🟢 Libre</td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <form action="guardar.php" method="post" class="admin-form">
                    <div class="admin-form-grid">
                        <div class="admin-field">
                            <label for="cliente">Nombre del cliente</label>
                            <input type="text" id="cliente" name="cliente" maxlength="140" required value="<?php echo htmlspecialchars((string)($old['cliente'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="admin-field">
                            <label for="telefono">Telefono</label>
                            <input type="text" id="telefono" name="telefono" maxlength="40" required value="<?php echo htmlspecialchars((string)($old['telefono'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="admin-field">
                            <label for="correo">Correo</label>
                            <input type="email" id="correo" name="correo" maxlength="160" required value="<?php echo htmlspecialchars((string)($old['correo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="admin-field">
                            <label for="servicio_id">Servicio</label>
                            <select id="servicio_id" name="servicio_id" required>
                                <?php foreach ($servicios as $item): ?>
                                    <option value="<?php echo (int)$item['id']; ?>" <?php echo $servicioId === (int)$item['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars((string)$item['nombre'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo (int)($item['duracion'] ?? 0); ?> min)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="admin-field">
                            <label for="empleado">Empleado (opcional)</label>
                            <input type="text" id="empleado" name="empleado" maxlength="140" value="<?php echo htmlspecialchars((string)($old['empleado'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="admin-field">
                            <label for="fecha">Fecha</label>
                            <input type="date" id="fecha" name="fecha" required value="<?php echo htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="admin-field">
                            <label for="hora">Hora</label>
                            <select id="hora" name="hora" required>
                                <option value="">Seleccionar hora</option>
                                <?php $horaOld = (string)($old['hora'] ?? ''); ?>
                                <?php foreach ($horasDisponibles as $hora): ?>
                                    <option value="<?php echo htmlspecialchars($hora, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $horaOld === $hora ? 'selected' : ''; ?>><?php echo htmlspecialchars($hora, ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="admin-field">
                            <label for="pago">Pago</label>
                            <?php $pagoOld = (string)($old['pago'] ?? 'No aplica'); ?>
                            <select id="pago" name="pago" required>
                                <?php foreach (pagos_cita_validos() as $pago): ?>
                                    <option value="<?php echo htmlspecialchars($pago, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $pagoOld === $pago ? 'selected' : ''; ?>><?php echo htmlspecialchars($pago, ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="admin-field" style="grid-column: 1 / -1;">
                            <label for="observaciones">Observaciones</label>
                            <textarea id="observaciones" name="observaciones" maxlength="1200"><?php echo htmlspecialchars((string)($old['observaciones'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>

                        <div class="admin-field">
                            <label for="estado">Estado</label>
                            <?php $estadoOld = (string)($old['estado'] ?? 'Pendiente'); ?>
                            <select id="estado" name="estado" required>
                                <?php foreach (estados_cita_validos() as $estado): ?>
                                    <option value="<?php echo htmlspecialchars($estado, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $estadoOld === $estado ? 'selected' : ''; ?>><?php echo htmlspecialchars($estado, ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="admin-actions-row">
                        <button type="submit" class="admin-btn">Guardar cita</button>
                        <a href="listar.php" class="admin-action">Cancelar</a>
                    </div>
                </form>
            </section>
        </section>
    </main>
</div>

<?php include(__DIR__ . '/../Includes/footer.php'); ?>
