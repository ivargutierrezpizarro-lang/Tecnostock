<?php
session_start();
// Validar si el usuario ha iniciado sesión; si no, redirigir al login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

require_once 'conexion.php';

// Capturar término de búsqueda si el usuario buscó algo
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

try {
    if (!empty($busqueda)) {
        // Consulta preparada para buscar por código, nombre o categoría
        $sql = "SELECT p.*, c.nombre AS categoria_nombre 
                FROM producto p 
                LEFT JOIN categoria c ON p.id_categoria = c.id_categoria 
                WHERE p.codigo LIKE :busq OR p.nombre LIKE :busq OR c.nombre LIKE :busq
                ORDER BY p.id_producto DESC";
        $stmt = $conexion->prepare($sql);
        $param = "%$busqueda%";
        $stmt->bindParam(':busq', $param, PDO::PARAM_STR);
        $stmt->execute();
    } else {
        // Consulta para listar todos los productos con su categoría
        $sql = "SELECT p.*, c.nombre AS categoria_nombre 
                FROM producto p 
                LEFT JOIN categoria c ON p.id_categoria = c.id_categoria 
                ORDER BY p.id_producto DESC";
        $stmt = $conexion->query($sql);
    }
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Error al cargar los productos: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Principal - TecnoStock</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <!-- Barra de Navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">TecnoStock</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item me-3">
                        <span class="text-white-50">Hola, <strong><?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></strong></span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm" href="logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Encabezado y Botones de Acción -->
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h2>Inventario de Productos</h2>
                <p class="text-muted">Gestión de stock, entradas y salidas</p>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="productos/crear.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Nuevo Producto</a>
                <a href="movimientos/registrar.php" class="btn btn-success"><i class="bi bi-arrow-left-right"></i> Registrar Movimiento</a>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Formulario de Búsqueda -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="index.php" class="row g-3">
                    <div class="col-md-10">
                        <input type="text" class="form-control" name="busqueda" placeholder="Buscar por código, nombre o categoría..." value="<?php echo htmlspecialchars($busqueda); ?>">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-secondary">Buscar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Productos -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Stock Actual</th>
                                <th>Stock Mínimo</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($productos)): ?>
                                <?php foreach ($productos as $prod): ?>
                                    <!-- Evaluar si el stock actual es menor al mínimo para destacar la alerta -->
                                    <?php 
                                        $esStockBajo = ($prod['stock_actual'] < $prod['stock_minimo']); 
                                    ?>
                                    <tr class="<?php echo $esStockBajo ? 'table-warning' : ''; ?>">
                                        <td><code><?php echo htmlspecialchars($prod['codigo']); ?></code></td>
                                        <td>
                                            <?php echo htmlspecialchars($prod['nombre']); ?>
                                            <?php if ($esStockBajo): ?>
                                                <span class="badge bg-danger ms-1" title="Stock por debajo del mínimo definido">Stock Bajo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($prod['categoria_nombre'] ?? 'Sin categoría'); ?></td>
                                        <td>$<?php echo number_format($prod['precio'], 2, ',', '.'); ?></td>
                                        <td><strong><?php echo $prod['stock_actual']; ?></strong></td>
                                        <td><?php echo $prod['stock_minimo']; ?></td>
                                        <td>
                                            <?php if ($prod['estado'] == 1): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Desactivado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="productos/editar.php?id=<?php echo $prod['id_producto']; ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                            <a href="productos/desactivar.php?id=<?php echo $prod['id_producto']; ?>" class="btn btn-sm btn-outline-danger" title="Desactivar" onclick="return confirm('¿Estás seguro de desactivar este producto?');"><i class="bi bi-slash-circle"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No se encontraron productos registrados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>