```php
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'conexion.php';

// Consultar el historial de compras
$sql = "SELECT
            c.id AS numero_factura,
            c.fecha,
            p.nombre_empresa AS proveedor,
            u.nombre_completo AS cajero,
            c.total
        FROM compras c
        INNER JOIN proveedores p ON c.proveedor_id = p.id
        INNER JOIN usuarios u ON c.usuario_id = u.id
        ORDER BY c.id DESC";

$resultado = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Compras</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 20px;
        }

        .contenedor {
            background: #ffffff;
            padding: 25px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        .encabezado {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 10px;
        }

        .btn-regresar {
            background: #64748b;
            color: #ffffff;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #cbd5e1;
            text-align: left;
        }

        th {
            background-color: #1e293b;
            color: #ffffff;
        }

        .monto {
            color: #059669;
            font-weight: bold;
        }

        .btn-detalle {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 13px;
            font-weight: bold;
        }

        .btn-detalle:hover {
            background: #1d4ed8;
        }

        @media (max-width: 700px) {
            .encabezado {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
<div class="contenedor">

    <div class="encabezado">
        <h2>Historial de Ingresos de Mercadería</h2>
        <a href="dashboard.php" class="btn-regresar">
            Regresar al Panel
        </a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Número</th>
                <th>Fecha y Hora</th>
                <th>Proveedor</th>
                <th>Usuario Responsable</th>
                <th>Total Invertido</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
        <?php if ($resultado && $resultado->num_rows > 0): ?>

            <?php while ($fila = $resultado->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php echo (int) $fila['numero_factura']; ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $fila['fecha'],
                            ENT_QUOTES,
                            'UTF-8'
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $fila['proveedor'],
                            ENT_QUOTES,
                            'UTF-8'
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $fila['cajero'],
                            ENT_QUOTES,
                            'UTF-8'
                        );
                        ?>
                    </td>

                    <td class="monto">
                        $<?php echo number_format((float) $fila['total'], 2); ?>
                    </td>

                    <td>
                        <a
                            href="detalle_compra.php?id=<?php echo (int) $fila['numero_factura']; ?>"
                            class="btn-detalle"
                        >
                            Ver Detalle
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>

        <?php else: ?>
            <tr>
                <td colspan="6">
                    No hay registros de compras disponibles.
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

</div>
</body>
</html>

                