
<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../conexion.php';

// Obtener productos activos para el select
try {
    $stmt = $conexion->query("SELECT id_producto, codigo, nombre, stock_actual FROM producto WHERE estado = 1");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al cargar productos: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_producto = $_POST['id_producto'];
    $tipo = $_POST['tipo'];
    $cantidad = intval($_POST['cantidad']);
    $observacion = trim($_POST['observacion']);
    $id_usuario = $_SESSION['id_usuario'];

    if (!empty($id_producto) && !empty($tipo) && $cantidad > 0) {
        try {
            $conexion->beginTransaction();

            // Consultar stock actual
            $stmtProd = $conexion->prepare("SELECT stock_actual FROM producto WHERE id_producto = :id FOR UPDATE");
            $stmtProd->execute([':id' => $id_producto]);
            $prodData = $stmtProd->fetch(PDO::FETCH_ASSOC);

            if (!$prodData) {
                throw new Exception("El producto seleccionado no existe.");
            }

            $stockActual = intval($prodData['stock_actual']);

            // Validar stock disponible
            if ($tipo === 'SALIDA' && $stockActual < $cantidad) {
                throw new Exception("Stock insuficiente. Stock actual disponible: $stockActual.");
            }

            // Calcular nuevo stock
            $nuevoStock = ($tipo === 'ENTRADA')
                ? ($stockActual + $cantidad)
                : ($stockActual - $cantidad);

            // Registrar movimiento
            $sqlMov = "INSERT INTO movimiento (tipo, cantidad, observacion, id_producto, id_usuario)
                       VALUES (:tipo, :cantidad, :observacion, :id_producto, :id_usuario)";

            $stmtMov = $conexion->prepare($sqlMov);
            $stmtMov->execute([
                ':tipo' => $tipo,
                ':cantidad' => $cantidad,
                ':observacion' => $observacion,
                ':id_producto' => $id_producto,
                ':id_usuario' => $id_usuario
            ]);

            // Actualizar stock
            $sqlUpd = "UPDATE producto SET stock_actual = :nuevo_stock WHERE id_producto = :id_producto";
            $stmtUpd = $conexion->prepare($sqlUpd);
            $stmtUpd->execute([
                ':nuevo_stock' => $nuevoStock,
                ':id_producto' => $id_producto
            ]);

            $conexion->commit();

            echo "<script>alert('Movimiento registrado y stock actualizado exitosamente.'); window.location='../index.php';</script>";
            exit();

        } catch (Exception $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            $error = "Error en la transacción: " . $e->getMessage();
        }
    } else {
        $error = "Por favor complete los campos obligatorios y asegúrese de que la cantidad sea mayor a 0.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registrar Movimiento - TecnoStock</title>

    <!-- Bootstrap -->
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
        .card-movimiento {
            background: #ffffff;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .card-header-custom {
            background: #ffffff;
            border-bottom: 1px solid #edf0f2;
            padding: 22px 28px;
        }

        .card-body-custom {
            padding: 28px;
        }

        /* Icono principal */
        .icon-container {
            width: 48px;
            height: 48px;
            background: rgba(65, 200, 115, 0.12);
            color: #36a960;
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
            border-color: #4197c8;
            box-shadow: 0 0 0 0.2rem rgba(65, 151, 200, 0.12);
        }

        textarea.form-control {
            resize: vertical;
        }

        /* Botones */
        .btn-movimiento {
            background-color: var(--color-movimiento) !important;
            border-color: var(--color-movimiento) !important;
            color: #ffffff !important;
            font-weight: 500;
            border-radius: 8px;
            padding: 10px 18px;
        }

        .btn-movimiento:hover {
            background-color: var(--color-movimiento-hover) !important;
            border-color: var(--color-movimiento-hover) !important;
        }

        .btn-cancelar {
            background-color: #808080;
            border-color: #808080;
            color: #ffffff;
            font-weight: 500;
            border-radius: 8px;
            padding: 10px 18px;
        }

        .btn-cancelar:hover {
            background-color: #666666;
            border-color: #666666;
            color: #ffffff;
        }

        /* Información inferior */
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
                    <h2 class="page-title">Registrar Movimiento</h2>
                    <p class="page-description">
                        Registra entradas y salidas para mantener actualizado el inventario.
                    </p>
                </div>

                <!-- Tarjeta del formulario -->
                <div class="card card-movimiento">

                    <div class="card-header-custom">

                        <div class="d-flex align-items-center gap-3">

                            <div class="icon-container">
                                <i class="bi bi-arrow-left-right"></i>
                            </div>

                            <div>
                                <h5 class="fw-bold mb-1">Entrada o Salida de Stock</h5>
                                <small class="text-muted">
                                    Completa los datos del movimiento
                                </small>
                            </div>

                        </div>

                    </div>

                    <div class="card-body-custom">

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger shadow-sm" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <form action="registrar.php" method="POST">

                            <!-- Seleccionar producto -->
                            <div class="mb-4">

                                <label class="form-label">
                                    <i class="bi bi-box-seam me-1"></i>
                                    Seleccionar Producto
                                </label>

                                <select class="form-select" name="id_producto" required>

                                    <option value="">Seleccione un producto...</option>

                                    <?php foreach ($productos as $p): ?>

                                        <option value="<?php echo $p['id_producto']; ?>">
                                            <?php echo htmlspecialchars(
                                                $p['codigo'] . ' - ' .
                                                $p['nombre'] .
                                                ' (Stock actual: ' .
                                                $p['stock_actual'] . ')'
                                            ); ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                            <!-- Tipo y cantidad -->
                            <div class="row">

                                <div class="col-md-6 mb-4">

                                    <label class="form-label">
                                        <i class="bi bi-arrow-left-right me-1"></i>
                                        Tipo de Movimiento
                                    </label>

                                    <select class="form-select" name="tipo" required>

                                        <option value="ENTRADA">
                                            ENTRADA (Sumar stock)
                                        </option>

                                        <option value="SALIDA">
                                            SALIDA (Restar stock)
                                        </option>

                                    </select>

                                </div>

                                <div class="col-md-6 mb-4">

                                    <label class="form-label">
                                        <i class="bi bi-123 me-1"></i>
                                        Cantidad
                                    </label>

                                    <input
                                        type="number"
                                        class="form-control"
                                        name="cantidad"
                                        min="1"
                                        placeholder="Ingrese la cantidad"
                                        required
                                    >

                                </div>

                            </div>

                            <!-- Observación -->
                            <div class="mb-4">

                                <label class="form-label">
                                    <i class="bi bi-chat-left-text me-1"></i>
                                    Observación / Motivo
                                </label>

                                <textarea
                                    class="form-control"
                                    name="observacion"
                                    rows="3"
                                    placeholder="Ej. Reposición de proveedor, venta directa, etc."
                                ></textarea>

                            </div>

                            <!-- Información -->
                            <div class="info-box mb-4">

                                <i class="bi bi-info-circle me-2"></i>

                                <strong>Importante:</strong>
                                El stock del producto se actualizará automáticamente
                                después de registrar el movimiento. Las salidas no
                                pueden superar el stock disponible.

                            </div>

                            <!-- Botones -->
                            <div class="d-flex justify-content-between flex-wrap gap-2">

                                <a href="../index.php" class="btn btn-cancelar">
                                    <i class="bi bi-x-circle me-1"></i>
                                    Cancelar
                                </a>

                                <button type="submit" class="btn btn-movimiento">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Registrar Movimiento
                                </button>

                            </div>

                        </form>

                    </div>
                </div>

                <p class="text-center text-muted mt-4 small">
                    <i class="bi bi-shield-check me-1"></i>
                    TecnoStock - Sistema de Gestión de Inventario
                </p>

            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
