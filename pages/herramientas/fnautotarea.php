<?php
ini_set('memory_limit', '4000M');
require_once  '../usuarios/reg.php';
require_once '../../plugins/PHPExcel/Classes/PHPExcel/IOFactory.php';


if(isset($_POST['tarea_proc']))
{   
    lista_procedimientos($_POST['tarea_proc']);
}

if(isset($_POST['tarea']))
{   
    lista_consultas($_POST['tarea']);
}

if(isset($_POST['tarea_excel']))
{   
    lista_excel($_POST['tarea_excel']);
}

if(isset($_POST['tarea_correo']))
{   
  lista_correos($_POST['tarea_correo']);
}

if(isset($_POST['tarea_graf_enc']))
{   
  lista_grafico_det($_POST['tarea_graf_enc']);
}

if((isset($_POST['codigo'])) && (isset($_POST['secuencia'])))
{   
  create_table($_POST['codigo'],$_POST['secuencia']);
}

if((isset($_POST['clave'])) && (isset($_POST['prioridad'])))
{   
  create_grafic($_POST['clave'],$_POST['prioridad']);
}


/*if(isset($_POST['code']))
{
  create_excel($_POST['code']);
}*/


/*function enviarVariablesPorPost($url, $variables) {
  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_POSTFIELDS, $variables);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  $respuesta = curl_exec($curl);
  curl_close($curl);
  return $respuesta;
}


$url = 'http://www.ejemplo.com/receptor.php';
$variables = array(
  'variable1' => 'valor1',
  'variable2' => 'valor2',
  'variable3' => 'valor3'
);

$respuesta = enviarVariablesPorPost($url, $variables);
echo $respuesta;*/
// Función de comparación para ordenar por fecha, categoría y valor ascendente
function compararFechasCategoriasValores($a, $b) {
  // Compara las fechas
  $resultadoFecha = strcmp($a['fecha'], $b['fecha']);
  if ($resultadoFecha != 0) {
      return $resultadoFecha;
  }

  // Si las fechas son iguales, compara las categorías
  $resultadoCategoria = strcmp($a['categoria'], $b['categoria']);
  if ($resultadoCategoria != 0) {
      return $resultadoCategoria;
  }

  // Si las categorías son iguales, compara los valores
  return $a['valor'] - $b['valor'];
}

/*funcion que genera los gráficos */
function create_grafic($codigo,$prioridad)
{

      $conn = conexion_bd(2);
      
      $query = "SELECT A.ID, A.TITULOGRAFICO, A.EJEVERTICAL, A.EJEHORIZONTAL, A.PRIORIDAD,
                B.PRIORIDAD_DET, B.NOMBREDATO, B.CONSULTADATO
                from TRAFICO.AUTO_TAREAS_GRAFICO_ENC a 
                inner join TRAFICO.AUTO_TAREAS_GRAFICO_DET b on A.ID = b.ID AND A.PRIORIDAD = B.PRIORIDAD_ENC
                WHERE A.ID = '$codigo' and A.PRIORIDAD = $prioridad
                ORDER BY A.PRIORIDAD, B.PRIORIDAD_ENC ASC";
      
      $stmt = $conn->prepare($query);
      $stmt->execute();
      
      $series = array();
      $eje_vertical="";
      $eje_horizontal="";
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          
          $titulo_grafico = $row["TITULOGRAFICO"];
          $serie_datos = $row["NOMBREDATO"];
          $scrip_datos = $row["CONSULTADATO"];
          $eje_vertical = $row["EJEVERTICAL"];
          $eje_horizontal = $row["EJEHORIZONTAL"];
      
          $stmt_datos = $conn->prepare($scrip_datos);
          $stmt_datos->execute();
          
      
          while ($row_datos = $stmt_datos->fetch(PDO::FETCH_ASSOC))
          {
      
            array_push($series,array('fecha' => $row_datos["FECHA"], 'categoria' => $serie_datos, 'valor' => $row_datos["VALOR"]));

          }
      
      }

      // Ordenar el array usando la función de comparación personalizada
      uasort($series, 'compararFechasCategoriasValores');

      $data = $series;
    // Preparar los datos para Highcharts
