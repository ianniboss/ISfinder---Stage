<?php 
// Checks the radio button that was checked unless requested from NomenclatureRecherche
function check($name, $bouton, $coche) {
    $retour = "";

    if (!empty($_GET[$name]) && htmlentities($_GET[$name] == $bouton) && $coche == 1) {
        $retour = "checked='checked'";
    } else if ($bouton == 1 && $coche == 1) {
        $retour = "checked='checked'";
    }
    return $retour;
}

// Checks if $nom exists in the database
function exist($nom) {
    $req_nom = "SELECT `ID_ET` FROM `element_transposable` WHERE `ET_name` = '$nom'";
    $result_nom = execute_sql($req_nom);
    $reponse = mysqli_fetch_array($result_nom);
    $retour = (is_null($reponse)) ? "" : $reponse["ID_ET"];

    if (!$reponse) {
        $req_nom = "SELECT `Element_transposable_ID_ET` FROM `synonyme` WHERE `Synonyme` = '$nom'";
        $result_nom = execute_sql($req_nom);
        $reponse = mysqli_fetch_array($result_nom);
        $retour = (is_null($reponse)) ? "" : $reponse['Element_transposable_ID_ET'];
    }

    return $retour;    
}
?>
