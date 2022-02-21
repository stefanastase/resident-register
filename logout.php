<?php
// Initializare sesiune
session_start();
 
// Stergere variabile din sesiune
$_SESSION = array();
 
// Stergerea sesiunii
session_destroy();
 
// Redirect catre pagina de login
header("location: login.php");
exit;
?>