<?php 
require_once  '../usuarios/reg.php';

   
ejecutar_procedimiento($_GET['proc']); 



function ejecutar_procedimiento($nombre_procedimiento) {
    try {
        $conn = conexion_bd(2); // Supongo que tienes una función llamada "conexion_bd" para establecer la conexión

        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Preparar la llamada al procedimiento almacenado
        $sql = "BEGIN $nombre_procedimiento; END;";
        $stmt = $conn->prepare($sql);
        
        // Respuesta exitosa (código 200)
        http_response_code(200);
        echo "Respuesta exitosa.";

        // Ejecutar el procedimiento almacenado
        $stmt->execute();
        
        echo "Ejecutando el procedimiento.";
        
        echo "Procedimiento $nombre_procedimiento ejecutado correctamente.";
        
    } catch (PDOException $e) {
        // Página no encontrada (código 404)
        http_response_code(404);
        echo "Error al ejecutar el procedimiento: " . $e->getMessage();
    }
}

?>