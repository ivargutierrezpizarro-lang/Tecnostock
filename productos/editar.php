<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../conexion.php';

$id_producto = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($id_producto)) {
    header("Location: ../index.php");
    exit();
}

// Obtener datos del producto a editar
try {
    $stmt = $conexion->prepare("SELECT * FROM producto WHERE id_producto = :id");
    $stmt->execute([':id' => $id_producto]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        header("Location: ../index.php");
        exit();
    }

    // Obtener categorías activas
    $stmtCat = $conexion->query("SELECT * FROM categoria WHERE estado = 1");
    $categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Error al cargar los datos: " . $e->getMessage();
}

// Procesar actualización si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = $_POST['precio'];
    $id_categoria = $_POST['id_categoria'];
    $stock_minimo = $_POST['stock_minimo'];

    try {
        $updateSql = "UPDATE producto SET 
                        nombre = :nombre, 
                        descripcion = :descripcion, 
                        precio = :precio, 
                        id_categoria = :id_categoria, 
                        stock_minimo = :stock_minimo 
                      WHERE id_producto = :id";
        
        $updateStmt = $conexion->prepare($updateSql);
        $updateStmt->execute([
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':precio' => $precio,
            ':id_categoria' => $id_categoria,
            ':stock_minimo' => $stock_minimo,
            ':id' => $id_producto
        ]);

        header("Location: ../index.php");
        exit();

    } catch (PDOException $e) {
        $error = "Error al actualizar el producto: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto - TecnoStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="mb-4">Editar Producto: <code><?php echo htmlspecialchars($producto['codigo']); ?></code></h3>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form action="editar.php?id=<?php echo $id_producto; ?>" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Nombre del Producto</label>
                                <input type="text" class="form-control" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" name="descripcion" rows="2"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Precio ($)</label>
                                    <input type="number" step="0.01" class="form-control" name="precio" min="0" value="<?php echo $producto['precio']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Categoría</label>
                                    <select class="form-select" name="id_categoria" required>
                                        <option value="">Seleccione...</option>
                                        <?php foreach ($categorias as $cat): ?>
                                            <option value="<?php echo $cat['id_categoria']; ?>" <?php echo ($cat['id_categoria'] == $producto['id_categoria']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Stock Actual (Solo lectura)</label>
                                    <input type="number" class="form-control" value="<?php echo $producto['stock_actual']; ?>" disabled>
                                    <small class="text-muted">El stock se modifica mediante Movimientos.</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Stock Mínimo (Alerta)</label>
                                    <input type="number" class="form-control" name="stock_minimo" min="0" value="<?php echo $producto['stock_minimo']; ?>" required>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="../index.php" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>