<?php
require_once  '../usuarios/reg.php';

if(isset($_POST['tarea_subpro']))
{   
    lista_subprocesos($_POST['tarea_subpro']);
}

if(isset($_GET['proc']))
{
$id_proc = $_GET['proc'];

exec("php fnprocedimientos.php?proc=$id_proc > /dev/null 2>&1 &");
}

// Recibe los datos enviados por POST
$data = json_decode(file_get_contents("php://input"), true);

//print_r($data);

if(isset($data['id_sp']))
{   
    ejecutar_procedimiento($data['id_sp']);
}



function lista_subprocesos($var)
{
    try {
      $conn = conexion_bd(2);
    
    
      $query = "SELECT * from tblsubprocesos where id_procesos = '$var'";
      
      $stmt = $conn->prepare($query);
      $stmt->execute();
      
      //$data = [];
      //$json= array();
      $tabla_encabezado = "<table id='tblsubprocesos' class='table table-bordered table-striped'>
      <thead>
      <tr>
        <th>Fuente</th>
        <th>Estado</th>
        <th>Fecha</th>
        <th>Log</th>
    
      </tr>
      </thead><tbody>";
      
      $tabla_cuerpo ="";
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

         // $data = array('id'=>$row['ID'], 'obj'=>$row['OBJETO'], 'prioridad'=>$row['PRIORIDAD']);

         // $json["data"][]=$data;
          
            $tabla_encabezado .= "<tr>
                <td>".$row['NOMBRECORTO']."</td>".
                "<td>".$row['ESTADO']."</td>".
                "<td>".$row['FECHA']."</td>".
                "<td>".$row['LOG']."</td>".
            "</tr>";
      }
       
     $tabla_procedimientos = $tabla_encabezado . $tabla_cuerpo . "</tbody><tfoot>
      <tr>
        <th>Fuente</th>
        <th>Estado</th>
        <th>Fecha</th>
        <th>Log</th>
      </tr>
      </tfoot>
   </table>";

   echo $tabla_procedimientos;
      //echo json_encode($json);

     

     } catch (\Throwable $th) {
        throw $th;
       }
}


/*function ejecutar_procedimiento($nombre_procedimiento) {

    try {
        $conn = conexion_bd(2);

        //$pdo = new PDO($dsn, $usuario, $contrasena);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Preparar la llamada al procedimiento almacenado
        $sql = "BEGIN $nombre_procedimiento; END;";
        $stmt = $conn->prepare($sql);

        // Ejecutar el procedimiento almacenado
        $stmt->execute();

        echo "Procedimiento $nombre_procedimiento ejecutado correctamente.";
    } catch (PDOException $e) {
        echo "Error al ejecutar el procedimiento: " . $e->getMessage();
    }
}*/

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