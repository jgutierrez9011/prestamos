<nav class='main-header navbar navbar-expand navbar-white navbar-light'>
  <ul class='navbar-nav'>
    <li class='nav-item'>
      <a class='nav-link' data-widget='pushmenu' href='#' role='button'><i class='fas fa-bars'></i></a>
    </li>
  </ul>

  <ul class='navbar-nav ml-auto'>
    <li class='nav-item dropdown'>
      <a class='nav-link' data-toggle='dropdown' href='#'>
        <i class='far fa-user fa-fw'></i><i class='fa fa-caret-down'></i>
      </a>
      <div class='dropdown-menu dropdown-menu-lg dropdown-menu-right'>
        <div class='dropdown-divider'></div>
        <a href='#' class='dropdown-item' data-toggle='modal' data-target='#modalCambiarContrasena'>
          <i class='fas fa-key mr-2'></i> Cambiar contraseña
        </a>
        <div class='dropdown-divider'></div>
        <a href='#' onClick='return salir()' class='dropdown-item'>
          <i class='fas fa-sign-out mr-2'></i> Cerrar sesión
          <span class='float-right text-muted text-sm'></span>
        </a>
        <div class='dropdown-divider'></div>
      </div>
    </li>
  </ul>
</nav>

<!-- Modal Cambiar Contraseña -->
<div class="modal fade" id="modalCambiarContrasena" tabindex="-1" role="dialog" aria-labelledby="modalCambiarContrasenaLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="formCambiarContrasena" autocomplete="off">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalCambiarContrasenaLabel">Cambiar contraseña</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="actualContrasena">Contraseña actual</label>
            <input type="password" class="form-control" id="actualContrasena" name="actualContrasena" required minlength="6">
          </div>
          <div class="form-group">
            <label for="nuevaContrasena">Nueva contraseña</label>
            <input type="password" class="form-control" id="nuevaContrasena" name="nuevaContrasena" required minlength="8">
          </div>
          <div class="form-group">
            <label for="confirmarContrasena">Confirmar nueva contraseña</label>
            <input type="password" class="form-control" id="confirmarContrasena" name="confirmarContrasena" required minlength="8">
          </div>
          <div id="mensajeContrasena" class="text-danger"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Cambiar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Main Sidebar Container -->
<aside class='main-sidebar sidebar-dark-primary elevation-4'>
  <a href='../../pages/usuarios/inicio.php' class='brand-link'>
    <span class='brand-text font-weight-light'>CREDIMORE</span>
  </a>

  <div class='sidebar'>
    <br>
    <div class='form-inline'>
      <div class='input-group' data-widget='sidebar-search'>
        <input class='form-control form-control-sidebar' type='search' placeholder='Search' aria-label='Search'>
        <div class='input-group-append'>
          <button class='btn btn-sidebar'>
            <i class='fas fa-search fa-fw'></i>
          </button>
        </div>
      </div>
    </div>

    <nav class='mt-2'>
      <ul class='nav nav-pills nav-sidebar flex-column' data-widget='treeview' role='menu' data-accordion='false'>
        <?php if ($menuData['hasMenu']): ?>
          <?php foreach ($menuData['mainMenu'] as $item): ?>
            <li class='nav-item'>
              <a href='#' class='nav-link'>
                <i class='nav-icon fas <?= htmlspecialchars($item['strclassicono']) ?>'></i>
                <p>
                  <?= htmlspecialchars($item['strmenu']) ?>
                  <?php if (!empty($item['submenus'])): ?>
                    <i class='right fas fa-angle-left text-warning'></i>
                  <?php endif; ?>
                </p>
              </a>
              
              <?php if (!empty($item['submenus'])): ?>
                <ul class='nav nav-treeview'>
                  <?php foreach ($item['submenus'] as $submenu): ?>
                    <li class='nav-item'>
                      <a href='<?= htmlspecialchars($submenu['strnombreform']) ?>' class='nav-link'>
                        <i class='far fa-dot-circle nav-icon'></i>
                        <p><?= htmlspecialchars($submenu['strformulario']) ?></p>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
</aside>
<script type="text/javascript">
		function salir()
		{
			//Confirma si el usuario desa salir del sistema
			var confirmar = confirm("Esta seguro que desea salir?");
			if (confirmar) //Si la condicion devuelve true entonces lo redirecciona a salir.php
			{
				window.location = "../../pages/usuarios/salir.php"; //Redireccion a salir.php
			}
		}

    // Validación y envío del formulario de cambio de contraseña
  document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formCambiarContrasena');
    form && form.addEventListener('submit', function(e) {
      e.preventDefault();
      const actual = form.actualContrasena.value;
      const nueva = form.nuevaContrasena.value;
      const confirmar = form.confirmarContrasena.value;
      const mensaje = document.getElementById('mensajeContrasena');
      mensaje.textContent = '';

      if (nueva !== confirmar) {
        mensaje.textContent = 'Las contraseñas nuevas no coinciden.';
        return;
      }
      if (nueva.length < 8) {
        mensaje.textContent = 'La nueva contraseña debe tener al menos 8 caracteres.';
        return;
      }
      const formData = new FormData();
      formData.append('action', 'cambiar_contrasena');
      formData.append('actualContrasena', actual);
      formData.append('nuevaContrasena', nueva);
      fetch('../../pages/usuarios/fnusuario.php', {
            method: 'POST',
            body: formData
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              mensaje.textContent = '';
              alert('Contraseña cambiada correctamente.');
              $('#modalCambiarContrasena').modal('hide');
              // Limpiar los campos del formulario
              form.reset();
            } else {
              mensaje.textContent = data.message || 'Error al cambiar la contraseña.';
            }
          })
          .catch(() => {
            mensaje.textContent = 'Error de conexión.';
          });
    });
  });
</script>