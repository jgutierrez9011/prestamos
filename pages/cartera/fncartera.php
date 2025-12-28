<?php
// fncartera.php

class Cartera {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listar() {
        try {
            $query = "SELECT * FROM tblcatcartera ORDER BY idcartera";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al listar carteras: " . $e->getMessage());
        }
    }

    public function insertar($descripcion, $minimo, $maximo, $usuario) {
        try {
            $query = "INSERT INTO tblcatcartera (descripcion, monto_minimo, monto_maximo, fecha_creacion, usuario_creo, estado)
                      VALUES (:descripcion, :minimo, :maximo, CURRENT_TIMESTAMP, :usuario, true)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':minimo', $minimo);
            $stmt->bindParam(':maximo', $maximo);
            $stmt->bindParam(':usuario', $usuario);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error al insertar cartera: " . $e->getMessage());
        }
    }

    public function actualizar($id, $descripcion, $minimo, $maximo, $estado, $usuario) {
        try {
            $query = "UPDATE tblcatcartera
                      SET descripcion = :descripcion, monto_minimo = :minimo, monto_maximo = :maximo, 
                          estado = :estado, fecha_modificacion = CURRENT_TIMESTAMP, usuario_modifico = :usuario
                      WHERE idcartera = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':minimo', $minimo);
            $stmt->bindParam(':maximo', $maximo);
            $stmt->bindParam(':estado', $estado, PDO::PARAM_BOOL);
            $stmt->bindParam(':usuario', $usuario);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error al actualizar cartera: " . $e->getMessage());
        }
    }

    public function eliminar($id) {
        try {
            $query = "DELETE FROM tblcatcartera WHERE idcartera = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error al eliminar cartera: " . $e->getMessage());
        }
    }

    public function obtenerPorId($id) {
        try {
            $query = "SELECT * FROM tblcatcartera WHERE idcartera = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener cartera: " . $e->getMessage());
        }
    }
}
