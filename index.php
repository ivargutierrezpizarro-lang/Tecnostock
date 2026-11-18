<?php
require_once 'includes/funciones.php';
requerir_login('');
require_once 'conexion.php';

$busqueda  = trim((string)($_GET['busqueda'] ?? ''));
$productos = [];
$error     = null;

try {
    $sql = "SELECT p.*, c.nombre AS categoria_nombre
            FROM producto p
            LEFT JOIN categoria c ON p.id_categoria = c.id_categoria";
    $params = [];

    if ($busqueda !== '') {
        // Se escapan % y _ para que se busquen como texto literal
        $like = '%' . addcslashes($busqueda, '%_\\') . '%';
        $sql .= " WHERE p.codigo LIKE :b1 OR p.nombre LIKE :b2 OR c.nombre LIKE :b3";
        $params = [':b1' => $like, ':b2' => $like, ':b3' => $like];
    }
    $sql .= " ORDER BY p.estado DESC, p.id_producto DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    $productos = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Listado: ' . $e->getMessage());
    $error = 'No se pudieron cargar los productos. Intente nuevamente.';
}

// Productos ACTIVOS con stock actual menor al mínimo
$bajos = array_filter($productos, function ($p) {
    return (int)$p['estado'] === 1 && (int)$p['stock_actual'] < (int)$p['stock_minimo'];
});

$titulo = 'Panel Principal';
$base   = '';
require 'includes/header.php';
?>
<div class="container main-container">

    <div class="row align-items-center mb-4 g-3">
        <div class="col-md-5">
            <h2 class="fw-bold mb-1">Inventario General</h2>
            <p class="text-muted mb-0">Control centralizado de existencias, precios y categorías</p>
        </div>
        <div class="col-md-7 text-md-end">
            <div class="d-flex flex-wrap justify-content-md-end gap-2">
                <a href="categorias/crear.php" class="btn btn-categoria"><i class="bi bi-tags me-1"></i> Nueva Categoría</a>
                <a href="productos/crear.php" class="btn btn-base"><i class="bi bi-plus-circle me-1"></i> Nuevo Producto</a>
                <a href="movimientos/registrar.php" class="btn btn-movimiento"><i class="bi bi-arrow-left-right me-1"></i> Movimiento</a>
            </div>
        </div>
    </div>

    <?php flash_mostrar(); ?>

    <?php if ($error): ?>
        <div class="alert alert-danger shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo e($error); ?></div>
    <?php endif; ?>

    <?php if (count($bajos) > 0): ?>
        <div class="alert alert-warning shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Alerta de stock bajo:</strong>
            <?php echo count($bajos); ?> producto(s) tienen stock por debajo del mínimo:
            <?php echo e(implode(', ', array_map(function ($p) { return $p['codigo']; }, $bajos))); ?>.
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-body p-3">
            <form method="GET" action="index.php" class="row g-2">
                <div class="col-md-8 col-lg-9">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0 shadow-none" name="busqueda"
                               maxlength="100" placeholder="Buscar por código, nombre de producto o categoría..."
                               value="<?php echo e($busqueda); ?>" aria-label="Buscar productos">
                    </div>
                </div>
                <div class="col-md-4 col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-base flex-fill">Buscar</button>
                    <?php if ($busqueda !== ''): ?>
                        <a href="index.php" class="btn btn-gray-custom" title="Limpiar búsqueda"><i class="bi bi-x-lg"></i></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="ps-4">Código</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Mín.</th>
                            <th>Estado</th>
                            <th class="text-center pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($productos)): ?>
                        <?php foreach ($productos as $prod): ?>
                            <?php
                                $activo  = (int)$prod['estado'] === 1;
                                $esBajo  = $activo && (int)$prod['stock_actual'] < (int)$prod['stock_minimo'];
                                $clases  = ($esBajo ? 'fila-stock-bajo ' : '') . ($activo ? '' : 'fila-inactiva');
                            ?>
                            <tr class="<?php echo $clases; ?>">
                                <td class="ps-4"><code><?php echo e($prod['codigo']); ?></code></td>
                                <td>
                                    <span class="fw-semibold"><?php echo e($prod['nombre']); ?></span>
                                    <?php if ($esBajo): ?>
                                        <span class="badge badge-stock-bajo ms-1 py-1 px-2" title="Stock por debajo del mínimo definido">
                                            <i class="bi bi-exclamation-circle me-1"></i>Stock Bajo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge badge-categoria px-2 py-1"><?php echo e($prod['categoria_nombre'] ?? 'Sin categoría'); ?></span></td>
                                <td><strong>$<?php echo number_format((float)$prod['precio'], 0, ',', '.'); ?></strong></td>
                                <td><span class="fs-6 fw-bold <?php echo $esBajo ? 'text-danger' : ''; ?>"><?php echo (int)$prod['stock_actual']; ?></span></td>
                                <td class="text-muted"><?php echo (int)$prod['stock_minimo']; ?></td>
                                <td>
                                    <?php if ($activo): ?>
                                        <span class="badge bg-success px-2 py-1">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary px-2 py-1">Desactivado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center pe-4">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="productos/editar.php?id=<?php echo (int)$prod['id_producto']; ?>"
                                           class="btn btn-sm btn-light border text-primary" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <?php if ($activo): ?>
                                            <a href="movimientos/registrar.php?id=<?php echo (int)$prod['id_producto']; ?>"
                                               class="btn btn-sm btn-light border text-success" title="Registrar movimiento">
                                                <i class="bi bi-arrow-left-right"></i>
                                            </a>
                                            <form action="productos/desactivar.php" method="POST" class="d-inline"
                                                  onsubmit="return confirm('¿Desactivar este producto? Su historial de movimientos se conserva.');">
                                                <?php echo csrf_input(); ?>
                                                <input type="hidden" name="id" value="<?php echo (int)$prod['id_producto']; ?>">
                                                <button type="submit" class="btn btn-sm btn-light border text-warning" title="Desactivar">
                                                    <i class="bi bi-slash-circle"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form action="productos/activar.php" method="POST" class="d-inline">
                                                <?php echo csrf_input(); ?>
                                                <input type="hidden" name="id" value="<?php echo (int)$prod['id_producto']; ?>">
                                                <button type="submit" class="btn btn-sm btn-light border text-success" title="Activar">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-folder2-open display-6 d-block mb-2 text-black-50"></i>
                                <?php echo $busqueda !== ''
                                    ? 'No se encontraron productos que coincidan con la búsqueda.'
                                    : 'Aún no hay productos registrados en el inventario.'; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require 'includes/footer.php'; ?>
