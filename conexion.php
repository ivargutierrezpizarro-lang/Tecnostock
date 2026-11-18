<?php
// Configuración de conexión PDO a MySQL (XAMPP por defecto)
$host     = 'localhost';
$dbname   = 'bd_tecnostock';
$username = 'root';
$password = '';

try {
    $conexion = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,   // consultas preparadas reales
        ]
    );
} catch (PDOException $e) {
    // No se muestran detalles técnicos al usuario; se registran en el log del servidor
    error_log('Error de conexión: ' . $e->getMessage());
    http_response_code(500);
    exit('No se pudo conectar con la base de datos. Verifique que MySQL esté activo y que exista bd_tecnostock.');
}
