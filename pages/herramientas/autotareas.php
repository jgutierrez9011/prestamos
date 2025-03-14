<?php
require_once  '../usuarios/reg.php';
require_once 'fnautotarea.php'
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
  <!-- DataTables -->
  <link rel="stylesheet" href="../../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="../../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="../../plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="../../plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="../../plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="../../plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">

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
            <h1>Catalogo de Auto tareas</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Herramientas</a></li>
              <li class="breadcrumb-item active">Auto Tareas</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>



    <!-- Main content -->
    <section class="content">

    <div class="row">
      <div class="col-md-12 mb-12">
         <div id="resultados_ajax"></div>
      </div>
    </div>

   <!-- <div id="container" style="width: 600px; height: 400px;"></div> -->

   <br>

   <div class="row">
    <div class="col-12">
      <div class="d-flex justify-content-end">
        <a href="#new" role="button" class="btn btn-primary btn-sm ml-auto" data-toggle="modal"><strong><i class="fa fa-plus"></i> Auto Tarea</strong></a>
      </div>
    </div>
  </div>

  <br>

  <?php require_once 'maestroautotarea.php'; ?>

  <br>

      <!-- Default box -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Buscar por :</h3>

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

        <div class="row">

            <div class="col-12 col-sm-6">
            <label>Seleccion:</label>
                  <div class="input-group">
    
                      <select id="cmbtarea" class="form-control form-control-sm select2 select2-danger" data-dropdown-css-class="select2-danger" style="">
                      <option value=" "> </option>
                      <?php
                       $json = lista_autotareas();
                       $data = json_decode($json,true);
                       foreach($data as $row){
                       ?>
                       <option value="<?php echo $row["id"]; ?>"><?php echo $row["text"]; ?></option>
                       <?php }  ?>
                      </select>
              
                      <span class="input-group-btn">
                            <button class="btn btn-default btn-sm" type="button" data-toggle="modal" id="nuevo_cliente" data-target="#update"><i class="fas fa-edit"></i> Editar</button>
                      </span>
                    
                  </div>

            </div>

            

        </div>
         
          <br>
          <?php
           /* echo "idusuario =" . $_SESSION["idusuario"] . "\n" .
                 "nombreusuario = " . $_SESSION["nombreusuario"] . "\n" .
                 "correousuario = " .$_SESSION["correousuario"] . "\n" .
                 "perfilusuario = " .$_SESSION["perfilusuario"]; */?>
        </div>
        <!-- /.card-body -->
       <!-- <div class="card-footer">
          Footer
        </div> -->
        <!-- /.card-footer-->
      </div>
      <!-- /.card -->

      <div class="row">
      <div class="col-12 col-sm-12">
            <div class="card card-danger card-outline card-tabs">
              <div class="card-header p-0 pt-1 border-bottom-0">
                <ul class="nav nav-tabs" id="custom-tabs-three-tab" role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" id="custom-tabs-three-home-tab" data-toggle="pill" href="#custom-tabs-three-home" role="tab" aria-controls="custom-tabs-three-home" aria-selected="true">Procedimientos</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill" href="#custom-tabs-three-profile" role="tab" aria-controls="custom-tabs-three-profile" aria-selected="false">Consultas</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-three-messages-tab" data-toggle="pill" href="#custom-tabs-three-messages" role="tab" aria-controls="custom-tabs-three-messages" aria-selected="false">Excel</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-three-settings-tab" data-toggle="pill" href="#custom-tabs-three-graficos" role="tab" aria-controls="custom-tabs-three-settings" aria-selected="false">Gráficos</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-three-settings-tab" data-toggle="pill" href="#custom-tabs-three-settings" role="tab" aria-controls="custom-tabs-three-settings" aria-selected="false">Correos</a>
                  </li>
                </ul>
              </div>
              <div class="card-body">
                <div class="tab-content" id="custom-tabs-three-tabContent">
                  <div class="tab-pane fade show active" id="custom-tabs-three-home" role="tabpanel" aria-labelledby="custom-tabs-three-home-tab">
                     
                      <!-- TABLA DE PROCEDIMIENTOS -->
                      <div class="row">
                        <div class="col-md-12 mb-12">
                          <div id="resultados_ajax_proc"></div>
                        </div>
                      </div>

                      <br>

                      <div class="row">
                      <div class="col-12">
                        <div class="d-flex justify-content-end">
                          <a href="#new_procedimiento" role="button" id="mod_procedimiento" class="btn btn-primary btn-sm ml-auto" data-toggle="modal"><strong><i class="fa fa-plus"></i> Procedimiento</strong></a>
                        </div>
                      </div>
                      </div>

                      <br>
                      <div id="loader_det_pro"></div>
                      <br>
                      <div id="tbl_procedimientos"></div>

                  </div>
                  <div class="tab-pane fade" id="custom-tabs-three-profile" role="tabpanel" aria-labelledby="custom-tabs-three-profile-tab">
                     
                     <!-- TABLA DE CONSULTAS -->
                     <div class="row">
                        <div class="col-md-12 mb-12">
                          <div id="resultados_ajax_consulta"></div>
                        </div>
                      </div>
                     <br>

