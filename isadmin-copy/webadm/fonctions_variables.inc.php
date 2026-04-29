<?php
define(INFINI,'&infin;');
/**
 * Renvoi la valeur la variable d'environnement "$variable" indiqu&eacute;e,
 * renvoi la valeur "$defaut" si elle n'existe pas.
 * @param string $variable	Nom de la variable a chercher,
 * @param string $defaut	Valeur par d&eacute;faut si la variable n'existe pas.
 * */
function getVar($variable, $defaut='') {
	if (isset($_REQUEST[$variable])) {
		$retour = $_REQUEST[$variable];
		if (is_null($retour)) {
			$retour = ( (is_integer($defaut)) ? 0 : "" );
		}
	} else {
		$retour = $defaut;
	}
	return $retour;
}

/**
 * Renvoi la valeur la variable de session indiqu&eacute;e,
 * renvoi la valeur "$defaut" si elle n'existe pas.
 * @param string $variable	Nom de la variable a chercher,
 * @param string $defaut	Valeur par d&eacute;faut si la variable n'existe pas.
 * */
function getSession($variable, $defaut=null) {
	if (isset($_SESSION[$variable])) { return($_SESSION[$variable]); } else { return($defaut); }
}

/**
 * Cr&eacute;e ou modifie la valeur de la variable de session indiqu&eacute;e, la valeur par d&eacute;faut est NULL
 * @param string $variable	Nom de la variable a chercher,
 * @param string $valeur	Valeur affect&eacute;e &agrave; la variable, par d&eacute;faut NULL.
 *							$valeur peut prendre la valeur ++ ou -- pour incr&eacute;menter ou d&eacute;cr&eacute;menter la variable
 *							dans ces cas, si la variable n'existe pas, elle est initialis&eacute;e &agrave; 1 (++) ou &agrave; 0 (--).
 * */
function setSession($variable, $valeur=null) {
	$_SESSION[$variable] = $valeur;
}
/**
 * Supprime la valeur de la variable de session indiqu&eacute;e
 * @param string $variable	Nom de la variable a chercher.
 * */
function delSession($variable) {
	$_SESSION[$variable] = '';
}
?>