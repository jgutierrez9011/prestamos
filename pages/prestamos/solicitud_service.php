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
                    a.utilidad_final,
					e.id_prestamo,
                    coalesce(a.costo_unitario,0.00) costo_unitario,
                    coalesce(a.precio_venta,0.00) precio_venta,
                    coalesce(a.unidades_producidas,0.00) unidades_producidas,
                    a.total_ingreso,
                    a.total_gasto
                FROM 
                    SolicitudPrestamo a 
                LEFT JOIN 
                    clientes b ON a.idcliente = b.idcliente
                LEFT JOIN 
                    estatus_solicitud c ON a.idestatus = c.idestatus
                LEFT JOIN 
                    tblcatusuario d ON a.usuario_creo = d.intid
				LEFT JOIN
				     prestamo e on a.id_solicitud = e.id_solicitud
                WHERE 
                    a.cod_solicitud = ?
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

    public function getAllSolicitudes($rol_usuario, $idcartera) {
        try {
            $sql = "SELECT a.id_solicitud, a.cod_solicitud, b.nombre, a.fecha_creo fecha_solicitud, a.monto_solicitado, 
                                c.nombre estatus , a.plazo_solicitado, a.tasa, 
                                concat(d.strpnombre,' ',d.strsnombre,' ',d.strpapellido,' ',d.strsapellido) oficial_credito,
								 e.id_prestamo, f.idcartera, f.descripcion
                                from SolicitudPrestamo a 
                                left join clientes b on a.idcliente = b.idcliente
                                left join estatus_solicitud c on a.idestatus = c.idestatus
                                left join tblcatusuario d on a.usuario_creo = d.intid
								 left join prestamo e on a.id_solicitud = e.id_solicitud
								 left join tblcatcartera f on a.idcartera = f.idcartera";
            
            // Añadir condición WHERE según el rol
            if ($rol_usuario == 'Administrador') {
                // Administrador ve todas las solicitudes
                $stmt = $this->base_de_datos->query($sql);
            } else {
                // Otros roles filtran por idcartera (asumiendo que existe a.idcartera)
                $sql .= " WHERE a.idcartera = :idcartera";
                $stmt = $this->base_de_datos->prepare($sql);
                $stmt->bindParam(':idcartera', $idcartera, PDO::PARAM_INT);
                $stmt->execute();
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error en la base de datos: " . $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    public function createSolicitud($data) {
        try{

            $tipo_cliente = cliente_existe($data['idcliente']);
            $recurrente = ($tipo_cliente > 0) ? 'Recurrente' : 'Nuevo';
            $numero_solicitud = seq_solicitud_credito() + 1;
            
            
            if($data['rubro']==='comercio' || $data['rubro']==='servicio'){

                $stmt = $this->base_de_datos->prepare("
                        INSERT INTO SolicitudPrestamo (
                            cod_solicitud, idcliente, actividad_economica, direccion_negocio, telefono, tipo_local,
                            tiempo_operar, rubro, monto_solicitado, plazo_solicitado, tasa,
                            venta_promedio_bueno, venta_promedio_mediano, venta_promedio_bajo,
                            promedio_venta, ventas_mensuales, otros_ingresos_negocio, aportes_familiares,
                            otros_ingresos, gasto_costo_venta, gastos_negocio, cuotas_credito,
                            gastos_familiares, utilidad_final, tipo_promedio, idcartera, idestatus,
                            fecha_creo, usuario_creo, tipo_cliente, total_ingreso, total_gasto
                        ) VALUES (
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, current_timestamp, ?, ?, ?, ?
                        )
                    ");

                    
                    $stmt->execute([
                        $numero_solicitud, $data['idcliente'], $data['actividad_economica'], $data['direccion_negocio'],
                        $data['telefono'], $data['tipo_local'], $data['tiempo_operar'], $data['rubro'],
                        $data['monto_solicitado'], $data['plazo_solicitado'], $data['tasa'],
                        $data['venta_promedio_bueno'], $data['venta_promedio_mediano'], $data['venta_promedio_bajo'],
                        $data['promedio_venta'], $data['ventas_mensuales'], $data['otros_ingresos_negocio'],
                        $data['aportes_familiares'], $data['otros_ingresos'], $data['gasto_costo_venta'],
                        $data['gastos_negocio'], $data['cuotas_credito'], $data['gastos_familiares'],
                        $data['utilidad_final'], $data['tipo_promedio'], $_SESSION["carterausuario"], 1,
                        $_SESSION["idusuario"], $recurrente, $data['total_ingresos'], $data['total_gastos']
                    ]);

            } else {

                $stmt = $this->base_de_datos->prepare("
                        INSERT INTO SolicitudPrestamo (
                            cod_solicitud, idcliente, actividad_economica, direccion_negocio, telefono, tipo_local,
                            tiempo_operar, rubro, monto_solicitado, plazo_solicitado, tasa,
                            venta_promedio_bueno, venta_promedio_mediano, venta_promedio_bajo,
                            promedio_venta, ventas_mensuales, otros_ingresos_negocio, aportes_familiares,
                            otros_ingresos, gasto_costo_venta, gastos_negocio, cuotas_credito,
                            gastos_familiares, utilidad_final, tipo_promedio, idcartera, idestatus,
                            fecha_creo, usuario_creo, tipo_cliente, total_ingreso, total_gasto,costo_unitario,precio_venta,unidades_producidas
                        ) VALUES (
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, current_timestamp, ?, ?, ?, ?,?,?,?
                        )
                    ");

                    
                    $stmt->execute([
                        $numero_solicitud, $data['idcliente'], $data['actividad_economica'], $data['direccion_negocio'],
                        $data['telefono'], $data['tipo_local'], $data['tiempo_operar'], $data['rubro'],
                        $data['monto_solicitado'], $data['plazo_solicitado'], $data['tasa'],
                        $data['venta_promedio_bueno'], $data['venta_promedio_mediano'], $data['venta_promedio_bajo'],
                        $data['promedio_venta'], $data['ventas_mensuales'], $data['otros_ingresos_negocio'],
                        $data['aportes_familiares'], $data['otros_ingresos'], $data['gasto_costo_venta'],
                        $data['gastos_negocio'], $data['cuotas_credito'], $data['gastos_familiares'],
                        $data['utilidad_final'], $data['tipo_promedio'], $_SESSION["carterausuario"], 1,
                        $_SESSION["idusuario"], $recurrente, $data['total_ingresos'], $data['total_gastos'],$data['costo_unitario'],$data['precio_venta'],$data['unidad_producida']
                    ]);

            }
            

        return ["message" => "Solicitud de credito registrada exitosamente."];

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
            
            // Preparar la consulta
            $stmt = $this->base_de_datos->prepare("
                UPDATE SolicitudPrestamo SET
                    idestatus = ?, 
                    fecha_modifico = current_timestamp, 
                    usuario_modifico = ?
                WHERE cod_solicitud = ?
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

    function calcularPromedioVentas($tipoPromedio, $ventaBuena, $ventaMedia, $ventaBaja) {
        $promedio = ($ventaBuena + $ventaMedia + $ventaBaja) / 3;
        
        if (strtolower($tipoPromedio) == 'diario') {
            $resultado = $promedio * 30;
        } elseif (strtolower($tipoPromedio) == 'semanal') {
            $resultado = $promedio * 4;
        } else {
            return ['error' => "Tipo de promedio no válido. Use 'diario' o 'semanal'."];
        }
        
        return ['venta_promedio' => round($resultado, 2)];
    }

    function validarMontoCartera($cod_cartera, $monto)
    {
            // Preparar la consulta para buscar los límites de la cartera
            $sql = "SELECT monto_minimo, monto_maximo FROM public.tblcatcartera WHERE idcartera = :idcartera AND estado = true LIMIT 1";
            $stmt = $this->base_de_datos->prepare($sql);
            $stmt->bindParam(':idcartera', $cod_cartera, PDO::PARAM_STR);
            $stmt->execute();

            $cartera = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cartera) {
                $montoMinimo = $cartera['monto_minimo'];
                $montoMaximo = $cartera['monto_maximo'];

                if ($monto < $montoMinimo) {
                    return ['mensaje' => 'El monto está por debajo del mínimo permitido.'];
                } elseif ($monto > $montoMaximo) {
                    return ['mensaje' => 'El monto está por encima del máximo permitido.'];
                } else {
                    return ['mensaje' => 'El monto está dentro del rango permitido.'];
                }
            } else {
                return ['mensaje' => 'Cartera no encontrada o inactiva.'];
            }
     }

     
     function calcularCostoVenta($rubro, $ventasMensuales, $costoUnitario = null, $precioVenta = null, $unidadesProducidas = null) {
        try {
            // Consultar configuración del rubro
            $sql = "SELECT margen_venta, tipo_calculo 
                    FROM configuracion_costo_venta 
                    WHERE rubro = :rubro AND activo = true
                    LIMIT 1";
            
            $stmt = $this->base_de_datos->prepare($sql);
            $stmt->bindParam(':rubro', $rubro);
            $stmt->execute();
            $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$config) {
                throw new Exception("No se encontró configuración activa para el rubro: $rubro");
            }
    
            $tipoCalculo = $config['tipo_calculo'];
            $margenVenta = $config['margen_venta'];
            $costoVenta = 0;
    
            // Cálculo basado en tipo
            if ($tipoCalculo === 'POR_MARGEN') {
                $costoVenta = (1 - ($margenVenta / 100)) * $ventasMensuales;
    
            } elseif ($tipoCalculo === 'COSTO_UNITARIO') {
                // Validar solo si se necesita
                if (!is_numeric($costoUnitario) || !is_numeric($precioVenta) || !is_numeric($unidadesProducidas) || $precioVenta == 0) {
                    throw new Exception("Se requieren costoUnitario, precioVenta y unidadesProducidas válidos para el rubro Producción.");
                }
    
                $totalCostoProduccion = $costoUnitario * $unidadesProducidas;
                $totalVentasProduccion = $precioVenta * $unidadesProducidas;
                $margenCostoVenta = $totalCostoProduccion / $totalVentasProduccion;
    
                $costoVenta = $ventasMensuales * $margenCostoVenta;
            } else {
                throw new Exception("Tipo de cálculo desconocido: $tipoCalculo");
            }
    
            return round($costoVenta, 2);
    
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
    
    
    
}
?>