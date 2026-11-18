
<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);

    if (!empty($nombre)) {
        try {
            $stmt = $conexion->prepare(
                "INSERT INTO categoria (nombre, descripcion)
                 VALUES (:nombre, :descripcion)"
            );

            $stmt->execute([
                ':nombre' => $nombre,
                ':descripcion' => $descripcion
            ]);

            echo "<script>
                alert('Categoría creada con éxito');
                window.location='crear.php';
            </script>";
            exit();

        } catch (PDOException $e) {
            $error = "Error al guardar la categoría: " . $e->getMessage();
        }
    } else {
        $error = "El nombre de la categoría es obligatorio.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Categoría - TecnoStock</title>

    <!-- Bootstrap -->
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

        /* Navbar */
        .navbar {
            background-color: #212529;
            border-bottom: 1px solid #343a40;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
        }

        .navbar-brand {
            color:#880707 !important;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .navbar-brand i {
            color: var(--color-base);
        }

        /* Tarjetas */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(128, 128, 128, 0.08);
        }

        /* Formularios */
        .form-control {
            border-radius: 8px;
            padding: 10px 12px;
            border: 1px solid #dee2e6;
        }

        .form-control:focus {
            border-color: var(--color-tone-1);
            box-shadow: 0 0 0 0.25rem rgba(200, 65, 83, 0.15);
        }

        /* Botón principal */
        .btn-base {
            background-color: var(--color-base) !important;
            border-color: var(--color-base) !important;
            color: #ffffff !important;
            font-weight: 500;
            border-radius: 8px;
            padding: 9px 16px;
        }

        .btn-base:hover,
        .btn-base:focus,
        .btn-base:active {
            background-color: var(--color-tone-1) !important;
            border-color: var(--color-tone-1) !important;
            color: #ffffff !important;
        }

        /* Botón gris */
        .btn-gray-custom {
            background-color: var(--color-gray) !important;
            border-color: var(--color-gray) !important;
            color: #ffffff !important;
            font-weight: 500;
            border-radius: 8px;
            padding: 9px 16px;
        }

        .btn-gray-custom:hover,
        .btn-gray-custom:focus,
        .btn-gray-custom:active {
            background-color: #6b6b6b !important;
            border-color: #6b6b6b !important;
            color: #ffffff !important;
        }

        .page-title {
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #6c757d;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="bi bi-box-seam me-2"></i>TecnoStock
            </a>

            <a href="../index.php" class="btn btn-gray-custom btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Volver al inventario
            </a>
        </div>
    </nav>

    <!-- Contenido -->
    <main class="container py-5">

        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">

                <div class="mb-4">
                    <h3 class="page-title text-dark">
                        <i class="bi bi-tags me-2" style="color: var(--color-base);"></i>
                        Registrar Nueva Categoría
                    </h3>

                    <p class="text-muted small mb-0">
                        Agregue una nueva clasificación para organizar los productos.
                    </p>
                </div>

                <div class="card bg-white">
                    <div class="card-body p-4">

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger border-0 shadow-sm">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <form action="crear.php" method="POST">

                            <div class="mb-3">
                                <label class="form-label">
                                    Nombre de la Categoría
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    name="nombre"
                                    placeholder="Ej: Accesorios, Computación..."
                                    value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>"
                                    required
                                >
                            </div>

                            <div class="mb-4">
                                <label class="form-label">
                                    Descripción
                                </label>

                                <textarea
                                    class="form-control"
                                    name="descripcion"
                                    rows="3"
                                    placeholder="Ingrese una descripción para la categoría..."
                                ><?php echo isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : ''; ?></textarea>
                            </div>

                            <hr class="text-muted opacity-25">

                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                                <a href="../index.php" class="btn btn-gray-custom">
                                    <i class="bi bi-arrow-left me-1"></i>
                                    Volver
                                </a>

                                <button type="submit" class="btn btn-base">
                                    <i class="bi bi-check-lg me-1"></i>
                                    Guardar Categoría
                                </button>

                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>

    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
