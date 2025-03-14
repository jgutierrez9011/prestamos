<!DOCTYPE html>
<html>
<head>
    <title>Salida en tiempo real de números generados por Python en PHP</title>
</head>
<body>

<h1>Salida en tiempo real de números generados por Python en PHP</h1>

<div>
    <h2>Salida del script de Python:</h2>
    <pre>
        <?php
        $cmd = 'python test.py ' . intval($_GET['start']) . ' ' . intval($_GET['end']);
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
        ?>
    </pre>
</div>

</body>
</html>

