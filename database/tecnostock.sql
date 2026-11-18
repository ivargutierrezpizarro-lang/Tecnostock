-- ============================================================
-- TecnoStock - Script de base de datos (reproducible)
-- Requiere MySQL 8.0.16+ o MariaDB 10.2.1+ (para que CHECK se aplique)
-- Uso: phpMyAdmin > Importar, o:  mysql -u root < tecnostock.sql
-- ============================================================
DROP DATABASE IF EXISTS bd_tecnostock;
CREATE DATABASE bd_tecnostock CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bd_tecnostock;

-- Tabla Usuario
CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(100) NOT NULL,
    correo     VARCHAR(100) NOT NULL UNIQUE,
    clave      VARCHAR(255) NOT NULL,            -- hash bcrypt (password_hash)
    estado     TINYINT NOT NULL DEFAULT 1 CHECK (estado IN (0,1))
) ENGINE=InnoDB;

-- Tabla Categoría
CREATE TABLE categoria (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre       VARCHAR(100) NOT NULL UNIQUE,
    descripcion  TEXT,
    estado       TINYINT NOT NULL DEFAULT 1 CHECK (estado IN (0,1))
) ENGINE=InnoDB;

-- Tabla Producto (código único; precio y stocks no negativos)
CREATE TABLE producto (
    id_producto  INT AUTO_INCREMENT PRIMARY KEY,
    codigo       VARCHAR(50)  NOT NULL UNIQUE,
    nombre       VARCHAR(100) NOT NULL,
    descripcion  TEXT,
    precio       DECIMAL(10,2) NOT NULL CHECK (precio >= 0),
    stock_actual INT NOT NULL CHECK (stock_actual >= 0),
    stock_minimo INT NOT NULL CHECK (stock_minimo >= 0),
    estado       TINYINT NOT NULL DEFAULT 1 CHECK (estado IN (0,1)),
    id_categoria INT NOT NULL,
    CONSTRAINT fk_producto_categoria
        FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria),
    INDEX idx_producto_nombre (nombre),
    INDEX idx_producto_categoria (id_categoria)
) ENGINE=InnoDB;

-- Tabla Movimiento (entradas y salidas). Todos los campos clave son obligatorios.
CREATE TABLE movimiento (
    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
    fecha_hora    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tipo          ENUM('ENTRADA','SALIDA') NOT NULL,
    cantidad      INT NOT NULL CHECK (cantidad > 0),
    observacion   TEXT,
    id_producto   INT NOT NULL,
    id_usuario    INT NOT NULL,
    CONSTRAINT fk_mov_producto FOREIGN KEY (id_producto) REFERENCES producto(id_producto),
    CONSTRAINT fk_mov_usuario  FOREIGN KEY (id_usuario)  REFERENCES usuario(id_usuario),
    INDEX idx_mov_producto (id_producto),
    INDEX idx_mov_fecha (fecha_hora)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Datos de prueba
-- ------------------------------------------------------------
-- Usuario administrador (contraseña: 1234, guardada como hash bcrypt)
INSERT INTO usuario (nombre, correo, clave, estado) VALUES
('Administrador', 'admin@tecnostock.com',
 '$2y$10$vEnG1W7970GpYxBJc6GOcOoJZxtpZCz3ZPqWelmbx5yfHLx6G4jlq', 1);

INSERT INTO categoria (nombre, descripcion) VALUES
('Accesorios',  'Cables, mouse y periféricos básicos'),
('Computación', 'Teclados, webcams y componentes'),
('Audio',       'Audífonos y parlantes');

-- stock_actual coincide con la suma de los movimientos de más abajo
INSERT INTO producto (codigo, nombre, descripcion, precio, stock_actual, stock_minimo, estado, id_categoria) VALUES
('TEC-001', 'Audífonos Inalámbricos',      'Bluetooth 5.0, 20 h de batería', 19990, 25,  5, 1, 3),
('TEC-002', 'Mouse Óptico USB',            'Mouse 1200 DPI',                  6990,  3, 10, 1, 1),  -- stock bajo
('TEC-003', 'Teclado Mecánico',            'Switch rojo, layout español',    39990, 12,  4, 1, 2),
('TEC-004', 'Cable HDMI 2m',               'HDMI 2.0 4K',                     4990,  0,  5, 1, 1),  -- stock bajo (0)
('TEC-005', 'Webcam HD (descontinuada)',   'Webcam 720p',                    15990,  7,  2, 0, 2);  -- desactivado

INSERT INTO movimiento (fecha_hora, tipo, cantidad, observacion, id_producto, id_usuario) VALUES
(NOW() - INTERVAL 10 DAY, 'ENTRADA', 30, 'Stock inicial',   1, 1),
(NOW() - INTERVAL 5 DAY,  'SALIDA',   5, 'Venta directa',   1, 1),
(NOW() - INTERVAL 10 DAY, 'ENTRADA', 15, 'Stock inicial',   2, 1),
(NOW() - INTERVAL 3 DAY,  'SALIDA',  12, 'Venta mayorista', 2, 1),
(NOW() - INTERVAL 10 DAY, 'ENTRADA', 12, 'Stock inicial',   3, 1),
(NOW() - INTERVAL 10 DAY, 'ENTRADA', 10, 'Stock inicial',   4, 1),
(NOW() - INTERVAL 2 DAY,  'SALIDA',  10, 'Venta mayorista', 4, 1),
(NOW() - INTERVAL 10 DAY, 'ENTRADA',  7, 'Stock inicial',   5, 1);
