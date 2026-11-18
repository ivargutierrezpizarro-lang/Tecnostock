<?php
require_once '../includes/funciones.php';
requerir_login('../');
require_once '../conexion.php';

exigir_post_csrf();

[$errores, $datos] = validar_producto($conexion, $_POST, true);

if (!$errores) {
    try {
        $conexion->beginTransaction();

        $stmt = $conexion->prepare(
            'INSERT INTO producto (codigo, nombre, descripcion, precio, stock_actual, stock_minimo, id_categoria)
             VALUES (:codigo, :nombre, :descripcion, :precio, :stock_actual, :stock_minimo, :id_categoria)'
        );
        $stmt->execute([
            ':codigo'       => $datos['codigo'],
            ':nombre'       => $datos['nombre'],
            ':descripcion'  => $datos['descripcion'],
            ':precio'       => $datos['precio'],
            ':stock_actual' => $datos['stock_actual'],
            ':stock_minimo' => $datos['stock_minimo'],
            ':id_categoria' => $datos['id_categoria'],
        ]);
        $idProducto = (int)$conexion->lastInsertId();

        // Trazabilidad: el stock inicial queda registrado como una ENTRADA
        if ($datos['stock_actual'] > 0) {
            $mov = $conexion->prepare(
                "INSERT INTO movimiento (tipo, cantidad, observacion, id_producto, id_usuario)
                 VALUES ('ENTRADA', :cant, 'Stock inicial', :prod, :usr)"
            );
            $mov->execute([
                ':cant' => $datos['stock_actual'],
                ':prod' => $idProducto,
                ':usr'  => $_SESSION['id_usuario'],
            ]);
        }

        $conexion->commit();
        flash('success', 'Producto "' . $datos['nombre'] . '" registrado correctamente.');
        header('Location: ../index.php');
        exit();

    } catch (PDOException $e) {
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }
        if (($e->errorInfo[1] ?? 0) == 1062) {
            $errores[] = 'El código "' . $datos['codigo'] . '" ya existe. Cada producto debe tener un código único.';
        } else {
            error_log('Guardar producto: ' . $e->getMessage());
            $errores[] = 'No se pudo registrar el producto. Intente nuevamente.';
        }
    }
}

// Hubo errores: se vuelve al formulario conservando lo escrito
$_SESSION['errores'] = $errores;
$_SESSION['old']     = $_POST;
header('Location: crear.php');
exit();
