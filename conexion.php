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
    // Si hay un error de conexión, se detiene la ejecución y se muestra el mensaje
    echo "Error de conexión: " . $e->getMessage();
    exit();
}
?>