<div class="row">
 <div class="col-12">
   <div class="d-flex justify-content-end">
     <a href="#new_consulta" role="button" class="btn btn-primary btn-sm ml-auto" data-toggle="modal"><strong><i class="fa fa-plus"></i> Consulta</strong></a>
   </div>
 </div>
</div>

<br>
                     <div id="loader_det_con"></div>
                     <br>
                     <div id="tbl_consultas"></div>

                  </div>
                  <div class="tab-pane fade" id="custom-tabs-three-messages" role="tabpanel" aria-labelledby="custom-tabs-three-messages-tab">
                     
                     <!-- TABLA DE EXCEL -->
                     <div class="row">
                        <div class="col-md-12 mb-12">
                          <div id="resultados_ajax_excel"></div>
                        </div>
                     </div>
                     
                      <br>

                      <div class="row">
                      <div class="col-12">
                        <div class="d-flex justify-content-end">
                          <a href="#new_excel" role="button" class="btn btn-primary btn-sm ml-auto" data-toggle="modal"><strong><i class="fa fa-plus"></i> Excel</strong></a>
                        </div>
                      </div>
                      </div>

                      <br>

                     <div id="loader_det_excel"></div>
                     <br>
                     <div id="btn_exp_excel" style="display: none;">
                     <form action="exportarexcel.php" method="post">
                     <input type="hidden" id="dato_excel" name="dato_excel" />
                     <button id="btnexportar_xls" type="submit" class="btn btn-block bg-gradient-info">Exportar bases</button>
                     </form>
                     </div>
                     <br>
                     <div id="tbl_excel"></div>

                  </div>
                  <div class="tab-pane fade" id="custom-tabs-three-settings" role="tabpanel" aria-labelledby="custom-tabs-three-settings-tab">
                     
                     <!-- TABLA DE CORREO -->
                     <div class="row">
                        <div class="col-md-12 mb-12">
                          <div id="resultados_ajax_correo"></div>
                        </div>
                     </div>

                     <br>

                      <div class="row">
                      <div class="col-12">
                        <div class="d-flex justify-content-end">
                          <a href="#new_correo" role="button" class="btn btn-primary btn-sm ml-auto" data-toggle="modal"><strong><i class="fa fa-plus"></i> Correo</strong></a>
                        </div>
                      </div>
                      </div>

                      <br>

                     <div id="loader_det_correo"></div>
                     <br>
                     <div id="tbl_correo"></div>
                    
                  </div>
                  <div class="tab-pane fade" id="custom-tabs-three-graficos" role="tabpanel" aria-labelledby="custom-tabs-three-settings-tab">
                     

                    <h6 class="text-center">Generales de los gráficos</h6>
                     <!--  TABLA DE ENCABEZADO DE GRAFICOS -->
                     <br>

<div class="row">
 <div class="col-12">
   <div class="d-flex justify-content-end">
     <a href="#" role="button" class="btn btn-primary btn-sm ml-auto" data-toggle="modal"><strong><i class="fa fa-plus"></i> Gráfico</strong></a>
   </div>
 </div>
</div>

<br>
                     <div id="loader_det_graf_enc"></div>
                     <br>
                     <div id="tbldet_graf_enc"></div>

                     <br>
                      
                     <!-- <h6 class="text-center">Serie de datos de los gráficos</h6> -->
                      <!--  TABLA DE DETALLE DE GRAFICOS -->
                     <div id="loader_det_graf_det"></div>
                     <br>
                     <div id="tbl_det_graf_det"></div>

                  </div>
                </div>
              </div>
              <!-- /.card -->
            </div>
          </div>
      </div><!-- END CARD--> 
      
      
      <!-- The Modal -->
