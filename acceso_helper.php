<?php
require_once '../cn.php';

function validarAcceso($usuario, $rutaFormulario, $conexionPDO)
{
    $sql = "SELECT COUNT(*) 
            FROM tblcatusuario u
            INNER JOIN tblcatperfilusrfrm puf ON u.intidperfil = puf.idperfil
            INNER JOIN tblcatformularios f ON puf.idfrm = f.idfrm
            WHERE u.strusuario = ? 
              AND f.strnombreform like ? 
              AND puf.bolactivo = '1'
              AND u.bolactivo = '1'";

    $stmt = $conexionPDO->prepare($sql);
    $stmt->execute([$usuario, "%$rutaFormulario%"]);
    $row = $stmt->fetch(PDO::FETCH_NUM);

    return $row[0] > 0;
}

?>