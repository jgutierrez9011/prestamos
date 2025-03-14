<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1">
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
