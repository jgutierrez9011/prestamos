<?php
// fnrpt_abono.php

class Rptabono {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene reporte de abonos con filtros por cartera, tipo de usuario y rango de fechas
     * 
     * @param string|null $cartera Nombre de la cartera a filtrar
     * @param string|null $tipoUsuario Tipo de usuario (administrador o no)
     * @param string|null $fechaInicio Fecha de inicio en formato YYYY-MM-DD
     * @param string|null $fechaFin Fecha de fin en formato YYYY-MM-DD
     * @return array Resultados del reporte
     * @throws Exception Si ocurre un error en la consulta
     */
    public function obtenerReporteAbonos($cartera = null, $tipoUsuario = null, $fechaInicio = null, $fechaFin = null) {
    try {
        $query = "SELECT e.descripcion as cartera, a.cod_solicitud, d.nombre as cliente, a.telefono, 
                         to_char(c.fecha_creo, 'YYYY-MM-DD') as fecha_creo, sum(c.monto_abonado) as monto_abonado,
                         concat(f.strpnombre,' ',f.strsnombre,' ',f.strpapellido,' ',f.strsapellido) as usuario_creo
                  FROM solicitudprestamo a
                  INNER JOIN clientes d ON a.idcliente = d.idcliente
                  INNER JOIN tblcatcartera e ON a.idcartera = e.idcartera 
                  INNER JOIN prestamo b ON a.id_solicitud = b.id_solicitud
                  INNER JOIN abono c ON b.id_prestamo = c.id_prestamo
                  INNER JOIN tblcatusuario f ON c.usuario_creo = f.intid";

        $condiciones = [];

        if ($tipoUsuario !== 'Administrador' && !is_null($cartera)) {
            $condiciones[] = "a.idcartera = :cartera";
        }

        // Manejo seguro de fechas
        if (!empty($fechaInicio) && !empty($fechaFin)) {
            $condiciones[] = "c.fecha_creo::date BETWEEN :fechaInicio AND :fechaFin";
        }

        if (!empty($condiciones)) {
            $query .= " WHERE " . implode(" AND ", $condiciones);
        }

        $query .= " GROUP BY e.descripcion, a.cod_solicitud, d.nombre, a.telefono, 
                           to_char(c.fecha_creo, 'YYYY-MM-DD'),
                           concat(f.strpnombre,' ',f.strsnombre,' ',f.strpapellido,' ',f.strsapellido)
                  ORDER BY a.cod_solicitud ASC";

                  

        $stmt = $this->conn->prepare($query);

        if ($tipoUsuario !== 'Administrador' && !is_null($cartera)) {
            $stmt->bindParam(':cartera', $cartera, PDO::PARAM_INT);
        }

        if (!empty($fechaInicio) && !empty($fechaFin)) {
            $stmt->bindParam(':fechaInicio', $fechaInicio);
            $stmt->bindParam(':fechaFin', $fechaFin);
        }
       
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    } catch (PDOException $e) {
        throw new Exception("Error al generar el reporte de abonos: " . $e->getMessage());
    }
}
}
?>