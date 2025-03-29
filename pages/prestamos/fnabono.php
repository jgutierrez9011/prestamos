<?php
// fnabono.php

class Abono {
    private $conn;

    // Constructor para inicializar la conexión a la base de datos
    public function __construct($db) {
        $this->conn = $db;
    }

    // Crear un nuevo abono
    public function crearAbono($id_prestamo, $fecha_abono, $monto_abonado, $es_prorroga) {
        try {
            $query = "INSERT INTO abono (id_prestamo, fecha_abono, monto_abonado, es_prorroga, usuario_creo, fecha_creo) 
                      VALUES (:id_prestamo, :fecha_abono, :monto_abonado, :es_prorroga, :usuariocreo, current_timestamp)";
            $stmt = $this->conn->prepare($query);

            // Limpiar y vincular los parámetros
            $stmt->bindParam(":id_prestamo", $id_prestamo);
            $stmt->bindParam(":fecha_abono", $fecha_abono);
            $stmt->bindParam(":monto_abonado", $monto_abonado);
            $stmt->bindParam(":es_prorroga", $es_prorroga, PDO::PARAM_BOOL);
            $stmt->bindParam(":usuariocreo", $_SESSION["idusuario"]);

            // Ejecutar la consulta
            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            // Captura errores específicos de PDO
            throw new Exception("Error en la base de datos: " . $e->getMessage());
        } catch (Exception $e) {
            // Captura cualquier otra excepción
            throw new Exception($e->getMessage());
        }
    }

    // Obtener todos los abonos de un préstamo
    public function obtenerAbonosPorPrestamo($id_prestamo) {
        try {
            $query = "SELECT a.id_abono, a.id_prestamo, a.fecha_abono, to_char(a.fecha_creo,'dd-mm-yyyy') fecha_creo, a.monto_abonado, d.montotal, 
                        --sum(coalesce(c.monto_abonado,0)) total_abonado_prorroga, 
                        sum(e.monto_abonado) total_abonado,
                        d.montotal - sum(e.monto_abonado) saldo,
                        case when a.es_prorroga = 'true' then 'PRORROGA' ELSE 'ABONO' END concepto ,
                        concat(b.strpnombre,' ',b.strsnombre,' ',b.strpapellido,' ',b.strsapellido)ejecutivo
                        FROM abono a left join tblcatusuario b on a.usuario_creo = b.intid
                        left join (select id_abono, id_prestamo, monto_abonado, es_prorroga from abono where es_prorroga = 'true') c on a.id_prestamo = c.id_prestamo and
                        a.id_abono >= c.id_abono
                        left join (select id_abono, id_prestamo, monto_abonado, es_prorroga from abono where es_prorroga = 'false') e on a.id_prestamo = e.id_prestamo and
                        a.id_abono >= e.id_abono
                        left join (select id_prestamo, montotal from prestamo) d on a.id_prestamo = d.id_prestamo
                        WHERE a.id_prestamo = :id_prestamo
                        group by a.id_abono, a.id_prestamo, a.fecha_abono, to_char(a.fecha_creo,'dd/mm/yyyy'), a.monto_abonado, d.montotal,
                        concat(b.strpnombre,' ',b.strsnombre,' ',b.strpapellido,' ',b.strsapellido),
                        a.es_prorroga
                        order by a.id_abono asc";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_prestamo", $id_prestamo);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Captura errores específicos de PDO
            throw new Exception("Error en la base de datos: " . $e->getMessage());
        } catch (Exception $e) {
            // Captura cualquier otra excepción
            throw new Exception($e->getMessage());
        }
    }

    // Actualizar un abono existente
    public function actualizarAbono($id_abono, $fecha_abono, $monto_abonado, $es_prorroga) {
        try {
            $query = "UPDATE abono SET fecha_abono = :fecha_abono, monto_abonado = :monto_abonado, es_prorroga = :es_prorroga 
                      WHERE id_abono = :id_abono";
            $stmt = $this->conn->prepare($query);

            // Limpiar y vincular los parámetros
            $stmt->bindParam(":id_abono", $id_abono);
            $stmt->bindParam(":fecha_abono", $fecha_abono);
            $stmt->bindParam(":monto_abonado", $monto_abonado);
            $stmt->bindParam(":es_prorroga", $es_prorroga, PDO::PARAM_BOOL);

            // Ejecutar la consulta
            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            // Captura errores específicos de PDO
            throw new Exception("Error en la base de datos: " . $e->getMessage());
        } catch (Exception $e) {
            // Captura cualquier otra excepción
            throw new Exception($e->getMessage());
        }
    }

    // Eliminar un abono
    public function eliminarAbono($id_abono) {
        try {
            $query = "DELETE FROM abono WHERE id_abono = :id_abono";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_abono", $id_abono);

            // Ejecutar la consulta
            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            // Captura errores específicos de PDO
            throw new Exception("Error en la base de datos: " . $e->getMessage());
        } catch (Exception $e) {
            // Captura cualquier otra excepción
            throw new Exception($e->getMessage());
        }
    }
}
?>