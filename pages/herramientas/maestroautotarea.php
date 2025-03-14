 <!-- Modal para nueva auto tarea-->
 <div id="new" class="modal fade"  role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
     <div class="modal-dialog modal-md">
        <div class="modal-content">
           <div class="modal-header">
           <h4 class="modal-title">Nueva auto tarea</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
              </button>
           </div>
           <div class="modal-body">
             <form role="form" name="frm_crear_autotarea" id="frm_crear_autotarea" method="post">
               <div class="form-group">
                 <strong>ID</strong><br>
                 <select id="cmb_new_codigo" name="cmb_new_codigo" class="form-control form-control-sm select3 select2-danger" data-dropdown-css-class="select2-danger" style="">
                      <option value=""> </option>
                      <?php
                       $json = lista_seguimiento_auto();
                       $data = json_decode($json,true);
                       foreach($data as $row){
                       ?>
                       <option value="<?php echo $row["id"]; ?>"><?php echo $row["text"]; ?></option>
                       <?php }  ?>
                 </select>
               </div>
               <br>
               <div class="form-group">
                 <strong>Descripción</strong><br>
                 <input type="text" id="desc_auto_tarea" name="desc_auto_tarea" class="form-control" autocomplete="off" required>
               </div>
               <br>
               <div class="form-group">
                 <strong>Asunto</strong><br>
                 <input type="text" id="asunto_auto_tarea" name="asunto_auto_tarea" class="form-control" autocomplete="off" required>
               </div>
               <br>
             <div class="modal-footer justify-content-between">
                 <button class="btn btn-default" data-dismiss="modal" aria-hidden="true"><strong>Cerrar</strong></button>
                 <button type="submit" id="guardar_cuenta" name="guardar_cuenta" class="btn btn-primary guardar_datos"><strong>Guardar</strong></button>
             </div>
             </form>
           </div>
        </div>
     </div>
   </div>

 

 <!-- Modal para editar auto tarea-->
   <div id="update" class="modal fade" role="dialog" aria-labelledby="myModalLabelEdit" aria-hidden="true">
     <div class="modal-dialog modal-md">
        <div class="modal-content">
           <div class="modal-header">
           <h4 class="modal-title">Editar auto tarea</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
              </button>
           </div>
           <div class="modal-body">
             <form role="form" name="frm_editar_autotarea" id="frm_editar_autotarea" method="post">
             <div class="form-group">
                 <strong>ID</strong><br>
                 <input type="text" readonly id="id_auto_tarea_up" name="id_auto_tarea_up" class="form-control" autocomplete="off" required>
               </div>
               <br>
               <div class="form-group">
                 <strong>Descripción</strong><br>
                 <input type="text" id="desc_auto_tarea_up" name="desc_auto_tarea_up" class="form-control" autocomplete="off" required>
               </div>
               <br>
               <div class="form-group">
                 <strong>Asunto</strong><br>
                 <input type="text" id="asunto_auto_tarea_up" name="asunto_auto_tarea_up" class="form-control" autocomplete="off" required>
               </div>
               <br>
             <div class="modal-footer justify-content-between">
                 <button class="btn btn-default" data-dismiss="modal" aria-hidden="true"><strong>Cerrar</strong></button>
                 <button type="submit" id="btn_editar_autotarea" name="btn_editar_autotarea" class="btn btn-primary editar_autotarea"><strong>Guardar</strong></button>
             </div>
             </form>
           </div>
        </div>
     </div>
   </div>


    <!-- Modal para nuevo procedimiento-->
    <div id="new_procedimiento" class="modal fade" role="dialog" aria-labelledby="myModalLabelEdit" aria-hidden="true">
     <div class="modal-dialog modal-md">
        <div class="modal-content">
           <div class="modal-header">
           <h4 class="modal-title">Nuevo procedimiento</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
              </button>
           </div>
           <div class="modal-body">
             <form role="form" name="frm_new_procedimiento" id="frm_new_procedimiento" method="post">
             <div class="form-group">
                 <strong>ID</strong><br>
                 <input type="text" readonly id="id_procedimiento" name="id_procedimiento" class="form-control" autocomplete="off" required>
               </div>
               <br>
               <div class="form-group">
                 <strong>Procedimiento almacenado</strong><br>
                 <input type="text" id="txtprocedimeinto" name="txtprocedimeinto" class="form-control" autocomplete="off" required>
               </div>
               <br>
             <div class="modal-footer justify-content-between">
                 <button class="btn btn-default" data-dismiss="modal" aria-hidden="true"><strong>Cerrar</strong></button>
                 <button type="submit" id="btn_save_proc" name="btn_save_proc" class="btn btn-primary save_proc"><strong>Guardar</strong></button>
             </div>
             </form>
           </div>
        </div>
     </div>
   </div>

       <!-- Modal para nueva consulta-->
       <div id="new_consulta" class="modal fade" role="dialog" aria-labelledby="myModalLabelEdit" aria-hidden="true">
     <div class="modal-dialog modal-md">
        <div class="modal-content">
           <div class="modal-header">
           <h4 class="modal-title">Nueva consulta</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
              </button>
           </div>
           <div class="modal-body">
             <form role="form" name="frm_new_consulta" id="frm_new_consulta" method="post">
             <div class="form-group">
                 <strong>ID</strong><br>
                 <input type="text" readonly id="id_consulta" name="id_consulta" class="form-control" autocomplete="off" required>
               </div>
               <br>
               <div class="form-group">
                 <strong>Descripcion</strong><br>
                 <input type="text" id="txtdescripcion" name="txtdescripcion" class="form-control" autocomplete="off" required>
               </div>
               <br>
               <div class="form-group">
              <label for="comment">Consulta</label>
              <textarea class="form-control" rows="5" id="txtconsulta" name="txtconsulta"></textarea>
              </div>
              <br>
             <div class="modal-footer justify-content-between">
                 <button class="btn btn-default" data-dismiss="modal" aria-hidden="true"><strong>Cerrar</strong></button>
                 <button type="submit" id="btn_save_consulta" name="btn_save_consulta" class="btn btn-primary save_consulta"><strong>Guardar</strong></button>
             </div>
             </form>
           </div>
        </div>
     </div>
   </div>

          <!-- Modal para nueva excel-->
          <div id="new_excel" class="modal fade" role="dialog" aria-labelledby="myModalLabelEdit" aria-hidden="true">
     <div class="modal-dialog modal-md">
        <div class="modal-content">
           <div class="modal-header">
           <h4 class="modal-title">Nuevo excel</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
              </button>
           </div>
           <div class="modal-body">
             <form role="form" name="frm_new_excel" id="frm_new_excel" method="post">
             <div class="form-group">
                 <strong>ID</strong><br>
                 <input type="text" readonly id="id_excel" name="id_excel" class="form-control" autocomplete="off" required>
               </div>
               <br>
               <div class="form-group">
                 <strong>Nombre de la hoja</strong><br>
                 <input type="text" id="txthojaexcel" name="txthojaexcel" class="form-control" autocomplete="off" required>
               </div>
               <br>
               <div class="form-group">
              <label for="comment">Consulta</label>
              <textarea class="form-control" rows="5" name="textconsultahoja" id="textconsultahoja"></textarea>
              </div>
              <br>
             <div class="modal-footer justify-content-between">
                 <button class="btn btn-default" data-dismiss="modal" aria-hidden="true"><strong>Cerrar</strong></button>
                 <button type="submit" id="btn_save_excel" name="btn_save_excel" class="btn btn-primary save_excel"><strong>Guardar</strong></button>
             </div>
             </form>
           </div>
        </div>
     </div>
   </div>


   <!-- Modal para nueva correo-->
   <div id="new_correo" class="modal fade" role="dialog" aria-labelledby="myModalLabelEdit" aria-hidden="true">
     <div class="modal-dialog modal-md">
        <div class="modal-content">
           <div class="modal-header">
           <h4 class="modal-title">Nuevo correo</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
              </button>
           </div>
           <div class="modal-body">
             <form role="form" name="frm_new_correo" id="frm_new_correo" method="post">
             <div class="form-group">
                 <strong>ID</strong><br>
                 <input type="text" readonly id="id_correo" name="id_correo" class="form-control" autocomplete="off" required>
               </div>
               <br>
               <div class="form-group">
                 <strong>Correo</strong><br>
                 <input type="text" id="txtcorreo" name="txtcorreo" class="form-control" autocomplete="off" required>
               </div>
               <br>
             <div class="modal-footer justify-content-between">
                 <button class="btn btn-default" data-dismiss="modal" aria-hidden="true"><strong>Cerrar</strong></button>
                 <button type="submit" id="btn_save_correo" name="btn_save_correo" class="btn btn-primary save_correo"><strong>Guardar</strong></button>
             </div>
             </form>
           </div>
        </div>
     </div>
   </div>