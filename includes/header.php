<?php
// Cabecera y barra de navegación compartidas.
// Variables esperadas: $titulo (string), $base ('' o '../')
$base   = $base ?? '';
$titulo = $titulo ?? 'TecnoStock';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($titulo); ?> - TecnoStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $base; ?>assets/css/estilos.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-tecnostock sticky-top py-3">
    <div class="container">
        <a class="navbar-brand fs-4" href="<?php echo $base; ?>index.php">
            <i class="bi bi-box-seam me-2"></i>TecnoStock
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-label="Mostrar menú">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?php echo $base; ?>index.php">Inventario</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo $base; ?>movimientos/historial.php">Historial</a></li>
            </ul>
            <span class="text-light small me-3">
                Bienvenido, <strong><?php echo e($_SESSION['nombre_usuario'] ?? ''); ?></strong>
            </span>
            <a class="btn btn-outline-light btn-sm" href="<?php echo $base; ?>logout.php">
                <i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión
            </a>
        </div>
    </div>
</nav>
