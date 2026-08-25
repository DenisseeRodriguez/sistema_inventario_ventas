<?php

// 1. Iniciar sesión
session_start();

// 2. Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// 3. Incluir la conexión a la base de datos
require_once 'conexion.php';

// 4. Verificar que los datos lleguen por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 5. Capturar y limpiar los datos
    $empresa = trim($_POST['empresa']);
    $contacto = trim($_POST['contacto']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);

    try {

        // 6. Preparar la consulta SQL
        $sql = "INSERT INTO proveedores 
                (nombre_empresa, contacto, telefono, direccion)
                VALUES (?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        // 7. Los cuatro campos son texto
        $stmt->bind_param(
            "ssss",
            $empresa,
            $contacto,
            $telefono,
            $direccion
        );

        // 8. Ejecutar la inserción
        $stmt->execute();

        // 9. Cerrar la sentencia
        $stmt->close();

        // 10. Regresar al catálogo de proveedores
        header("Location: proveedores.php");
        exit();

    } catch (mysqli_sql_exception $e) {

        die("Error crítico al registrar el proveedor: " . $e->getMessage());
    }

} else {

    // Si entran directamente por la URL
    header("Location: proveedores.php");
    exit();
}

?>