<?php
require_once '../includes/funciones.php';
requerir_login('../');
require_once '../conexion.php';

const CANTIDAD_MAXIMA = 1000000;

$errores   = [];
$productos = [];
$old = [
    'id_producto' => (string)($_GET['id'] ?? ''),   // permite preseleccionar desde el listado
    'tipo'        => 'ENTRADA',
    'cantidad'    => '',
    'observacion' => '',
];

try {
    $productos = $conexion->query(
        'SELECT id_producto, codigo, nombre, stock_actual FROM producto WHERE estado = 1 ORDER BY nombre'
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Cargar productos (movimiento): ' . $e->getMessage());
    $errores[] = 'No se pudieron cargar los productos.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigir_post_csrf();

    $old['id_producto'] = (string)($_POST['id_producto'] ?? '');
    $old['tipo']        = (string)($_POST['tipo'] ?? '');
    $old['cantidad']    = (string)($_POST['cantidad'] ?? '');
    $old['observacion'] = trim((string)($_POST['observacion'] ?? ''));

    $idProducto = entero_no_negativo($old['id_producto']);
    $cantidad   = entero_no_negativo($old['cantidad']);

    // Validaciones de servidor (se repiten las de JavaScript)
    if (!$idProducto) {
        $errores[] = 'Debe seleccionar un producto.';
    }
    if (!in_array($old['tipo'], ['ENTRADA', 'SALIDA'], true)) {
        $errores[] = 'El tipo de movimiento debe ser ENTRADA o SALIDA.';
    }
    if ($cantidad === null || $cantidad < 1) {
        $errores[] = 'La cantidad debe ser un número entero mayor a 0.';
    } elseif ($cantidad > CANTIDAD_MAXIMA) {
        $errores[] = 'La cantidad no puede superar ' . number_format(CANTIDAD_MAXIMA, 0, ',', '.') . ' unidades.';
    }
    if (mb_strlen($old['observacion']) > 500) {
        $errores[] = 'La observación no puede superar los 500 caracteres.';
    }

    if (!$errores) {
        try {
            $conexion->beginTransaction();

            // Bloquea la fila para evitar que dos movimientos simultáneos corrompan el stock
            $st = $conexion->prepare('SELECT stock_actual, estado FROM producto WHERE id_producto = :id FOR UPDATE');
            $st->execute([':id' => $idProducto]);
            $prod = $st->fetch();

            if (!$prod) {
                throw new RuntimeException('El producto seleccionado no existe.');
            }
            if ((int)$prod['estado'] !== 1) {
                throw new RuntimeException('El producto está desactivado; actívelo para registrar movimientos.');
            }

            $stock = (int)$prod['stock_actual'];
            if ($old['tipo'] === 'SALIDA' && $cantidad > $stock) {
                throw new RuntimeException("Stock insuficiente. Disponible: $stock; solicitado: $cantidad.");
            }
            $nuevoStock = $old['tipo'] === 'ENTRADA' ? $stock + $cantidad : $stock - $cantidad;

            $mov = $conexion->prepare(
                'INSERT INTO movimiento (tipo, cantidad, observacion, id_producto, id_usuario)
                 VALUES (:tipo, :cantidad, :obs, :prod, :usr)'
            );
            $mov->execute([
                ':tipo'     => $old['tipo'],
                ':cantidad' => $cantidad,
                ':obs'      => $old['observacion'],
                ':prod'     => $idProducto,
                ':usr'      => $_SESSION['id_usuario'],
            ]);

            $upd = $conexion->prepare('UPDATE producto SET stock_actual = :nuevo WHERE id_producto = :id');
            $upd->execute([':nuevo' => $nuevoStock, ':id' => $idProducto]);

            $conexion->commit();
            flash('success', "Movimiento registrado. Nuevo stock: $nuevoStock.");
            header('Location: ../index.php');
            exit();

        } catch (RuntimeException $e) {          // errores de regla de negocio: mensaje seguro
            if ($conexion->inTransaction()) { $conexion->rollBack(); }
            $errores[] = $e->getMessage();
        } catch (PDOException $e) {              // errores técnicos: no se muestran al usuario
            if ($conexion->inTransaction()) { $conexion->rollBack(); }
            error_log('Registrar movimiento: ' . $e->getMessage());
            $errores[] = 'No se pudo registrar el movimiento. Intente nuevamente.';
        }
    }
}

$titulo = 'Registrar Movimiento';
$base   = '../';
require '../includes/header.php';
?>
<div class="container main-container">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="mb-4">
                <h2 class="page-title">Registrar Movimiento</h2>
                <p class="page-description">Registra entradas y salidas para mantener actualizado el inventario.</p>
            </div>

            <?php foreach ($errores as $err): ?>
                <div class="alert alert-danger shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo e($err); ?></div>
            <?php endforeach; ?>

            <div class="card bg-white">
                <div class="card-header-custom">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-container verde"><i class="bi bi-arrow-left-right"></i></div>
                        <div>
                            <h5 class="fw-bold mb-1">Entrada o Salida de Stock</h5>
                            <small class="text-muted">Completa los datos del movimiento</small>
                        </div>
                    </div>
                </div>
                <div class="card-body-custom">
                    <form action="registrar.php" method="POST" class="needs-validation" novalidate>
                        <?php echo csrf_input(); ?>

                        <div class="mb-4">
                            <label class="form-label" for="id_producto"><i class="bi bi-box-seam me-1"></i>Producto</label>
                            <select class="form-select" id="id_producto" name="id_producto" required>
                                <option value="">Seleccione un producto...</option>
                                <?php foreach ($productos as $p): ?>
                                    <option value="<?php echo (int)$p['id_producto']; ?>"
                                            data-stock="<?php echo (int)$p['stock_actual']; ?>"
                                            <?php echo $old['id_producto'] === (string)$p['id_producto'] ? 'selected' : ''; ?>>
                                        <?php echo e($p['codigo'] . ' - ' . $p['nombre'] . ' (Stock: ' . $p['stock_actual'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div id="ayuda-stock" class="form-text"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label" for="tipo"><i class="bi bi-arrow-left-right me-1"></i>Tipo de Movimiento</label>
                                <select class="form-select" id="tipo" name="tipo" required>
                                    <option value="ENTRADA" <?php echo $old['tipo'] === 'ENTRADA' ? 'selected' : ''; ?>>ENTRADA (suma stock)</option>
                                    <option value="SALIDA"  <?php echo $old['tipo'] === 'SALIDA'  ? 'selected' : ''; ?>>SALIDA (resta stock)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label" for="cantidad"><i class="bi bi-123 me-1"></i>Cantidad</label>
                                <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" max="<?php echo CANTIDAD_MAXIMA; ?>"
                                       step="1" required placeholder="Ingrese la cantidad" value="<?php echo e($old['cantidad']); ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="observacion"><i class="bi bi-chat-left-text me-1"></i>Observación / Motivo</label>
                            <textarea class="form-control" id="observacion" name="observacion" rows="3" maxlength="500"
                                      placeholder="Ej. Reposición de proveedor, venta directa, etc."><?php echo e($old['observacion']); ?></textarea>
                        </div>

                        <div class="info-box mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Importante:</strong> el stock se actualiza automáticamente al registrar el movimiento.
                            Una salida no puede dejar el stock bajo cero. El movimiento queda asociado a tu usuario, con fecha y hora.
                        </div>

                        <div class="d-flex justify-content-between flex-wrap gap-2">
                            <a href="../index.php" class="btn btn-gray-custom"><i class="bi bi-x-circle me-1"></i>Cancelar</a>
                            <button type="submit" class="btn btn-movimiento"><i class="bi bi-check-circle me-1"></i>Registrar Movimiento</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require '../includes/footer.php'; ?>
