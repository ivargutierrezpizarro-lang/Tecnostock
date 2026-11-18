<?php
require_once '../includes/funciones.php';
requerir_login('../');
require_once '../conexion.php';

// Solo POST con token CSRF (no se modifican datos mediante enlaces GET)
exigir_post_csrf();

try {
    if (cambiar_estado_producto($conexion, $_POST['id'] ?? null, 0)) {
        flash('success', 'Producto desactivado correctamente.');
    } else {
        flash('danger', 'No se pudo cambiar el estado: el producto no existe o ya estaba en ese estado.');
    }
} catch (PDOException $e) {
    error_log('desactivar producto: ' . $e->getMessage());
    flash('danger', 'Ocurrió un error al cambiar el estado del producto.');
}

header('Location: ../index.php');
exit();