$dataHighcharts = array(
    'title' => $titulo_grafico,
    'xAxis' => array(
        'title' => $eje_horizontal
    ),
    'yAxis' => array(
        'title' => $eje_vertical 
    ),
    'series' => array()
);

    // Obtener las categorías únicas
    $categories = array_unique(array_column($data, 'categoria'));

    // Preparar los datos para Highcharts
    $seriesData = array();
    foreach ($categories as $category) {
        $categoryData = array();
        foreach ($data as $item) {
            if ($item['categoria'] === $category) {
                $fecha = strtotime($item['fecha']) * 1000; // Convierte la fecha a milisegundos UNIX
                $valor = $item['valor'];
                $categoryData[] = array($fecha, (float)$valor);
            }
        }

        $dataHighcharts['series'][] = array('name' => $category, 'data' => $categoryData);
    }

    echo json_encode($dataHighcharts);
   
}


/*Funcion de excel que crea el excel a partir de la consulta almacenada en la tabla de auto tareas */
function create_excel($codigo)
{

   // Crear la conexión
   $conexion = conexion_bd(2);

  // Prepara la consulta
  $consulta = $conexion->prepare("SELECT * FROM TRAFICO.AUTO_TAREAS_EXCEL WHERE  ID = :valor
  ORDER BY PRIORIDAD ASC");

  // Asigna el valor al parámetro :valor
  $valor = base64_decode($codigo);
  $consulta->bindParam(':valor', $valor);
  

  // Ejecuta la consulta
  $consulta->execute();

  // Recupera todos los registros en un array
  $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);

  // Obtiene el número de tablas a crear en el excel
  $numFilas = count($resultados);
   
  $k=0;
  // Instantiate a new PHPExcel object
  $objPHPExcel = new PHPExcel();
  // Recorre los resultados utilizando un bucle for
  foreach ($resultados as $fila){

    
    // Accede a los valores de cada columna para la fila actual
    $script = $fila['CONSULTA'];
    $namesheet = $fila['NOMBREHOJA'];
    
    //Start adding next sheets
    $stmt = $conexion->prepare($script);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $numrow = count($result);

    // Add new sheet
    $objWorkSheet = $objPHPExcel->createSheet($k); //Setting index when creating
        
    // Obtener metadatos de las columnas
    $columnas = array();
    for ($i = 1; $i < $stmt->columnCount(); $i++) {
    $meta = $stmt->getColumnMeta($i);
    $columnas[] = $meta['name'];
    }


    // Initialise the Excel row number
    $rowCountcolum = 1;
    $column = 'A';
    

    for ($i = 1; $i < count($columnas); $i++)
    {
      $nombreColumna = $columnas[$i];
      $objWorkSheet->setCellValue($column.$rowCountcolum, $nombreColumna);
      $column++;
    }

    //start while loop to get data
    $rowCount = 2;
    
    $stmt = $conexion->prepare($script);
    $stmt->execute();

    while($row = $stmt->fetch(PDO::FETCH_NUM))
    {
        $column = 'A';
        
      
        for($j=1; $j<count($columnas);$j++)
        {   
          
            if(!isset($row[$j]))
                $value = NULL;
            elseif ($row[$j] != "")
                $value = strip_tags($row[$j]);
            else
                $value = "";
            
            $objWorkSheet->setCellValue($column.$rowCount, $value);
            $column++;
        }
        $rowCount++;
    }

    // Rename sheet
    $objWorkSheet->setTitle($namesheet);
    
  }
  
  $encabezado = 'Content-Disposition: attachment;filename="'.$valor.'.xls"';
  header('Content-Type: application/vnd.ms-excel');
  header($encabezado);
  header('Cache-Control: max-age=0');
  $objWriter=PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');
  $objWriter->save('php://output');
  exit;

  
    

      
  
}

//echo create_excel("SUNYMDU=");

/*Crea la tabla en tiempo de ejecucion con la consulta definida en la tabla*/
function create_table($id,$serie)
{
  // Crear la conexión
  $conn = conexion_bd(2);

  // Establecer el modo de error de PDO a excepción
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $sentencia = "SELECT CONSULTA FROM TRAFICO.AUTO_TAREAS_CONSULTAS WHERE  ID = '$id' and PRIORIDAD = '$serie'
  ORDER BY PRIORIDAD ASC";

  $stmt = $conn->prepare($sentencia);
  $stmt->execute();

  $sql_variable="";

  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      
    $sql_variable = $row['CONSULTA'];
  }

  //$stmt = $conn->prepare($query);
  //$stmt->execute();

  $sql = $sql_variable;
  $result = $conn->query($sql);


  $tabla_html="";

