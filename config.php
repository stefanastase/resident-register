<?php

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'password');
define('DB_NAME', 'evidentapopulatiei');

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// Verificare conexiune
if($link === false){
    die("ERROARE: Conexiunea la baza de date a esuat. " . mysqli_connect_error());
}
?>