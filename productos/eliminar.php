<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../conexion.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        // Opcional: Si necesitas borrar los movimientos asociados antes para evitar conflictos de llave foránea
        $stmtMov = $conexion->prepare("DELETE FROM movimiento WHERE id_producto = :id");
        $stmtMov->execute([':id' => $id]);

        // Borrar permanentemente el producto
        $stmt = $conexion->prepare("DELETE FROM producto WHERE id_producto = :id");
        $stmt->execute([':id' => $id]);
        
    } catch (PDOException $e) {
        // Si hay error por restricciones de FK u otro motivo
        echo "<script>alert('No se puede eliminar el producto porque tiene registros asociados.'); window.location='../index.php';</script>";
        exit();
    }
}

header("Location: ../index.php");
exit();
?>