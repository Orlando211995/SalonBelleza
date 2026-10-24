<?php
require_once __DIR__ . '/../Includes/auth.php';
require_once __DIR__ . '/_reporte.php';

$reporte = reporte_desde_peticion();
$e = static fn($texto) => htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $e($reporte['titulo']); ?> | Alfredo Salon Estudio CR</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; margin: 24px; color: #111827; }
        .acciones { margin-bottom: 18px; display: flex; gap: 8px; }
        .btn { padding: 8px 14px; border: 1px solid #1f2937; background: #fff; color: #111827; text-decoration: none; font-size: 13px; cursor: pointer; border-radius: 4px; }
        .btn.principal { background: #1f2937; color: #fff; }
        .marca { color: #6b7280; font-size: 12px; margin: 0; }
        h1 { margin: 2px 0 4px; font-size: 24px; }
        .meta { color: #6b7280; font-size: 12px; margin: 0 0 16px; }
        h2 { font-size: 15px; margin: 22px 0 8px; }
        .resumen { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 8px; }
        .tarjeta { border: 1px solid #d1d5db; background: #f9fafb; padding: 10px 12px; border-radius: 4px; }
        .tarjeta span { display: block; font-size: 11px; color: #6b7280; }
        .tarjeta strong { font-size: 17px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        th { background: #1f2937; color: #fff; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        td.num, th.num { text-align: right; }
        tr.total td { background: #f3f4f6; font-weight: bold; }
        .vacio { color: #6b7280; font-style: italic; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        @page { size: A4 landscape; margin: 12mm; }
        @media print {
            body { margin: 0; }
            .acciones { display: none; }
            th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .tarjeta, tr.total td, tbody tr:nth-child(even) { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="acciones">
        <button class="btn principal" type="button" onclick="window.print()">Imprimir</button>
        <a class="btn" href="javascript:history.back()">Volver</a>
    </div>

    <p class="marca">Alfredo Salon Estudio CR</p>
    <h1><?php echo $e($reporte['titulo']); ?></h1>
    <p class="meta">Generado: <?php echo $e($reporte['generado']); ?> &nbsp;|&nbsp; <?php echo $e($reporte['filtros']); ?></p>

    <?php if ($reporte['resumen']): ?>
        <div class="resumen">
            <?php foreach ($reporte['resumen'] as [$etiqueta, $valor, $tipo]): ?>
                <div class="tarjeta"><span><?php echo $e($etiqueta); ?></span><strong><?php echo $e(reporte_formatear($valor, $tipo)); ?></strong></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php foreach ($reporte['secciones'] as $seccion): ?>
        <h2><?php echo $e($seccion['titulo']); ?></h2>
        <table>
            <thead>
                <tr>
                    <?php foreach ($seccion['columnas'] as [$nombre, $tipo]): ?>
                        <th class="<?php echo $tipo !== 'texto' ? 'num' : ''; ?>"><?php echo $e($nombre); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!$seccion['filas']): ?>
                    <tr><td class="vacio" colspan="<?php echo count($seccion['columnas']); ?>"><?php echo $e($seccion['vacio']); ?></td></tr>
                <?php endif; ?>
                <?php foreach ($seccion['filas'] as $fila): ?>
                    <tr>
                        <?php foreach ($seccion['columnas'] as $i => [, $tipo]): ?>
                            <td class="<?php echo $tipo !== 'texto' ? 'num' : ''; ?>"><?php echo $e(reporte_formatear($fila[$i] ?? '', $tipo)); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                <?php if ($seccion['total'] !== null && $seccion['filas']): ?>
                    <tr class="total">
                        <?php foreach ($seccion['columnas'] as $i => [, $tipo]): ?>
                            <td class="<?php echo $tipo !== 'texto' ? 'num' : ''; ?>"><?php echo $e(reporte_formatear($seccion['total'][$i] ?? '', $tipo)); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endforeach; ?>

    <script>
        window.addEventListener('load', function () { window.print(); });
    </script>
</body>
</html>
