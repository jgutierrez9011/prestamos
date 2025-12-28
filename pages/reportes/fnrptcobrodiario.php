<?php
// fnrpt_cobro_diario.php

class RptCobroDiario {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene el reporte de cobro diario, filtrando por fechas y cartera según el perfil de usuario.
     * 
     * @param string|null $fechaInicio Fecha de inicio en formato YYYY-MM-DD
     * @param string|null $fechaFin Fecha de fin en formato YYYY-MM-DD
     * @param int|null $codigoCartera Código de la cartera
     * @param string|null $perfilUsuario Perfil del usuario (por ejemplo: "Administrador")
     * @return array Resultados del reporte
     * @throws Exception Si ocurre un error en la ejecución de la consulta
     */
    public function obtenerReporteCobroDiario($fechaInicio = null, $fechaFin = null, $codigoCartera = null, $codigoCarterafiltro = null,$perfilUsuario = null) {
        try {
            // Fecha por defecto: hoy
            $hoy = date('Y-m-d');
            if (empty($fechaInicio)) $fechaInicio = $hoy;
            if (empty($fechaFin)) $fechaFin = $hoy;

            $query = "
            SELECT 
                cartera.idcartera AS codigo_cartera,
                cartera.descripcion AS cartera,
                d.cod_solicitud,
                a.id_prestamo,
                cli.nombre AS cliente,
                cli.telefono,
                cli.direccion_domicilio,
                cli.direccion_negocio,
                b.fecha_desembolso,
                vence.fecha_vence,
                a.fecha_pago AS fecha_abono,
                a.row_number AS numero_cuota,
                a.monto_cuota AS valor_cuota,
                COALESCE(SUM(cuotas_mora.saldo_por_cuota), 0.00)::float AS saldo_mora,
                a.monto_cuota + COALESCE(SUM(cuotas_mora.saldo_por_cuota), 0.00)::float AS cuota_mas_mora,
                COALESCE(SUM(c.abonado), 0.00)::float AS abonado,
                (SELECT montotal FROM prestamo WHERE id_prestamo = a.id_prestamo) - COALESCE(SUM(c.abonado), 0.00)::float AS saldo
            FROM (
                SELECT id_pago, id_prestamo, fecha_pago, monto_cuota,
                       ROW_NUMBER() OVER (PARTITION BY id_prestamo ORDER BY fecha_pago) 
                FROM calendariopago
            ) a
            INNER JOIN prestamo b ON a.id_prestamo = b.id_prestamo
            INNER JOIN (
                SELECT id_prestamo, MAX(fecha_pago) AS fecha_vence
                FROM calendariopago
                GROUP BY id_prestamo
            ) vence ON b.id_prestamo = vence.id_prestamo
            INNER JOIN solicitudprestamo d ON b.id_solicitud = d.id_solicitud
            INNER JOIN tblcatcartera cartera ON d.idcartera = cartera.idcartera
            INNER JOIN estatus_solicitud e ON d.idestatus = e.idestatus
            INNER JOIN clientes cli ON d.idcliente = cli.idcliente
            LEFT JOIN (
                SELECT id_prestamo, TO_CHAR(fecha_creo, 'YYYY-MM-DD')::date AS fecha_abono, SUM(monto_abonado) AS abonado
                FROM abono
                GROUP BY id_prestamo, TO_CHAR(fecha_creo, 'YYYY-MM-DD')
            ) c ON a.id_prestamo = c.id_prestamo AND c.fecha_abono <= a.fecha_pago
            LEFT JOIN (
                SELECT id_pago, id_prestamo, fecha_pago, saldo_por_cuota 
                FROM vw_vista_mora_por_cuota
                WHERE estatus IN ('Mora')
            ) cuotas_mora ON a.id_prestamo = cuotas_mora.id_prestamo AND cuotas_mora.fecha_pago = a.fecha_pago
            WHERE d.idestatus IN (3, 5, 6)
              AND a.fecha_pago BETWEEN :fechaInicio AND :fechaFin";

            // Si no es administrador, se filtra por código de cartera
            if ($perfilUsuario !== 'Administrador' && !empty($codigoCartera)) {
                $query .= " AND cartera.idcartera = :codigoCartera";
            }

            if ($perfilUsuario == 'Administrador' && !empty($codigoCarterafiltro)) {
                $query .= " AND cartera.idcartera = :codigoCartera";
            }

            $query .= "
            GROUP BY 
                cartera.idcartera, cartera.descripcion, d.cod_solicitud, a.id_prestamo, 
                cli.nombre, cli.telefono, cli.direccion_domicilio, cli.direccion_negocio,
                b.fecha_desembolso, vence.fecha_vence, a.fecha_pago, a.row_number, a.monto_cuota
            ORDER BY d.cod_solicitud ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':fechaInicio', $fechaInicio);
            $stmt->bindParam(':fechaFin', $fechaFin);

            if ($perfilUsuario !== 'Administrador' && !empty($codigoCartera)) {
                $stmt->bindParam(':codigoCartera', $codigoCartera, PDO::PARAM_INT);
            }elseif($perfilUsuario == 'Administrador' && !empty($codigoCarterafiltro)){
                $stmt->bindParam(':codigoCartera', $codigoCarterafiltro, PDO::PARAM_INT);
                 
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            throw new Exception("Error al generar el reporte de cobro diario: " . $e->getMessage());
        }
    }
}
?>
