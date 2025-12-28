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
        $fechainicio = $_GET['fechainicio'] ?? null;
        $fechafin = $_GET['fechafin'] ?? null;
        $cod_solicitud = isset($_GET['cod_solicitud']) ? (int)$_GET['cod_solicitud'] : null;
        $codigoCarterafiltro = isset($_GET['codigoCarterafiltro']) ? (int)$_GET['codigoCarterafiltro'] : null;
        $cartera = $_SESSION["carterausuario"] ?? null;

        try {
            // Usamos el perfil desde sesión o asumimos "Usuario"
            $perfilUsuario = $_SESSION["perfilusuario"] ?? 'Usuario';

            $resultado = $reporteService->ReporteMovimientoPorCartera(
                $fechainicio,
                $fechafin,
                $cod_solicitud,
                $cartera, // codigoCartera no se usa desde vista
                $codigoCarterafiltro,
                $perfilUsuario
            );

            echo json_encode($resultado ?: []);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
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
