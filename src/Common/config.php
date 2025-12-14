<?php

$host = "localhost";
$user = "PHP_AGENT";
$password = "KYjf_1orRRw.mz[J";
$dbname = "inventory-procurement-spa";
$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>