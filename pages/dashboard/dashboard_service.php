<?php
header("Content-Type: application/json");
require_once '../cn.php';
require_once 'fndashboard.php';

try {
    $pdo = $base_de_datos;
    $dashboardBL = new Dashboard($pdo);
} catch (PDOException $e) {
    error_log('dashboard_service DB connection error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Ocurrió un error interno en el servidor."]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Podrías agregar filtros aquí si en el futuro deseas hacerlo (por fecha, cartera, etc.)
        $totalAbonado = $dashboardBL->getTotalAbonos();
        $saldoPendiente = $dashboardBL->getSaldoPendiente();
        $interesColacado = $dashboardBL->getInteresColocadoPendiente();

        echo json_encode([
            "total_abonado" => floatval($totalAbonado),
            "saldo_pendiente" => floatval($saldoPendiente),
            "interes_pendiente" => floatval($interesColacado)
        ]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Método no permitido"]);
        break;
}
?>
