<!DOCTYPE html>
<html>
<head>
    <title>Ejecución de script de Python en PHP</title>
</head>
<body>

<h1>Ejecución de script de Python en PHP</h1>

<form action="test.php" method="GET">
    <label for="start">Inicio del rango:</label>
    <input type="number" id="start" name="start">
    <label for="end">Fin del rango:</label>
    <input type="number" id="end" name="end">
    <input type="submit" value="Generar números">
</form>

<div>
    <h2>Salida del script de Python:</h2>
    <pre>
        <?php
        if (isset($_GET['start']) && isset($_GET['end'])) {
            $start = intval($_GET['start']);
            $end = intval($_GET['end']);

            $cmd = 'python test.py ' . escapeshellarg($start) . ' ' . escapeshellarg($end);
            $descriptorspec = array(
                0 => array("pipe", "r"),
                1 => array("pipe", "w"),
                2 => array("pipe", "w")
            );

            $process = proc_open($cmd, $descriptorspec, $pipes);

            if (is_resource($process)) {
                while ($s = fgets($pipes[1])) {
                    echo $s;
                    flush();
                }
                fclose($pipes[1]);
                $return_value = proc_close($process);
            }
        }
        ?>
    </pre>
</div>

</body>
</html>