<div class="modal" id="myModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Modal Heading</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <div class="modal-body">
        
        <div id="loader_elemento"></div>
        <br>
        <div id="elemento"></div>
        <div class="table-responsive">

        <div id="container"></div>

        </div>
        

      
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
      </div>

    </div>
  </div>
</div>

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
<!-- DataTables  & Plugins -->
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../../plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../../plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="../../plugins/jszip/jszip.min.js"></script>
<script src="../../plugins/pdfmake/pdfmake.min.js"></script>
<script src="../../plugins/pdfmake/vfs_fonts.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="../../plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
<!-- Select2 -->
<script src="../../plugins/select2/js/select2.full.min.js"></script>

<script src="../../plugins/highcharts/highcharts.js"></script>
<script src="../../plugins/highcharts/series-label.js"></script>
<script src="../../plugins/highcharts/exporting.js"></script>
<script src="../../plugins/highcharts/export-data.js"></script>
<script src="../../plugins/highcharts/accessibility.js"></script>

<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="../../dist/js/demo.js"></script>


<script>
   

  $(function () {

    var codigo = 0;
    //Initialize Select2 Elements
    $('.select2').select2()
    $('.select3').select2()

    //Initialize Select2 Elements
    $('.select2bs4').select2({
      theme: 'bootstrap4'
    })

    

    //$('#mod_procedimiento').click(function(event) 
    //{
    //event.preventDefault(); // Evitar el comportamiento predeterminado del enlace

     // Hacer algo con los datos obtenidos
     //document.getElementById("id_auto_tarea_up").value = id;


   //});

       
    function format_tbl_procedimientos(){
    var tbl_procedimientos = $('#tblprocedimientos').DataTable({
    destroy: true,
    order: [[1, "asc"]],
    dom:'Bfrtip',
    buttons: ['copy','csv','excel','print'],
    language:{
      lengthMenu: "Mostrar _MENU_ registros por pagina",
      info: "Mostrando pagina _PAGE_ de _PAGES_",
      infoEmpty: "No hay registros disponibles",
      infoFiltered: "(filtrada de _MAX_ registros)",
      loadingRecords: "Cargando...",
      processing:     "Procesando...",
      search: "Buscar:",
      zeroRecords:    "No se encontraron registros coincidentes",
      paginate: {
        next:       "Siguiente",
        previous:   "Anterior"
      },
    }
   });
  }

   function load_det_procedimientos(codtarea){
   $('#tbl_procedimientos').html('');

   $.ajax({
      url:"fnautotarea.php",
      type:"POST",
      data:{tarea_proc:codtarea},
      beforeSend: function(objeto){
      $('#loader_det_pro').html('<img src="../image/ajax-loader.gif" alt="loading"/> Cargando...');
     },
     success:function(data){
       $("#tbl_procedimientos").html(data).fadeIn('slow');
       $('#loader_det_pro').html('');
       format_tbl_procedimientos();
     }
   })
 }

 function format_tbl_consultas(){
    var tbl_consultas = $('#tblconsultas').DataTable({
    destroy: true,
    order: [[1, "asc"]],
    dom:'Bfrtip',
    buttons: ['copy','csv','excel','print'],
    language:{
      lengthMenu: "Mostrar _MENU_ registros por pagina",
      info: "Mostrando pagina _PAGE_ de _PAGES_",
      infoEmpty: "No hay registros disponibles",
      infoFiltered: "(filtrada de _MAX_ registros)",
      loadingRecords: "Cargando...",
      processing:     "Procesando...",
      search: "Buscar:",
      zeroRecords:    "No se encontraron registros coincidentes",
      paginate: {
        next:       "Siguiente",
        previous:   "Anterior"
      },
    }
   });
  }
   
  function load_det_consultas(codtarea){

   $.ajax({
      url:"fnautotarea.php",
      type:"POST",
      data:{tarea:codtarea},
      beforeSend: function(objeto){
      $('#tbl_consultas').html('');
      $('#loader_det_con').html('<img src="../image/ajax-loader.gif" alt="loading"/> Cargando...');
     },
     success:function(data){
       $("#tbl_consultas").html(data).fadeIn('slow');
       $('#loader_det_con').html('');
       format_tbl_consultas();
     }
   })
 }


 function format_tbl_excel(){
    var tbl_consultas = $('#tblexcel').DataTable({
    destroy: true,
    order: [[1, "asc"]],
    dom:'Bfrtip',
    buttons: ['copy','csv','excel','print'],
    language:{
      lengthMenu: "Mostrar _MENU_ registros por pagina",
      info: "Mostrando pagina _PAGE_ de _PAGES_",
      infoEmpty: "No hay registros disponibles",
      infoFiltered: "(filtrada de _MAX_ registros)",
      loadingRecords: "Cargando...",
      processing:     "Procesando...",
      search: "Buscar:",
      zeroRecords:    "No se encontraron registros coincidentes",
      paginate: {
        next:       "Siguiente",
        previous:   "Anterior"
      },
    }
   });
  }
   
  function load_det_excel(codtarea){

   $.ajax({
      url:"fnautotarea.php",
      type:"POST",
      data:{tarea_excel:codtarea},
      beforeSend: function(objeto){
      $('#tbl_excel').html('');
      $('#loader_det_excel').html('<img src="../image/ajax-loader.gif" alt="loading"/> Cargando...');
     },
     success:function(data){
       $("#tbl_excel").html(data).fadeIn('slow');
       $('#loader_det_excel').html('');
       format_tbl_excel();
     }
   })
 }

 function format_tbl_correo(){
    var tbl_consultas = $('#tblcorreo').DataTable({
    destroy: true,
    order: [[1, "asc"]],
    dom:'Bfrtip',
    buttons: ['copy','csv','excel','print'],
    language:{
      lengthMenu: "Mostrar _MENU_ registros por pagina",
      info: "Mostrando pagina _PAGE_ de _PAGES_",
      infoEmpty: "No hay registros disponibles",
      infoFiltered: "(filtrada de _MAX_ registros)",
      loadingRecords: "Cargando...",
      processing:     "Procesando...",
      search: "Buscar:",
      zeroRecords:    "No se encontraron registros coincidentes",
      paginate: {
        next:       "Siguiente",
        previous:   "Anterior"
      },
    }
   });
  }
   
  function load_det_correo(codtarea){

   $.ajax({
      url:"fnautotarea.php",
      type:"POST",
      data:{tarea_correo:codtarea},
      beforeSend: function(objeto){
      $('#tbl_correo').html('');
      $('#loader_det_correo').html('<img src="../image/ajax-loader.gif" alt="loading"/> Cargando...');
     },
     success:function(data){
       $("#tbl_correo").html(data).fadeIn('slow');
       $('#loader_det_correo').html('');
       format_tbl_correo();
     }
   })
 }

 function format_tbl_graf_enc(){
    var tbl_consultas = $('#tbl_det_graf_enc').DataTable({
    destroy: true,
    order: [[1, "asc"]],
    dom:'Bfrtip',
    buttons: ['copy','csv','excel','print'],
    language:{
      lengthMenu: "Mostrar _MENU_ registros por pagina",
      info: "Mostrando pagina _PAGE_ de _PAGES_",
      infoEmpty: "No hay registros disponibles",
      infoFiltered: "(filtrada de _MAX_ registros)",
      loadingRecords: "Cargando...",
      processing:     "Procesando...",
      search: "Buscar:",
      zeroRecords:    "No se encontraron registros coincidentes",
      paginate: {
        next:       "Siguiente",
        previous:   "Anterior"
      },
    }
   });
  }
   
  function load_det_graf_enc(codtarea){

   $.ajax({
      url:"fnautotarea.php",
      type:"POST",
      data:{tarea_graf_enc:codtarea},
      beforeSend: function(objeto){
      $('#tbldet_graf_enc').html('');
      $('#loader_det_graf_enc').html('<img src="../image/ajax-loader.gif" alt="loading"/> Cargando...');
     },
     success:function(data){
       $("#tbldet_graf_enc").html(data).fadeIn('slow');
       $('#loader_det_graf_enc').html('');
       format_tbl_graf_enc();
     }
   })
 }

function load_edit_auto_tarea(codtarea){

  

    $.ajax({
      url:"fnprocautotareas.php",
      type:"POST",
      dataType: 'json',
      data:{codeditar:codtarea},
      success:function(data){
     
        
        // Iterar sobre los elementos del JSON
$.each(data, function(index, item) {
  
  var id = item.ID;
  var descripcion = item.DESCRIPCION;
  var asunto = item.ASUNTO;

  // Hacer algo con los datos obtenidos
  document.getElementById("id_auto_tarea_up").value = id;
  document.getElementById("desc_auto_tarea_up").value = descripcion;
  document.getElementById("asunto_auto_tarea_up").value = asunto;

  document.getElementById("id_procedimiento").value = id;
  document.getElementById("id_consulta").value = id;
  document.getElementById("id_excel").value = id;
  document.getElementById("id_correo").value = id;
  
});

      }
    })
}

  $('#cmbtarea').change(function()
  {
    var id = document.querySelector("#cmbtarea").value;
    var boton = document.getElementById("btn_exp_excel");
    codigo = id;
    load_det_procedimientos(id);
    load_det_consultas(id);
    load_det_excel(id);
    load_det_graf_enc(id)
    load_det_correo(id);

    load_edit_auto_tarea(id);
    
    document.getElementById("dato_excel").value = id;
    boton.style.display = "block";
  })

 
  
		$( "#frm_crear_autotarea" ).submit(function( event ) {
		  $('.guardar_datos').attr("disabled", true);
		  var parametros = $(this).serialize();
		  $.ajax({
				type: "POST",
				url: "fnprocautotareas.php",
				data: parametros,
				 beforeSend: function(objeto){
					$("#resultados_ajax").html("Mensaje: Cargando...");
				  },
				success: function(datos){
				$("#resultados_ajax").html(datos);
				$('.guardar_datos').attr("disabled", false);
				window.setTimeout(function() {
				$(".alert").fadeTo(900, 0).slideUp(500, function(){
				$(this).remove(); location.reload();});}, 5000);
        $('#new').modal('hide');
			  }
		});
		  event.preventDefault();
		});


    $( "#frm_editar_autotarea" ).submit(function( event ) {
		  $('.editar_autotarea').attr("disabled", true);
		  var parametros = $(this).serialize();
		  $.ajax({
				type: "POST",
				url: "fnprocautotareas.php",
				data: parametros,
				 beforeSend: function(objeto){
					$("#resultados_ajax").html("Mensaje: Cargando...");
				  },
				success: function(datos){
				$("#resultados_ajax").html(datos);
				$('.editar_autotarea').attr("disabled", false);
				window.setTimeout(function() {
				$(".alert").fadeTo(900, 0).slideUp(500, function(){
				$(this).remove(); location.reload();});}, 5000);
        $('#update').modal('hide');
			  }
		});
		  event.preventDefault();
		});

    $("#frm_new_procedimiento").submit(function( event ) 
    {
		  $('.save_proc').attr("disabled", true);
		  var parametros = $(this).serialize();
		  $.ajax({
				type: "POST",
				url: "fnprocautotareas.php",
				data: parametros,
				 beforeSend: function(objeto){
					$("#resultados_ajax_proc").html("Mensaje: Cargando...");
				  },
				success: function(datos){
				$("#resultados_ajax_proc").html(datos);
				$('.save_proc').attr("disabled", false);

        load_det_procedimientos(codigo);
       
        document.getElementById("txtprocedimeinto").value = "";
        $('#new_consulta').modal('hide');
        
			  }
		});
		  event.preventDefault();
		});


    $("#frm_new_consulta").submit(function( event ) 
    {
		  $('.save_consulta').attr("disabled", true);
		  var parametros = $(this).serialize();
		  $.ajax({
				type: "POST",
				url: "fnprocautotareas.php",
				data: parametros,
				 beforeSend: function(objeto){
					$("#resultados_ajax_consulta").html("Mensaje: Cargando...");
				  },
				success: function(datos){
				$("#resultados_ajax_consulta").html(datos);
				$('.save_consulta').attr("disabled", false);

        load_det_consultas(codigo);
       
        document.getElementById("txtdescripcion").value = "";
        document.getElementById("txtconsulta").value = "";
        $('#new_consulta').modal('hide');
        
			  }
		});
		  event.preventDefault();
		});

    $("#frm_new_excel").submit(function( event ) 
    {
		  $('.save_excel').attr("disabled", true);
		  var parametros = $(this).serialize();
		  $.ajax({
				type: "POST",
				url: "fnprocautotareas.php",
				data: parametros,
				 beforeSend: function(objeto){
					$("#resultados_ajax_excel").html("Mensaje: Cargando...");
				  },
				success: function(datos){
				$("#resultados_ajax_excel").html(datos);
				$('.save_excel').attr("disabled", false);

        load_det_excel(codigo);
       
        document.getElementById("txthojaexcel").value = "";
        document.getElementById("textconsultahoja").value = "";
        $('#new_excel').modal('hide');
        
			  }
		});
		  event.preventDefault();
		});


    $("#frm_new_correo").submit(function( event ) 
    {
		  $('.save_correo').attr("disabled", true);
		  var parametros = $(this).serialize();
		  $.ajax({
				type: "POST",
				url: "fnprocautotareas.php",
				data: parametros,
				 beforeSend: function(objeto){
					$("#resultados_ajax_correo").html("Mensaje: Cargando...");
				  },
				success: function(datos){
				$("#resultados_ajax_correo").html(datos);
				$('.save_correo').attr("disabled", false);

        load_det_correo(codigo);
       
        document.getElementById("txtcorreo").value = "";
        $('#new_correo').modal('hide');
        
			  }
		});
		  event.preventDefault();
		});
 

  })

  function format_tbl_query(){
    var tbl_consultas = $('#tblquery').DataTable({
    destroy: true,
    order: [[1, "asc"]],
    dom:'Bfrtip',
    buttons: ['copy','csv','excel','print'],
    language:{
      lengthMenu: "Mostrar _MENU_ registros por pagina",
      info: "Mostrando pagina _PAGE_ de _PAGES_",
      infoEmpty: "No hay registros disponibles",
      infoFiltered: "(filtrada de _MAX_ registros)",
      loadingRecords: "Cargando...",
      processing:     "Procesando...",
      search: "Buscar:",
      zeroRecords:    "No se encontraron registros coincidentes",
      paginate: {
        next:       "Siguiente",
        previous:   "Anterior"
      },
    }
   });
  }

  function view_resumen(cod,sec)
  {
    $("#myModal").modal("show");

    $.ajax({
      url:"fnautotarea.php",
      type:"POST",
      data:{codigo:cod, secuencia:sec},
      beforeSend: function(objeto){
      $('#elemento').html('');
      $('#loader_elemento').html('<img src="../image/ajax-loader.gif" alt="loading"/> Cargando...');
     },
     success:function(data){
       
       $("#elemento").html(data).fadeIn('slow');
       $('#loader_elemento').html('');
       format_tbl_query();
     }
   })

      
  }


  function view_graf(cod,sec)
  {
    $("#myModal").modal("show");
    $('#elemento').html('');
    $('#container').html('');
    $('#highcharts-data-table-0').empty();
    $('#loader_elemento').html('<img src="../image/ajax-loader.gif" alt="loading"/> Cargando...');

   $.ajax({
                url: 'fnautotarea.php',
                type:"POST",
                dataType: 'json',
                data:{clave:cod, prioridad:sec},
                success: function(data) {
                    
                  $('#loader_elemento').html('');

                    // Crear el gráfico de líneas con Highcharts
                    Highcharts.chart('container', {
                        chart: {
                            type: 'line'
                        },
                        title: {
                            text: data.title // Título del gráfico
                        },
                        xAxis: {
                            type: 'datetime',
                            dateTimeLabelFormats: {
                                day: '%e %b %Y' // Formato de etiquetas de fecha (día, mes, año)
                            },
                            title: {
                                text: data.xAxis.title // Título del eje x
                            }
                        },
                        yAxis: {
                            title: {
                                text: data.yAxis.title // Título del eje y
                            }
                        },
                        series: data.series
                    });
                }
            });

      
  }

/*$('#btnexportar_xls').click(function(event){

var id = document.querySelector("#cmbtarea").value;
var boton = document.getElementById("btn_exp_excel");

$.ajax({
  type: "POST",
  url: "fnautotarea.php",
  data: {code:id } ,
beforeSend: function(objeto){

 $("#loader_det_excel").html("Mensaje: Cargando excel...");
 boton.disabled = true;

},
success: function(data){
  
  $("#loader_det_excel").html(data);
  boton.disabled = false; 

},
error : function(xhr, status) {
 alert('Disculpe, existió un problema');
},
})

event.preventDefault();

});*/

</script>
</body>
</html>
