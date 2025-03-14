<?php
/*
CADENA DE CONEXION A SQL SERVER
 */
function conexion_bd()
{
  $pass = "Jfgg";
  $usuario = "sa";
  $nombreBaseDeDatos = "MSLAB";
  # Puede ser 127.0.0.1 o el nombre de tu equipo; o la IP de un servidor remoto
  $rutaServidor = "PCCISS";
  try {
      $base_de_datos = new PDO("sqlsrv:server=$rutaServidor;database=$nombreBaseDeDatos", $usuario, $pass);

      $base_de_datos->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  } catch (Exception $e) {
      echo "Ocurrió un error con la base de datos: " . $e->getMessage();
  }
}



function globales_usuario($val)
{
  /*Conexion con la base de postgresql*/
  $con = conexion_bd(1);

  $usuario = $val;
  /*Funcion que escapa la variable*/
  $usuario = comillas_inteligentes($usuario);
  /*se consulta el id de cargo del usuario logueado*/
  $sql="SELECT a.intidempleado, concat(strpnombre::text,' ',strsnombre::text,' ',strpapellido::text,' ',strsapellido::text) empleado,
               coalesce(j.intidzona,0) intidzona, coalesce(j.intiddptonic,0) intiddptonic, coalesce(b.intidtienda,0)intidtienda, coalesce(a.intidcargo,0) intidcargo,
			   coalesce(a.intidfuncion,0) intidfuncion,
               coalesce(a.strpermiso,'0') strpermiso, i.strtienda, i.strtiendaunificada, k.strzona, a.intcarnet, l.strperfil, o.strdepartamento,
			   a.strusropen, a.strusrwebclient
        FROM msgsac.tblcatempleado as a
        left join msgsac.tbltrnempzona as b on a.intidempleado = b.intidempleado and b.bolactivo = 'true' and b.bolcomisiona = 'true'
        left join msgsac.tblcattienda as i on b.intidtienda = i.intidtienda
        left join msgsac.tblcatdepartamentos as j on i.intiddepto = j.intiddepto
        left join msgsac.tblcatzonas as k on j.intidzona = k.intidzona
        left join msgsac.tblcatperfilusr as l on a.intidperfil = l.idperfil
		left join msgsac.tblcatdepartamentosnic as o on j.intiddptonic = o.intiddptonic
        where a.strcorreo = '$usuario' and a.bolactivo = 'true'
        order by strpnombre desc
        limit 1";

  $resul_g = pg_query($con,$sql);
  $row_g = pg_fetch_array($resul_g);
  $_SESSION["idcargo"] = $row_g['intidcargo'];
  $_SESSION["idempleado"] = $row_g['intidempleado'];
  $_SESSION["permiso"] = $row_g['strpermiso'];
  $_SESSION["dzona"] = $row_g['intidzona'];
  $_SESSION["iddptonic"] = $row_g['intiddptonic'];
  $_SESSION["idtienda"] = $row_g['intidtienda'];
  $_SESSION["empleado"] = $row_g['empleado'];
  $_SESSION["perfil"] = $row_g['strperfil'];
  $_SESSION["carnet"] = $row_g['intcarnet'];
  $_SESSION["idfuncion"] = $row_g['intidfuncion'];
  $_SESSION["tienda"] = $row_g['strtienda'];
  $_SESSION["tiendaunificada"] = $row_g['strtiendaunificada'];
  $_SESSION["zona"] = $row_g['strzona'];
  $_SESSION["departamento"] = $row_g['strdepartamento'];
  $_SESSION["useropen"] = $row_g['strusropen'];
  $_SESSION["userbscs"] = $row_g['strusrwebclient'];
  $_SESSION["search_clienteUnic"] = '';
  $_SESSION["search_clienteUni_name"] = '';
  $_SESSION["search_clienteUni_cedula"] = '';

  echo $_SESSION["idempleado"];
}

if  (isset($_COOKIE['COOKIE_INDEFINED_SESSION']) && empty ($_SESSION["user"]))
  {
  	if ($_COOKIE['COOKIE_INDEFINED_SESSION'])
    {
      $pgcon = conexion_bd(1);

  		$nombre_user = $_COOKIE['COOKIE_DATA_INDEFINED_SESSION']['nombre'];
  		$password_user = $_COOKIE['COOKIE_DATA_INDEFINED_SESSION']['password'];

  		$_SESSION["user"] = $nombre_user;
  		//AQUI HACES LA QUERY PARA BUSCAR EN TU BD UN USUARIO Y SU PASSWORD CON LAS VARIABLES ANTERIORES

      /* Autentificacion del usuario */
      $sql= "SELECT * FROM msgsac.tblcatempleado where strcorreo='".$nombre_user."' and bolactivo = 'true' and bolloginweb='true'";
      //Crea la consulta a la BD
      $resultuser = pg_query($pgcon,$sql);
      //Cuenta el numero de filas
      $cmdtuplas = pg_affected_rows($resultuser);
      //Verifica el numero de filas
      if($cmdtuplas == 1)
      {
        //Obtiene las filas que retorna la consulta
        $sql= "SELECT * FROM msgsac.tblcatempleado where strpassword='".$password_user."' and strcorreo='".$nombre_user."' and bolactivo = 'true' and bolloginweb='true'";
        $resultuser = pg_query($pgcon,$sql);
        $cmdtuplas = pg_affected_rows($resultuser);
        $fila = pg_fetch_assoc($resultuser);
        $user_id = $fila["strpassword"];

          if($password_user == $user_id)
          {
             globales_usuario($_SESSION["user"]);
  	 	       header("Location:index.php");; //envias al usuario a home.php si se lo encontro en la BD!
  	      }
       }
    }
  }

/*Funcion que valida la entrada de las cadenas y las escapa
 para evitar inyeccion SQL*/
function comillas_inteligentes($valor)
{
    // Retirar las barras
    $valor = stripslashes($valor);

    // Colocar comillas si no es entero
    if (!is_numeric($valor)) {
        $valor = pg_escape_string($valor);
    }

    return $valor;
}

 ?>
