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
        <a href='#' onClick='return salir()' class='dropdown-item'>
          <i class='fas fa-sign-out mr-2'></i> Cerrar sesión
          <span class='float-right text-muted text-sm'></span>
        </a>
        <div class='dropdown-divider'></div>
      </div>
    </li>
  </ul>
</nav>

<!-- Main Sidebar Container -->
<aside class='main-sidebar sidebar-dark-primary elevation-4'>
  <a href='#' class='brand-link'>
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
                    <i class='right fas fa-angle-left'></i>
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
</script>