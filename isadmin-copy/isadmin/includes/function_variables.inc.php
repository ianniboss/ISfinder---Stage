<?php
/**
 * Retourne le contenu de la variable POST si elle existe,
 *  sinon le contenu de la variable GET si elle existe,
 *  sinon le contenu de la variable SESSION si elle existe,
 *  sinon la valeur de $defaut, qui est la valeur NULL par défaut
 */
function getVar($variable, $defaut = NULL) {
    return (
        (isset($_POST[$variable])) ? $_POST[$variable] : (
            (isset($_GET[$variable])) ? $_GET[$variable] : getSession($variable, $defaut)
        )
    );
}

/**
 * Retourne le contenu de la variable SESSION si elle existe, sinon la valeur de $defaut, qui est la valeur NULL par défaut
 * Ajoute la constante PREFIXE_SESSION
 * Retourne la valeur par défaut si la variable n'est pas indiquée ou si elle n'existe pas
 */
function getSession($variable = NULL, $defaut = NULL) {
    $retour = $defaut;
    if (!is_null($variable)) {
        $variable = strtoupper($variable);
        if (isset($_SESSION[PREFIXE_SESSION . $variable])) {
            $retour = $_SESSION[PREFIXE_SESSION . $variable];
        }
    }
    return $retour;
}

/**
 * Crée une variable SESSION, la vide si $valeur n'est pas indiquée
 * Ne fait rien si la variable n'est pas indiquée
 */
function setSession($variable = NULL, $valeur = NULL) {
    if (!is_null($variable)) {
        if (is_null($valeur)) {
            $valeur = '';
        }
        $_SESSION[PREFIXE_SESSION . strtoupper($variable)] = $valeur;
    }
}

/**
 * Supprime toutes les variables de session préfixées par PREFIXE_SESSION
 * Si $filtre est indiqué, ne supprime que les variables de session dont le nom contient le filtre
 */
function flushSession($filtre = NULL) {
    foreach ($_SESSION as $cle => $valeur) {
        if (substr($cle, 0, strlen(PREFIXE_SESSION)) == PREFIXE_SESSION) {
            if (!is_null($filtre)) {
                if (strpos($cle, $filtre) !== false) {    // chaine de filtrage trouvée
                    $_SESSION[$cle] = '';
                }
            } else {
                $_SESSION[$cle] = '';
            }
        }
    }
}
