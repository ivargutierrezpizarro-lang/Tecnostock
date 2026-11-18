# TecnoStock – Sistema de inventario


## 1. Análisis

### Problema
TecnoStock lleva sus productos en una planilla: hay códigos duplicados, cantidades erróneas y poca claridad sobre el stock real.

### Alcance (MVP)
Gestión de productos, categorías y movimientos de inventario, con control de stock mínimo. **Fuera de alcance:** ventas, compras a proveedores, múltiples roles, reportes avanzados.

### Actor
**Encargado de inventario** (usuario `usuario` con estado activo): administra productos, categorías y movimientos.

### Requerimientos funcionales

| ID | Requerimiento | Implementado en |
|----|---------------|-----------------|
| RF1 | Iniciar sesión con usuario registrado | `login.php`, `procesar_login.php`, `logout.php` |
| RF2 | Registrar productos (código, nombre, categoría, descripción, precio, stock actual y mínimo) | `productos/crear.php`, `productos/guardar.php` |
| RF3 | Consultar listado de productos | `index.php` |
| RF4 | Buscar por nombre, código o categoría | `index.php` (formulario GET) |
| RF5 | Modificar precio, descripción, categoría y stock mínimo; el stock actual cambia por movimientos | `productos/editar.php` |
| RF6 | Desactivar (y reactivar) productos | `productos/desactivar.php`, `productos/activar.php` |
| RF7 | Registrar entradas y salidas | `movimientos/registrar.php` |
| RF8 | Alerta cuando stock actual < stock mínimo | `index.php` (banner + insignia por fila) |
| RF9 | Registrar categorías | `categorias/crear.php` |
| RF10 | Consultar historial de movimientos (trazabilidad) | `movimientos/historial.php` |


### Reglas de negocio y su control

| ID | Regla | Dónde se controla |
|----|-------|-------------------|
| RN1 | El código de producto no se repite | BD: `UNIQUE (codigo)`. PHP: captura error 1062 y muestra mensaje claro |
| RN2 | Precio, stocks y cantidad no negativos | BD: `CHECK`. JS: `validaciones.js`. PHP: `validar_producto()` y `registrar.php` |
| RN3 | Todo movimiento registra fecha, tipo, cantidad, producto y usuario | BD: columnas `NOT NULL` + FK. PHP: usuario tomado de la sesión |
| RN4 | Una salida no puede dejar el stock bajo cero | JS (muestra stock disponible). PHP: valida dentro de transacción con `FOR UPDATE`. BD: `CHECK (stock_actual >= 0)` |
| RN5 | Un producto con movimientos no se elimina; se desactiva | **No existe función de eliminación física.** Las FK impiden borrar productos con movimientos |
| RN6 | El stock se actualiza automáticamente tras cada movimiento válido | `registrar.php`: INSERT del movimiento + UPDATE del stock en una sola transacción |

## 2. Base de datos

Entidades: `usuario`, `categoria`, `producto`, `movimiento` (3FN). Relaciones: `categoria 1─N producto`, `producto 1─N movimiento`, `usuario 1─N movimiento`. Script reproducible en `database/tecnostock.sql` (crea la BD, tablas, restricciones y datos de prueba).

## 3. Seguridad y calidad

- PDO con consultas preparadas reales (`ATTR_EMULATE_PREPARES = false`).
- Contraseñas con `password_hash` / `password_verify`; `session_regenerate_id` al ingresar.
- Token CSRF en todos los formularios; activar/desactivar solo por POST.
- Salida escapada con `htmlspecialchars` (`e()`); errores técnicos solo en el log del servidor.
- Validación en JavaScript (`assets/js/validaciones.js`) **y** repetida en PHP.
- Transacciones en movimientos y en el registro de producto con stock inicial.
- Código organizado: `includes/` (funciones, cabecera, pie), `assets/` (CSS, JS), un módulo por carpeta.

## 5. Plan de pruebas

Completar la columna **Resultado** (OK / FALLA) y guardar capturas como evidencia en `docs/evidencias/` (nombre sugerido: `P01.png`, `P02.png`…). Datos de partida: los del script SQL.

| ID | Tipo | Caso | Datos / pasos | Resultado esperado | Resultado |
|----|------|------|---------------|--------------------|-----------|
| P01 | Normal | Login correcto | admin@tecnostock.com / 1234 | Ingresa al inventario y muestra el nombre |OK |
| P02 | Error | Login con clave errónea | admin@tecnostock.com / abc | Mensaje "Correo o contraseña incorrectos" |OK |
| P03 | Error | Acceso sin sesión | Abrir `index.php` sin iniciar sesión | Redirige a `login.php` |OK |
| P04 | Normal | Crear producto | TEC-100, "Parlante", $9.990, cat. Audio, stock 10, mínimo 3 | Aparece en el listado; en el historial hay una ENTRADA "Stock inicial" de 10 |OK |
| P05 | Error | Código duplicado (RN1) | Crear otro producto con código TEC-001 | Mensaje "El código … ya existe"; no se crea |OK |
| P06 | Límite | Precio negativo (RN2) | Precio = -5 | JS lo bloquea; si se evita JS, PHP lo rechaza |OK |
| P07 | Límite | Stock mínimo decimal | Stock mínimo = 2.5 | Mensaje "número entero" |OK |
| P08 | Normal | Buscar por código | Buscar "TEC-100" | Solo se muestra el parlante | OK|
| P09 | Normal | Buscar por categoría | Buscar "Audio" | Solo productos de Audio |OK |
| P10 | Límite | Búsqueda sin resultados | Buscar "zzz" | Mensaje "No se encontraron productos…" |OK |
| P11 | Normal | Editar producto | Cambiar precio de TEC-100 a 42.000 | Se guarda y se ve en el listado; el código y el stock no cambian |OK |
| P12 | Normal | Entrada de stock | TEC-001, ENTRADA 5 | Stock pasa de 0 a 5; desaparece la alerta de TEC-001 |OK |
| P13 | Normal | Salida válida | TEC-001, SALIDA 5 | Stock pasa de 30 a 5 |OK |
| P14 | Error | Salida mayor al stock (RN4) | TEC-004 (stock 0), SALIDA 1 | Mensaje "Stock insuficiente"; stock no cambia; no se crea movimiento |OK |
| P15 | Error | Cantidad 0 o negativa | Cantidad = 0 y luego -3 | Rechazada por JS y por PHP |OK |
| P16 | Normal | Alerta de stock bajo (RF8) | Ver listado inicial | Banner y insignia en TEC-002 y TEC-004 (stock < mínimo) |OK |
| P17 | Normal | Desactivar producto (RN5) | Desactivar TEC-001 | Queda "Desactivado"; su historial se conserva; ya no aparece en Movimientos |OK |
| P18 | Normal | Reactivar producto | Activar TEC-005 | Vuelve a estado Activo |OK |
| P19 | Error | Movimiento sobre producto desactivado | Enviar POST con id de producto inactivo | Rechazado: "producto desactivado" |OK |



## 6. Estructura

```
tecnostock/
├── index.php  login.php  procesar_login.php  logout.php  conexion.php
├── assets/css/estilos.css   assets/js/validaciones.js
├── includes/  funciones.php  header.php  footer.php
├── categorias/  crear.php
├── productos/   crear.php  guardar.php  editar.php  activar.php  desactivar.php
├── movimientos/ registrar.php  historial.php
└── database/tecnostock.sql
```
