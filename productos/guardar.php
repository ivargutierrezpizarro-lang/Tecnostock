<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo = trim($_POST['codigo']);
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = $_POST['precio'];
    $id_categoria = $_POST['id_categoria'];
    $stock_actual = $_POST['stock_actual'];
    $stock_minimo = $_POST['stock_minimo'];

    try {
        $sql = "INSERT INTO producto (codigo, nombre, descripcion, precio, stock_actual, stock_minimo, id_categoria) 
                VALUES (:codigo, :nombre, :descripcion, :precio, :stock_actual, :stock_minimo, :id_categoria)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':codigo' => $codigo,
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':precio' => $precio,
            ':stock_actual' => $stock_actual,
            ':stock_minimo' => $stock_minimo,
            ':id_categoria' => $id_categoria
        ]);

        header("Location: ../index.php");
        exit();

    } catch (PDOException $e) {
        echo "<script>alert('Error al registrar producto (¿Código duplicado?): " . $e->getMessage() . "'); window.location='crear.php';</script>";
    }
}