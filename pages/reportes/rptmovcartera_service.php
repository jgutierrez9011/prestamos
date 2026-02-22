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
        $fechainicio = isset($_GET['fechainicio']) ? trim($_GET['fechainicio']) : null;
        $fechafin = isset($_GET['fechafin']) ? trim($_GET['fechafin']) : null;
        $cod_solicitud = isset($_GET['cod_solicitud']) ? trim($_GET['cod_solicitud']) : null;
        $codigoCarterafiltro = isset($_GET['codigoCarterafiltro']) ? trim($_GET['codigoCarterafiltro']) : null;
        $cartera = $_SESSION["carterausuario"] ?? null;

        try {
            // Usamos el perfil desde sesión o asumimos "Usuario"
            $perfilUsuario = $_SESSION["perfilusuario"] ?? 'Usuario';

            // Validar fechas si se proporcionan
            $fiObj = null;
            $ffObj = null;
            if ($fechainicio) {
                $fiObj = DateTime::createFromFormat('Y-m-d', $fechainicio);
                if (!$fiObj || $fiObj->format('Y-m-d') !== $fechainicio) {
                    http_response_code(400);
                    echo json_encode(["error" => "fechainicio inválida, formato YYYY-MM-DD esperado."]);
                    exit;
                }
            }
            if ($fechafin) {
                $ffObj = DateTime::createFromFormat('Y-m-d', $fechafin);
                if (!$ffObj || $ffObj->format('Y-m-d') !== $fechafin) {
                    http_response_code(400);
                    echo json_encode(["error" => "fechafin inválida, formato YYYY-MM-DD esperado."]);
                    exit;
                }
            }
            if ($fiObj && $ffObj && $fiObj > $ffObj) {
                http_response_code(400);
                echo json_encode(["error" => "fechainicio no puede ser posterior a fechafin."]);
                exit;
            }

            // Validar enteros opcionales
            $cod_solicitud_int = null;
            if ($cod_solicitud !== null && $cod_solicitud !== '') {
                if (!ctype_digit($cod_solicitud) || strlen($cod_solicitud) > 10) {
                    http_response_code(400);
                    echo json_encode(["error" => "cod_solicitud inválido."]);
                    exit;
                }
                $cod_solicitud_int = (int)$cod_solicitud;
            }
            $codigoCarterafiltro_int = null;
            if ($codigoCarterafiltro !== null && $codigoCarterafiltro !== '') {
                if (!ctype_digit($codigoCarterafiltro) || strlen($codigoCarterafiltro) > 10) {
                    http_response_code(400);
                    echo json_encode(["error" => "codigoCarterafiltro inválido."]);
                    exit;
                }
                $codigoCarterafiltro_int = (int)$codigoCarterafiltro;
            }

            $resultado = $reporteService->ReporteMovimientoPorCartera(
                $fechainicio,
                $fechafin,
                $cod_solicitud_int,
                $cartera, // codigoCartera no se usa desde vista
                $codigoCarterafiltro_int,
                $perfilUsuario
            );

            echo json_encode($resultado ?: []);
        } catch (Exception $e) {
            http_response_code(500);
            error_log('rptmovcartera_service error: ' . $e->getMessage());
            echo json_encode(["error" => "Ocurrió un error interno en el servidor."]);
        }
        break;

    case 'POST':
    case 'PUT':
    case 'DELETE':
        http_response_code(405); // Método no permitido
        echo json_encode(["message" => "Método no permitido para este recurso."]);
        break;

    default:
        http_response_code(405); // Método HTTP no soportado
        echo json_encode(["message" => "Método HTTP no soportado."]);
        break;
}
?>
