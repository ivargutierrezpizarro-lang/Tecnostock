<?php
require_once '../includes/funciones.php';
requerir_login('../');
require_once '../conexion.php';

$movs  = [];
$error = null;
try {
    $movs = $conexion->query(
        'SELECT m.id_movimiento, m.fecha_hora, m.tipo, m.cantidad, m.observacion,
                p.codigo, p.nombre AS producto, u.nombre AS usuario
           FROM movimiento m
           JOIN producto p ON p.id_producto = m.id_producto
           JOIN usuario  u ON u.id_usuario  = m.id_usuario
          ORDER BY m.fecha_hora DESC, m.id_movimiento DESC
          LIMIT 200'
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Historial: ' . $e->getMessage());
    $error = 'No se pudo cargar el historial.';
}

$titulo = 'Historial de Movimientos';
$base   = '../';
require '../includes/header.php';
?>
<div class="container main-container">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h2 class="page-title mb-1">Historial de Movimientos</h2>
            <p class="page-description mb-0">Últimos 200 movimientos de entrada y salida.</p>
        </div>
        <a href="registrar.php" class="btn btn-movimiento"><i class="bi bi-arrow-left-right me-1"></i>Nuevo movimiento</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo e($error); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="ps-4">Fecha y hora</th><th>Producto</th><th>Tipo</th>
                            <th>Cantidad</th><th>Usuario</th><th>Observación</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($movs): foreach ($movs as $m): ?>
                        <tr>
                            <td class="ps-4"><?php echo e(date('d-m-Y H:i', strtotime($m['fecha_hora']))); ?></td>
                            <td><code><?php echo e($m['codigo']); ?></code> <?php echo e($m['producto']); ?></td>
                            <td>
                                <span class="badge <?php echo $m['tipo'] === 'ENTRADA' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo e($m['tipo']); ?>
                                </span>
                            </td>
                            <td class="fw-bold"><?php echo (int)$m['cantidad']; ?></td>
                            <td><?php echo e($m['usuario']); ?></td>
                            <td class="text-muted"><?php echo e($m['observacion']); ?></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">Aún no hay movimientos registrados.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require '../includes/footer.php'; ?>