// Verificar si hay resultados
//if ($result->rowCount() > 0) {
    // Obtener los nombres de las columnas
    $columns = [];
    for ($i = 0; $i < $result->columnCount(); $i++) {
        $column = $result->getColumnMeta($i);
        $columns[] = $column['name'];
    }

    // Generar la tabla HTML
    $tabla_html = "<div class='table-responsive'><table id='tblquery' class='table table-bordered table-striped'><thead>";

    // Generar la fila de encabezado con los nombres de las columnas
    $tabla_html .= "<tr>";
    foreach ($columns as $column) {
      $tabla_html .= "<th>" . $column . "</th>";
    }
    $tabla_html .= "</tr></thead><tbody>";

    // Generar las filas con los valores de la consulta
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
      $tabla_html .= "<tr>";
        foreach ($row as $value) {
          $tabla_html .= "<td>" . $value . "</td>";
        }
        $tabla_html .= "</tr>";
    }

    $tabla_html .= "</tbody></table></div>";

    echo $tabla_html;
    
//} else {
    //echo "No se encontraron resultados.";

    
//}

}

function lista_autotareas()
{
   try {
    $conn = conexion_bd(2);

  $json = [];

  $query = "SELECT UPPER(ID) ID, UPPER(ID ||' - '|| ASUNTO) descripcion 
            from trafico.auto_tareas";
  
  $stmt = $conn->prepare($query);
  $stmt->execute();

  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    
      $json[] = ['id'=>base64_encode($row['ID']), 'text'=>$row['DESCRIPCION']];

  }
   
   return json_encode($json);

   } catch (\Throwable $th) {
    throw $th;
    
   }
}


function lista_seguimiento_auto()
{
   try {
    $conn = conexion_bd(2);

  $json = [];

  $query = "SELECT A.STRDESCRIPCION || '0' ||to_char(NVL(MAX(B.NUM),0) + 1) CODIGO
  from tbl_cat_seguimiento A 
  left join
  (
    SELECT 
    REGEXP_REPLACE(ID, '[0-9]', '') AS codigo, ID,
    cast( replace( translate(lower(replace(regexp_replace(replace(ID,'.',''),' ','.',1,1),' ','')),  'abcédefghijklmnopqrstuvwxyz_-', rpad('#',26,'#')),'#','') as integer ) as num,
    COUNT(1) 
    FROM AUTO_TAREAS
    GROUP BY ID
    ORDER BY codigo,num
  ) B on A.STRDESCRIPCION = B.codigo
  GROUP BY A.STRDESCRIPCION";
  
  $stmt = $conn->prepare($query);
  $stmt->execute();

  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    
      $json[] = ['id'=>base64_encode($row['CODIGO']), 'text'=>$row['CODIGO']];
  }

   return json_encode($json);
   } catch (\Throwable $th) {
    throw $th;
   }
}


function lista_procedimientos($var)
{
    try {
      $conn = conexion_bd(2);
    
      $q = base64_decode($var);
    
      $query = "SELECT * FROM TRAFICO.AUTO_TAREAS_PROCEDURES WHERE  ID = '$q'
                ORDER BY PRIORIDAD ASC";
      
      $stmt = $conn->prepare($query);
      $stmt->execute();
      
      //$data = [];
      //$json= array();
      $tabla_encabezado = "<table id='tblprocedimientos' class='table table-bordered table-striped'>
      <thead>
      <tr>
        <th>id</th>
        <th>Objeto</th>
        <th>Prioridad</th>
        <th>Acciones</th>
    
      </tr>
      </thead><tbody>";
      
      $tabla_cuerpo ="";
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

         // $data = array('id'=>$row['ID'], 'obj'=>$row['OBJETO'], 'prioridad'=>$row['PRIORIDAD']);

         // $json["data"][]=$data;
          
            $tabla_encabezado .= "<tr>
                <td>".$row['ID']."</td>".
                "<td>".$row['OBJETO']."</td>".
                "<td>".$row['PRIORIDAD']."</td>".
                "<td></td>".
            "</tr>";
      }
       
     $tabla_procedimientos = $tabla_encabezado . $tabla_cuerpo . "</tbody><tfoot>
      <tr>
        <th>id</th>
        <th>obj</th>
        <th>prioridad</th>
        <th>Acciones</th>
      </tr>
      </tfoot>
   </table>";

   echo $tabla_procedimientos;
      //echo json_encode($json);

     

     } catch (\Throwable $th) {
        throw $th;
       }
}


