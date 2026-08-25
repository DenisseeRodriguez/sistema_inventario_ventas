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
    $usuario_id = $_SESSION['user_id'];
    $proveedor_id = $_POST['proveedor_id'];
    $producto_id = $_POST['producto_id'];
    $cantidad = $_POST['cantidad'];
    $precio_compra = $_POST['precio_compra'];

    // 6. Calcular el total de la compra
    $total_compra = $cantidad * $precio_compra;

    try {

        // ==========================================
        // FASE 1: INSERTAR MAESTRO
        // ==========================================

        $sql_compras = "INSERT INTO compras
                        (proveedor_id, usuario_id, total)
                        VALUES (?, ?, ?)";

        $stmt1 = $conn->prepare($sql_compras);

        // proveedor_id = entero
        // usuario_id = entero
        // total = decimal
        $stmt1->bind_param(
            "iid",
            $proveedor_id,
            $usuario_id,
            $total_compra
        );

        // Ejecutar el INSERT de la cabecera
        $stmt1->execute();

        // ==========================================
        // CAPTURAR EL ID DE LA NUEVA COMPRA
        // ==========================================

        $id_nueva_compra = $conn->insert_id;

        $stmt1->close();


        // ==========================================
        // FASE 2: INSERTAR DETALLE
        // ==========================================

        $sql_detalle = "INSERT INTO detalle_compras
                        (compra_id, producto_id, cantidad, precio_compra)
                        VALUES (?, ?, ?, ?)";

        $stmt2 = $conn->prepare($sql_detalle);

        // compra_id = entero
        // producto_id = entero
        // cantidad = entero
        // precio_compra = decimal
        $stmt2->bind_param(
            "iiid",
            $id_nueva_compra,
            $producto_id,
            $cantidad,
            $precio_compra
        );

        // Ejecutar el INSERT del detalle
        $stmt2->execute();

        $stmt2->close();


        // ==========================================
        // REDIRECCIÓN
        // ==========================================

        header("Location: dashboard.php");
        exit();

    } catch (mysqli_sql_exception $e) {

        die(
            "Error crítico en la transacción Maestro-Detalle: "
            . $e->getMessage()
        );
    }

} else {

    // Si entran directamente por la URL
    header("Location: dashboard.php");
    exit();
}

?>