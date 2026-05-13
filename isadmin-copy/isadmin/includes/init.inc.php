<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Constantes pour accès BDD
define('DB_server', 'localhost');
define('DB_user', 'ibinsyahrulazlan');
define('DB_password', 'yNCNLvH9vwX^f~$i');
define('DB_bdd', 'isfinder');
?>