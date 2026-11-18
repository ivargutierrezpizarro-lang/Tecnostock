<?php
require_once 'includes/funciones.php';

if (isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

$mensajes = [
    'campos'      => 'Por favor complete todos los campos.',
    'credenciales'=> 'Correo o contraseña incorrectos.',
    'inactivo'    => 'Su usuario está desactivado. Contacte al administrador.',
    'sistema'     => 'Ocurrió un error en el sistema. Intente nuevamente.',
];
$error = $mensajes[$_GET['error'] ?? ''] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - TecnoStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --color-primary:#C84153; --color-tone-1:#BC525C; --color-gray-dark:#808080; --color-bg:#f4f5f7; }
        body { font-family:'Inter',sans-serif; background:var(--color-bg); min-height:100vh; display:flex; align-items:center; }
        .card-login { border:none; border-radius:16px; box-shadow:0 10px 25px rgba(128,128,128,.15); overflow:hidden; }
        .login-header { background:var(--color-primary); color:#fff; padding:30px 20px; text-align:center; }
        .form-control:focus { border-color:var(--color-tone-1); box-shadow:0 0 0 .25rem rgba(200,65,83,.15); }
        .btn-custom-login { background:var(--color-primary)!important; border-color:var(--color-primary)!important; color:#fff!important; font-weight:500; padding:10px; }
        .btn-custom-login:hover { background:var(--color-tone-1)!important; border-color:var(--color-tone-1)!important; }
        .text-muted-custom { color:var(--color-gray-dark)!important; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card card-login bg-white">
                <div class="login-header">
                    <h3 class="fw-bold m-0">TecnoStock</h3>
                    <p class="text-white-50 small mt-1 mb-0">Sistema de Control de Inventario</p>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2 small" role="alert"><?php echo e($error); ?></div>
                    <?php endif; ?>
                    <form action="procesar_login.php" method="POST" class="needs-validation" novalidate>
                        <?php echo csrf_input(); ?>
                        <div class="mb-3">
                            <label for="correo" class="form-label small fw-semibold text-muted-custom">Correo electrónico</label>
                            <input type="email" class="form-control" id="correo" name="correo" maxlength="100" required autofocus>
                        </div>
                        <div class="mb-4">
                            <label for="clave" class="form-label small fw-semibold text-muted-custom">Contraseña</label>
                            <input type="password" class="form-control" id="clave" name="clave" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-custom-login shadow-none">Ingresar al Sistema</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="assets/js/validaciones.js"></script>
</body>
</html>
