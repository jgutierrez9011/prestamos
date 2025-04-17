<?php
// fnobligaciones.php

class ObligacionFinanciera {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listar() {
        try {
            $query = "SELECT * FROM obligacionesfinancieras ORDER BY id_obligacion";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al listar obligaciones: " . $e->getMessage());
        }
    }

    public function insertar($id_solicitud, $institucion, $monto_inicial, $saldo, $cuota) {
        try {
            $query = "INSERT INTO obligacionesfinancieras (id_solicitud, institucion, monto_inicial, saldo, cuota)
                      VALUES (:id_solicitud, :institucion, :monto_inicial, :saldo, :cuota)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_solicitud', $id_solicitud);
            $stmt->bindParam(':institucion', $institucion);
            $stmt->bindParam(':monto_inicial', $monto_inicial);
            $stmt->bindParam(':saldo', $saldo);
            $stmt->bindParam(':cuota', $cuota);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error al insertar obligación: " . $e->getMessage());
        }
    }

    public function actualizar($id, $id_solicitud, $institucion, $monto_inicial, $saldo, $cuota) {
        try {
            $query = "UPDATE obligacionesfinancieras
                      SET id_solicitud = :id_solicitud,
                          institucion = :institucion,
                          monto_inicial = :monto_inicial,
                          saldo = :saldo,
                          cuota = :cuota
                      WHERE id_obligacion = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_solicitud', $id_solicitud);
            $stmt->bindParam(':institucion', $institucion);
            $stmt->bindParam(':monto_inicial', $monto_inicial);
            $stmt->bindParam(':saldo', $saldo);
            $stmt->bindParam(':cuota', $cuota);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error al actualizar obligación: " . $e->getMessage());
        }
    }

    public function eliminar($id) {
        try {
            $query = "DELETE FROM obligacionesfinancieras WHERE id_obligacion = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error al eliminar obligación: " . $e->getMessage());
        }
    }

    public function obtenerPorId($id) {
        try {
            $query = "SELECT * FROM obligacionesfinancieras WHERE id_obligacion = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener obligación: " . $e->getMessage());
        }
    }
}
