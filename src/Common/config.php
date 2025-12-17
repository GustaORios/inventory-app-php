<?php

$host = "localhost";
$user = "PHP_AGENT"; 
$password = "N4Qdu5[NDs2kcH_u"; 
$dbname = "inventory-procurement-spa";
$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

return $conn;
?>