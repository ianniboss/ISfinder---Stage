<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$host = "192.168.12.42";
$user = "isadmin";
$password = "PtG8adm2is";
$bdd = "ISsubmit";

echo "test\n";

// Connexion à la base de données
$mysqli = new mysqli($host, $user, $password, $bdd);

// Vérifier la connexion
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
} else {
    echo "connected";
}

phpinfo();
?>
