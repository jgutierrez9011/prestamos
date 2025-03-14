<?php
// Definir los detalles de la conexión
$host = "192.168.8.201:3871";
$dbname = "AINP";
$username = "TRAFICO";
$password = "AdminAI_37";

// Crear una conexión a la base de datos Oracle utilizando PDO
try {
    $conn = new PDO("oci:dbname=$host/$dbname", $username, $password);
} catch (PDOException $e) {
    echo "Error al conectarse a la base de datos: " . $e->getMessage();
    die();
}

// Realizar una consulta a la base de datos
$query = "SELECT to_char(to_DATE(dias.periodo,'RRRR-MM-DD'),'RRRR-MM-DD') periodo,
       dato16.periodo Periodo_dato16, dato16.fecha_carga fecha_carga_16, dato16.\"0016\" valor_dato_16,
       dato24.periodo Periodo_dato24, dato24.fecha_carga fecha_carga_24, dato24.\"0024\" valor_dato_24
from
(
select periodo from tbl_metricas group by periodo
) dias
left join
(
SELECT *
FROM
(

         select a.Id_dato, a.periodo, a.fecha_carga, a.valor
         from tbl_metricas a
         inner join vw_metricas b
         on b.Id_dato = a.Id_dato and b.periodo = a.periodo and a.fecha_carga = b.FECHA_CARGA and a.TECNOLOGIA = b.tecnologia and a.servicio = b.servicio
         where a.Id_dato in ('0016') and a.TECNOLOGIA = '0001' and a.servicio in ('0003','0008','0009','0009','0010') /*and a.periodo = '20220517'*/

)
PIVOT
(
  sum(valor) FOR Id_dato IN ('0016' as \"0016\")
)
order by periodo asc
) dato16 on dias.periodo = dato16.periodo
left join
(
SELECT *
FROM
(
         select a.Id_dato, a.periodo, a.fecha_carga, a.valor
         from tbl_metricas a
         inner join vw_metricas b
         on b.Id_dato = a.Id_dato and b.periodo = a.periodo and a.fecha_carga = b.FECHA_CARGA and a.TECNOLOGIA = b.tecnologia and a.servicio = b.servicio
         where a.Id_dato in ('0024') and a.TECNOLOGIA = '0001' and a.servicio in ('0003','0008','0009','0009','0010') /*and a.periodo = '20220517'*/

)
PIVOT
(
  sum(valor) FOR Id_dato IN ('0024' as \"0024\")
)
order by periodo asc
) dato24 on dias.periodo = dato24.periodo
where length(dias.periodo) = 8 and substr(dias.periodo,7,8) not in ('51','65') and substr(dias.periodo,1,6) = '202301'
order by dias.periodo asc";
$stmt = $conn->prepare($query);
$stmt->execute();

// Recorrer los resultados de la consulta
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    //print_r($row['VALOR_DATO_16']."\n");
    echo $row['VALOR_DATO_16']."\r";
}

// Cerrar la conexión a la base de datos
$conn = null;
?>
