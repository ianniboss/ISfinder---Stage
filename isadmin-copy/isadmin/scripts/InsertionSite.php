<?php
session_start();
foreach ($_POST as $index => $valeur) {
    $_SESSION[$index] = strip_tags($valeur);
}

$_SESSION['nb_site'] += 1;

// On retourne au formulaire de modification d'une fiche
$name = $_SESSION['ET_name'];
$base = $_SESSION['bdd'];
header("Location: ../ficheIS.php?name=$name&bdd=$base&val_session=1" . "#InsertionSite");

?>

