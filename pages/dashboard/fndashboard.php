<?php
require_once '../cn.php';

class Dashboard {
    private $base_de_datos;

    public function __construct($pdo) {
        $this->base_de_datos = $pdo;
    }

    /**
     * Retorna la suma total de abonos diarios desde la vista
     *
     * @return float|null
     */
    public function getTotalAbonos() {
        try {
            //$sql = "SELECT SUM(total_abonado) AS total FROM vista_abonos_diarios_por_prestamo";
            $sql = "SELECT sum(monto_abonado) AS total FROM abono";
            $stmt = $this->base_de_datos->prepare($sql);
            $stmt->execute();

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            return $resultado['total'] ?? 0.00;

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error en la base de datos: " . $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }
    /**
     * Retorna la suma total de abonos diarios desde la vista
     *
     * @return float|null
     */
    public function getSaldoPendiente() {
        try {
            $sql = "select 
                    (select sum(monto_cuota) 
                    from calendariopago) - 
                    (SELECT sum(monto_abonado) FROM public.abono) saldo_pendiente";
            $stmt = $this->base_de_datos->prepare($sql);
            $stmt->execute();

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            return $resultado['saldo_pendiente'] ?? 0.00;

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error en la base de datos: " . $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }
    /**
     * Retorna la suma total de abonos diarios desde la vista
     *
     * @return float|null
     */
    public function getInteresColocadoPendiente() {
        try {
            $sql = "select sum(interes) interes_pendiente from calendariopago where estado = 'Pendiente'";
            $stmt = $this->base_de_datos->prepare($sql);
            $stmt->execute();

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            return $resultado['interes_pendiente'] ?? 0.00;

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error en la base de datos: " . $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }
}
?>
