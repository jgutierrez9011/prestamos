<?php
ini_set('memory_limit', '4000M');
require_once  '../usuarios/reg.php';
require_once '../../plugins/PHPExcel/Classes/PHPExcel/IOFactory.php';

if (isset($_POST['dato_excel'])){
   

    $codigo = $_POST['dato_excel'];
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
    

      
  

?>