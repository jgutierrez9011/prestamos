<?php
// fnreporte.php

class Reportes {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Generar reporte por préstamo y/o cartera, o todos si no se envían filtros
    public function ReportePrestamoMora($id_prestamo = null, $codigoCartera = null, $codigoCarterafiltro = null, $perfilUsuario = null) {
        try {
            $query = "SELECT 
                        b.descripcion AS cartera,
                        c.id_solicitud, 
                        c.cod_solicitud::int as cod_solicitud , 
                        f.id_prestamo,
                        h.nombre AS estatus,
                        a.idcliente AS numero_cliente, 
                        a.nombre AS cliente, 
                        a.direccion_domicilio, 
                        a.direccion_negocio, 
                        a.telefono,
                        I.vencimiento_prestamo,
                        ROUND((SUM(coalesce(g.dias_transcurridos_mora,0.0))/7.0), 2) AS dias_promedio,
						f.saldo,
                        coalesce(g.cuotas_vencidas,0) as cuotas_vencidas,
                        SUM(coalesce(g.monto_cuota,0.0)) AS saldo_mora,
                        SUM(coalesce(g.dias_transcurridos_mora,0.0)) AS dias_mora
                    FROM clientes a 
                    LEFT JOIN solicitudprestamo c ON a.idcliente = c.idcliente
					LEFT JOIN tblcatcartera b ON c.idcartera = b.idcartera
                    LEFT JOIN prestamo f ON c.id_solicitud = f.id_solicitud
                    LEFT JOIN (
                        SELECT
                            d.id_prestamo,
                            COUNT(d.id_pago) as cuotas_vencidas,
							 MIN(d.fecha_pago) AS fecha_primer_pago_vencido,
                            d.monto_cuota as monto_cuota,
                            sum(coalesce(e.monto_abonado,0.0)) AS monto_abonado,
                            CURRENT_DATE - MIN(d.fecha_pago) AS dias_transcurridos_mora
                            FROM calendariopago d 
                            LEFT JOIN (
                                SELECT 
                                    id_pago,  
                                    fecha_registro::date AS fecha_registro, 
                                    SUM(monto_aplicado) AS monto_abonado 
                                FROM abono_cuota
                                GROUP BY id_pago
								, fecha_registro::date
                            ) e ON d.id_pago = e.id_pago
                            WHERE d.fecha_pago < CURRENT_DATE 
                            AND COALESCE(e.monto_abonado, 0) < d.monto_cuota
                            GROUP BY d.id_prestamo, d.monto_cuota
                                                ) g ON f.id_prestamo = g.id_prestamo
                                                LEFT JOIN estatus_solicitud h ON c.idestatus = h.idestatus
                                                LEFT JOIN (
                                                    SELECT 
                                                        id_prestamo, 
                                                        MAX(fecha_pago) AS vencimiento_prestamo 
                                                    FROM calendariopago 
                                                    GROUP BY id_prestamo
                                                ) I ON f.id_prestamo = I.id_prestamo
                                                WHERE f.id_solicitud IS NOT NULL 
                                                AND c.idestatus IN (3, 5,6)";
            
                // Determinar el código de cartera que se usará
            $codigoFinal = null;
            if ($perfilUsuario !== 'Administrador' && !empty($codigoCartera)) {
                $codigoFinal = $codigoCartera;
            } elseif ($perfilUsuario === 'Administrador' && !empty($codigoCarterafiltro)) {
                $codigoFinal = $codigoCarterafiltro;
            }

            // Agregar condiciones dinámicamente según los parámetros recibidos
            if (!is_null($id_prestamo)) {
                $query .= " AND c.cod_solicitud::int = :id_prestamo";
            }

            if (!is_null($codigoFinal)) {
                $query .= " AND b.idcartera = :id_cartera";
            }

            $query .= " GROUP BY 
                        b.descripcion, 
                        c.id_solicitud, 
                        c.cod_solicitud, 
                        f.id_prestamo,
                        h.nombre, 
                        a.idcliente, 
                        a.nombre, 
                        a.direccion_domicilio, 
                        a.direccion_negocio, 
                        a.telefono, 
                        I.vencimiento_prestamo,
						g.cuotas_vencidas";

            $stmt = $this->conn->prepare($query);

            

            // Vincular parámetros solo si se recibieron
            if (!is_null($id_prestamo)) {
                $stmt->bindParam(":id_prestamo", $id_prestamo, PDO::PARAM_INT);
            }


            // Asignar parámetros según los valores disponibles
            if (!is_null($codigoFinal)) {
                $stmt->bindParam(":id_cartera", $codigoFinal, PDO::PARAM_INT);
            }

            $stmt->execute();
            
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Siempre retorna un array (aunque esté vacío)
            return $resultados ?: [];

        } catch (PDOException $e) {
            throw new Exception("Error al generar el reporte: " . $e->getMessage());
        }
    }
    
