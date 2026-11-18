<?php
// Funciones compartidas: sesión, escape, CSRF, mensajes flash y validaciones de servidor.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Escapa texto para imprimirlo en HTML. */
function e($valor): string
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

/** Exige sesión iniciada. $base es la ruta relativa a la raíz ('' o '../'). */
function requerir_login(string $base = ''): void
{
    if (!isset($_SESSION['id_usuario'])) {
        header("Location: {$base}login.php");
        exit();
    }
}

/* ---------- CSRF ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_input(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/** Detiene la ejecución si el método no es POST o el token no coincide. */
function exigir_post_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Método no permitido.');
    }
    $enviado = $_POST['csrf'] ?? '';
    if (!is_string($enviado) || !hash_equals($_SESSION['csrf'] ?? '', $enviado)) {
        http_response_code(400);
        exit('Solicitud no válida (token de seguridad incorrecto). Vuelva atrás y reintente.');
    }
}

/* ---------- Mensajes flash ---------- */
function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'msg' => $mensaje];
}

function flash_mostrar(): void
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $icono = $f['tipo'] === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill';
        echo '<div class="alert alert-' . e($f['tipo']) . ' alert-dismissible shadow-sm" role="alert">'
           . '<i class="bi bi-' . $icono . ' me-2"></i>' . e($f['msg'])
           . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button></div>';
    }
}

/* ---------- Validaciones de servidor ---------- */

/** Devuelve un entero >= 0 si $v es un entero válido (sin decimales ni texto); si no, null. */
function entero_no_negativo($v): ?int
{
    if (!is_string($v) && !is_int($v)) {
        return null;
    }
    $v = trim((string)$v);
    if ($v === '' || !ctype_digit($v) || strlen($v) > 9) {
        return null;
    }
    return (int)$v;
}

/** Valida los datos de un producto. $esNuevo incluye código y stock inicial. */
function validar_producto(PDO $conexion, array $d, bool $esNuevo): array
{
    $errores = [];
    $l = [];

    $l['nombre']      = trim((string)($d['nombre'] ?? ''));
    $l['descripcion'] = trim((string)($d['descripcion'] ?? ''));
    $precioTxt        = trim((string)($d['precio'] ?? ''));

    if ($esNuevo) {
        $l['codigo'] = trim((string)($d['codigo'] ?? ''));
        if ($l['codigo'] === '' || strlen($l['codigo']) > 50) {
            $errores[] = 'El código es obligatorio (máximo 50 caracteres).';
        } elseif (!preg_match('/^[A-Za-z0-9._-]+$/', $l['codigo'])) {
            $errores[] = 'El código solo puede tener letras, números, punto, guion y guion bajo.';
        }
    }

    if ($l['nombre'] === '' || mb_strlen($l['nombre']) > 100) {
        $errores[] = 'El nombre es obligatorio (máximo 100 caracteres).';
    }

    if (!is_numeric($precioTxt) || (float)$precioTxt < 0 || (float)$precioTxt > 99999999.99) {
        $errores[] = 'El precio debe ser un número mayor o igual a 0.';
    }
    $l['precio'] = is_numeric($precioTxt) ? round((float)$precioTxt, 2) : 0;

    $l['stock_minimo'] = entero_no_negativo($d['stock_minimo'] ?? null);
    if ($l['stock_minimo'] === null) {
        $errores[] = 'El stock mínimo debe ser un número entero mayor o igual a 0.';
    }

    if ($esNuevo) {
        $l['stock_actual'] = entero_no_negativo($d['stock_actual'] ?? null);
        if ($l['stock_actual'] === null) {
            $errores[] = 'El stock inicial debe ser un número entero mayor o igual a 0.';
        }
    }

    $idCat = entero_no_negativo($d['id_categoria'] ?? null);
    $l['id_categoria'] = $idCat;
    if ($idCat === null || $idCat === 0) {
        $errores[] = 'Debe seleccionar una categoría.';
    } else {
        $q = $conexion->prepare('SELECT 1 FROM categoria WHERE id_categoria = :id AND estado = 1');
        $q->execute([':id' => $idCat]);
        if (!$q->fetchColumn()) {
            $errores[] = 'La categoría seleccionada no existe o está desactivada.';
        }
    }

    return [$errores, $l];
}

/** Cambia el estado (1 activo / 0 desactivado) de un producto. */
function cambiar_estado_producto(PDO $conexion, $id, int $estado): bool
{
    $id = entero_no_negativo($id);
    if (!$id) {
        return false;
    }
    $st = $conexion->prepare('UPDATE producto SET estado = :e WHERE id_producto = :id');
    $st->execute([':e' => $estado, ':id' => $id]);
    return $st->rowCount() > 0;
}
