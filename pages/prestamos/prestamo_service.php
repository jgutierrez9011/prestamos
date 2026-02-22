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
        $sql = "SELECT a.cod_solicitud, b.fecha_desembolso, c.idcliente, c.nombre, b.*, d.abonado, coalesce(b.montotal - d.abonado,b.montotal)  as saldo_pendiente, d.cantidad_abonos
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
    /*public function createPrestamo($data) {

        try{
            // Calcular la cantidad de semanas a partir de los meses
        $plazoMeses = $data['plazo'];
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

        $sql = "INSERT INTO prestamo (id_solicitud, monto_aprobado, interes, plazo, saldo, fecha_primer_cuota, comentario, usuario_creo, monto_interes, montotal, frecuencia, modalidad, monto_cuota, interes_semanal, fecha_desembolso)
                VALUES (:id_solicitud, :monto_aprobado, :interes, :plazo, :saldo, :fecha_primer_cuota, :comentario, :usuario_creo, :interesTotal, :montototal, :frecuencia, :tipomodalidad, :monto_cuota, :interessemanal, :date_desembolso)";
        $stmt = $this->base_de_datos->prepare($sql);
        $stmt->execute([
            'id_solicitud' => $data['id_solicitud'],
            'monto_aprobado' => $data['monto_aprobado'],
            'interes' => $data['interes'],
            'plazo' => $data['plazo'],
            'saldo' => round($montoTotal,2),
            'fecha_primer_cuota' => $data['fecha_primer_cuota'],
            'comentario' => $data['comentario'],
            'usuario_creo' => $_SESSION["idusuario"],
            'interesTotal' => round($interesTotal,2),
            'montototal' => round($montoTotal,2),
            'frecuencia' => $plazoSemanas,
            'tipomodalidad' => 'Semanal',
            'monto_cuota' => round($pagoSemanal,2),
            'interessemanal' => round($interesSemanal, 2),
            'date_desembolso' => $data['fecha_desembolso']
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
    }*/

    public function createPrestamo($data) {
    try {
        // Validaciones básicas de entrada
        if (empty($data['id_solicitud']) || !ctype_digit(strval($data['id_solicitud']))) {
            throw new InvalidArgumentException('id_solicitud inválido');
        }
        if (empty($data['monto_aprobado']) || !is_numeric($data['monto_aprobado']) || $data['monto_aprobado'] <= 0) {
            throw new InvalidArgumentException('monto_aprobado inválido');
        }
        if (!isset($data['plazo']) || !is_numeric($data['plazo']) || $data['plazo'] <= 0) {
            throw new InvalidArgumentException('plazo inválido');
        }
        if (!isset($data['interes']) || !is_numeric($data['interes']) || $data['interes'] < 0) {
            throw new InvalidArgumentException('interes inválido');
        }
        if (isset($data['fecha_desembolso'])) {
            $fd = DateTime::createFromFormat('Y-m-d', $data['fecha_desembolso']);
            if (!$fd || $fd->format('Y-m-d') !== $data['fecha_desembolso']) {
                throw new InvalidArgumentException('fecha_desembolso inválida, use YYYY-MM-DD');
            }
        }

        $plazoMeses = $data['plazo'];
        $monto = $data['monto_aprobado'];
        $interesMensual = $data['interes'];
        $modalidad = strtolower($data['modalidad'] ?? 'semanal'); // debe venir desde el formulario

        // Calcular número de pagos según modalidad
        switch ($modalidad) {
            case 'diaria':
                $numPagos = $plazoMeses * 30;
                break;
            case 'quincenal':
                $numPagos = $plazoMeses * 2;
                break;
            case 'mensual':
                $numPagos = $plazoMeses;
                break;
            case 'semanal':
                $numPagos = ceil($plazoMeses * 4);
            default:
                $numPagos = ceil($plazoMeses * 4);
                break;
        }

        // Interés total simple y monto total a pagar
        $interesTotal = $monto * ($interesMensual / 100) * $plazoMeses;
        $montoTotal = $monto + $interesTotal;

        // Calcular cuota y su desglose
        $montoCuota = $montoTotal / $numPagos;
        $interesPorCuota = $interesTotal / $numPagos;

        $sql = "INSERT INTO prestamo (
                    id_solicitud, monto_aprobado, interes, plazo, saldo,
                    fecha_primer_cuota, comentario, usuario_creo,
                    monto_interes, montotal, frecuencia, modalidad,
                    monto_cuota, interes_semanal, fecha_desembolso
                ) VALUES (
                    :id_solicitud, :monto_aprobado, :interes, :plazo, :saldo,
                    :fecha_primer_cuota, :comentario, :usuario_creo,
                    :interesTotal, :montototal, :frecuencia, :tipomodalidad,
                    :monto_cuota, :interes_por_cuota, :date_desembolso
                )";

        $stmt = $this->base_de_datos->prepare($sql);
        $stmt->execute([
            'id_solicitud' => $data['id_solicitud'],
            'monto_aprobado' => $monto,
            'interes' => $interesMensual,
            'plazo' => $plazoMeses,
            'saldo' => round($montoTotal, 2),
            'fecha_primer_cuota' => $data['fecha_primer_cuota'],
            'comentario' => $data['comentario'],
            'usuario_creo' => $_SESSION["idusuario"],
            'interesTotal' => round($interesTotal, 2),
            'montototal' => round($montoTotal, 2),
            'frecuencia' => $numPagos,
            'tipomodalidad' => ucfirst($modalidad),
            'monto_cuota' => round($montoCuota, 2),
            'interes_por_cuota' => round($interesPorCuota, 2),
            'date_desembolso' => $data['fecha_desembolso']
        ]);

        return $this->base_de_datos->lastInsertId();

    } catch (PDOException $e) {
        error_log('prestamo_service createPrestamo PDOException: ' . $e->getMessage());
        return ["error" => "Ocurrió un error interno en el servidor."];
    } catch (InvalidArgumentException $e) {
        return ["error" => $e->getMessage()];
    } catch (Exception $e) {
        error_log('prestamo_service createPrestamo Exception: ' . $e->getMessage());
        return ["error" => "Ocurrió un error interno en el servidor."];
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

    /*function generarCalendarioPagos_simple($monto, $interesMensual, $plazoMeses, $fechaInicioStr) {
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
    } */
    
function generarCalendarioPagos_simple($monto, $interesMensual, $plazoMeses, $fechaInicioStr, $modalidad = 'semanal') {
    $calendario = [];
    $fechaInicio = new DateTime($fechaInicioStr);
    $modalidad = strtolower($modalidad);

    // Determinar número de pagos y el intervalo según modalidad
    switch ($modalidad) {
        case 'diario':
            $modalidad = 'diario';
            $numPagos = $plazoMeses * 30; // Aproximado
            $intervalo = ' day';
            break;
        case 'quincenal':
            $modalidad = 'quincenal';
            $numPagos = $plazoMeses * 2;
            $intervalo = ' days';
            break;
        case 'mensual':
            $modalidad = 'mensual';
            $numPagos = $plazoMeses;
            $intervalo = ' month';
            break;
        case 'semanal':
            $modalidad = 'semanal';
            $numPagos = ceil($plazoMeses * 4); // Aprox. 4 semanas por mes
            $intervalo = ' week';
        default:
            $modalidad = 'semanal';
            $numPagos = ceil($plazoMeses * 4); // Aprox. 4 semanas por mes
            $intervalo = ' week';
            break;
    }

    // Calcular interés total (simple) y cuota
    $interesTotal = $monto * ($interesMensual / 100) * $plazoMeses;
    $montoTotal = $monto + $interesTotal;
    $cuota = $montoTotal / $numPagos;
    $interesPorCuota = $interesTotal / $numPagos;
    $saldoPendiente = $montoTotal;

    for ($i = 0; $i < $numPagos; $i++) {
        $abonoCapital = $cuota - $interesPorCuota;
        $saldoPendiente -= $cuota;
        
        if($modalidad === 'quincenal'){

             $fechaPago = (clone $fechaInicio)->modify("+".($i * 15)." days");

        }else{
 
             $fechaPago = (clone $fechaInicio)->modify("+ {$i} $intervalo");

        }
        



        $calendario[] = [
            'numero_pago' => ($i + 1),
            'modalidad' => ucfirst($modalidad),
            'fecha_pago' => $fechaPago->format('Y-m-d'),
            'cuota' => round($cuota, 2),
            'interes' => round($interesPorCuota, 2),
            'abono_capital' => round($abonoCapital, 2),
            'saldo_pendiente' => round(max($saldoPendiente, 0), 2)
        ];
    }

    return $calendario;
}



    
}
?>