function lista_consultas($var)
{
    try {
      $conn = conexion_bd(2);
    
      $q = base64_decode($var);
    
      $query = "SELECT * FROM TRAFICO.AUTO_TAREAS_CONSULTAS WHERE  ID = '$q'
                ORDER BY PRIORIDAD ASC";
      
      $stmt = $conn->prepare($query);
      $stmt->execute();
      
      //$data = [];
      //$json= array();
      $tabla_encabezado = "<table id='tblconsultas' class='table table-bordered table-striped'>
      <thead>
      <tr>
        <th>id</th>
        <th>Acciones</th>
        <th>Descripcion</th>
        <th>Consulta</th>
        <th>Prioridad</th>
        
    
      </tr>
      </thead><tbody>";

      /*$acciones = "<div class='btn-group pull-right open'>
                   <button type='button' class='btn btn-default dropdown-toggle' data-toggle='dropdown' aria-expanded='true'>Acciones <span class='fa fa-caret-down'></span></button>
                   <ul class='dropdown-menu'>
                   <li><a href='#' class='cobro' data-target='#cobrosModal' data-toggle='modal' data-id='205' id='205'><i class='fa fa-file-o'></i> Ver</a></li>
                   </ul>
                   </div>";*/
      
      $tabla_cuerpo ="";
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

          //$data = array('id'=>$row['ID'], 'obj'=>$row['OBJETO'], 'prioridad'=>$row['PRIORIDAD']);

          //$json["data"][]=$data;
            $codigo = $row['ID']; $secuencia = (string)$row['PRIORIDAD'];
          
            $tabla_encabezado .= '<tr>
                <td>'.$row['ID'].'</td>
                <td>
                    <div class="btn-group">
                        <button type="button" class="btn btn-info">Acciones</button>
                        <button type="button" class="btn btn-info dropdown-toggle dropdown-icon" data-toggle="dropdown" aria-expanded="false">
                          <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <div class="dropdown-menu" role="menu" style="">
                          <a class="dropdown-item" onclick="view_resumen(\''.$codigo.'\',\''.$secuencia.'\');">Ver</a>
                        </div>
                    </div>
                </td>'.
                '<td>'.$row['DESCRIPCION'].'</td>'.
                '<td>'.$row['CONSULTA'].'</td>'.
                '<td>'.$row['PRIORIDAD'].'</td>
                </tr>';

            
      }
       
     $tabla_consultas = $tabla_encabezado . $tabla_cuerpo . "</tbody><tfoot>
      <tr>
      <th>id</th>
      <th>Acciones</th>
      <th>Descripcion</th>
      <th>Consulta</th>
      <th>Prioridad</th>
      
      </tr>
      </tfoot>
   </table>";

   echo $tabla_consultas;
      //echo json_encode($json);

     

       } catch (\Throwable $th) {
        throw $th;
       }
}

