<?php
/**
 * Retourne une chaine de caract&egrave;re contenant tous les &eacute;l&eacute;ments du tableau $tableau :
 * - les 2&egrave; et suivants &eacute;l&eacute;ments du tableau sont pr&eacute;c&eacute;d&eacute;s de la chaine $separateur
 * - le dernier &eacute;l&eacute;ment du tablau est pr&eacute;c&eacute;d&eacute; de la chaîne $separateurFin
 * Si $separateur et $separateurFin ne sont pas indiqu&eacute;s (ou invalides), les valeurs par d&eacute;faut sont utilis&eacute;es,
 * Si $separateur est indiqu&eacute; mais pas $separateurFin (ou invalide), $separateurFin sera identique &agrave; $separateur,
 * Si $separateurFin est indiqu&eacute; mais pas $separateur (ou invalide), la valeur par d&eacute;faut de $separateur sera utilis&eacute;e.
 * Si le tableau n'a qu'un &eacute;l&eacute;ment, aucun s&eacute;parateur ne sera utilis&eacute;,
 * Si le tableau n'a que deux &eacute;l&eacute;ments, seul $separateurFin sera utilis&eacute;.
 **/
define('SEPARATEUR_DEFAUT', ', ');
define('SEPARATEUR_FIN_DEFAUT', ' & ');
function array2str($tableau, $separateur=FALSE, $separateurFin=FALSE) {
    if (!is_string($separateur)) {    // $separateur n'est pas une chaîne
        $separateur = SEPARATEUR_DEFAUT;
        if (!is_string($separateurFin)) { $separateurFin = SEPARATEUR_FIN_DEFAUT; }
    } else {    // separateur est indiqu&eacute;
        if (!is_string($separateurFin)) { $separateurFin = $separateur; }
    }
    $retour = '';
    $index = 1;
    foreach ($tableau as $element) {
        $retour.= ( ($index>1) ? ( ($index==array_count_values($tableau)) ? $separateurFin : $separateur ) : '' ).$element;
        $index++;
    }
    return $retour;
}

/**
 * Retourne TRUE si $valeur est entre les valeurs $min et $max, FALSE si c'est en dehors.
 * Si $inclure == TRUE (par d&eacute;faut), on inclut les valeurs limite, si == FALSE, on les exclut.
 **/
function between($valeur, $vMin, $vMax, $inclure = TRUE) {
    return ( $inclure ? ( ($valeur>=$vMin) and ($valeur<=$vMax) ) : ( ($valeur>$vMin) and ($valeur<$vMax) ) );
}


?>