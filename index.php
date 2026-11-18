
<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

require_once 'conexion.php';

$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

try {
    if (!empty($busqueda)) {
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

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --color-base: #C84153;
            --color-tone-1: #BC525C;
            --color-categoria: #4197c8;
            --color-categoria-hover: #357da8;
            --color-movimiento: #41c873;
            --color-movimiento-hover: #36a960;
            --color-gray: #808080;
            --bg-body: #f7f8f9;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: #2b2b2b;
        }

        /* Navbar oscura */
        .navbar-tecnostock {
            background: #212529;
            border-bottom: 1px solid #343a40;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .navbar-brand {
            color: #880707 !important;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .navbar-tecnostock .text-muted {
            color: #ced4da !important;
        }

        .navbar-tecnostock .text-dark {
            color: #ffffff !important;
        }

        .navbar-tecnostock .navbar-toggler {
            border-color: #ffffff;
        }

        .navbar-tecnostock .navbar-toggler-icon {
            filter: invert(1);
        }

        .navbar-tecnostock .btn-outline-secondary {
            color: #ffffff;
            border-color: #adb5bd;
        }

        .navbar-tecnostock .btn-outline-secondary:hover {
            background-color: #ffffff;
            color: #212529;
        }

        /* Botones personalizados */
        .btn-base {
            background-color: var(--color-base) !important;
            border-color: var(--color-base) !important;
            color: #ffffff !important;
            font-weight: 500;
        }

        .btn-base:hover, .btn-base:focus, .btn-base:active {
            background-color: var(--color-tone-1) !important;
            border-color: var(--color-tone-1) !important;
            color: #ffffff !important;
        }

        .btn-categoria {
            background-color: var(--color-categoria) !important;
            border-color: var(--color-categoria) !important;
            color: #ffffff !important;
            font-weight: 500;
        }

        .btn-categoria:hover, .btn-categoria:focus, .btn-categoria:active {
            background-color: var(--color-categoria-hover) !important;
            border-color: var(--color-categoria-hover) !important;
            color: #ffffff !important;
        }

        .btn-movimiento {
            background-color: var(--color-movimiento) !important;
            border-color: var(--color-movimiento) !important;
            color: #ffffff !important;
            font-weight: 500;
        }

        .btn-movimiento:hover, .btn-movimiento:focus, .btn-movimiento:active {
            background-color: var(--color-movimiento-hover) !important;
            border-color: var(--color-movimiento-hover) !important;
            color: #ffffff !important;
        }

        /* Tarjetas */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(128, 128, 128, 0.05);
        }

        /* Cabecera de la tabla */
        .table th {
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background-color: #3b3b3b !important;
            color: #ffffff;
            border: none;
        }

        .table td {
            vertical-align: middle;
            font-size: 0.95rem;
            border-color: #edf0f2;
        }

        /* Fila de stock bajo */
        .table-warning-custom {
            background-color: rgba(200, 65, 83, 0.04) !important;
        }

        .badge-stock-bajo {
            background-color: #fdf2f2;
            color: var(--color-base);
            border: 1px solid rgba(200, 65, 83, 0.2);
            font-weight: 500;
        }

        .badge-categoria {
            background-color: rgba(65, 151, 200, 0.12);
            color: #2b6cb0;
            font-weight: 500;
        }
    </style>
</head>

