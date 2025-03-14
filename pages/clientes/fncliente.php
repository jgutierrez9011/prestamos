<?php
require_once '../cn.php';
header("Content-Type: application/json");


try {
    $pdo = $base_de_datos;
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión: " . $e->getMessage()]);
    exit;
}

$method = $_SERVER["REQUEST_METHOD"];

switch ($method) {
    case "GET":
        try {
            if (isset($_GET["idcliente"])) {
                
                // Buscar un solo cliente por ID
                $stmt = $pdo->prepare("SELECT * FROM clientes WHERE idcliente = :idcliente");
                $stmt->execute([":idcliente" => $_GET["idcliente"]]);
                $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($cliente) {
                    echo json_encode($cliente);
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "Cliente no encontrado"]);
                }
            } else {
                // Obtener todos los clientes
                $stmt = $pdo->query("SELECT * FROM clientes");
                $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($clientes);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error al obtener clientes 456: " . $e->getMessage()]);
        }
        break;

    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);
         // Validar si se está realizando una búsqueda o una inserción
         if (isset($data["cedula"]) && !isset($data["nombre"], $data["telefono"])) {
            // Búsqueda de cliente por cédula
            try {
                $stmt = $pdo->prepare("SELECT * FROM clientes WHERE cedula = :cedula");
                $stmt->execute([":cedula" => $data["cedula"]]);
                $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($cliente) {
                    http_response_code(200); // Cliente encontrado
                    echo json_encode(["cliente" => $cliente]);
                } else {
                    http_response_code(404); // Cliente no encontrado
                    echo json_encode(["error" => "Cliente no encontrado"]);
                }
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(["error" => "Error en la búsqueda: " . $e->getMessage()]);
            }
        } elseif (isset($data["cedula"], $data["nombre"], $data["telefono"])) {
            // Inserción de un nuevo cliente
            try {
                $stmt = $pdo->prepare("INSERT INTO clientes (cedula, nombre, telefono, estado_civil, actividad_economica, direccion_domicilio, tipo_vivienda, anos_habitar, direccion_negocio, tipo_local, tiempo_operar, rubro, idcartera, fecha_creo, usuario_creo) 
                                      VALUES (:cedula, :nombre, :telefono, :estado_civil, :actividad_economica, :direccion_domicilio, :tipo_vivienda, :anos_habitar, :direccion_negocio, :tipo_local, :tiempo_operar, :rubro, :idcartera, current_timestamp, :usuario_creo)");

                // Establecer la zona horaria de Nicaragua
                date_default_timezone_set('America/Managua');

                $stmt->execute([
                    ":cedula" => $data["cedula"],
                    ":nombre" => $data["nombre"],
                    ":telefono" => $data["telefono"],
                    ":estado_civil" => $data["estado_civil"] ?? null,
                    ":actividad_economica" => $data["actividad_economica"] ?? null,
                    ":direccion_domicilio" => $data["direccion_domicilio"] ?? null,
                    ":tipo_vivienda" => $data["tipo_vivienda"] ?? null,
                    ":anos_habitar" => $data["anos_habitar"] ?? null,
                    ":direccion_negocio" => $data["direccion_negocio"] ?? null,
                    ":tipo_local" => $data["tipo_local"] ?? null,
                    ":tiempo_operar" => $data["tiempo_operar"] ?? null,
                    ":rubro" => $data["rubro"] ?? null,
                    ":idcartera" => $_SESSION["carterausuario"] ?? null,
                    ":usuario_creo" => $_SESSION["idusuario"] ?? null
                ]);

                http_response_code(201); // Cliente registrado correctamente
                echo json_encode(["message" => "Cliente registrado correctamente"]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(["error" => "Error al insertar cliente: " . $e->getMessage()]);
            }
        } else {
            http_response_code(400); // Datos incompletos
            echo json_encode(["error" => "Datos incompletos"]);
        }
        break;

    case "PUT":
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data["cedula"])) {
            http_response_code(response_code: 400);
            echo json_encode(["error" => "Cédula no proporcionada"]);
            exit;
        }
        try {
            $stmt = $pdo->prepare("UPDATE clientes SET nombre = :nombre, cedula =:cedula, telefono = :telefono, estado_civil = :estado_civil, actividad_economica = :actividad_economica, direccion_domicilio = :direccion_domicilio, tipo_vivienda = :tipo_vivienda, anos_habitar = :anos_habitar, direccion_negocio = :direccion_negocio, tipo_local = :tipo_local, tiempo_operar = :tiempo_operar, rubro = :rubro, usuario_modifico = :usuario_modifico, fecha_modifico = current_timestamp WHERE idcliente = :idcliente");
            
            $stmt->execute([
                ":idcliente" => $data["idcliente"],
                ":cedula" => $data["cedula"],
                ":nombre" => $data["nombre"] ?? null,
                ":telefono" => $data["telefono"] ?? null,
                ":estado_civil" => $data["estado_civil"] ?? null,
                ":actividad_economica" => $data["actividad_economica"] ?? null,
                ":direccion_domicilio" => $data["direccion_domicilio"] ?? null,
                ":tipo_vivienda" => $data["tipo_vivienda"] ?? null,
                ":anos_habitar" => $data["anos_habitar"] ?? null,
                ":direccion_negocio" => $data["direccion_negocio"] ?? null,
                ":tipo_local" => $data["tipo_local"] ?? null,
                ":tiempo_operar" => $data["tiempo_operar"] ?? null,
                ":rubro" => $data["rubro"] ?? null,
                ":usuario_modifico" => $_SESSION["idusuario"] ?? null
            ]);
            echo json_encode(["message" => "Cliente actualizado correctamente"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error al actualizar cliente: " . $e->getMessage()]);
            //echo json_encode(["error" => "Error al actualizar cliente: " . $e->getMessage(), "query" => $stmt->queryString, "params" => $data]);
        }
        break;

    case "DELETE":
        if (!isset($_GET["cedula"])) {
            http_response_code(400);
            echo json_encode(["error" => "Cédula no proporcionada"]);
            exit;
        }
        try {
            $stmt = $pdo->prepare("DELETE FROM clientes WHERE cedula = :cedula");
            $stmt->execute([":cedula" => $_GET["cedula"]]);
            echo json_encode(["message" => "Cliente eliminado correctamente"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error al eliminar cliente: " . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
        break;
}
?>