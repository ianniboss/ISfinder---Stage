<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/*
 *  Cette fonction retourne un tableau contenant la liste des fichiers contenus dans le dossier indiqué.
 * @param   string              $dossier  Nom du dossier à scanner, obligatoire
 * @param   string              $pattern  Texte devant se trouver dans le nom du fichier pour faire partie de la liste,
 *          array of string     $pattern  Tableau contenant tous les textes inclus dans les noms de fichiers pour faire partie de la liste,
 * @param   string              $option   Chaine indiquant les options, séparées par un caractère
 *                                          FICHIERS_CACHE    Retourne les fichiers cachés aussi (débutant par un .)
 *                                          SANS_DOSSIER      Ne retourne pas les dossiers
 *                                          SANS_FICHIER      Ne retourne pas les fichiers uniquement
 *                                          RECURSIF          Parcourir récursivement les dossiers
 *                                          AVEC_POINT        Ajouter les dossiers . et ..
 */
function getDir($dossier, $pattern=NULL, $option=NULL) {
    $retour = array();
    if (is_dir($dossier)) {
        $fichiers_cache = strpos($option, 'FICHIERS_CACHE');
        $sans_dossier = strpos($option, 'SANS_DOSSIER');
        $sans_fichier = strpos($option, 'SANS_FICHIER');
        $recursif = strpos($option, 'RECURSIF');
        $avec_point = strpos($option, 'AVEC_POINT');
        $liste_complete = scandir($dossier);
        foreach ($liste_complete as $fichier) {
            $ajouter = TRUE;
            if ( (in_array($fichier, array('.','..'))) and (!$avec_point) )  {    $ajouter = FALSE;  }
            if (is_dir($fichier)) { if ($sans_dossier) {   $ajouter = FALSE;   } }
                else {              if ($sans_fichier) {   $ajouter = FALSE;   } }
            
            if ( (is_string($pattern)) and ( !preg_match($pattern, $fichier)) ) {    $ajouter = FALSE;  }
            elseif (is_array($pattern)) {
                $ajout = FALSE;
                foreach($pattern as $pat) { // Parcours les pattern !
                    if ( (is_string($pat)) and ( preg_match($pat, $fichier)) ) {
                            $ajout = TRUE;
                    }
                }
                if ($ajout) { $ajouter = TRUE; }
            }
            if ($ajouter) {
                $retour[] = $dossier.'/'.$fichier;
            }
            if ( ($recursif) and (is_dir($dossier.'/'.$fichier)) ) {    // Récursion demandée !
                $retour = array_merge($retour, getDir($dossier.'/'.$fichier, $pattern, $option));
            }
        }
        return $retour;
    } else {  return(FALSE);  }
}