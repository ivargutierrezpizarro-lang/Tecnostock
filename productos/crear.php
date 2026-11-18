
<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../conexion.php';

try {
    $stmt = $conexion->query("SELECT * FROM categoria WHERE estado = 1");
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al cargar categorías: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Nuevo Producto - TecnoStock</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --color-base: #C84153;
            --color-tone-1: #BC525C;
            --color-tone-2: #B06064;
            --color-gray: #808080;
            --bg-body: #f7f8f9;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: #2b2b2b;
            min-height: 100vh;
        }

        /* Barra de navegación */
        .navbar-tecnostock {
            background: #212529;
            border-bottom: 1px solid #343a40;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .navbar-brand {
            color: #880707  !important;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        /* Contenido principal */
        .main-container {
            padding-top: 45px;
            padding-bottom: 50px;
        }

        .page-title {
            font-weight: 700;
            color: #2b2b2b;
            margin-bottom: 8px;
        }

        .page-description {
            color: #808080;
            font-size: 0.95rem;
        }

        /* Tarjeta */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .card-header-custom {
            background: #ffffff;
            border-bottom: 1px solid #edf0f2;
            padding: 22px 28px;
        }

        .card-body-custom {
            padding: 28px;
        }

        /* Icono */
        .icon-container {
            width: 48px;
            height: 48px;
            background: rgba(200, 65, 83, 0.10);
            color: var(--color-base);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        /* Formularios */
        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #3b3b3b;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 11px 13px;
            font-size: 0.9rem;
            color: #333333;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--color-tone-1);
            box-shadow: 0 0 0 0.2rem rgba(200, 65, 83, 0.12);
        }

        textarea.form-control {
            resize: vertical;
        }

        /* Botón principal */
        .btn-base {
            background-color: var(--color-base) !important;
            border-color: var(--color-base) !important;
            color: #ffffff !important;
            font-weight: 500;
            border-radius: 8px;
            padding: 10px 18px;
        }

        .btn-base:hover,
        .btn-base:focus,
        .btn-base:active {
            background-color: var(--color-tone-1) !important;
            border-color: var(--color-tone-1) !important;
            color: #ffffff !important;
        }

        /* Botón cancelar */
        .btn-gray-custom {
            background-color: var(--color-gray) !important;
            border-color: var(--color-gray) !important;
            color: #ffffff !important;
            font-weight: 500;
            border-radius: 8px;
            padding: 10px 18px;
        }

        .btn-gray-custom:hover,
        .btn-gray-custom:focus,
        .btn-gray-custom:active {
            background-color: #6b6b6b !important;
            border-color: #6b6b6b !important;
            color: #ffffff !important;
        }

        /* Información */
        .info-box {
            background: #f7f8f9;
            border: 1px solid #edf0f2;
            border-radius: 8px;
            padding: 13px 15px;
            color: #6c757d;
            font-size: 0.85rem;
        }

        /* Alertas */
        .alert {
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        @media (max-width: 576px) {
            .card-body-custom {
                padding: 20px;
            }

            .card-header-custom {
                padding: 18px 20px;
            }

            .main-container {
                padding-top: 25px;
            }
        }
    </style>
</head>

<body>

    <!-- Barra de navegación -->
    <nav class="navbar navbar-tecnostock py-3">
        <div class="container">

            <a class="navbar-brand fs-4" href="../index.php">
                <i class="bi bi-box-seam me-2"></i>TecnoStock
            </a>

            <a href="../index.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left me-1"></i>
                Volver al inventario
            </a>

        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="container main-container">

        <div class="row justify-content-center">

            <div class="col-lg-8 col-md-10">

                <!-- Encabezado -->
                <div class="mb-4">

                    <h2 class="page-title">Nuevo Producto</h2>

                    <p class="page-description">
                        Complete los datos del producto para agregarlo al inventario central.
                    </p>

                </div>

                <!-- Tarjeta del formulario -->
                <div class="card bg-white">

                    <div class="card-header-custom">

                        <div class="d-flex align-items-center gap-3">

                            <div class="icon-container">
                                <i class="bi bi-box-seam"></i>
                            </div>

                            <div>
                                <h5 class="fw-bold mb-1">
                                    Registrar Nuevo Producto
                                </h5>

                                <small class="text-muted">
                                    Información general y control de existencias
                                </small>
                            </div>

                        </div>

                    </div>

                    <div class="card-body-custom">

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger shadow-sm">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <form action="guardar.php" method="POST">

                            <!-- Código -->
                            <div class="mb-3">

                                <label class="form-label">
                                    <i class="bi bi-upc-scan me-1"></i>
                                    Código Único
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    name="codigo"
                                    required
                                    placeholder="Ej. TEC-001"
                                >

                            </div>

                            <!-- Nombre -->
                            <div class="mb-3">

                                <label class="form-label">
                                    <i class="bi bi-tag me-1"></i>
                                    Nombre del Producto
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    name="nombre"
                                    required
                                    placeholder="Ej. Audífonos Inalámbricos"
                                >

                            </div>

                            <!-- Descripción -->
                            <div class="mb-4">

                                <label class="form-label">
                                    <i class="bi bi-card-text me-1"></i>
                                    Descripción
                                </label>

                                <textarea
                                    class="form-control"
                                    name="descripcion"
                                    rows="3"
                                    placeholder="Breve detalle del producto..."
                                ></textarea>

                            </div>

                            <!-- Precio y categoría -->
                            <div class="row">

                                <div class="col-md-6 mb-3">

                                    <label class="form-label">
                                        <i class="bi bi-currency-dollar me-1"></i>
                                        Precio ($)
                                    </label>

                                    <input
                                        type="number"
                                        step="0.01"
                                        class="form-control"
                                        name="precio"
                                        min="0"
                                        required
                                        placeholder="0.00"
                                    >

                                </div>

                                <div class="col-md-6 mb-3">

                                    <label class="form-label">
                                        <i class="bi bi-tags me-1"></i>
                                        Categoría
                                    </label>

                                    <select
                                        class="form-select"
                                        name="id_categoria"
                                        required
                                    >

                                        <option value="">
                                            Seleccione categoría...
                                        </option>

                                        <?php foreach ($categorias as $cat): ?>

                                            <option value="<?php echo $cat['id_categoria']; ?>">
                                                <?php echo htmlspecialchars($cat['nombre']); ?>
                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>

                            </div>

                            <!-- Stock -->
                            <div class="row mb-4">

                                <div class="col-md-6 mb-3 mb-md-0">

                                    <label class="form-label">
                                        <i class="bi bi-boxes me-1"></i>
                                        Stock Inicial
                                    </label>

                                    <input
                                        type="number"
                                        class="form-control"
                                        name="stock_actual"
                                        min="0"
                                        required
                                        placeholder="0"
                                    >

                                </div>

                                <div class="col-md-6">

                                    <label class="form-label">
                                        <i class="bi bi-exclamation-circle me-1"></i>
                                        Stock Mínimo (Alerta)
                                    </label>

                                    <input
                                        type="number"
                                        class="form-control"
                                        name="stock_minimo"
                                        min="0"
                                        required
                                        placeholder="0"
                                    >

                                </div>

                            </div>

                            <!-- Información -->
                            <div class="info-box mb-4">

                                <i class="bi bi-info-circle me-2"></i>

                                <strong>Importante:</strong>
                                El stock mínimo permite identificar productos
                                que necesitan reposición cuando sus existencias
                                se encuentran por debajo del límite establecido.

                            </div>

                            <!-- Botones -->
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                                <a href="../index.php" class="btn btn-gray-custom">
                                    <i class="bi bi-arrow-left me-1"></i>
                                    Cancelar
                                </a>

                                <button type="submit" class="btn btn-base">
                                    <i class="bi bi-check-lg me-1"></i>
                                    Guardar Producto
                                </button>

                            </div>

                        </form>

                    </div>
                </div>

                <!-- Pie -->
                <p class="text-center text-muted mt-4 small">
                    <i class="bi bi-shield-check me-1"></i>
                    TecnoStock - Sistema de Gestión de Inventario
                </p>

            </div>

        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
