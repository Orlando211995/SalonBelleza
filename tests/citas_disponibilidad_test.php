<?php

require_once __DIR__ . '/../Admin/citas/_agenda.php';

function verificar_cita(bool $condicion, string $mensaje): void
{
    if (!$condicion) {
        throw new RuntimeException($mensaje);
    }
}

$lunes = '2026-11-23';
$sabado = '2026-11-28';
$domingo = '2026-11-29';
$horasEsperadas = [];
for ($minutos = 10 * 60; $minutos < 20 * 60; $minutos += 30) {
    $horasEsperadas[] = sprintf('%02d:%02d', intdiv($minutos, 60), $minutos % 60);
}

foreach ([$lunes, $sabado] as $fecha) {
    verificar_cita(generar_horas_disponibles($fecha, 60, []) === $horasEsperadas, 'Horario completo incorrecto.');
}
verificar_cita(generar_horas_disponibles($domingo, 30, []) === [], 'Domingo debe estar cerrado.');
verificar_cita(hora_cita_texto('10:00') === '10:00 AM', 'Formato de apertura incorrecto.');
verificar_cita(hora_cita_texto('19:30') === '07:30 PM', 'Formato de cierre incorrecto.');

$reservada = ['id' => 1, 'fecha' => $lunes, 'hora' => '11:00', 'duracion' => 60, 'estado' => 'Pendiente'];
foreach ([30, 60, 120] as $duracion) {
    $horas = generar_horas_disponibles($lunes, $duracion, [$reservada]);
    $esperadas = array_values(array_diff($horasEsperadas, ['11:00']));
    verificar_cita($horas === $esperadas, 'Solo la hora de inicio reservada debe quedar bloqueada.');
    verificar_cita(!hora_disponible_para_cita($lunes, '11:00', $duracion, [$reservada]), 'No se debe aceptar la hora reservada.');
    verificar_cita(hora_disponible_para_cita($lunes, '10:00', $duracion, [$reservada]), 'Las horas anteriores deben seguir disponibles.');
    verificar_cita(hora_disponible_para_cita($lunes, '10:30', $duracion, [$reservada]), 'Se permite un servicio que termine despues de otra cita.');
    verificar_cita(hora_disponible_para_cita($lunes, '11:30', $duracion, [$reservada]), 'Las horas posteriores deben seguir disponibles.');
}
verificar_cita(hora_disponible_para_cita($lunes, '11:00', 60, [$reservada], 1), 'Al editar se permite conservar la propia hora.');
verificar_cita(cita_ocupa_hora($lunes, '11:00', $reservada), 'El calendario debe marcar la hora reservada.');
verificar_cita(!cita_ocupa_hora($lunes, '11:30', $reservada), 'El calendario debe mostrar libre la siguiente hora.');
verificar_cita(hora_disponible_para_cita($lunes, '19:30', 120, []), 'La ultima cita puede terminar despues del cierre.');
verificar_cita(!hora_disponible_para_cita($lunes, '20:00', 30, []), 'No debe permitirse empezar a las 20:00.');
$reservada['estado'] = 'Cancelada';
verificar_cita(in_array('11:00', generar_horas_disponibles($lunes, 30, [$reservada]), true), 'Una cita cancelada debe liberar el horario.');
verificar_cita(in_array('11:00', generar_horas_disponibles($sabado, 30, [$reservada]), true), 'La reserva no debe afectar otras fechas.');

echo "Disponibilidad de citas verificada.\n";