function lista_excel($var)
{
    try {
      $conn = conexion_bd(2);
    
      $q = base64_decode($var);
    
      $query = "SELECT * FROM TRAFICO.AUTO_TAREAS_EXCEL WHERE  ID = '$q'
                ORDER BY PRIORIDAD ASC";
      
      $stmt = $conn->prepare($query);
      $stmt->execute();
      
      //$data = [];
      //$json= array();
      $tabla_encabezado = "<table id='tblexcel' class='table table-bordered table-striped'>
      <thead>
      <tr>
        <th>id</th>
        <th>Consulta</th>
        <th>Nombre de la hoja</th>
        <th>Prioridad</th>
        <th>Acciones</th>
    
      </tr>
      </thead><tbody>";
      
      $tabla_cuerpo ="";
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

          //$data = array('id'=>$row['ID'], 'obj'=>$row['OBJETO'], 'prioridad'=>$row['PRIORIDAD']);

          //$json["data"][]=$data;
          
            $tabla_encabezado .= "<tr>
                <td>".$row['ID']."</td>".
                "<td>".$row['CONSULTA']."</td>".
                "<td>".$row['NOMBREHOJA']."</td>".
                "<td>".$row['PRIORIDAD']."</td>".
                "<td></td>".
            "</tr>";
      }
       
     $tabla_consultas = $tabla_encabezado . $tabla_cuerpo . "</tbody><tfoot>
      <tr>
      <th>id</th>
      <th>Consulta</th>
      <th>Nombre de la hoja</th>
      <th>Prioridad</th>
      <th>Acciones</th>
      </tr>
      </tfoot>
   </table>";

   echo $tabla_consultas;
      //echo json_encode($json);

     

       } catch (\Throwable $th) {
        throw $th;
       }
}


function lista_correos($var)
{
    try {
      $conn = conexion_bd(2);
    
      $q = base64_decode($var);
    
      $query = "SELECT * FROM TRAFICO.AUTO_TAREAS_CORREOS WHERE  ID = '$q' ";
      
      $stmt = $conn->prepare($query);
      $stmt->execute();
      
      //$data = [];
      //$json= array();
      $tabla_encabezado = "<table id='tblcorreo' class='table table-bordered table-striped'>
      <thead>
      <tr>
        <th>Id</th>
        <th>Correo</th>
        <th>Acciones</th>
    
      </tr>
      </thead><tbody>";
      
      $tabla_cuerpo ="";
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

          //$data = array('id'=>$row['ID'], 'obj'=>$row['OBJETO'], 'prioridad'=>$row['PRIORIDAD']);

          //$json["data"][]=$data;
          
            $tabla_encabezado .= "<tr>
                <td>".$row['ID']."</td>".
                "<td>".$row['CORREO']."</td>".
                "<td></td>".
            "</tr>";
      }
       
     $tabla_consultas = $tabla_encabezado . $tabla_cuerpo . "</tbody><tfoot>
      <tr>
      <th>Id</th>
      <th>Correo</th>
      <th>Acciones</th>
      </tr>
      </tfoot>
   </table>";

   echo $tabla_consultas;
      //echo json_encode($json);

     

       } catch (\Throwable $th) {
        throw $th;
       }
}


function lista_grafico_enc($var)
{
    try {
      $conn = conexion_bd(2);
    
      $q = base64_decode($var);
    
      $query = "SELECT * FROM TRAFICO.AUTO_TAREAS_GRAFICO_ENC WHERE  ID = '$q' ";
      
      $stmt = $conn->prepare($query);
      $stmt->execute();
      
      //$data = [];
      //$json= array();
      $tabla_encabezado = "<table id='tbl_det_graf_enc' class='table table-bordered table-striped'>
      <thead>
      <tr>
        <th>Id</th>
        <th>Titulo del Grafico</th>
        <th>Titulo del eje vertical</th>
        <th>Titulo del eje horizontal</th>
        <th>Prioridad</th>
        <th>Muchos graficos</th>
        <th>Incluye hora</th>
        <th>Sub id</th>
        <th>Acciones</th>
    
      </tr>
      </thead><tbody>";
      
      $tabla_cuerpo ="";
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

          //$data = array('id'=>$row['ID'], 'obj'=>$row['OBJETO'], 'prioridad'=>$row['PRIORIDAD']);

          //$json["data"][]=$data;
          
            $tabla_encabezado .= "<tr>
                <td>".$row['ID']."</td>".
                "<td>".$row['TITULOGRAFICO']."</td>".
                "<td>".$row['EJEVERTICAL']."</td>".
                "<td>".$row['EJEHORIZONTAL']."</td>".
                "<td>".$row['PRIORIDAD']."</td>".
                "<td>".$row['MUCHOSGRAFICOS']."</td>".
                "<td>".$row['INCLUYEHORA']."</td>".
                "<td>".$row['SUB_ID']."</td>".
                "<td></td>".
            "</tr>";
      }
       
     $tabla_consultas = $tabla_encabezado . $tabla_cuerpo . "</tbody><tfoot>
      <tr>
      <th>Id</th>
      <th>Titulo del Grafico</th>
      <th>Eje vertical</th>
      <th>Eje horizontal</th>
      <th>Prioridad</th>
      <th>Muchos graficos</th>
      <th>Incluye hora</th>
      <th>Sub id</th>
      <th>Acciones</th>
      </tr>
      </tfoot>
   </table>";

   echo $tabla_consultas;
      //echo json_encode($json);

     

       } catch (\Throwable $th) {
        throw $th;
       }
}


