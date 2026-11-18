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
        $stmt = $conexion->prepare("UPDATE producto SET estado = 1 WHERE id_producto = :id");
        $stmt->execute([':id' => $id]);
    } catch (PDOException $e) {
        // Manejo de error opcional
    }
}

header("Location: ../index.php");
exit();
?>