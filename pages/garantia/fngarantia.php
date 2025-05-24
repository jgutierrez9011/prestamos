<?php
// fngarantia.php

class Garantia {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listar() {
        try {
            $query = "SELECT * FROM garantia ORDER BY id_garantia";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al listar garantías: " . $e->getMessage());
        }
    }

    public function insertar($cod_solicitud, $descripcion, $cantidad, $marca, $color, $ubicacion, $valor_realizacion) {
        try {
            // Primero obtenemos el id_solicitud basado en el cod_solicitud
            $queryId = "SELECT id_solicitud FROM solicitudprestamo WHERE cod_solicitud = :cod_solicitud";
            $stmtId = $this->conn->prepare($queryId);
            $stmtId->bindParam(':cod_solicitud', $cod_solicitud);
            $stmtId->execute();
        
            $resultado = $stmtId->fetch(PDO::FETCH_ASSOC);
            
            if (!$resultado || !isset($resultado['id_solicitud'])) {
                throw new Exception("No se encontró una solicitud con el código proporcionado");
            }

            $id_solicitud = $resultado['id_solicitud'];

            $query = "INSERT INTO garantia (id_solicitud, descripcion, cantidad, marca, color, ubicacion, valor_realizacion)
                      VALUES (:id_solicitud, :descripcion, :cantidad, :marca, :color, :ubicacion, :valor_realizacion)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_solicitud', $id_solicitud);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':cantidad', $cantidad);
            $stmt->bindParam(':marca', $marca);
            $stmt->bindParam(':color', $color);
            $stmt->bindParam(':ubicacion', $ubicacion);
            $stmt->bindParam(':valor_realizacion', $valor_realizacion);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error al insertar garantía: " . $e->getMessage());
        }
    }

    public function actualizar($id, $id_solicitud, $descripcion, $cantidad, $marca, $color, $ubicacion, $valor_realizacion) {
        try {
            $query = "UPDATE garantia
                      SET id_solicitud = :id_solicitud, descripcion = :descripcion, cantidad = :cantidad, 
                          marca = :marca, color = :color, ubicacion = :ubicacion, valor_realizacion = :valor_realizacion
                      WHERE id_garantia = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_solicitud', $id_solicitud);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':cantidad', $cantidad);
            $stmt->bindParam(':marca', $marca);
            $stmt->bindParam(':color', $color);
            $stmt->bindParam(':ubicacion', $ubicacion);
            $stmt->bindParam(':valor_realizacion', $valor_realizacion);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error al actualizar garantía: " . $e->getMessage());
        }
    }

    public function eliminar($id) {
        try {
            $query = "DELETE FROM garantia WHERE id_garantia = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Error al eliminar garantía: " . $e->getMessage());
        }
    }

    public function obtenerPorId($id) {
        try {
            $query = "SELECT a.* FROM garantia a 
                      inner join solicitudprestamo b on a.id_solicitud = b.id_solicitud
                      where b.cod_solicitud = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener garantía: " . $e->getMessage());
        }
    }
}
