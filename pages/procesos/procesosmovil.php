<?php
require_once  '../usuarios/reg.php';
require_once '../cn.php';
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
            <h1>Procesos de servicio movil</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Procesos</a></li>
              <li class="breadcrumb-item active">servicios movil</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>



    <!-- Main content -->
    <section class="content">

      <!-- Default box -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Listado</h3>

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

           <form class="needs-validation" method="post" action="">
             <!-- Primera linea de campos en el fromulario-->

            <div class="row">
                  <div class="col-md-4">
                     <a href="usuarios.php" class="btn btn-primary" role="button">Agregar procedimiento</a>
                  </div>
            </div>

           </form>
           <br>
           <div id="resultado"></div>
           <br>
           <div class="row">
		   
		   <div class='row table-responsive'>
                             <table class='table table-bordered table-striped' id='mitabla' style='width:100%'>
                         <thead>
                         <tr>
                         <th><p class='small'><strong>ID PROCESO</strong></p></th>
                         <th><p class='small'><strong>PROCESO ORACLE</strong></p></th>
                         <th><p class='small'><strong>PROCESO UNIX</strong></p></th>
                         <th><p class='small'><strong>OPCIONES</strong></p></th>
                         <th><p class='small'><strong>ESTADO</strong></p></th>
                         <th><p class='small'><strong>DESCRIPCION</strong></p></th>
                         <th><p class='small'><strong>LOG</strong></p></th>


                         </tr>
                         </thead>
                         <tbody>
             <?php
			 
			 $conn = conexion_bd(2);

             $query = "SELECT ID, NOMBRECORTO, NOMBRELARGO, LOG, DESCRIPCION, ESTADO, FECHA 
             FROM TBLPROCESOS
             WHERE UPPER(TRIM(ID)) LIKE '%SM%'
             ORDER BY ID ASC";
												 
             $stmt = $conn->prepare($query);
             $stmt->execute();
			 
			 $data = array();
		     while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ;?> 
                     <tr>
                      <td><a><?php echo $row['ID']; ?></a></td>
                      <td><a><?php echo $row['NOMBRECORTO'];?></a><br><small>Ejecutado: <?php echo $row['FECHA'];?></small></td>
										  <td><?php echo $row['NOMBRELARGO'];?></td>
										  <td>
                      
                       <!-- Agrupación de botones -->
                        <div class="btn-group" role="group">
                        <!-- <button type="button" class="btn btn-sm btn-secondary"><i class="fa fa-list-ul"></i></button> -->
                        <a href="#subprocesos" role="button" class="btn btn-sm btn-secondary sub_proceso" title="Ver Sub Procesos" data-toggle="modal" data-id="<?php echo $row['ID']; ?>"><i class="fa fa-list-ul"></i></a>
                        <?php if ($row['ESTADO'] <> 100) {;?>
                          <img src="../image/ajax-loader.gif" alt="loading"/>
                          <?php }else {;?>
                           <!-- <button type="button" class="btn btn-sm btn-info popConfirm" title="Ejecutar Procedimiento" id="<?php echo $row['NOMBRECORTO'];?>"><i class="fa fa-database"></i></button> -->
                           <a href="#" role="button" class="btn btn-sm btn-info popConfirm" title="Ejecutar Procedimiento" id="<?php echo $row['NOMBRECORTO'];?>"><i class="fa fa-database"></i></a>
                          <?php };?>
                        
                        <button type="button" class="btn btn-sm btn-success popConfirmlog" title="Limpiar Log"><i class="fa fa-eraser"></i></button>
                        <a href="#verlog" role="button" class="btn btn-sm btn-warning" title="Ver Log" data-toggle="modal"><i class="fas fa-eye"></i></a>
                        <!-- <button type="button" class="btn btn-sm btn-warning" title="Ver Log"><i class="fas fa-eye"></i></button>-->
                        </div>
                        
                        

                      </td>
										  <td>
                      <div class="progress progress-xs">
                          <?php if ($row['ESTADO'] <> 100) {;?>
                          <div class="progress-bar bg-danger" style="width: <?php echo $row['ESTADO'];?>%"></div>
                          <?php }else {;?>
                          <div class="progress-bar bg-success" style="width: <?php echo $row['ESTADO'];?>%"></div>
                          <?php };?>
                      </div>
                      <br><small><?php echo $row['ESTADO'];?>% Completado</small>
                      </td>
                      <td><?php echo $row['DESCRIPCION'];?></td>
                      <td><?php echo $row['LOG'];?></td>
                     </tr>
                                          

              
			   <?php }; ?>
			   </tbody>
                                          </table>
                                          </div>
         <br>
           </div>

        </div>
        <!-- /.card-body -->
        <div class="card-footer">

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

  <!-- Modal de Sub procesos-->
  <div class="modal fade" id="subprocesos" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">

        <div class="modal-header">
          <h4 class="modal-title" id="myModalLabel"><div id="titulo_subproceso"></div></h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">

         <!-- TABLA DE PROCEDIMIENTOS -->
         <div class="row">
                        <div class="col-md-12 mb-12">
                          <div id="resultados_ajax_subpro"></div>
                        </div>
         </div>

         <br>
                      <div id="loader_det_subpro"></div>
                      <br>
                      <div id="tbl_subprocesos"></div>


        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </div>
  </div>


    <!-- Modal de Ver log-->
    <div class="modal fade" id="verlog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">

        <div class="modal-header">
          <h4 class="modal-title" id="myModalLabel"><div id="titulo_log"></div></h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">

               <p class="excerpt">
                                       <ul class="nav nav-tabs justify-content-end bar_tabs" id="myTab" role="tablist">
                                           <li class="nav-item">
                                               <a class="nav-link active" id="Oracle-tab" data-toggle="tab" href="#Oracle1" role="tab" aria-controls="Oracle" aria-selected="true">Oracle</a>
                                           </li>
                                           <li class="nav-item">
                                               <a class="nav-link" id="Unix-tab" data-toggle="tab" href="#Unix1" role="tab" aria-controls="Unix" aria-selected="false">Unix</a>
                                           </li>

                                       </ul>
                                       <div class="tab-content" id="myTabContent">
                                           <div class="tab-pane fade active show" id="Oracle1" role="tabpanel" aria-labelledby="Oracle-tab">

                                               <textarea id="IdTextareaO" class="form-control" rows="10" style="white-space: pre-line; white-space: pre-wrap;">Cargando...</textarea>

                                           </div>
                                           <div class="tab-pane fade" id="Unix1" role="tabpanel" aria-labelledby="Unix-tab">

                                               <textarea id="IdTextareaU" class="form-control" rows="10" style="white-space: pre-line; white-space: pre-wrap;">Cargando...</textarea>

                                           </div>

                                       </div>
              </p>
              
              <div class="progress progress-sm">
                     <div class="progress-bar bg-success" style="width: 100%">100% completado</div>
              </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </div>
  </div>

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
<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="../../dist/js/demo.js"></script>
<!-- Page specific script -->
<script>
  $(function () {
    $("#mitabla").DataTable({
      "responsive": true, "lengthChange": false, "autoWidth": false,
      "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    $('#example2').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": false,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": true,
    });
  });
