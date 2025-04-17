<?php
// fnsucursal.php

class Sucursal {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listar() {
        try {
            $query = "SELECT * FROM sucursales ORDER BY sucursal_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al listar sucursales: " . $e->getMessage());
        }
    }

    public function insertar($nombre, $direccion, $telefono, $fecha_apertura) {
        try {
            $query = "INSERT INTO sucursales (nombre, direccion, telefono, fecha_apertura)
                      VALUES (:nombre, :direccion, :telefono, :fecha_apertura)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':direccion', $direccion);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':fecha_apertura', $fecha_apertura);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error al insertar sucursal: " . $e->getMessage());
        }
    }

    public function actualizar($id, $nombre, $direccion, $telefono, $fecha_apertura) {
        try {
            $query = "UPDATE sucursales
                      SET nombre = :nombre, direccion = :direccion, telefono = :telefono, fecha_apertura = :fecha_apertura
                      WHERE sucursal_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':direccion', $direccion);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':fecha_apertura', $fecha_apertura);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error al actualizar sucursal: " . $e->getMessage());
        }
    }

    public function eliminar($id) {
        try {
            $query = "DELETE FROM sucursales WHERE sucursal_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error al eliminar sucursal: " . $e->getMessage());
        }
    }

    public function obtenerPorId($id) {
        try {
            $query = "SELECT * FROM sucursales WHERE sucursal_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener sucursal: " . $e->getMessage());
        }
    }
}
