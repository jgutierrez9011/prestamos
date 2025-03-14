<?php
require_once  '../usuarios/reg.php';

if(isset($_POST['cmb_new_codigo']))
{
    fn_insert_autotarea($_POST['cmb_new_codigo'], $_POST["asunto_auto_tarea"], $_POST["desc_auto_tarea"]);
}

if(isset($_POST['codeditar']))
{
    fn_view_autotarea($_POST['codeditar']);
}

if(isset($_POST['id_auto_tarea_up']))
{
   fn_edit_autotarea($_POST['id_auto_tarea_up'],$_POST['desc_auto_tarea_up'],$_POST['asunto_auto_tarea_up']);
}

if(isset($_POST['id_procedimiento']))
{
    fn_new_procedimiento($_POST['id_procedimiento'], $_POST['txtprocedimeinto']);
}

if(isset($_POST['id_consulta']))
{
    fn_new_consultas($_POST['id_consulta'], $_POST['txtdescripcion'],$_POST['txtconsulta']);
}

if(isset($_POST['id_excel']))
{
    fn_new_excel($_POST['id_excel'], $_POST['textconsultahoja'], $_POST['txthojaexcel']);
}

if(isset($_POST['id_correo']))
{
fn_new_correo($_POST['id_correo'], $_POST['txtcorreo']);
}

function fn_insert_autotarea($id, $asunto, $descripcion)
{
    $conn = conexion_bd(2);

    $tipo = 'CORREO';
    $esquema = 'TRAFICO';
    $consecutivo = $id;
    $estado = 0;

    
    $sql = "INSERT INTO auto_tareas (ID, DESCRIPCION, ASUNTO, TIPO, ESQUEMA, ID_CENTINELA, ESTADO) 
            values (?, ?, ?, ?, ?, ?, ?)";

     $sentencia = $conn->prepare($sql);
     $sentencia->execute([base64_decode($id), $descripcion, $asunto, $tipo, $esquema,$consecutivo,$estado]); # Pasar en el mismo orden de los ?
     
     $cmdtuplas = $sentencia->rowCount();

     
    if($cmdtuplas == 1)
    { 
        echo "<div class='alert alert-success alert-dismissible alerta'>
              <a href='#'' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
              <strong>¡Exito!</strong> Se creo la auto tarea con exito.
              </div>";
    }
    else
   {
       echo "<div class='alert alert-warning alert-dismissible alerta'>
             <a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
             <strong>¡Disculpe!</strong> No se creo la auto tarea por favor intente nuevamente, si no reporta al administrador.
             </div>";
   }

}

function fn_view_autotarea($val)
{
    
    $val = base64_decode($val); 
    
    $conn = conexion_bd(2);

    $query = "SELECT ID , DESCRIPCION, ASUNTO FROM TRAFICO.AUTO_TAREAS WHERE ID = '$val'";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $data = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) 
    {
      
    array_push($data,$row);
    
    }
    
    echo json_encode($data);

}


function fn_edit_autotarea($id,$descripcion,$asunto)
{
    $conn = conexion_bd(2);

    $query = "UPDATE TRAFICO.AUTO_TAREAS
    SET  DESCRIPCION = '$descripcion'
        ,ASUNTO = '$asunto'
    WHERE ID = '$id'";

    $sentencia = $conn->prepare($query);
    $resultado = $sentencia->execute();
    $cmdtuplas = $sentencia->rowCount();

    if ($cmdtuplas == 1) {
        /*si se actualiza correctamente se envia token para mensaje de exito*/
        echo "<div class='alert alert-success alert-dismissible alerta'>
              <a href='#'' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
              <strong>¡Exito!</strong> Se actualizo la auto tarea con exito.
              </div>";
     } else {
        /*si no se actualiza correctamente se envia token para mensaje de exito*/
        echo "<div class='alert alert-warning alert-dismissible alerta'>
             <a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
             <strong>¡Disculpe!</strong> No se actualizo la auto tarea por favor intente nuevamente, si no reporta al administrador.
             </div>";
     }

}