</script>

<script>

$(document).ready(function(){

  function format_tbl_subprocedimientos(){
    var tbl_procedimientos = $('#tblsubprocesos').DataTable({
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

       function load_det_procedimientos(codsubpro){
          $('#tbl_subprocesos').html('');
          $('#titulo_subproceso').html('Lista de sub-procesos '+codsubpro);

          $.ajax({
              url:"fnprocesosmovil.php",
              type:"POST",
              data:{tarea_subpro:codsubpro},
              beforeSend: function(objeto){
              $('#loader_det_subpro').html('<img src="../image/ajax-loader.gif" alt="loading"/> Cargando...');
            },
            success:function(data){
              $("#tbl_subprocesos").html(data).fadeIn('slow');
              $('#loader_det_subpro').html('');
              format_tbl_subprocedimientos();
            }
          })
        }

        $(document).on('click', '.sub_proceso', function(){

          var id_proceso = $(this).data("id");

          //alert(id_proceso);

          load_det_procedimientos(id_proceso);
          /*var estado = $("#estado_" + employee_id).val();

          $('#idempleado').val(employee_id);
          $('#estado_usuario').val(estado);

            if(estado == '1'){
              $("#inactivar").show();
              $("#activar").hide();
            }else {
              $("#inactivar").hide();
              $("#activar").show();
            }*/

          });

          function enviarVariableAsincrona($valor) {

            // Valor de la variable que deseas enviar
            var proc = $valor; // Puedes cambiar esto según tus necesidades

            // Crear una instancia de XMLHttpRequest
            var xhttp = new XMLHttpRequest();

            // Definir la función que se ejecutará cuando la solicitud sea exitosa
            xhttp.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    // Aquí puedes manejar la respuesta del servidor
                    console.log("Respuesta del servidor:", this.responseText);
                }
            };

            // Armar la URL con el parámetro 'proc'
            var url = "fnprocesosmovil.php?proc=" + encodeURIComponent(proc);

            // Abrir la conexión y enviar la solicitud
            xhttp.open("GET", url, true);
            xhttp.send();

            }


          async function mostrarAlerta(nombre_proc) {
                const respuesta = confirm(`¿Desea ejecutar el procedimiento ${nombre_proc}?`);
                if (respuesta) {
                    alert("¡Has confirmado!");

                    //enviarVariableAsincrona(nombre_proc);
                    // Aquí puedes agregar más acciones si el usuario confirma.
                    try {
                        const response = await fetch("fnprocesosmovil.php", {
                            method: "POST",
                            body: JSON.stringify({ id_sp: nombre_proc }),
                        });
                        if (response.ok) {
                            const data = await response.text();
                            document.getElementById("resultado").textContent = data;
                        } else {
                            console.error("Error en la petición AJAX:", response.status);
                        }
                    } catch (error) {
                        console.error("Error en la petición AJAX:", error);
                    }
                } else {
                    alert("Has cancelado.");
                    // Aquí puedes agregar más acciones si el usuario cancela.
                }
           }


        function mostrarAlertalog() {
            var respuesta = confirm("¿Desea borrar el log?");
            if (respuesta) {
                alert("¡Has confirmado!");
                // Aquí puedes agregar más acciones si el usuario confirma.
            } else {
                alert("Has cancelado.");
                // Aquí puedes agregar más acciones si el usuario cancela.
            }
        }

        $(document).on('click', '.popConfirm', function(){

          var id_proceso = $(this).attr("id");

          mostrarAlerta(id_proceso);

          });

          $(document).on('click', '.popConfirmlog', function(){

            var id_proceso = $(this).attr("id");

            //alert(id_proceso);

            mostrarAlertalog();
            /*var estado = $("#estado_" + employee_id).val();

            $('#idempleado').val(employee_id);
            $('#estado_usuario').val(estado);

              if(estado == '1'){
                $("#inactivar").show();
                $("#activar").hide();
              }else {
                $("#inactivar").hide();
                $("#activar").show();
              }*/

            });

  });

</script>
</body>
</html>
