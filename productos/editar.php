<?php
require_once '../includes/funciones.php';
requerir_login('../');
require_once '../conexion.php';

$id = entero_no_negativo($_GET['id'] ?? null);
if (!$id) {
    header('Location: ../index.php');
    exit();
}

$errores = [];

try {
    $stmt = $conexion->prepare('SELECT * FROM producto WHERE id_producto = :id');
    $stmt->execute([':id' => $id]);
    $producto = $stmt->fetch();
    if (!$producto) {
        flash('danger', 'El producto solicitado no existe.');
        header('Location: ../index.php');
        exit();
    }
    $categorias = $conexion->query('SELECT id_categoria, nombre FROM categoria WHERE estado = 1 ORDER BY nombre')->fetchAll();
} catch (PDOException $e) {
    error_log('Editar (carga): ' . $e->getMessage());
    flash('danger', 'No se pudieron cargar los datos del producto.');
    header('Location: ../index.php');
    exit();
}

// Valores que se muestran en el formulario (BD o lo último enviado)
$form = [
    'nombre'       => $producto['nombre'],
    'descripcion'  => $producto['descripcion'],
    'precio'       => $producto['precio'],
    'id_categoria' => $producto['id_categoria'],
    'stock_minimo' => $producto['stock_minimo'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigir_post_csrf();
    [$errores, $datos] = validar_producto($conexion, $_POST, false);
    $form = array_merge($form, array_intersect_key($_POST, $form));

    if (!$errores) {
        try {
            $up = $conexion->prepare(
                'UPDATE producto
                    SET nombre = :nombre, descripcion = :descripcion, precio = :precio,
                        id_categoria = :id_categoria, stock_minimo = :stock_minimo
                  WHERE id_producto = :id'
            );
            $up->execute([
                ':nombre'       => $datos['nombre'],
                ':descripcion'  => $datos['descripcion'],
                ':precio'       => $datos['precio'],
                ':id_categoria' => $datos['id_categoria'],
                ':stock_minimo' => $datos['stock_minimo'],
                ':id'           => $id,
            ]);
            flash('success', 'Producto actualizado correctamente.');
            header('Location: ../index.php');
            exit();
        } catch (PDOException $e) {
            error_log('Editar producto: ' . $e->getMessage());
            $errores[] = 'No se pudo actualizar el producto. Intente nuevamente.';
        }
    }
}

$titulo = 'Editar Producto';
$base   = '../';
require '../includes/header.php';
?>
<div class="container main-container">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="mb-4">
                <h2 class="page-title">Editar Producto</h2>
                <p class="page-description">Código: <code><?php echo e($producto['codigo']); ?></code> (el código no se puede modificar)</p>
            </div>

            <?php foreach ($errores as $err): ?>
                <div class="alert alert-danger shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo e($err); ?></div>
            <?php endforeach; ?>

            <div class="card bg-white">
                <div class="card-body-custom">
                    <form action="editar.php?id=<?php echo (int)$id; ?>" method="POST" class="needs-validation" novalidate>
                        <?php echo csrf_input(); ?>

                        <div class="mb-3">
                            <label class="form-label" for="nombre">Nombre del Producto</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" maxlength="100" required
                                   value="<?php echo e($form['nombre']); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="descripcion">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo e($form['descripcion']); ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="precio">Precio ($)</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="precio" name="precio" required
                                       value="<?php echo e($form['precio']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="id_categoria">Categoría</label>
                                <select class="form-select" id="id_categoria" name="id_categoria" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?php echo (int)$cat['id_categoria']; ?>"
                                            <?php echo ((string)$form['id_categoria'] === (string)$cat['id_categoria']) ? 'selected' : ''; ?>>
                                            <?php echo e($cat['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label" for="stock_actual">Stock Actual (solo lectura)</label>
                                <input type="number" class="form-control" id="stock_actual" value="<?php echo (int)$producto['stock_actual']; ?>" disabled>
                                <small class="text-muted">El stock cambia solo mediante
                                    <a href="../movimientos/registrar.php?id=<?php echo (int)$id; ?>">movimientos de entrada/salida</a>.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="stock_minimo">Stock Mínimo (Alerta)</label>
                                <input type="number" min="0" step="1" class="form-control" id="stock_minimo" name="stock_minimo" required
                                       value="<?php echo e($form['stock_minimo']); ?>">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between flex-wrap gap-2">
                            <a href="../index.php" class="btn btn-gray-custom"><i class="bi bi-arrow-left me-1"></i>Cancelar</a>
                            <button type="submit" class="btn btn-base"><i class="bi bi-check-lg me-1"></i>Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require '../includes/footer.php'; ?>
