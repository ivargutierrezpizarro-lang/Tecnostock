<?php
$host = 'localhost';
$dbname = 'bd_tecnostock';
$username = 'root';
$password = '';

try {
    // Conexión a MySQL 
    $conexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Configurar el modo de error de PDO para excepciones
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    exit();
}
?>
```[cite: 7]

---

### 2. `login.php` (Raíz del proyecto)
```php
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - TecnoStock</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center py-4" style="height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-4 font-weight-bold text-primary">TecnoStock</h3>
                        <p class="text-center text-muted mb-4">Ingrese sus credenciales</p>
                        
                        <form action="procesar_login.php" method="POST">
                            <div class="mb-3">
                                <label for="correo" class="form-label">Correo electrónico</label>
                                <input type="email" class="form-control" id="correo" name="correo" required>
                            </div>
                            <div class="mb-3">
                                <label for="clave" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="clave" name="clave" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Ingresar</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
```[cite: 5]

---

### 3. `procesar_login.php` (Raíz del proyecto)
```php
<?php
session_start();
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = trim($_POST['correo']);
    $clave = trim($_POST['clave']);

    if (!empty($correo) && !empty($clave)) {
        try {
            $sql = "SELECT id_usuario, nombre, correo, clave, estado FROM usuario WHERE correo = :correo";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
            $stmt->execute();
            
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && $clave === $usuario['clave']) {
                if ($usuario['estado'] == 1) {
                    $_SESSION['id_usuario'] = $usuario['id_usuario'];
                    $_SESSION['nombre_usuario'] = $usuario['nombre'];
                    $_SESSION['correo_usuario'] = $usuario['correo'];

                    header("Location: index.php");
                    exit();
                } else {
                    echo "<script>alert('Su usuario está desactivado.'); window.location='login.php';</script>";
                }
            } else {
                echo "<script>alert('Correo o contraseña incorrectos.'); window.location='login.php';</script>";
            }

        } catch (PDOException $e) {
            echo "Error en el sistema: " . $e->getMessage();
        }
    } else {
        echo "<script>alert('Por favor complete todos los campos.'); window.location='login.php';</script>";
    }
} else {
    header("Location: login.php");
    exit();
}
?>
```[cite: 3]

---

### 4. `logout.php` (Raíz del proyecto)
```php
<?php
session_start();
session_destroy();
header("Location: login.php");
exit();
?>
```[cite: 4]

---

### 5. `index.php` (Raíz del proyecto)
```php
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

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
        <div class="row align-items-center mb-4">
            <div class="col-md-5">
                <h2>Inventario de Productos</h2>
                <p class="text-muted">Gestión de stock, entradas y salidas</p>
            </div>
            <div class="col-md-7 text-md-end">
                <a href="categorias/crear.php" class="btn btn-outline-primary mb-1"><i class="bi bi-tags"></i> Nueva Categoría</a>
                <a href="productos/crear.php" class="btn btn-primary mb-1"><i class="bi bi-plus-circle"></i> Nuevo Producto</a>
                <a href="movimientos/registrar.php" class="btn btn-success mb-1"><i class="bi bi-arrow-left-right"></i> Registrar Movimiento</a>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
        <?php endif; ?>

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
                                    <?php $esStockBajo = ($prod['stock_actual'] < $prod['stock_minimo']); ?>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```[cite: 6]

---

### 6. `database/tecnostock.sql` (Dentro de la carpeta `database/`)
```sql
-- Tabla Usuario
CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    clave VARCHAR(255) NOT NULL,
    estado TINYINT DEFAULT 1
);

-- Tabla Categoría
CREATE TABLE categoria (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado TINYINT DEFAULT 1
);

-- Tabla Producto
CREATE TABLE producto (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL CHECK (precio >= 0),
    stock_actual INT NOT NULL CHECK (stock_actual >= 0),
    stock_minimo INT NOT NULL CHECK (stock_minimo >= 0),
    estado TINYINT DEFAULT 1,
    id_categoria INT,
    FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria)
);

-- Tabla Movimiento
CREATE TABLE movimiento (
    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    tipo ENUM('ENTRADA', 'SALIDA') NOT NULL,
    cantidad INT NOT NULL CHECK (cantidad > 0),
    observacion TEXT,
    id_producto INT,
    id_usuario INT,
    FOREIGN KEY (id_producto) REFERENCES producto(id_producto),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);

-- Usuario administrador por defecto (Contraseña: 1234)
INSERT INTO usuario (nombre, correo, clave, estado) 
VALUES ('Administrador', 'admin@tecnostock.com', '1234', 1);
```[cite: 2]

---

### 7. `categorias/crear.php` (Dentro de la carpeta `categorias/`)
```php
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
            $stmt = $conexion->prepare("INSERT INTO categoria (nombre, descripcion) VALUES (:nombre, :descripcion)");
            $stmt->execute([':nombre' => $nombre, ':descripcion' => $descripcion]);
            echo "<script>alert('Categoría creada con éxito'); window.location='crear.php';</script>";
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
    <title>Nueva Categoría - TecnoStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="mb-4">Registrar Nueva Categoría</h3>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form action="crear.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Nombre de la Categoría</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" name="descripcion" rows="2"></textarea>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="../index.php" class="btn btn-secondary">Volver al Inicio</a>
                                <button type="submit" class="btn btn-primary">Guardar Categoría</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>