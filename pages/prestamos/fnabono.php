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
            $query = "INSERT INTO abono (id_prestamo, fecha_abono, monto_abonado, es_prorroga) 
                      VALUES (:id_prestamo, :fecha_abono, :monto_abonado, :es_prorroga)";
            $stmt = $this->conn->prepare($query);

            // Limpiar y vincular los parámetros
            $stmt->bindParam(":id_prestamo", $id_prestamo);
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

    // Obtener todos los abonos de un préstamo
    public function obtenerAbonosPorPrestamo($id_prestamo) {
        try {
            $query = "SELECT * FROM abono WHERE id_prestamo = :id_prestamo";
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