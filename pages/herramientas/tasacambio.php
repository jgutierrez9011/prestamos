<?php
require_once  '../usuarios/reg.php';
require_once '../cn.php';


//ini_set("soap.wsdl_cache_enabled", 0);
  /*
   * @EXAMPLE BCN MODIFICADO POR JHONNY F. GUTIERREZ  correo: jhonfc9011@gmail.com
   * Ejemplo de utilización del servicio WSDL, con PHP
   */

  /*
   * CODIGO CLIENTE WSDL DE PHP
   */



//print_r($base_de_datos);

  $servicio = "https://servicios.bcn.gob.ni/Tc_Servicio/ServicioTC.asmx?WSDL";
  $parametros = array();
  $mensaje ="";


  if (isset($_GET['Consultar']) && $_GET['Consultar'] == "Mes")
  {

if(isset($_POST['Year']) && isset($_POST['Month']))
{
  $parametros['Ano'] = (int)$_POST['Year'];
  $parametros['Mes'] = (int)$_POST['Month'];


  $ValorTasaMes = "";

  $options = [
  'cache_wsdl'     => WSDL_CACHE_NONE,
  'trace'          => 1,
  'stream_context' => stream_context_create(
      [
          'ssl' => [
              'verify_peer'       => false,
              'verify_peer_name'  => false,
              'allow_self_signed' => true
          ]
      ]
  ),
 'exceptions' => true,
];

  $client = new SoapClient($servicio, $options);

  $result = $client->RecuperaTC_Mes($parametros);

  $Class = (array) $result->RecuperaTC_MesResult;
  $ValorDelXML = $Class['any'];
  $xml = simplexml_load_string($ValorDelXML);
  $array = (array) $xml;
  $fecha = "";
  $tasa = "";
  $count=0;

  $fecha_array= array();
  $tasa_array= array();


  foreach ($array as $key => $a) {
                   //Recorremos el arreglo con todos los Datos
      foreach ($a as $key2 => $aa) {                      //Con este For, recorremos Los Dias del Mes

          foreach ($aa as $key3 => $a3)
          {                 //Con este for, recorremos las Fechas y Sus valores

              if ($key3 == "Fecha")
              {
                  array_push($fecha_array,$a3);
              }

              if ($key3 == "Valor")
              {
                  array_push($tasa_array,$a3);
              }
              if ($key3 == "Fecha" || $key3 == "Valor")
                  $ValorTasaMes .= ' ' . $key3 . '-' . $a3;;


          }
          //Terminado este For, pasa a la Siguiente Fecha
          $ValorTasaMes .='
';
      }

  }

  $anio = (int)$_POST['Year'];
  $mes = (int)$_POST['Month'];

  $conn = conexion_bd(2);
  $stmt = $conn->prepare("DELETE FROM trafico.tblcattasacambio WHERE EXTRACT(MONTH FROM FECHA) = :mes AND EXTRACT(YEAR FROM FECHA) = :anio");
  $stmt->bindValue(':mes',  $mes, PDO::PARAM_INT);
  $stmt->bindValue(':anio', $anio, PDO::PARAM_INT);
  $stmt->execute();
  $conn = null;

  for ($i = 0; $i <= count($fecha_array) - 1; $i++)
  {
    //pg_query(conexion_bd(1),"INSERT INTO public.tblcattasacambio(fecha, monto) VALUES ('$fecha_array[$i]',$tasa_array[$i])") ;
    $conn = conexion_bd(2);

    $sequence = 'secuencia_tc';
    $stmt = $conn->prepare("SELECT $sequence.NEXTVAL FROM DUAL");
    $stmt->execute();
    $nextVal = $stmt->fetchColumn();

    $stmt = $conn->prepare("INSERT INTO trafico.tblcattasacambio (ID, FECHA, MONTO) VALUES (:val1, :val2, :val3)");
    $newdate = date("d-m-Y", strtotime($fecha_array[$i]));
    $stmt->bindValue(':val1', $nextVal, PDO::PARAM_INT);
    $stmt->bindValue(':val2', $newdate, PDO::PARAM_STR);
    $stmt->bindValue(':val3', $tasa_array[$i], PDO::PARAM_STR);
    if(!($stmt->execute()))
    {
      $mensaje = "Error de insercion. ". "Secuencia: " .$nextVal ." fecha: " . $newdate ." valor:" . $tasa_array[$i]. "\n";
    }
    //$conn->commit();

    $conn = null;
  }

    $mensaje =  "<div class='alert alert-success'>
                 <strong>Exito!</strong> Se importo la tasa de cambio del mes $mes de $anio con exito!
                 </div>";

}



  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php require_once '../../titulo.php'; ?> | Blank Page</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<!-- Site wrapper -->
<div class="wrapper">
  <!-- Navbar -->
<!-- INICIA EL MENU -->
<?php require_once '../../menu.php'; ?>
<!-- TERMINA EL MENU -->

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Tipo de cambio emitido por el Banco central de Nicaragua</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Herramientas</a></li>
              <li class="breadcrumb-item active">Tasa de cambio</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">

      <?php
      if(isset($mensaje)){echo $mensaje;}
      ?>

      <!-- Default box -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Importar tasa de cambio mensual</h3>

          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
              <i class="fas fa-minus"></i>
            </button>
            <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
          <br>
          <form action="tasacambio.php?Consultar=Mes" method="POST">

            <div class="row">
              <div class="col-md-3 mb-3">
                <label for="email">A&ntilde;o</label>
                <input type="text" class="form-control" name="Year" id="Year" value="" size="10" required />
              </div>
              <div class="col-md-3 mb-3">
                <label for="pwd">Mes:</label>
                <input type="text" class="form-control" name="Month" id="Month" value="" size="10" required/>
              </div>
            </div>

            <div class="form-group">
              <label for="pwd">Resultado:</label>
              <textarea class="form-control" name="" rows="2" cols="35" readonly="readonly"><?php  if(isset($ValorTasaMes)) { echo $ValorTasaMes; }; ?></textarea>
            </div>

            <input type="submit" class="btn btn-default" name="Consultar" value="Insertar tasa de cambio" />

          </form>
          <script>
              var Fecha=new Date();   //Declaramos una variable para tomar las Fechas
              var Ano=Fecha.getFullYear();    //Tomamos el año actual en la variable "Ano""
              var Mes=Fecha.getMonth()+1;   //Tomamos el mes actual en la variable "Mes"
              //Le sumamos 1, por que toma como mes inicial "0"
              document.getElementById('Year').value = Ano;        //Asignamos al campo de texto "Year" el valor del Año
              document.getElementById('Month').value = Mes;       //Asignamos al campo de texto "Month" el valor del Mes
          </script>
        </div>
        <br>
        <!-- /.card-body -->
        <div class="card-footer">
          Footer
        </div>
        <!-- /.card-footer-->
      </div>
      <!-- /.card -->

    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- FOOTER -->
  <?php require_once '../../footer.php'; ?>
  <!-- FOOTER -->

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="../../dist/js/demo.js"></script>
</body>
</html>