<body>

    <!-- Barra de Navegación -->
    <nav class="navbar navbar-expand-lg navbar-tecnostock sticky-top mb-4 py-3">
        <div class="container">

            <a class="navbar-brand fs-4" href="index.php">
                <i class="bi bi-box-seam me-2"></i>TecnoStock
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">

                    <li class="nav-item me-3">
                        <span class="text-muted small">
                            Bienvenido,
                            <strong class="text-dark">
                                <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?>
                            </strong>
                        </span>
                    </li>

                    <li class="nav-item">
                        <a class="btn btn-outline-secondary btn-sm px-3 shadow-none" href="logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i>
                            Cerrar Sesión
                        </a>
                    </li>

                </ul>
            </div>
        </div>
    </nav>

    <div class="container pb-5">

        <!-- Encabezado y Botones de Acción -->
        <div class="row align-items-center mb-4 g-3">

            <div class="col-md-5">
                <h2 class="fw-bold mb-1 text-dark">Inventario General</h2>
                <p class="text-muted mb-0">
                    Control centralizado de existencias, precios y categorías
                </p>
            </div>

            <div class="col-md-7 text-md-end">
                <div class="d-flex flex-wrap justify-content-md-end gap-2">

                    <a href="categorias/crear.php" class="btn btn-categoria">
                        <i class="bi bi-tags me-1"></i> Nueva Categoría
                    </a>

                    <a href="productos/crear.php" class="btn btn-base">
                        <i class="bi bi-plus-circle me-1"></i> Nuevo Producto
                    </a>

                    <a href="movimientos/registrar.php" class="btn btn-movimiento">
                        <i class="bi bi-arrow-left-right me-1"></i> Movimiento
                    </a>

                </div>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger shadow-sm border-0" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Formulario de Búsqueda -->
        <div class="card mb-4">
            <div class="card-body p-3">

                <form method="GET" action="index.php" class="row g-2">

                    <div class="col-md-10">
                        <div class="input-group">

                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="bi bi-search"></i>
                            </span>

                            <input
                                type="text"
                                class="form-control border-start-0 ps-0 shadow-none"
                                name="busqueda"
                                placeholder="Buscar por código, nombre de producto o categoría..."
                                value="<?php echo htmlspecialchars($busqueda); ?>"
                            >

                        </div>
                    </div>

                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-base">
                            Buscar
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <!-- Tabla de Productos -->
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
                                <th>Stock Actual</th>
                                <th>Stock Mín.</th>
                                <th>Estado</th>
                                <th class="text-center pe-4">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if (!empty($productos)): ?>

                                <?php foreach ($productos as $prod): ?>

                                    <?php
                                        $esStockBajo = ($prod['stock_actual'] < $prod['stock_minimo']);
                                    ?>

                                    <tr class="<?php echo $esStockBajo ? 'table-warning-custom' : ''; ?>">

                                        <td class="ps-4">
                                            <code><?php echo htmlspecialchars($prod['codigo']); ?></code>
                                        </td>

                                        <td>
                                            <span class="fw-semibold text-dark">
                                                <?php echo htmlspecialchars($prod['nombre']); ?>
                                            </span>

                                            <?php if ($esStockBajo): ?>
                                                <span class="badge badge-stock-bajo ms-1 py-1 px-2" title="Stock por debajo del mínimo definido">
                                                    <i class="bi bi-exclamation-circle me-1"></i>
                                                    Stock Bajo
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <span class="badge badge-categoria px-2 py-1">
                                                <?php echo htmlspecialchars($prod['categoria_nombre'] ?? 'Sin categoría'); ?>
                                            </span>
                                        </td>

                                        <td>
                                            <strong>
                                                $<?php echo number_format($prod['precio'], 2, ',', '.'); ?>
                                            </strong>
                                        </td>

                                        <td>
                                            <span class="fs-6 fw-bold <?php echo $esStockBajo ? 'text-danger' : 'text-dark'; ?>">
                                                <?php echo $prod['stock_actual']; ?>
                                            </span>
                                        </td>

                                        <td>
                                            <span class="text-muted">
                                                <?php echo $prod['stock_minimo']; ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?php if ($prod['estado'] == 1): ?>
                                                <span class="badge bg-success bg-opacity-15 text-success px-2 py-1">
                                                    Activo
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary bg-opacity-15 text-secondary px-2 py-1">
                                                    Desactivado
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="text-center pe-4">
                                            <div class="d-flex justify-content-center gap-1">

                                                <!-- Botón Editar -->
                                                <a href="productos/editar.php?id=<?php echo $prod['id_producto']; ?>"
                                                   class="btn btn-sm btn-light border text-primary"
                                                   title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>

                                                <?php if ($prod['estado'] == 1): ?>

                                                    <!-- Desactivar -->
                                                    <a href="productos/desactivar.php?id=<?php echo $prod['id_producto']; ?>"
                                                       class="btn btn-sm btn-light border text-warning"
                                                       title="Desactivar"
                                                       onclick="return confirm('¿Estás seguro de desactivar este producto?');">
                                                        <i class="bi bi-slash-circle"></i>
                                                    </a>

                                                <?php else: ?>

                                                    <!-- Activar -->
                                                    <a href="productos/activar.php?id=<?php echo $prod['id_producto']; ?>"
                                                       class="btn btn-sm btn-light border text-success"
                                                       title="Activar">
                                                        <i class="bi bi-check-circle"></i>
                                                    </a>

                                                <?php endif; ?>

                                                <!-- Eliminar Permanente -->
                                                <a href="productos/eliminar.php?id=<?php echo $prod['id_producto']; ?>"
                                                   class="btn btn-sm btn-light border text-danger"
                                                   title="Eliminar Permanentemente"
                                                   onclick="return confirm('¡ADVERTENCIA! Esta acción eliminará el producto de forma permanente. ¿Deseas continuar?');">
                                                    <i class="bi bi-trash"></i>
                                                </a>

                                            </div>
                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-folder2-open display-6 d-block mb-2 text-black-50"></i>
                                        No se encontraron productos registrados en el inventario.
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>
            </div>
        </div>

    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
