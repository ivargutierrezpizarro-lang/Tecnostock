<?php
require_once 'includes/funciones.php';
require_once 'conexion.php';

// Solo se acepta POST con token CSRF válido
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}
exigir_post_csrf();

$correo = trim((string)($_POST['correo'] ?? ''));
$clave  = (string)($_POST['clave'] ?? '');

if ($correo === '' || $clave === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    header('Location: login.php?error=campos');
    exit();
}

try {
    $stmt = $conexion->prepare(
        'SELECT id_usuario, nombre, correo, clave, estado FROM usuario WHERE correo = :correo'
    );
    $stmt->execute([':correo' => $correo]);
    $usuario = $stmt->fetch();

    $valida = false;
    if ($usuario) {
        $hash = $usuario['clave'];
        if (password_verify($clave, $hash)) {
            $valida = true;
            // Si el algoritmo quedó obsoleto, se actualiza el hash
            if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                $up = $conexion->prepare('UPDATE usuario SET clave = :c WHERE id_usuario = :id');
                $up->execute([':c' => password_hash($clave, PASSWORD_DEFAULT), ':id' => $usuario['id_usuario']]);
            }
        } elseif (strpos($hash, '$2y$') !== 0 && hash_equals($hash, $clave)) {
            // Migración: usuarios antiguos con clave en texto plano -> se convierte a hash al ingresar
            $valida = true;
            $up = $conexion->prepare('UPDATE usuario SET clave = :c WHERE id_usuario = :id');
            $up->execute([':c' => password_hash($clave, PASSWORD_DEFAULT), ':id' => $usuario['id_usuario']]);
        }
    }

    if (!$valida) {
        header('Location: login.php?error=credenciales');
        exit();
    }
    if ((int)$usuario['estado'] !== 1) {
        header('Location: login.php?error=inactivo');
        exit();
    }

    session_regenerate_id(true);   // evita fijación de sesión
    $_SESSION['id_usuario']     = $usuario['id_usuario'];
    $_SESSION['nombre_usuario'] = $usuario['nombre'];
    $_SESSION['correo_usuario'] = $usuario['correo'];

    header('Location: index.php');
    exit();

} catch (PDOException $e) {
    error_log('Login: ' . $e->getMessage());
    header('Location: login.php?error=sistema');
    exit();
}
