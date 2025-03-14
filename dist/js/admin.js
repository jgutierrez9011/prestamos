
function encriptarDatos() {
    // Obtener los valores de los campos del formulario
    var nombre = document.getElementsByName('usuario')[0].value;
    var email = document.getElementsByName('passw')[0].value;
    
    // Encriptar los datos (puedes utilizar algún algoritmo de encriptación aquí)
    var datoEncriptado = btoa(nombre + '|' + email);
    
    // Asignar los datos encriptados al campo oculto
    document.getElementsByName('passw')[0].value = datoEncriptado;
    
    return true; // Permite que el formulario se envíe
  }