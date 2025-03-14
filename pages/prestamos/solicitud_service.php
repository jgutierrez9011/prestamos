<?php
require_once '../cn.php';
require_once 'fnclasificacion.php';

class SolicitudPrestamo {
    private $base_de_datos;

    public function __construct($pdo) {
        $this->base_de_datos = $pdo;
    }

    public function getSolicitud($id_solicitud) {
        try {
            $stmt = $this->base_de_datos->prepare("
                SELECT 
                    a.id_solicitud, 
                    a.cod_solicitud, 
                    b.nombre, 
                    b.cedula,
                    b.estado_civil, 
                    b.tipo_vivienda, 
                    b.anos_habitar, 
                    b.direccion_negocio,
                    a.telefono,
                    b.direccion_domicilio,
                    a.actividad_economica, 
                    a.rubro, 
                    a.tipo_local,
                    a.tiempo_operar, 
                    a.direccion_negocio,
                    a.fecha_creo AS fecha_solicitud, 
                    a.monto_solicitado, 
                    c.nombre AS estatus, 
                    a.plazo_solicitado, 
                    a.tasa, 
                    CONCAT(d.strpnombre, ' ', d.strsnombre, ' ', d.strpapellido, ' ', d.strsapellido) AS oficial_credito,
                    a.venta_promedio_bueno, 
                    a.venta_promedio_mediano, 
                    a.venta_promedio_bajo,
                    a.promedio_venta, 
                    a.tipo_promedio, 
                    a.ventas_mensuales,
                    a.otros_ingresos_negocio, 
                    a.aportes_familiares, 
                    a.otros_ingresos,
                    a.gasto_costo_venta, 
                    a.gastos_negocio, 
                    a.cuotas_credito,
                    a.gastos_familiares, 
                    a.utilidad_final
                FROM 
                    SolicitudPrestamo a 
                LEFT JOIN 
                    clientes b ON a.idcliente = b.idcliente
                LEFT JOIN 
                    estatus_solicitud c ON a.idestatus = c.idestatus
                LEFT JOIN 
                    tblcatusuario d ON a.usuario_creo = d.intid 
                WHERE 
                    id_solicitud = ?
            ");
            
            $stmt->execute([$id_solicitud]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($resultado === false) {
                throw new Exception("No se encontró la solicitud con el ID proporcionado.");
            }
    
            return $resultado;
    
        } catch (PDOException $e) {
            // Captura errores específicos de PDO
            http_response_code(500);
            echo json_encode(["error" => "Error en la base de datos: " . $e->getMessage()]);
        } catch (Exception $e) {
            // Captura cualquier otra excepción
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    public function getAllSolicitudes() {
        try{
            $stmt = $this->base_de_datos->query("SELECT a.id_solicitud, a.cod_solicitud, b.nombre, a.fecha_creo fecha_solicitud, a.monto_solicitado, 
                                c.nombre estatus , a.plazo_solicitado, a.tasa, 
                                concat(d.strpnombre,' ',d.strsnombre,' ',d.strpapellido,' ',d.strsapellido) oficial_credito
                                from SolicitudPrestamo a 
                                left join clientes b on a.idcliente = b.idcliente
                                left join estatus_solicitud c on a.idestatus = c.idestatus
                                left join tblcatusuario d on a.usuario_creo = d.intid");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

        }catch(PDOException $e) {
             // Captura errores específicos de PDO
             http_response_code(500);
             echo json_encode(["error" => "Error en la base de datos: " . $e->getMessage()]);

        }catch (Exception $e) {
            // Captura cualquier otra excepción
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
        
    }

    public function createSolicitud($data) {
        try{

            $stmt = $this->base_de_datos->prepare("
            INSERT INTO SolicitudPrestamo (
                cod_solicitud, idcliente, actividad_economica, direccion_negocio, telefono, tipo_local,
                tiempo_operar, rubro, monto_solicitado, plazo_solicitado, tasa,
                venta_promedio_bueno, venta_promedio_mediano, venta_promedio_bajo,
                promedio_venta, ventas_mensuales, otros_ingresos_negocio, aportes_familiares,
                otros_ingresos, gasto_costo_venta, gastos_negocio, cuotas_credito,
                gastos_familiares, utilidad_final, tipo_promedio, idcartera, idestatus,
                fecha_creo, usuario_creo, tipo_cliente
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, current_timestamp, ?, ?
            )
        ");

        $tipo_cliente = cliente_existe($data['idcliente']);
        $recurrente = ($tipo_cliente > 0) ? 'Recurrente' : 'Nuevo';
        $numero_solicitud = seq_solicitud_credito();

        $stmt->execute([
            $numero_solicitud, $data['idcliente'], $data['actividad_economica'], $data['direccion_negocio'],
            $data['telefono'], $data['tipo_local'], $data['tiempo_operar'], $data['rubro'],
            $data['monto_solicitado'], $data['plazo_solicitado'], $data['tasa'],
            $data['venta_promedio_bueno'], $data['venta_promedio_mediano'], $data['venta_promedio_bajo'],
            $data['promedio_venta'], $data['ventas_mensuales'], $data['otros_ingresos_negocio'],
            $data['aportes_familiares'], $data['otros_ingresos'], $data['gasto_costo_venta'],
            $data['gastos_negocio'], $data['cuotas_credito'], $data['gastos_familiares'],
            $data['utilidad_final'], $data['tipo_promedio'], $_SESSION["carterausuario"], 1,
            $_SESSION["idusuario"], $recurrente
        ]);

        return ["message" => "Solicitud creada exitosamente"];
        }catch(PDOException $e) {
           // Captura errores específicos de PDO
           http_response_code(500);
           echo json_encode(["error" => "Error en la base de datos: " . $e->getMessage()]);
        }catch (Exception $e) {
            // Captura cualquier otra excepción
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    public function updateSolicitud($data) {
        try{

            $stmt = $this->base_de_datos->prepare("
            UPDATE SolicitudPrestamo SET
                idcliente = ?, actividad_economica = ?, direccion_negocio = ?, telefono = ?,
                tipo_local = ?, tiempo_operar = ?, rubro = ?, monto_solicitado = ?,
                plazo_solicitado = ?, tasa = ?, venta_promedio_bueno = ?, venta_promedio_mediano = ?,
                venta_promedio_bajo = ?, promedio_venta = ?, ventas_mensuales = ?,
                otros_ingresos_negocio = ?, aportes_familiares = ?, otros_ingresos = ?,
                gasto_costo_venta = ?, gastos_negocio = ?, cuotas_credito = ?,
                gastos_familiares = ?, utilidad_final = ?, tipo_promedio = ?, idcartera = ?,
                idestatus = ?, fecha_modifico = ?, usuario_modifico = ?
            WHERE id_solicitud = ?
        ");
        $stmt->execute([
            $data['idcliente'], $data['actividad_economica'], $data['direccion_negocio'],
            $data['telefono'], $data['tipo_local'], $data['tiempo_operar'], $data['rubro'],
            $data['monto_solicitado'], $data['plazo_solicitado'], $data['tasa'],
            $data['venta_promedio_bueno'], $data['venta_promedio_mediano'], $data['venta_promedio_bajo'],
            $data['promedio_venta'], $data['ventas_mensuales'], $data['otros_ingresos_negocio'],
            $data['aportes_familiares'], $data['otros_ingresos'], $data['gasto_costo_venta'],
            $data['gastos_negocio'], $data['cuotas_credito'], $data['gastos_familiares'],
            $data['utilidad_final'], $data['tipo_promedio'], $data['idcartera'], $data['idestatus'],
            $data['fecha_modifico'], $data['usuario_modifico'], $data['id_solicitud']
        ]);

        return ["message" => "Solicitud actualizada exitosamente"];

        }catch(PDOException $e) {
            // Captura errores específicos de PDO
            http_response_code(500);
            echo json_encode(["error" => "Error en la base de datos: " . $e->getMessage()]);
         }catch (Exception $e) {
             // Captura cualquier otra excepción
             http_response_code(500);
             echo json_encode(["error" => $e->getMessage()]);
         }
    }

    public function updateSolicitudEstado($usuario, $estatus, $codsolicitud) {
        try {
            // Validar que el estado sea permitido
            /*$estatusPermitidos = [1, 2]; // Estados válidos para la actualización
            if (!in_array($estatus, $estatusPermitidos)) {
                return ["error" => "Estado no permitido"];
            }*/
    
            // Preparar la consulta
            $stmt = $this->base_de_datos->prepare("
                UPDATE SolicitudPrestamo SET
                    idestatus = ?, 
                    fecha_modifico = current_timestamp, 
                    usuario_modifico = ?
                WHERE id_solicitud = ? AND idestatus NOT IN (3,4,5,6,7)
            ");
            
            $stmt->execute([$estatus, $usuario, $codsolicitud]);
    
            // Verificar si se actualizó alguna fila
            if ($stmt->rowCount() > 0) {
                return ["message" => "Estatus de solicitud actualizado exitosamente"];
            } else {
                return ["error" => "No se pudo actualizar el estado. La solicitud no existe o ya está en un estado no modificable."];
            }
    
        } catch (PDOException $e) {
            return ["error" => "Error en la base de datos: " . $e->getMessage()];
        } catch (Exception $e) {
            return ["error" => $e->getMessage()];
        }
    }
    
}
?>