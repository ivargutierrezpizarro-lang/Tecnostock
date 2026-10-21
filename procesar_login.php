<?php
session_start();
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = trim($_POST['correo']);
    $clave = trim($_POST['clave']);

    if (!empty($correo) && !empty($clave)) {
        try {
            // Consulta preparada para buscar al usuario por correo
            $sql = "SELECT id_usuario, nombre, correo, clave, estado FROM usuario WHERE correo = :correo";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
            $stmt->execute();
            
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificar si el usuario existe y si la contraseña coincide 
            // (Nota: si usaste texto plano en la BD de prueba como '1234', comparamos directo. 
            // Más adelante se puede usar password_verify si implementas hash).
            if ($usuario && $clave === $usuario['clave']) {
                if ($usuario['estado'] == 1) {
                    // Guardar datos en la sesión
                    $_SESSION['id_usuario'] = $usuario['id_usuario'];
                    $_SESSION['nombre_usuario'] = $usuario['nombre'];
                    $_SESSION['correo_usuario'] = $usuario['correo'];

                    // Redirigir al panel principal (inventario)
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