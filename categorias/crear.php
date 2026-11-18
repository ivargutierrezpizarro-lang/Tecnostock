<?php
require_once '../includes/funciones.php';
requerir_login('../');
require_once '../conexion.php';

$errores = [];
$nombre = '';
$descripcion = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigir_post_csrf();
    $nombre      = trim((string)($_POST['nombre'] ?? ''));
    $descripcion = trim((string)($_POST['descripcion'] ?? ''));

    if ($nombre === '' || mb_strlen($nombre) > 100) {
        $errores[] = 'El nombre de la categoría es obligatorio (máximo 100 caracteres).';
    }
    if (mb_strlen($descripcion) > 500) {
        $errores[] = 'La descripción no puede superar los 500 caracteres.';
    }

    if (!$errores) {
        try {
            $stmt = $conexion->prepare('INSERT INTO categoria (nombre, descripcion) VALUES (:n, :d)');
            $stmt->execute([':n' => $nombre, ':d' => $descripcion]);
            flash('success', 'Categoría "' . $nombre . '" creada con éxito.');
            header('Location: crear.php');
            exit();
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? 0) == 1062) {
                $errores[] = 'Ya existe una categoría con ese nombre.';
            } else {
                error_log('Crear categoría: ' . $e->getMessage());
                $errores[] = 'No se pudo guardar la categoría. Intente nuevamente.';
            }
        }
    }
}

$titulo = 'Nueva Categoría';
$base   = '../';
require '../includes/header.php';
?>
<main class="container main-container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="mb-4">
                <h3 class="page-title"><i class="bi bi-tags me-2" style="color:var(--color-base)"></i>Registrar Nueva Categoría</h3>
                <p class="page-description mb-0">Agregue una nueva clasificación para organizar los productos.</p>
            </div>

            <?php flash_mostrar(); ?>
            <?php foreach ($errores as $err): ?>
                <div class="alert alert-danger shadow-sm"><i class="bi bi-exclamation-triangle me-2"></i><?php echo e($err); ?></div>
            <?php endforeach; ?>

            <div class="card bg-white">
                <div class="card-body p-4">
                    <form action="crear.php" method="POST" class="needs-validation" novalidate>
                        <?php echo csrf_input(); ?>
                        <div class="mb-3">
                            <label class="form-label" for="nombre">Nombre de la Categoría</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" maxlength="100" required
                                   placeholder="Ej: Accesorios, Computación..." value="<?php echo e($nombre); ?>">
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="descripcion">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" maxlength="500"
                                      placeholder="Descripción opcional de la categoría..."><?php echo e($descripcion); ?></textarea>
                        </div>
                        <hr class="text-muted opacity-25">
                        <div class="d-flex justify-content-between flex-wrap gap-2">
                            <a href="../index.php" class="btn btn-gray-custom"><i class="bi bi-arrow-left me-1"></i>Volver</a>
                            <button type="submit" class="btn btn-base"><i class="bi bi-check-lg me-1"></i>Guardar Categoría</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<?php require '../includes/footer.php'; ?>
