<?php
header('Content-Type: application/json');
require_once 'fncalendariopago.php';
require_once '../cn.php';

try {
    // Configuración de la conexión PDO
    $pdo = $base_de_datos;
    // Instanciar la clase Abono
    $calendarioPago = new CalendarioPago($pdo);

    // Obtener método de la solicitud
    $method = $_SERVER['REQUEST_METHOD'];
    //$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
    $input = json_decode(file_get_contents('php://input'), true);

    switch ($method) {
        case 'GET':
                if (isset($_GET['id_solicitud'])) {
                    // GET /servicio_calendariopago/prestamo/{id_prestamo}
                    $response = $calendarioPago->obtenerPorPrestamo($_GET['id_solicitud']);
                } elseif(isset($_GET['id_pago'])) {
                    // GET /servicio_calendariopago/{id_pago}
                    $response = $calendarioPago->obtener($_GET['id_pago']);
                }else{
                    throw new Exception("Parámetros inválidos");
                }
            
            break;

        case 'POST':
            // POST /servicio_calendariopago
            if (!empty($input)) {
                $required = [
                    'id_prestamo' => 'integer',
                    'fecha_pago' => 'string',
                    'monto_cuota' => 'numeric',
                    'interes' => 'numeric',
                    'principal' => 'numeric'
                ];
                
                $errors = [];
                
                foreach ($required as $field => $type) {
                    if (!isset($input[$field])) {
                        $errors[] = "El campo $field es requerido";
                        continue;
                    }
                    
                    switch ($type) {
                        case 'integer':
                            if (!is_numeric($input[$field])) $errors[] = "$field debe ser un número entero";
                            break;
                        case 'numeric':
                            if (!is_numeric($input[$field])) $errors[] = "$field debe ser un número";
                            break;
                        case 'string':
                            if (!is_string($input[$field])) $errors[] = "$field debe ser texto";
                            break;
                    }
                }
                
                if (empty($errors)) {
                    $response = $calendarioPago->crear(
                        (int)$input['id_prestamo'],
                        $input['fecha_pago'],
                        (float)$input['monto_cuota'],
                        (float)$input['interes'],
                        (float)$input['principal'],
                        $input['estado'] ?? 'Pendiente'
                    );
                } else {
                    throw new Exception("Errores de validación: " . implode(', ', $errors));
                }
            } else {
                throw new Exception("Datos no proporcionados");
            }
            break;

        case 'PUT':
            // PUT /servicio_calendariopago/{id_pago}
            if (isset($request[0]) && !empty($input)) {
                $response = $calendarioPago->actualizar($request[0], $input);
            } else {
                throw new Exception("Datos no proporcionados");
            }
            break;

        case 'DELETE':
            // DELETE /servicio_calendariopago/{id_pago}
            if (isset($request[0])) {
                $response = $calendarioPago->eliminar($request[0]);
            } else {
                throw new Exception("ID no proporcionado");
            }
            break;

        case 'PATCH':
            // PATCH /servicio_calendariopago/{id_pago}/estado
            if (isset($request[0]) && isset($request[1]) && $request[1] == 'estado' && isset($input['estado'])) {
                $response = $calendarioPago->cambiarEstado($request[0], $input['estado']);
            } else {
                throw new Exception("Datos incompletos para cambiar estado");
            }
            break;

        default:
            throw new Exception("Método no soportado");
            break;
    }

    echo json_encode(['success' => true, 'data' => $response]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => "Error en la base de datos: " . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>