    public function ReporteMovimientoPorCartera($fechaInicio = null, $fechaFin = null, $cod_solicitud = null, $codigoCartera = null, $codigoCarterafiltro = null, $perfilUsuario = null) {
    try {
        
        $query = "
            SELECT idcartera, descripcion, 
                   SUM(saldo_pendiente) AS saldo_pendiente, 
                   SUM(interes_pendiente) AS interes_pendiente, 
                   SUM(mora) AS mora,
                   CASE 
                       WHEN SUM(saldo_pendiente) = 0 THEN 0 
                       ELSE ROUND((SUM(mora) / SUM(saldo_pendiente)) * 100, 2) 
                   END AS porcentaje_mora
            FROM (
                SELECT 
                    d.idcartera, d.descripcion, 
                    SUM(a.saldo_pendiente) AS saldo_pendiente, 
                    SUM(a.interes_pendiente) AS interes_pendiente,
                    SUM(COALESCE(e.saldo_por_cuota, 0)) AS mora
                FROM vista_interes_pendiente_cuotas a 
                INNER JOIN prestamo c ON a.id_prestamo = c.id_prestamo
                INNER JOIN solicitudprestamo b ON c.id_solicitud = b.id_solicitud
                INNER JOIN tblcatcartera d ON b.idcartera = d.idcartera
                LEFT JOIN (
                    SELECT id_prestamo, fecha_pago, saldo_por_cuota
                    FROM vw_vista_mora_por_cuota
                    WHERE estado IN ('Pendiente', 'Parcial', 'Mora') 
                      AND fecha_pago < CURRENT_DATE
                ) e ON a.id_prestamo = e.id_prestamo AND a.fecha_pago = e.fecha_pago
                WHERE b.idestatus IN (3, 5, 6)
        ";

        // Determinar el código de cartera que se usará
        $codigoFinal = null;
        if ($perfilUsuario !== 'Administrador' && !empty($codigoCartera)) {
            $codigoFinal = $codigoCartera;
        } elseif ($perfilUsuario === 'Administrador' && !empty($codigoCarterafiltro)) {
            $codigoFinal = $codigoCarterafiltro;
        }

        if (!is_null($codigoFinal)) {
            $query .= " AND d.idcartera = :codigoCartera";
        }

        if (!is_null($fechaInicio) && !is_null($fechaFin)) {
            $query .= " AND a.fecha_pago BETWEEN :fechaInicio AND :fechaFin";
        }

        if (!is_null($cod_solicitud)) {
            $query .= " AND b.cod_solicitud = :codsolicitud";
        }

        $query .= " 
                GROUP BY d.idcartera, d.descripcion
            ) SUB
            GROUP BY idcartera, descripcion
        ";

        $stmt = $this->conn->prepare($query);

        // Asignar parámetros según los valores disponibles
        if (!is_null($codigoFinal)) {
            $stmt->bindParam(':codigoCartera', $codigoFinal, PDO::PARAM_INT);
        }

        if (!is_null($fechaInicio) && !is_null($fechaFin)) {
            $stmt->bindValue(':fechaInicio', $fechaInicio, PDO::PARAM_STR);
            $stmt->bindValue(':fechaFin', $fechaFin, PDO::PARAM_STR);
        }

        if (!is_null($cod_solicitud)) {
            $stmt->bindParam(':codsolicitud', $cod_solicitud, PDO::PARAM_INT);
        }

        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultados ?: [];

    } catch (PDOException $e) {
        throw new Exception("Error al ejecutar fn_reporte_movimiento_por_cartera: " . $e->getMessage());
    }
}


}

?>