function fn_prioridad_auto_tarea($codigo,$tipo)
{
    $conn = conexion_bd(2);
    // Consulta SQL para obtener el valor de una columna
    $query = "SELECT COUNT(1) + 1 SECUENCIA 
              FROM TRAFICO.AUTO_TAREAS_PRIORIDADES 
              WHERE ID = '$codigo'";

    $valor_condicion = $codigo;

    // Preparar la consulta
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':valor_condicion', $valor_condicion);

    // Ejecutar la consulta
    $stmt->execute();

    // Obtener el resultado
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    // Mostrar el valor de la columna
    $prioridad = $resultado['SECUENCIA'];

    $sql = "INSERT INTO TRAFICO.AUTO_TAREAS_PRIORIDADES (ID, TIPO, PRIORIDAD) VALUES ( ?, ?, ?)";

    $sentencia = $conn->prepare($sql);
    $sentencia->execute([$codigo, $tipo, $prioridad]); # Pasar en el mismo orden de los ?
     
    //$cmdtuplas = $sentencia->rowCount();


}

function fn_new_procedimiento($codigo, $objeto)
{
    
    $conn = conexion_bd(2);
    // Consulta SQL para obtener el valor de una columna
    $query = "SELECT (count(1) + 1) prioridad 
              from TRAFICO.AUTO_TAREAS_PROCEDURES
              where id = :valor_condicion";

    $valor_condicion = $codigo;

    // Preparar la consulta
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':valor_condicion', $valor_condicion);

    // Ejecutar la consulta
    $stmt->execute();

    // Obtener el resultado
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    // Mostrar el valor de la columna
    $prioridad = $resultado['PRIORIDAD'];
    
    if($prioridad == 1)
    {
        fn_prioridad_auto_tarea($codigo,'PROCEDURE');
    }
  
    $sql = "INSERT INTO TRAFICO.AUTO_TAREAS_PROCEDURES (ID, OBJETO, PRIORIDAD) VALUES (?, ?, ?)";

    $sentencia = $conn->prepare($sql);
    $sentencia->execute([$codigo, $objeto, $prioridad]); # Pasar en el mismo orden de los ?
     
    $cmdtuplas = $sentencia->rowCount();

    if ($cmdtuplas == 1) {
        /*si se actualiza correctamente se envia token para mensaje de exito*/
        echo "<div class='alert alert-success alert-dismissible alerta'>
              <a href='#'' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
              <strong>¡Exito!</strong> Se registro el procedimiento con exito.
              </div>";
     } else {
        /*si no se actualiza correctamente se envia token para mensaje de exito*/
        echo "<div class='alert alert-warning alert-dismissible alerta'>
             <a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
             <strong>¡Disculpe!</strong> No se registro el procedimiento por favor intente nuevamente, si no reporta al administrador.
             </div>";
     }

}


function fn_new_consultas($codigo, $descripcion, $consulta)
{
    try {
        $conn = conexion_bd(2);
    // Consulta SQL para obtener el valor de una columna
    $query = "SELECT (count(1) + 1) prioridad 
              from TRAFICO.AUTO_TAREAS_CONSULTAS
              where id = :valor_condicion";

    $valor_condicion = $codigo;

    // Preparar la consulta
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':valor_condicion', $valor_condicion);

    // Ejecutar la consulta
    $stmt->execute();

    // Obtener el resultado
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    // Mostrar el valor de la columna
    $prioridad = $resultado['PRIORIDAD'];
    
    if($prioridad == 1)
    {
        fn_prioridad_auto_tarea($codigo,'CONSULTA');
    }
  
    $sql = "INSERT INTO TRAFICO.AUTO_TAREAS_CONSULTAS (ID, DESCRIPCION, CONSULTA, PRIORIDAD) VALUES (:id, :descrip, :consult, :priori)";

    $sentencia = $conn->prepare($sql);
    $sentencia->bindParam(':id', $codigo);
    $sentencia->bindParam(':descrip', $descripcion);
    $sentencia->bindParam(':consult', $consulta);
    $sentencia->bindParam(':priori', $prioridad);
    $sentencia->execute(); # Pasar en el mismo orden de los ?
     
    print_r($sentencia);

    $cmdtuplas = $sentencia->rowCount();

    if ($cmdtuplas == 1) {
        
        echo "<div class='alert alert-success alert-dismissible alerta'>
              <a href='#'' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
              <strong>¡Exito!</strong> Se registro la consulta con exito.
              </div>";
     } else {
        
        echo "<div class='alert alert-warning alert-dismissible alerta'>
             <a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
             <strong>¡Disculpe!</strong> No se registro la consulta por favor intente nuevamente, si no reporta al administrador.
             </div>";
     }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();

        $errorInfo = $sentencia->errorInfo();
        echo "Error Code: " . $errorInfo[0] . "<br>";
        echo "SQL State: " . $errorInfo[1] . "<br>";
        echo "Error Message: " . $errorInfo[2] . "<br>";
    }
    

}

