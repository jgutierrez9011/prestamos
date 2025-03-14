<?php
require_once  '../usuarios/reg.php';
require_once '../cn.php';



function view_metric ()
{

$conn = conexion_bd(2);

$query = "SELECT to_char(to_DATE(dias.periodo,'RRRR-MM-DD'),'RRRR-MM-DD') periodo,
       dato16.periodo Periodo_dato16, dato16.fecha_carga fecha_carga_16, dato16.\"0016\" valor_dato_16,
       dato48.periodo Periodo_dato48, dato48.fecha_carga fecha_carga_48, dato48.\"0048\" valor_dato_48,
       dato48.\"0048\"- dato16.\"0016\" diferencia
from
(
select periodo from tbl_metricas group by periodo
) dias
left join
(
SELECT *
FROM
(
         select a.Id_dato, a.periodo, a.fecha_carga, a.valor
         from tbl_metricas a
         inner join vw_metricas b
         on b.Id_dato = a.Id_dato and b.periodo = a.periodo and a.fecha_carga = b.FECHA_CARGA and a.TECNOLOGIA = b.tecnologia and a.servicio = b.servicio
         where a.Id_dato in ('0016') and a.TECNOLOGIA = '0001' and a.servicio in ('0003','0008','0009','0009','0010') /*and a.periodo = '20220517'*/
)
PIVOT
(
  sum(valor) FOR Id_dato IN ('0016' as \"0016\")
)
--where to_number(periodo,'99999999') between 20220512 and 20220512
order by periodo asc
) dato16 on dias.periodo = dato16.periodo
left join
(
SELECT *
FROM
(        select a.Id_dato, a.periodo, a.fecha_carga, a.valor
         from tbl_metricas a
         inner join vw_metricas b
         on b.Id_dato = a.Id_dato and b.periodo = a.periodo and a.fecha_carga = b.FECHA_CARGA and a.TECNOLOGIA = b.tecnologia and a.servicio = b.servicio
         where a.Id_dato in ('0048') and a.TECNOLOGIA = '0001' and a.servicio in ('0003','0008','0009','0009','0010') /*and a.periodo = '20220517'*/
)
PIVOT
(
  sum(valor) FOR Id_dato IN ('0048' as \"0048\")
)
/*where to_number(periodo,'99999999') between 20220501 and 20220520*/
order by periodo asc
) dato48 on dias.periodo = dato48.periodo
where length(dias.periodo) = 8 and substr(dias.periodo,7,8) not in ('51','65') and substr(dias.periodo,1,6) = '202303'
order by dias.periodo asc";

$stmt = $conn->prepare($query);
$stmt->execute();

$data = array();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  /*  foreach ($row as $item) {
      array_push($data,$item);
      array_push($data,($item !== null ? htmlentities($item, ENT_QUOTES) : "&nbsp;"));
    }*/
array_push($data,$row);

}

return json_encode($data);

}

function fn_lista_periodos()
{
   try {
    $conn = conexion_bd(2);

  $json = [];

  $query = "WITH Months AS (
    SELECT ADD_MONTHS(TRUNC(SYSDATE, 'MM'), -(LEVEL - 1)) AS Month
    FROM dual
    CONNECT BY LEVEL <= 12
  )
  SELECT TO_CHAR(Month, 'YYYYMM') AS PERIODO
  FROM Months";
  
  $stmt = $conn->prepare($query);
  $stmt->execute();

  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    
      $json[] = ['id'=>$row['PERIODO'], 'text'=>$row['PERIODO']];

      //echo $row['ID'] .','. $row['DESCRIPCION'];
  }
   //print_r($json);
   
   return json_encode($json);
   } catch (\Throwable $th) {
    throw $th;
    
   }
}

function fn_lista_metricas()
{
   try {
    $conn = conexion_bd(2);

  $json = [];

  $query = "SELECT DISTINCT STRMETRICA FROM tbl_parametros";
  
  $stmt = $conn->prepare($query);
  $stmt->execute();

  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    
      $json[] = ['id'=>$row['STRMETRICA'], 'text'=>$row['STRMETRICA']];

      //echo $row['ID'] .','. $row['DESCRIPCION'];
  }
   //print_r($json);
   
   return json_encode($json);
   } catch (\Throwable $th) {
    throw $th;
    
   }
}

/*Crea la tabla en tiempo de ejecucion con la consulta definida en la tabla*/
function create_table($consulta)
{
  // Crear la conexión
  $conn = conexion_bd(2);

  // Establecer el modo de error de PDO a excepción
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $sql = $consulta;
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
    $tabla_html = "<div class='table-responsive'><table id='example1' class='table table-bordered table-striped'><thead>";

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

function fn_consultar_metricas($metrica,$mes)
{
  $conn = conexion_bd(2);

  $query = "SELECT STRMETRICA, STRSENTENCIA, FILTROS  FROM tbl_parametros WHERE strmetrica = '$metrica'";

  $stmt = $conn->prepare($query);
  $stmt->execute();
  
  //$data = array();
  $sentencia_sql = "";

  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    
      //echo $row['STRMETRICA'] . '-' . $row['STRSENTENCIA'] . '-' . $row['FILTROS'] ;

      $sentencia_sql = $row['STRSENTENCIA'] .' '. $row['FILTROS'] ;
  
  }
  
  $periodo = $mes;

  $sentencia_sql = str_replace("argumento1", $periodo, $sentencia_sql);

  //print($sentencia_sql);

  /*$stmt = $conn->prepare($sentencia);
  $stmt->execute();

  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
 
  array_push($data,$row);
  
  }
  
  return (json_encode($data));*/

  create_table($sentencia_sql);
}

//fn_consultar_metricas('T3','202310');

 ?>
