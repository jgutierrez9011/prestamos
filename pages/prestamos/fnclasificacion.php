<?php
require_once  '../usuarios/reg.php';

 function cliente_existe($idcliente){

    try{

        $pdo = conexion_bd(3);

        $stmt = $pdo->prepare("SELECT count(id_solicitud) cantidad FROM SolicitudPrestamo WHERE idcliente = :idcliente");
                $stmt->execute([":idcliente" => $idcliente]);
                $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

                return $cliente['cantidad']; 
    }catch(PDOException $e){
        echo "Ocurrió un error con la base de datos: " . $e->getMessage();
    }
 }

 function seq_solicitud_credito() {
    try {
        $pdo = conexion_bd(3);

        // Consulta para obtener la secuencia
        $stmt = $pdo->prepare("SELECT COUNT(id_solicitud) + 1 AS secuencia FROM solicitudprestamo");
        $stmt->execute();
        
        // Devuelve directamente el valor de la secuencia
        return $stmt->fetch(PDO::FETCH_ASSOC)['secuencia'];
    } catch (PDOException $e) {
        // Manejo de errores
        echo "Ocurrió un error con la base de datos: " . $e->getMessage();
        return null; // Devuelve null en caso de error
    }
}

function obtenerSolicitudesPrestamo() {
    try {
        // Conectar a la base de datos
        $pdo = conexion_bd(3); // Asegúrate de que esta función retorne una instancia de PDO

        // Consulta SQL
        $sql = "
            SELECT 
                a.id_solicitud, 
                a.cod_solicitud, 
                b.nombre, 
                a.fecha_creo AS fecha_solicitud, 
                a.monto_solicitado, 
                c.nombre AS estatus, 
                a.plazo_solicitado, 
                a.tasa, 
                CONCAT(d.strpnombre, ' ', d.strsnombre, ' ', d.strpapellido, ' ', d.strsapellido) AS oficial_credito
            FROM SolicitudPrestamo a
            LEFT JOIN clientes b ON a.idcliente = b.idcliente
            LEFT JOIN estatus_solicitud c ON a.idestatus = c.idestatus
            LEFT JOIN tblcatusuario d ON a.usuario_creo = d.intid
        ";

        // Preparar y ejecutar la consulta
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        // Obtener los resultados como un array asociativo
        $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Retornar los resultados en formato JSON
        return json_encode($solicitudes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } catch (PDOException $e) {
        // Manejo de errores
        return json_encode(["error" => "Ocurrió un error con la base de datos: " . $e->getMessage()]);
    }
}


?>