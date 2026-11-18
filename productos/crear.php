<?php
require_once '../includes/funciones.php';
requerir_login('../');
require_once '../conexion.php';

$categorias = [];
$errores    = $_SESSION['errores'] ?? [];
$old        = $_SESSION['old'] ?? [];
unset($_SESSION['errores'], $_SESSION['old']);

try {
    $categorias = $conexion->query('SELECT id_categoria, nombre FROM categoria WHERE estado = 1 ORDER BY nombre')->fetchAll();
} catch (PDOException $e) {
    error_log('Cargar categorías: ' . $e->getMessage());
    $errores[] = 'No se pudieron cargar las categorías.';
}

$v = function ($campo) use ($old) { return e($old[$campo] ?? ''); };

$titulo = 'Nuevo Producto';
$base   = '../';
require '../includes/header.php';
?>
<div class="container main-container">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="mb-4">
                <h2 class="page-title">Nuevo Producto</h2>
                <p class="page-description">Complete los datos del producto para agregarlo al inventario central.</p>
            </div>

            <?php foreach ($errores as $err): ?>
                <div class="alert alert-danger shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo e($err); ?></div>
            <?php endforeach; ?>

            <?php if (!$categorias): ?>
                <div class="alert alert-warning">
                    Aún no hay categorías activas. <a href="../categorias/crear.php" class="alert-link">Cree una categoría</a> antes de registrar productos.
                </div>
            <?php endif; ?>

            <div class="card bg-white">
                <div class="card-header-custom">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-container"><i class="bi bi-box-seam"></i></div>
                        <div>
                            <h5 class="fw-bold mb-1">Registrar Nuevo Producto</h5>
                            <small class="text-muted">Información general y control de existencias</small>
                        </div>
                    </div>
                </div>
                <div class="card-body-custom">
                    <form action="guardar.php" method="POST" class="needs-validation" novalidate>
                        <?php echo csrf_input(); ?>

                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label class="form-label" for="codigo"><i class="bi bi-upc-scan me-1"></i>Código Único</label>
                                <input type="text" class="form-control" id="codigo" name="codigo" maxlength="50" required
                                       placeholder="Ej. TEC-001" value="<?php echo $v('codigo'); ?>">
                            </div>
                            <div class="col-md-7 mb-3">
                                <label class="form-label" for="nombre"><i class="bi bi-tag me-1"></i>Nombre del Producto</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" maxlength="100" required
                                       placeholder="Ej. Audífonos Inalámbricos" value="<?php echo $v('nombre'); ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="descripcion"><i class="bi bi-card-text me-1"></i>Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"
                                      placeholder="Breve detalle del producto..."><?php echo $v('descripcion'); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="precio"><i class="bi bi-currency-dollar me-1"></i>Precio ($)</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="precio" name="precio" required
                                       placeholder="0.00" value="<?php echo $v('precio'); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="id_categoria"><i class="bi bi-tags me-1"></i>Categoría</label>
                                <select class="form-select" id="id_categoria" name="id_categoria" required>
                                    <option value="">Seleccione categoría...</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?php echo (int)$cat['id_categoria']; ?>"
                                            <?php echo ((string)($old['id_categoria'] ?? '') === (string)$cat['id_categoria']) ? 'selected' : ''; ?>>
                                            <?php echo e($cat['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label" for="stock_actual"><i class="bi bi-boxes me-1"></i>Stock Inicial</label>
                                <input type="number" min="0" step="1" class="form-control" id="stock_actual" name="stock_actual" required
                                       placeholder="0" value="<?php echo $v('stock_actual'); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="stock_minimo"><i class="bi bi-exclamation-circle me-1"></i>Stock Mínimo (Alerta)</label>
                                <input type="number" min="0" step="1" class="form-control" id="stock_minimo" name="stock_minimo" required
                                       placeholder="0" value="<?php echo $v('stock_minimo'); ?>">
                            </div>
                        </div>

                        <div class="info-box mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Importante:</strong> si el stock inicial es mayor a 0, se registra automáticamente
                            un movimiento de ENTRADA ("Stock inicial") para mantener la trazabilidad. Cuando el stock actual
                            sea menor al mínimo, el producto se marcará con alerta de stock bajo.
                        </div>

                        <div class="d-flex justify-content-between flex-wrap gap-2">
                            <a href="../index.php" class="btn btn-gray-custom"><i class="bi bi-arrow-left me-1"></i>Cancelar</a>
                            <button type="submit" class="btn btn-base"><i class="bi bi-check-lg me-1"></i>Guardar Producto</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require '../includes/footer.php'; ?>
