<?php
header("Content-Type: application/json");
require_once '../cn.php';
require_once 'fnmora.php'; // Aquí está definida la clase Reportes

// Crear instancia del servicio de reportes
$reporteService = new Reportes($base_de_datos);

// Obtener el método HTTP
$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case 'GET':
        // Obtener parámetros de la URL y saneamiento
        $id_prestamo = isset($_GET['id_prestamo']) ? trim($_GET['id_prestamo']) : null;
        $codigoCarterafiltro = isset($_GET['codigoCarterafiltro']) ? trim($_GET['codigoCarterafiltro']) : null;
        $cartera = $_SESSION["carterausuario"] ?? null;

        try {
            // Ejecutar reporte

            $perfilUsuario = $_SESSION["perfilusuario"] ?? 'Usuario';

            // Validar id_prestamo si se proporciona
            $id_prestamo_int = null;
            if ($id_prestamo !== null && $id_prestamo !== '') {
                if (!ctype_digit($id_prestamo) || strlen($id_prestamo) > 10) {
                    http_response_code(400);
                    echo json_encode(["error" => "id_prestamo inválido."]);
                    exit;
                }
                $id_prestamo_int = (int)$id_prestamo;
            }

            // Validar codigoCarterafiltro si se proporciona
            $codigoCarterafiltro_int = null;
            if ($codigoCarterafiltro !== null && $codigoCarterafiltro !== '') {
                if (!ctype_digit($codigoCarterafiltro) || strlen($codigoCarterafiltro) > 10) {
                    http_response_code(400);
                    echo json_encode(["error" => "codigoCarterafiltro inválido."]);
                    exit;
                }
                $codigoCarterafiltro_int = (int)$codigoCarterafiltro;
            }

            $resultado = $reporteService->ReportePrestamoMora($id_prestamo_int, $cartera, $codigoCarterafiltro_int, $perfilUsuario);

            if ($resultado) {
                echo json_encode($resultado);
            } else {
                http_response_code(204); // Sin contenido
                echo json_encode(["message" => "No se encontraron registros."]);
            }
        } catch (Exception $e) {
            http_response_code(500); // Error interno del servidor
            error_log('rptmora_service error: ' . $e->getMessage());
            echo json_encode(["error" => "Ocurrió un error interno en el servidor."]);
            exit;
        }
        break;

    case 'POST':
    case 'PUT':
    case 'DELETE':
        http_response_code(405); // Método no permitido
        echo json_encode(["message" => "Método no permitido para este recurso."]);
        break;

    default:
        http_response_code(405); // Método no permitido
        echo json_encode(["message" => "Método HTTP no soportado."]);
        break;
}
?>
