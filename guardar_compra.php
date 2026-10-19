```php
<?php

// 1. Iniciar sesión
session_start();

// 2. Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// 3. Conexión a la base de datos
require_once 'conexion.php';

// 4. Verificar que los datos lleguen por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 5. Capturar los datos del formulario y de la sesión
    $usuario_id = (int) $_SESSION['user_id'];
    $proveedor_id = filter_input(INPUT_POST, 'proveedor_id', FILTER_VALIDATE_INT);
    $producto_id = filter_input(INPUT_POST, 'producto_id', FILTER_VALIDATE_INT);
    $cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);
    $precio_compra = filter_input(INPUT_POST, 'precio_compra', FILTER_VALIDATE_FLOAT);

    // 6. Validar los datos recibidos
    if (
        $proveedor_id === false || $proveedor_id === null || $proveedor_id <= 0 ||
        $producto_id === false || $producto_id === null || $producto_id <= 0 ||
        $cantidad === false || $cantidad === null || $cantidad <= 0 ||
        $precio_compra === false || $precio_compra === null || $precio_compra < 0
    ) {
        die("Error: verifica que el proveedor, producto, cantidad y precio sean válidos.");
    }

    // 7. Calcular el total de la compra
    $total_compra = $cantidad * $precio_compra;

    try {

        // Iniciar transacción
        $conn->begin_transaction();

        // ==========================================
        // FASE 1: INSERTAR MAESTRO
        // ==========================================

        $sql_compras = "INSERT INTO compras
                        (proveedor_id, usuario_id, total)
                        VALUES (?, ?, ?)";

        $stmt1 = $conn->prepare($sql_compras);

        $stmt1->bind_param(
            "iid",
            $proveedor_id,
            $usuario_id,
            $total_compra
        );

        $stmt1->execute();

        // Capturar el ID de la nueva compra
        $id_nueva_compra = $conn->insert_id;

        $stmt1->close();

        // ==========================================
        // FASE 2: INSERTAR DETALLE
        // ==========================================

        $sql_detalle = "INSERT INTO detalle_compras
                        (compra_id, producto_id, cantidad, precio_compra)
                        VALUES (?, ?, ?, ?)";

        $stmt2 = $conn->prepare($sql_detalle);

        $stmt2->bind_param(
            "iiid",
            $id_nueva_compra,
            $producto_id,
            $cantidad,
            $precio_compra
        );

        $stmt2->execute();
        $stmt2->close();

        // ==========================================
        // FASE 3: ACTUALIZAR EL INVENTARIO FÍSICO
        // ==========================================

        // Sumar la cantidad comprada al stock actual
        $sql_stock = "UPDATE productos
                      SET stock = stock + ?
                      WHERE id = ?";

        $stmt3 = $conn->prepare($sql_stock);

        $stmt3->bind_param(
            "ii",
            $cantidad,
            $producto_id
        );

        $stmt3->execute();

        // Verificar que el producto exista
        if ($stmt3->affected_rows !== 1) {
            throw new Exception(
                "No se pudo actualizar el inventario. Verifica el ID del producto."
            );
        }

        $stmt3->close();

        // ==========================================
        // CONFIRMAR LA TRANSACCIÓN
        // ==========================================

        $conn->commit();

        // Redirigir al dashboard
        header("Location: dashboard.php");
        exit();

    } catch (Throwable $e) {

        // Revertir los cambios si ocurre un error
        $conn->rollback();

        error_log("Error al registrar compra: " . $e->getMessage());

        die(
            "No se pudo registrar la compra. "
            . "No se guardaron los cambios. "
            . "Revisa los datos e inténtalo nuevamente."
        );
    }

} else {

    // Si entran directamente por la URL
    header("Location: dashboard.php");
    exit();
}

?>
```