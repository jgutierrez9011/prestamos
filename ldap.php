<?php


//configurando la conexion con nuestro servidor de directorio activo

$ldap_dn = "CN=jhonny.gutierrez,DC=americamovil.ca1,DC=ca1";
$ldap_password = "Jgfeb2023**";

$ldap_con = ldap_connect("172.24.2.180")
    or die("No se pudo conectar a LDAP server");

ldap_set_option($ldap_con, LDAP_OPT_PROTOCOL_VERSION, 3);

//autenticando con nuestro servidor LDAP
if (ldap_bind($ldap_con, $ldap_dn, $ldap_password)){
    echo "Autenticacion exitosa";
} else {
    echo "Autenticacion fallida";
}

//cerrando la conexion
ldap_close($ldap_con);

/*$ldap_dn = "cn=jhonny.gutierrez,dc=americamovil.ca1,dc=com";
$ldap_password = "Jgfeb2023**";
$ldap_con = ldap_connect("ldap:fon-pro-dni-02.claroni.americamovil.ca1");

ldap_set_option($ldap_con, LDAP_OPT_PROTOCOL_VERSION, 3);

if (@ldap_bind($ldap_con, $ldap_dn, $ldap_password)) {
    echo "Autenticación exitosa";
} else {
    echo "Autenticación fallida";
}

ldap_close($ldap_con);*/
?>
