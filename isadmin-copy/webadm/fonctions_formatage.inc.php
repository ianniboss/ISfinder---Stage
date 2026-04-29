<?php

/**
 *	Cette fonction renvoi la chaine de caractère indiquée, en remplaçant les caractères du milieu par "[...]".
 **/
function tronqueTexte($texte, $car_avant=10, $car_apres=10) {
	$retour = "";
	$taille_texte = strlen($texte);
	if ( $taille_texte<=($car_avant+$car_apres) ) {
		$retour = $texte;
	} else {
		$retour = substr($texte,0,$car_avant).'[&hellip;]'.substr($texte,$taille_texte-$car_apres);
	}
	return $retour;
}

/**
 *	Cette fonction renvoi la chaine de caractère indiquée, débarrassée d'espaces en début et fin, espace intermédiaires remplacés par "_", et en minuscules.
 **/
function versMotCle($texte) {
	$resultat = str_replace(" ", "_", strtolower(trim($texte)));
	return $resultat;
}

?>