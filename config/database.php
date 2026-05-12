<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Change si ton user MySQL est différent
define('DB_PASS', '');           // Ton mot de passe MySQL
define('DB_NAME', 'bibliotheque_db');

function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
    if ($conn->connect_error) {
        http_response_code(500);
        die(json_encode(['error' => 'Connexion échouée: ' . $conn->connect_error]));
    }
    return $conn;
}
?>