<?php

$host = "localhost";
$user = "root"; // MUDANÇA: Agora usando o usuário root
$password = "ApnneJDj9427*4"; // MUDANÇA: A nova senha forte que você definiu
$dbname = "inventory-procurement-spa";
$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    // Se a senha no phpMyAdmin estiver correta, esta linha NUNCA será alcançada
    die("Connection failed: " . $conn->connect_error);
}

// Mantenha o retorno para os Models
return $conn; 

?>