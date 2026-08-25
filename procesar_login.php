<?php

// 1. Iniciar el motor de sesiones
session_start();

// 2. Incluir la conexión a la base de datos
require_once 'conexion.php';

// 3. Validar que la información venga del formulario POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Obtener los datos enviados
    $user = trim($_POST['usuario']);
    $password = trim($_POST['password']);

    try {

        // 4. Consulta segura con marcador de posición
        $sql = "SELECT id, nombre_completo, password, rol 
                FROM usuarios 
                WHERE usuario = ?";

        // 5. Preparar la consulta
        $stmt = $conn->prepare($sql);

        // 6. Vincular el usuario
        $stmt->bind_param("s", $user);

        // 7. Ejecutar la consulta
        $stmt->execute();

        // Obtener el resultado
        $result = $stmt->get_result();

        // 8. Verificar si existe el usuario
        if ($result->num_rows === 1) {

            // Obtener los datos del usuario
            $row = $result->fetch_assoc();

            // 9. Verificar la contraseña
            if ($password === $row['password']) {

                // Guardar los datos en la sesión
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['nombre'] = $row['nombre_completo'];
                $_SESSION['rol'] = $row['rol'];

                // Redirigir al Dashboard oficial
                header("Location: dashboard.php");
                exit();

            } else {

                // Contraseña incorrecta
                header("Location: index.php?error=1");
                exit();
            }

        } else {

            // Usuario no encontrado
            header("Location: index.php?error=1");
            exit();
        }

        // Cerrar la consulta
        $stmt->close();

    } catch (mysqli_sql_exception $e) {

        // Mostrar error si ocurre un problema con MySQL
        die("Error de autenticación en el servidor: " . $e->getMessage());
    }

} else {

    // Si entran directamente al archivo, regresar al login
    header("Location: index.php");
    exit();
}

?>