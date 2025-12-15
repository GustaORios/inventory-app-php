<?php

$host = "localhost";
$user = "root"; 
$password = "ApnneJDj9427*4"; 
$dbname = "inventory-procurement-spa";
$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

return $conn; 

?>