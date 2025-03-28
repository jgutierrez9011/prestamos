<?php
class CalendarioPago {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Crea un nuevo registro en calendariopago
     */
    public function crear($id_prestamo, $fecha_pago, $monto_cuota, $interes, $principal, $saldo, $estado = 'Pendiente') {
        try {
            $this->pdo->beginTransaction();

            // Modificación específica para PostgreSQL para retornar el ID insertado
            $sql = "INSERT INTO calendariopago 
                    (id_prestamo, fecha_pago, monto_cuota, interes, principal, estado, saldo, usuario_creo, fecha_creo) 
                    VALUES (:id_prestamo, :fecha_pago, :monto_cuota, :interes, :principal, :estado, :saldos, :usuario_creo,current_timestamp)
                    RETURNING id_pago";
                    
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_prestamo' => $id_prestamo,
                ':fecha_pago' => $fecha_pago,
                ':monto_cuota' => $monto_cuota,
                ':interes' => $interes,
                ':principal' => $principal,
                ':estado' => $estado,
                ':saldos' => $saldo,
                ':usuario_creo' => $_SESSION["idusuario"]
            ]);

            // Obtenemos el ID directamente del resultado (PostgreSQL specific)
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_pago = $result['id_pago'];
            
            $this->pdo->commit();
            return $id_pago;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new Exception("Error en la base de datos: " . $e->getMessage());
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Obtiene un registro por ID
     */
    public function obtener($id_pago) {
        try {
            $sql = "SELECT * FROM calendariopago WHERE id_pago = :id_pago";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_pago' => $id_pago]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error en la base de datos: " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Obtiene todos los pagos de un préstamo
     */
    public function obtenerPorPrestamo($id_solicitud) {
        try {
            $sql = "SELECT b.modalidad, c.fecha_pago, c.monto_cuota, c.interes, c.principal, c.saldo from solicitudprestamo a 
                    inner join prestamo b on a.id_solicitud = b.id_solicitud
                    inner join calendariopago c on b.id_prestamo = c.id_prestamo
                    where a.cod_solicitud = :id_solicitud
                    ORDER BY c.fecha_pago";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_solicitud' => $id_solicitud]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error en la base de datos: " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Actualiza un registro
     */
    public function actualizar($id_pago, $datos) {
        try {
            $this->pdo->beginTransaction();

            $campos = [];
            $valores = [':id_pago' => $id_pago];

            foreach ($datos as $campo => $valor) {
                $campos[] = "$campo = :$campo";
                $valores[":$campo"] = $valor;
            }

            $sql = "UPDATE calendariopago SET " . implode(', ', $campos) . " WHERE id_pago = :id_pago";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($valores);

            $this->pdo->commit();

            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new Exception("Error en la base de datos: " . $e->getMessage());
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Elimina un registro
     */
    public function eliminar($id_pago) {
        try {
            $this->pdo->beginTransaction();

            $sql = "DELETE FROM calendariopago WHERE id_pago = :id_pago";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_pago' => $id_pago]);

            $this->pdo->commit();

            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new Exception("Error en la base de datos: " . $e->getMessage());
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Cambia el estado de un pago
     */
    public function cambiarEstado($id_pago, $estado) {
        try {
            $this->pdo->beginTransaction();

            $sql = "UPDATE calendariopago SET estado = :estado WHERE id_pago = :id_pago";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':estado' => $estado,
                ':id_pago' => $id_pago
            ]);

            $this->pdo->commit();

            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new Exception("Error en la base de datos: " . $e->getMessage());
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception($e->getMessage());
        }
    }
}
?>