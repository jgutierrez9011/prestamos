<?php
require_once '../cn.php';
require_once 'fncalendariopago.php';


class PrestamoService {
    private $base_de_datos;

    public function __construct($base_de_datos) {
        $this->base_de_datos = $base_de_datos;
    }

    // Obtener todos los préstamos
    public function getPrestamos(): mixed {
        $sql = "SELECT * FROM prestamo";
        $stmt = $this->base_de_datos->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener un préstamo por ID
    public function getPrestamo($cod_solicitud) {
        $sql = "SELECT c.nombre, b.*, d.abonado, coalesce(b.montotal - d.abonado,b.montotal)  as saldo_pendiente, d.cantidad_abonos
                FROM solicitudPrestamo a 
                inner join prestamo b on a.id_solicitud = b.id_solicitud
                inner join clientes c on a.idcliente = c.idcliente
                left join (SELECT id_prestamo, count(1) cantidad_abonos, sum(monto_abonado) abonado
                FROM public.abono 
                GROUP BY id_prestamo) d on b.id_prestamo = d.id_prestamo
                WHERE cod_solicitud = :cod_solicitud";
        $stmt = $this->base_de_datos->prepare($sql);
        $stmt->execute(['cod_solicitud' => $cod_solicitud]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear un nuevo préstamo
    public function createPrestamo($data) {

        try{

            // Calcular la cantidad de semanas a partir de los meses
        $plazoMeses =$data['plazo'];
        $plazoSemanas = ceil($plazoMeses * 4); // Redondear hacia arriba para evitar semanas incompletas

        // Calcular el interés total simple
        $monto = $data['monto_aprobado'];
        $interesMensual= $data['interes'];
        $interesTotal = $monto * ($interesMensual / 100) * $plazoMeses;

        // Calcular el monto total a pagar
        $montoTotal = $monto + $interesTotal;

        // Calcular el pago semanal
        $pagoSemanal = $montoTotal / $plazoSemanas;

        $interesSemanal = $interesTotal / $plazoSemanas;

        $sql = "INSERT INTO prestamo (id_solicitud, monto_aprobado, interes, plazo, saldo, fecha_primer_cuota, comentario, usuario_creo, monto_interes, montotal, frecuencia, modalidad, monto_cuota, interes_semanal)
                VALUES (:id_solicitud, :monto_aprobado, :interes, :plazo, :saldo, :fecha_primer_cuota, :comentario, :usuario_creo, :interesTotal, :montototal, :frecuencia, :tipomodalidad, :monto_cuota, :interessemanal)";
        $stmt = $this->base_de_datos->prepare($sql);
        $stmt->execute([
            'id_solicitud' => $data['id_solicitud'],
            'monto_aprobado' => $data['monto_aprobado'],
            'interes' => $data['interes'],
            'plazo' => $data['plazo'],
            'saldo' => $data['saldo'],
            'fecha_primer_cuota' => $data['fecha_primer_cuota'],
            'comentario' => $data['comentario'],
            'usuario_creo' => $_SESSION["idusuario"],
            'interesTotal' => round($interesTotal,2),
            'montototal' => round($montoTotal,2),
            'frecuencia' => $plazoSemanas,
            'tipomodalidad' => 'Semanal',
            'monto_cuota' => round($pagoSemanal,2),
            'interessemanal' => round($interesSemanal, 2)
        ]);
        
        return $this->base_de_datos->lastInsertId();

        } catch (PDOException $e) {
            // Captura errores específicos de PDO
            http_response_code(500);
            echo json_encode(["error" => "Error en la base de datos: " . $e->getMessage()]);
        } catch (Exception $e) {
            // Captura cualquier otra excepción
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // Actualizar un préstamo
    public function updatePrestamo($id_prestamo, $data) {
        $sql = "UPDATE prestamo
                SET id_solicitud = :id_solicitud,
                    monto_aprobado = :monto_aprobado,
                    interes = :interes,
                    plazo = :plazo,
                    saldo = :saldo,
                    fecha_primer_cuota = :fecha_primer_cuota,
                    comentario = :comentario
                WHERE id_prestamo = :id_prestamo";
        $stmt = $this->base_de_datos->prepare($sql);
        $stmt->execute([
            'id_solicitud' => $data['id_solicitud'],
            'monto_aprobado' => $data['monto_aprobado'],
            'interes' => $data['interes'],
            'plazo' => $data['plazo'],
            'saldo' => $data['saldo'],
            'fecha_primer_cuota' => $data['fecha_primer_cuota'],
            'comentario' => $data['comentario'],
            'id_prestamo' => $id_prestamo
        ]);
        return $stmt->rowCount();
    }

    // Eliminar un préstamo
    public function deletePrestamo($id_prestamo) {
        $sql = "DELETE FROM prestamo WHERE id_prestamo = :id_prestamo";
        $stmt = $this->base_de_datos->prepare($sql);
        $stmt->execute(['id_prestamo' => $id_prestamo]);
        return $stmt->rowCount();
    }
    
    function generarCalendarioPagos_compuesto($monto, $interesMensual, $plazoMeses, $fechaInicioStr) {
        $calendario = [];
        $fechaInicio = new DateTime($fechaInicioStr);
        
        // Calcular la cantidad de semanas a partir de los meses
        $plazoSemanas = $plazoMeses * 4.33;
        $plazoSemanas = ceil($plazoSemanas); // Redondear hacia arriba para evitar semanas incompletas
        
        // Calcular la tasa de interés semanal
        $tasaSemanal = ($interesMensual / 100) / 4.33;
        
        // Calcular el pago semanal con la fórmula de amortización
        $pagoSemanal = ($monto * $tasaSemanal) / (1 - pow(1 + $tasaSemanal, -$plazoSemanas));
        
        $saldoPendiente = $monto;
        
        for ($i = 0; $i < $plazoSemanas; $i++) {
            $interesSemanal = $saldoPendiente * $tasaSemanal;
            $abonoCapital = $pagoSemanal - $interesSemanal;
            $saldoPendiente -= $abonoCapital;
            
            $fechaPago = (clone $fechaInicio)->modify("+{$i} week");
            
            $calendario[] = [
                'semana' => $i + 1,
                'fecha_pago' => $fechaPago->format('Y-m-d'),
                'pago_total' => round($pagoSemanal, 2),
                'interes' => round($interesSemanal, 2),
                'abono_capital' => round($abonoCapital, 2),
                'saldo_pendiente' => round(max($saldoPendiente, 0), 2)
            ];
        }
        
        return $calendario;
    }

    function generarCalendarioPagos_simple($monto, $interesMensual, $plazoMeses, $fechaInicioStr) {
        $calendario = [];
        $fechaInicio = new DateTime($fechaInicioStr);
        
        // Calcular la cantidad de semanas a partir de los meses
        $plazoSemanas = ceil($plazoMeses * 4); // Redondear hacia arriba para evitar semanas incompletas
        
        // Calcular el interés total simple
        $interesTotal = $monto * ($interesMensual / 100) * $plazoMeses;
        
        // Calcular el monto total a pagar
        $montoTotal = $monto + $interesTotal;
        
        // Calcular el pago semanal
        $pagoSemanal = $montoTotal / $plazoSemanas;
        
        $saldoPendiente = $montoTotal;
        $interesSemanal = $interesTotal / $plazoSemanas;
        
        for ($i = 0; $i < $plazoSemanas; $i++) {
            $abonoCapital = $pagoSemanal - $interesSemanal;
            $saldoPendiente -= $pagoSemanal;
            
            $fechaPago = (clone $fechaInicio)->modify("+{$i} week");
            
            $calendario[] = [
                'semana' => $i + 1,
                'fecha_pago' => $fechaPago->format('Y-m-d'),
                'cuota' => round($pagoSemanal, 2),
                'interes' => round($interesSemanal, 2),
                'abono_capital' => round($abonoCapital, 2),
                'saldo_pendiente' => round(max($saldoPendiente, 0), 2)
            ];
        }
        
        return $calendario;
    }    
    

    
}
?>