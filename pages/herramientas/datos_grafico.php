<?php
require_once  '../usuarios/reg.php';
      $conn = conexion_bd(2);

      $json = [];
      
      $query = "SELECT A.ID, A.TITULOGRAFICO, A.EJEVERTICAL, A.EJEHORIZONTAL, A.PRIORIDAD,
                B.PRIORIDAD_DET, B.NOMBREDATO, B.CONSULTADATO
                from TRAFICO.AUTO_TAREAS_GRAFICO_ENC a 
                inner join TRAFICO.AUTO_TAREAS_GRAFICO_DET b on A.ID = b.ID AND A.PRIORIDAD = B.PRIORIDAD_ENC
                WHERE A.ID = 'VOZPP001' and A.PRIORIDAD = 1
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

      $data = $series;
    // Datos de ejemplo
    /*$data = array(
        array('fecha' => '2023-01-01', 'categoria' => 'A', 'valor' => 10),
        array('fecha' => '2023-01-02', 'categoria' => 'A', 'valor' => 15),
        array('fecha' => '2023-01-03', 'categoria' => 'A', 'valor' => 20),
        array('fecha' => '2023-01-01', 'categoria' => 'B', 'valor' => 8),
        array('fecha' => '2023-01-02', 'categoria' => 'B', 'valor' => 12),
        array('fecha' => '2023-01-03', 'categoria' => 'B', 'valor' => 18),
        // Agrega más datos aquí
    );*/
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
                 //$categoryData[] = "[$fecha, $valor]";
                $categoryData[] = array($fecha, (float)$valor);
            }
        }
        //$categoryData = implode(',', $categoryData);
        //$seriesData[] = "{name: '$category', data: [$categoryData]}";
        //$data['series'][] = array('name' => $category, 'data' => $categoryData);

        $dataHighcharts['series'][] = array('name' => $category, 'data' => $categoryData);
    }

    //$seriesData = implode(',', $seriesData);
    
    //echo json_encode($seriesData);
    //echo json_encode($data);
    echo json_encode($dataHighcharts);
    ?>