function lista_grafico_det($var)
{
    try {
      $conn = conexion_bd(2);
    
      $q = base64_decode($var);
    
      $query = "SELECT A.ID, A.TITULOGRAFICO, A.EJEVERTICAL, A.EJEHORIZONTAL, A.PRIORIDAD,
                B.PRIORIDAD_DET, B.NOMBREDATO, B.CONSULTADATO
                from TRAFICO.AUTO_TAREAS_GRAFICO_ENC a 
                inner join TRAFICO.AUTO_TAREAS_GRAFICO_DET b on A.ID = b.ID AND A.PRIORIDAD = B.PRIORIDAD_ENC
                WHERE A.ID = '$q'
                ORDER BY A.PRIORIDAD, B.PRIORIDAD_ENC ASC";
      
      $stmt = $conn->prepare($query);
      $stmt->execute();
      
      //$data = [];
      //$json= array();
      $tabla_encabezado = "<div class='table-responsive'>
      <table id='tbl_det_graf_enc' class='table table-bordered table-striped'>
      <thead>
      <tr>
        <th>Id</th>
        <th>Acciones</th>
        <th>Titulo del Grafico</th>
        <th>Titulo del eje vertical</th>
        <th>Titulo del eje horizontal</th>
        <th>Prioridad grafico</th>
        <th>Prioridad datos</th>
        <th>Serie de datos</th>
        <th>Consulta sql</th>
        <th>Acciones</th>
    
      </tr>
      </thead><tbody>";
      
      $tabla_cuerpo ="";
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) 
      {

          //$data = array('id'=>$row['ID'], 'obj'=>$row['OBJETO'], 'prioridad'=>$row['PRIORIDAD']);

          //$json["data"][]=$data;
          
            $codigo = $row['ID']; $secuencia = $row['PRIORIDAD'];
            $tabla_encabezado .= "<tr>
                 <td>".$row['ID']."</td>".
                 '<td>
                 <div class="btn-group">
                        <button type="button" class="btn btn-info">Acciones</button>
                        <button type="button" class="btn btn-info dropdown-toggle dropdown-icon" data-toggle="dropdown" aria-expanded="false">
                          <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <div class="dropdown-menu" role="menu" style="">
                          <a class="dropdown-item" onclick="view_graf(\''.$codigo.'\',\''.$secuencia.'\');">Ver</a>
                        </div>
                    </div>
                </td>'.
                "<td>".$row['TITULOGRAFICO']."</td>".
                "<td>".$row['EJEVERTICAL']."</td>".
                "<td>".$row['EJEHORIZONTAL']."</td>".
                "<td>".$row['PRIORIDAD']."</td>".
                "<td>".$row['PRIORIDAD_DET']."</td>".
                "<td>".$row['NOMBREDATO']."</td>".
                "<td>".$row['CONSULTADATO']."</td>".
                "<td></td>".
            "</tr>";
      }
       
     $tabla_consultas = $tabla_encabezado . $tabla_cuerpo . "</tbody><tfoot>
      <tr>
      <th>Id</th>
        <th>Titulo del Grafico</th>
        <th>Titulo del eje vertical</th>
        <th>Titulo del eje horizontal</th>
        <th>Prioridad grafico</th>
        <th>Prioridad datos</th>
        <th>Serie de datos</th>
        <th>Consulta sql</th>
        <th>Acciones</th>
      </tr>
      </tfoot>
   </table></div>";

   echo $tabla_consultas;
      //echo json_encode($json);

     

       } catch (\Throwable $th) {
        throw $th;
       }
}


?>