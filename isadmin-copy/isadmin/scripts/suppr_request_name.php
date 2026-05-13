<?php
require_once("../includes/function.inc.php");

$ID_Request = $_GET['ID_Request'];

// Supprimer 1 ligne dans request_names + le submiter
$cnx_sub = connexion("ISsubmit");

if ($cnx_sub) {
    $requete = "SELECT `submiters_ID_Submiter` FROM `request_names` WHERE `ID_Request_names` = $ID_Request";
    $result = execute_sql($cnx_sub, $requete);
    $ID_Submiter = mysqli_fetch_row($result);

    $requete = "DELETE FROM `request_names` WHERE `ID_Request_names` = $ID_Request";
    $result = execute_sql($cnx_sub, $requete);

    $requete = "DELETE FROM `submiters` WHERE `ID_Submiter` = $ID_Submiter[0]";
    $result = execute_sql($cnx_sub, $requete);

    mysqli_close($cnx_sub);
}

// Retour à la liste
header("Location: ../liste_request_names.php");

?>
