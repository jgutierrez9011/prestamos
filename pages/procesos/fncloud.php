<?php 


if(isset($_POST["exec"])){

    exec_script_python();

}

function load_file_odin(){

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $target_dir = "D:\\report\\odin\\";  // Carpeta de destino
        $target_file = $target_dir . basename($_FILES["rptodin"]["name"]);
        $uploadOk = 1;
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
        // Verifica si el archivo es un archivo Excel
        if ($fileType != "xlsx" && $fileType != "xls" && $fileType != "xlsb") {
            echo "Solo se permiten archivos de Excel (xlsx, xls).";
            $uploadOk = 0;
        }
    
        // Verifica si hay errores durante la carga
        if ($uploadOk == 0) {
            //echo "Error al cargar el archivo.";
            {header("location: cloud.php?token=2");}
        } else {
            if (move_uploaded_file($_FILES["rptodin"]["tmp_name"], $target_file)) {
                //echo "El archivo " . basename($_FILES["rptodin"]["name"]) . " ha sido cargado correctamente.";
                header("location: cloud.php?token=1");
            } else {
                //echo "Error al cargar el archivo.";
                header("location: cloud.php?token=2");
            }
        }
    }

};

function exec_script_python()
{

    
    // Ruta al archivo Python
    $python_script = "D:\\report\\odin\\cargar_clientes_cloud.py";
    
    // Abrir el script de Python para lectura
    $handle = popen("python $python_script 2>&1", 'r');
    
    // Leer y mostrar la salida en tiempo real
    while (!feof($handle)) {
        $output = fgets($handle);
        echo nl2br($output);
        flush(); // Limpia el búfer de salida para mostrar la salida en tiempo real
        ob_flush();
        usleep(100000); // Espera 100 ms para evitar saturar la salida
    }
    
    // Cerrar el proceso
    pclose($handle);
   
    

};



?>