<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - TecnoStock</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-primary: #C84153;
            --color-tone-1: #BC525C;
            --color-tone-2: #B06064;
            --color-gray-dark: #808080;
            --color-bg: #f4f5f7;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--color-bg);
            height: 100vh;
            display: flex;
            align-items: center;
        }

        .card-login {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(128, 128, 128, 0.15);
            overflow: hidden;
        }

        .login-header {
            background-color: var(--color-primary);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }

        .form-control:focus {
            border-color: var(--color-tone-1);
            box-shadow: 0 0 0 0.25rem rgba(200, 65, 83, 0.15);
        }

        .btn-custom-login {
            background-color: var(--color-primary) !important;
            border-color: var(--color-primary) !important;
            color: #ffffff !important;
            font-weight: 500;
            padding: 10px;
            transition: background-color 0.2s;
        }

        .btn-custom-login:hover, .btn-custom-login:focus, .btn-custom-login:active {
            background-color: var(--color-tone-1) !important;
            border-color: var(--color-tone-1) !important;
            color: #ffffff !important;
        }

        .text-muted-custom {
            color: var(--color-gray-dark) !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card card-login bg-white">
                    <div class="login-header">
                        <h3 class="fw-bold m-0">TecnoStock</h3>
                        <p class="text-white-50 small mt-1 mb-0">Sistema de Control de Inventario</p>
                    </div>
                    <div class="card-body p-4">
                        <form action="procesar_login.php" method="POST">
                            <div class="mb-3">
                                <label for="correo" class="form-label small fw-semibold text-muted-custom">Correo electrónico</label>
                                <input type="email" class="form-control" id="correo" name="correo" required>
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
</body>
</html>