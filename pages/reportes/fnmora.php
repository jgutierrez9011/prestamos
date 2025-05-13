<?php
// fnreporte.php

class Reportes {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Generar reporte por préstamo y/o cartera, o todos si no se envían filtros
    public function ReportePrestamoMora($id_prestamo = null, $id_cartera = null) {
        try {
            $query = "SELECT 
                        b.descripcion AS cartera, 
                        c.id_solicitud, 
                        c.cod_solicitud, 
                        f.id_prestamo,
                        h.nombre AS estatus,
                        a.idcliente AS numero_cliente, 
                        a.nombre AS cliente, 
                        a.direccion_domicilio, 
                        a.direccion_negocio, 
                        a.telefono,
                        I.vencimiento_prestamo,
                        ROUND((SUM(g.dias_transcurridos_mora)/7.0), 2) AS dias_promedio,
                        COUNT(g.id_pago) AS cuotas_vencidas,
                        SUM(g.monto_cuota) AS saldo_mora,
                        SUM(g.dias_transcurridos_mora) AS dias_mora
                    FROM clientes a 
                    LEFT JOIN tblcatcartera b ON a.idcartera = b.idcartera
                    LEFT JOIN solicitudprestamo c ON a.idcliente = c.idcliente
                    LEFT JOIN prestamo f ON c.id_solicitud = f.id_solicitud
                    LEFT JOIN (
                        SELECT 
                            d.id_prestamo, 
                            d.id_pago, 
                            d.monto_cuota, 
                            COALESCE(e.monto_abonado, 0) AS monto_abonado, 
                            COALESCE(e.fecha_registro, CURRENT_DATE) - d.fecha_pago AS dias_transcurridos_mora,
                            d.fecha_pago, 
                            COALESCE(e.fecha_registro, CURRENT_DATE) AS fecha_registro
                        FROM calendariopago d 
                        LEFT JOIN (
                            SELECT 
                                id_pago,  
                                fecha_registro::date AS fecha_registro, 
                                SUM(monto_aplicado) AS monto_abonado 
                            FROM abono_cuota
                            GROUP BY id_pago, fecha_registro::date
                        ) e ON d.id_pago = e.id_pago
                        WHERE d.fecha_pago < CURRENT_DATE 
                          AND COALESCE(e.fecha_registro, CURRENT_DATE) - d.fecha_pago > 0
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
                      AND c.idestatus IN (3, 6)";

            // Agregar condiciones dinámicamente según los parámetros recibidos
            if (!is_null($id_prestamo)) {
                $query .= " AND c.cod_solicitud = :id_prestamo";
            }

            if (!is_null($id_cartera)) {
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
                        I.vencimiento_prestamo";

            $stmt = $this->conn->prepare($query);

            // Vincular parámetros solo si se recibieron
            if (!is_null($id_prestamo)) {
                $stmt->bindParam(":id_prestamo", $id_prestamo, PDO::PARAM_INT);
            }

            if (!is_null($id_cartera)) {
                $stmt->bindParam(":id_cartera", $id_cartera, PDO::PARAM_INT);
            }

            $stmt->execute();
            
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Siempre retorna un array (aunque esté vacío)
            return $resultados ?: [];

        } catch (PDOException $e) {
            throw new Exception("Error al generar el reporte: " . $e->getMessage());
        }
    }

    public function ReporteMovimientoPorCartera($fechaInicio = null, $fechaFin = null) {
        try {
            $query = "SELECT * FROM fn_reporte_movimiento_por_cartera(:fechaInicio, :fechaFin)";
            $stmt = $this->conn->prepare($query);

            // Asignar null si no se pasó parámetro
            $stmt->bindValue(':fechaInicio', $fechaInicio, is_null($fechaInicio) ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':fechaFin', $fechaFin, is_null($fechaFin) ? PDO::PARAM_NULL : PDO::PARAM_STR);

            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $resultados ?: [];

        } catch (PDOException $e) {
            throw new Exception("Error al ejecutar fn_reporte_movimiento_por_cartera: " . $e->getMessage());
        }
    
}

}

?>