function fn_new_excel($codigo, $consulta, $nombrehoja)
{
    
    $conn = conexion_bd(2);
    // Consulta SQL para obtener el valor de una columna
    $query = "SELECT (count(1) + 1) prioridad 
              from TRAFICO.AUTO_TAREAS_EXCEL
              where id = :valor_condicion";

    $valor_condicion = $codigo;

    // Preparar la consulta
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':valor_condicion', $valor_condicion);

    // Ejecutar la consulta
    $stmt->execute();

    // Obtener el resultado
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    // Mostrar el valor de la columna
    $prioridad = $resultado['PRIORIDAD'];
    
    if($prioridad == 1)
    {
        fn_prioridad_auto_tarea($codigo,'EXCEL');
    }
  
    $sql = "INSERT INTO TRAFICO.AUTO_TAREAS_EXCEL (ID, CONSULTA, NOMBREHOJA, PRIORIDAD) VALUES (?, ?, ?, ?)";

    $sentencia = $conn->prepare($sql);
    $sentencia->execute([$codigo, trim($consulta), $nombrehoja, $prioridad]); # Pasar en el mismo orden de los ?
     
    $cmdtuplas = $sentencia->rowCount();

    if ($cmdtuplas == 1) {
        /*si se actualiza correctamente se envia token para mensaje de exito*/
        echo "<div class='alert alert-success alert-dismissible alerta'>
              <a href='#'' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
              <strong>¡Exito!</strong> Se registro la consulta para excel con exito.
              </div>";
     } else {
        /*si no se actualiza correctamente se envia token para mensaje de exito*/
        echo "<div class='alert alert-warning alert-dismissible alerta'>
             <a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
             <strong>¡Disculpe!</strong> No se registro la consulta para excel por favor intente nuevamente, si no reporta al administrador.
             </div>";
     }

}


function fn_new_correo($codigo, $correo)
{
    
    $conn = conexion_bd(2);

    // Consulta SQL para obtener el valor de una columna
    $query = "SELECT (count(1) + 1) prioridad 
              from TRAFICO.AUTO_TAREAS_CORREOS
              where id = :valor_condicion";

    $valor_condicion = $codigo;

    // Preparar la consulta
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':valor_condicion', $valor_condicion);

    // Ejecutar la consulta
    $stmt->execute();

    // Obtener el resultado
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    // Mostrar el valor de la columna
    $prioridad = $resultado['PRIORIDAD'];
    
    if($prioridad == 1)
    {
        fn_prioridad_auto_tarea($codigo,'CORREO');
    }
  
    $sql = "INSERT INTO TRAFICO.AUTO_TAREAS_CORREOS (ID, CORREO) VALUES (?, ?)";

    $sentencia = $conn->prepare($sql);
    $sentencia->execute([$codigo, trim($correo)]); # Pasar en el mismo orden de los ?
     
    $cmdtuplas = $sentencia->rowCount();

    if ($cmdtuplas == 1) {
        /*si se actualiza correctamente se envia token para mensaje de exito*/
        echo "<div class='alert alert-success alert-dismissible alerta'>
              <a href='#'' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
              <strong>¡Exito!</strong> Se registro el correo con exito.
              </div>";
     } else {
        /*si no se actualiza correctamente se envia token para mensaje de exito*/
        echo "<div class='alert alert-warning alert-dismissible alerta'>
             <a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>
             <strong>¡Disculpe!</strong> No se registro el correo por favor intente nuevamente, si no reporta al administrador.
             </div>";
     }

}


?>