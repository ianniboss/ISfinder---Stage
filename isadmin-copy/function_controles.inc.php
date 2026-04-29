<?PHP

define('PREG_ADR_IP', "#^[1-2]?[0-9]?[0-9]+(.[1-2]?[0-9]?[0-9]+){3}$#");	// Format adesse IP : 192.168.10.1
function estAdresseIP($adr) {		return (preg_match(PREG_ADR_IP, $adr)==1);	}

/**
 * Renvoie TRUE si l'adresse IP indiquée correspond au critere indiqué, FALSE sinon.<br />
 * Les criteres acceptés sont : ADR_IP_OK (IP valide), ADR_IP_BDD (IP dans la base), ADR_IP_LIBRE (IP libre dans la base), ADR_IP_UTIL (IP utilisée dans la base)
 **/
define('ADR_IP_OK', 1);		// Adresse IP Valide
define('ADR_IP_BDD', 2);	// Adresse IP Valide ET dans la base de données
define('ADR_IP_LIBRE', 3);	// Adresse IP Valide ET dans la base de données ET libre (nom vide)
define('ADR_IP_UTIL', 4);	// Adresse IP Valide ET dans la base de données ET utilisée (nom rempli)
function estAdresseIPValide($adr, $critere=NULL) {
	if (!in_array($critere, array(ADR_IP_OK, ADR_IP_BDD, ADR_IP_LIBRE, ADR_IP_UTIL))) {	$critere = ADR_IP_BDD; }
	$retour = FALSE;
	if (estAdresseIP($adr)) {
		if ($critere==ADR_IP_OK) {	// cherche juste si adresse ip valide
			$retour = TRUE;
		} else {	// cherche si ip dans la base
			$infoIP = sqlTrouve('lan', 'adr_ip="'.$adr.'"');
			if (!is_array($infoIP)) {	// ip dans la base...
				if ($critere==ADR_IP_BDD) {	// cherche si adresse ip dans la base, donc valide
					$retour = TRUE;
				} else {	// cherche si ip libre ou utilisée
					if ($infoIP['nom']=="") {	// ip libre !
						$retour = ($critere==ADR_IP_LIBRE);
					} else {					// ip occupée
						$retour = ($critere==ADR_IP_UTIL);
					}
				}
			}
		}
	}
	return $retour;
}

define('PREG_ADR_RES', "#^[1-2]?[0-9]?[0-9]+(.[1-2]?[0-9]?[0-9]+){3}(/[1-2]?[0-9]?[0-9]+){1}$#");	// Format adresse reseau : 192.168.10.0/24
function estAdresseReseau($adr) {	return preg_match(PREG_ADR_RES, $adr);	}

define('PREG_ADR_MAC', '#^([0-9a-zA-Z]{2}:){5}[0-9a-zA-Z]{2}$#');	// Format adresse MAC : 21:32:32:32:ab:ab
function estAdresseMAC($adr) {		return preg_match(PREG_ADR_MAC, $adr);	}

define('PREG_ADR_MAIL', " /^[^\W][a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\@[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\.[a-zA-Z]{2,4}$/ ");	// Format adresse MAIL : prenom.nom-compose@serveur.domaine.pays
function estAdresseMail($adr) {		return preg_match(PREG_ADR_MAIL, $adr);	}

define('PREG_ADR_SITE', "\^(http|https|ftp):\/\/([\w]*)\.([\w]*)\.(com|fr|net|org|biz|info|mobi|us|cc|bz|tv|ws|name|co|me)(\.[a-z]{1,3})?\z/i ");	// Format Site WEB
function estAdresseSite($adr) {		return preg_match(PREG_ADR_SITE, $adr);	}

define('PREG_CODE_POSTAL', " \^[0-9]{5,5}$\ ");
function estCodePostal($code) {		return preg_match(PREG_CODE_POSTAL, $code);	}

define('PREG_CODE_COULEUR', " \^#(?:(?:[a-f\d]{3}){1,2})$/i ");
function estCodeCouleur($code) {	return preg_match(PREG_CODE_COULEUR, $code);	}

define('PREG_TELEPHONE', " \^(\d\d\s){4}(\d\d)$\ ");
function estTelephone($tel) {		return preg_match(PREG_TELEPHONE, $tel);	}

/*	COMMENTEE CAR NON FONCTIONNEL !
define('PREG_DATE', " \^\d{1,2}/\d{1,2}/\d{2,4}$\ " );
define('PREG_DATE_LONG', " \^\d{1,2}/\d{1,2}/\d{2,4} d{1,2}:d{1,2}:d{1,2}$\ " );
function estDate($date) {
	return ( (preg_match(PREG_DATE, trim($date))) or (preg_match(PREG_DATE_LONG, trim($date))) );
}
define('PREG_DATE_SQL', " \^\d{4}-\d{2}-\d{2}$\ " );
define('PREG_DATE_SQL_LONG', " \^\d{4}-\d{2}-\d{2} d{2}:d{2}:d{2}$\ " );
function estDateSQL($date) {
	return ( (preg_match(PREG_DATE_SQL, trim($date))) or (preg_match(PREG_DATE_SQL_LONG, trim($date))) );
}*/

/**
 * fonction qui supprime toutes les balises HTML ET convertit les caractères en codes HTML (y compris les ' et " !
 **/
function texteVersBDD($texte) {
	$retour = strip_tags($texte);
	$retour = htmlentities( $retour, ENT_QUOTES | ENT_IGNORE, "UTF-8");
	return $retour;
}

/**
 * Fonction qui supprime tous les espaces d'une chaine
 * - "Le chat dort" devient "Lechatdort"
 */
function nettoieEspace($nom) {
        return str_replace(' ', '', $nom);
}
/**
 * fonction qui enlève tout caractère qui n'est pas une lettre, ou un chiffre ; remplace la ponctuation par un tiret ; remplace les accents courants par des lettres
 * - "Jérome_1,2" devient "Jerome_1-2"
 **/
function nettoieNom($nom) {
	$tableauPonctuationModifiee = array('"', '&', "'", '+', '#', '<', '>', '*', ',', ';', '.', ':', '/', '\\', '=', '!', '?');	// Ponctuations à remplacer par "-"
	$tableauPonctuationOK = array('-', '_');	// Ponctuation acceptée
	$tabMinA = array(131, 132, 133, 134, 160, 198);		$tabMaxA = array(142, 143, 181, 182, 183, 199);
	$tabMinC = array(135, 184);							$tabMaxC = array(128);
	$tabMinE = array(130, 136, 137, 138);				$tabMaxE = array(144, 210, 211, 212);
	$tabMinI = array(139, 140, 141, 161);				$tabMaxI = array(214, 215, 216, 222);
	$tabMinN = array(164);								$tabMaxN = array(165);
	$tabMinO = array(147, 148, 149, 153, 162);			$tabMaxO = array(224, 226, 227, 228, 229);
	$tabMinU = array(129, 150, 151, 163, 230);			$tabMaxU = array(154, 233, 234, 235);
	$tabMinY = array(152, 236);							$tabMaxY = array(237);
	if ($nom<>"") {
		$lettres = str_split($nom, 1);
		$retour = "";
		foreach($lettres as $lettre) {
			if (in_array($lettre,$tableauPonctuationOK)) {	$retour.= $lettre; }
			elseif (in_array($lettre,$tableauPonctuationModifiee)) {	$retour.= "-"; }
			elseif (in_array(ord($lettre), $tabMinA)) {		$retour.= "a"; }
			elseif (in_array(ord($lettre), $tabMaxA)) {		$retour.= "A"; }
			elseif (in_array(ord($lettre), $tabMinC)) {		$retour.= "c"; }
			elseif (in_array(ord($lettre), $tabMaxC)) {		$retour.= "C"; }
			elseif (in_array(ord($lettre), $tabMinE)) {		$retour.= "e"; }
			elseif (in_array(ord($lettre), $tabMaxE)) {		$retour.= "E"; }
			elseif (in_array(ord($lettre), $tabMinI)) {		$retour.= "i"; }
			elseif (in_array(ord($lettre), $tabMaxI)) {		$retour.= "I"; }
			elseif (in_array(ord($lettre), $tabMinN)) {		$retour.= "n"; }
			elseif (in_array(ord($lettre), $tabMaxN)) {		$retour.= "N"; }
			elseif (in_array(ord($lettre), $tabMinO)) {		$retour.= "o"; }
			elseif (in_array(ord($lettre), $tabMaxO)) {		$retour.= "O"; }
			elseif (in_array(ord($lettre), $tabMinU)) {		$retour.= "u"; }
			elseif (in_array(ord($lettre), $tabMaxU)) {		$retour.= "U"; }
			elseif (in_array(ord($lettre), $tabMinY)) {		$retour.= "y"; }
			elseif (in_array(ord($lettre), $tabMaxY)) {		$retour.= "Y"; }
			elseif ( between($lettre, "ascii_minuscule") 
				or between($lettre, "ascii_majuscule") 
				or between($lettre, "ascii_nombre") ) {	$retour.= $lettre; }
		}
	}
	return $retour;
}
/**
 * fonction qui remplace les accents courants par des lettres
 * - "Jérôme Biguet" devient "Jerome Biguet"
 **/
function nettoieAccents($nom) {
	$nomHTML = htmlentities($nom, ENT_COMPAT, 'UTF-8');
	$listeVoyelles = array('a', 'e', 'i', 'o', 'u', 'y');
	$listeAccents = array('acute;', 'grave;', 'circ;', 'ring;', 'tilde;', 'uml;');
	$tableau = array();
	foreach ($listeVoyelles as $voyelle) {
		$valeursMin = array();
		$valeursMAJ = array();
		foreach ($listeAccents as $accent) {
			$valeursMin[] = "&".strtolower($voyelle).$accent;
			$valeursMAJ[] = "&".strtoupper($voyelle).$accent;
		}
		$tableau[strtolower($voyelle)] = $valeursMin;
		$tableau[strtoupper($voyelle)] = $valeursMAJ;
	}
	$tableau['ae'] = array('&aelig;');	$tableau['AE'] = array('&AElig;');
	$tableau['c'] = array('&ccedil;');	$tableau['C'] = array('&Ccedil;');
	$tableau['oe'] = array('&oelig;');	$tableau['OE'] = array('&OElig;');
	$tableau['s'] = array('&scaron;');	$tableau['S'] = array('&Scaron;');
	$tableau['n'] = array('&ntilde;');	$tableau['N'] = array('&Ntilde;');
	$retour = "";
	if ($nom<>"") {
		$retour = texteVersBDD($nom);
		foreach ($tableau as $lettre=>$listeValeur) {
			$retour = str_replace($listeValeur, $lettre, $retour);
		}
	}
	return $retour;
}
/**
 * fonction qui enlève toute ponctuation d'une chaine, insère : (deux-points) tous les 2 caractères et la met en majuscules
 * - "123456789AbC" devient "12:34:56:78:9A:BC"
 **/
function nettoieAdresseMAC($ether) {
	$tableauPonctuation = array('"', '&', "'", '+', '-', '#', '<', '>', '*', ',', ';', '.', ':', '/', '\\', '=', '_', '!', '?');	// Ponctuations à supprimer
	$sans_ponctuation = strtoupper(str_replace($tableauPonctuation, "", $ether));	// Supprime ponctuation
	$morceaux_deux_caracteres = str_split($sans_ponctuation, 2);	// Sépare les groupes de 2 caractères
	$ether = implode(':', $morceaux_deux_caracteres);	// Réunion de tout avec insertion des :
	return $ether;
}
/**
 * fonction qui enlève toute ponctuation d'une chaine, insère . (point) tous les 2 caractères
 * - "1234" devient "12.34"
 **/
function nettoieTelephone($tel) {
	$tableauPonctuation = array('"', '&', "'", '+', '-', '#', '<', '>', '*', ',', ';', '.', ':', '/', '\\', '=', '_', '!', '?');	// Ponctuations à supprimer
	$sans_ponctuation = strtoupper(str_replace($tableauPonctuation, "", $tel));	// Supprime ponctuation
	$morceaux_deux_caracteres = str_split($sans_ponctuation, 2);	// Sépare les groupes de 2 caractères
	$tel = implode('.', $morceaux_deux_caracteres);	// Réunion de tout avec insertion des .
	return $tel;
}
/**
 * fonction qui supprime toute lettre d'une chaine, remplace toute ponctuation par des points,
 * supprime les points qui en suivent un autre ou sont en début ou fin de chaine, ignore les caractères à partir du 4ème point.
 * - "12a3-45b-67--c89" devient "123.45.67.89"
 **/
function nettoieAdresseIP($ip) {
	$tableauPonctuation = array('"', '&', "'", '+', '-', '#', '<', '>', '*', ',', ';', '.', ':', '/', '\\', '=', '_', '!', '?');	// Ponctuations à remplacer par des points
	$tableauChiffres = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
	if ($ip<>"") {
		$nbr_point = 0;
		$car_prec_point = TRUE;
		$resultat = "";
		$chaine = str_split($ip);
		foreach ($chaine as $car) {
			if (in_array($car, $tableauPonctuation)) {
				if ( (!$car_prec_point) and ($nbr_point<3) ) {
					$resultat.= ".";
					$nbr_point+= 1;
					$car_prec_point = TRUE;
				}
			} elseif (in_array($car, $tableauChiffres)) {
				if ( ($nbr_point<4) ) {
					$resultat.= $car;
					$car_prec_point = FALSE;
				}
			}
		}
	}
	return $resultat;
}
/**
 * fonction qui supprime toute lettre d'une chaine, remplace toute ponctuation par des points,
 * supprime les points qui en suivent un autre ou sont en début ou fin de chaine, ignore les caractères à partir du 5ème point,
 * le 4ème point est remplacé par un slash.
 * - "12a3-45b-67--c89" devient "123.45.67.89"
 **/
function nettoieAdresseReseau($adresse) {
	$tableauPonctuation = array('"', '&', "'", '+', '-', '#', '<', '>', '*', ',', ';', '.', ':', '/', '\\', '=', '_', '!', '?');	// Ponctuations à remplacer par des points
	$tableauChiffres = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
	if ($adresse<>"") {
		$nbr_point = 0;
		$car_prec_point = TRUE;
		$resultat = "";
		$chaine = str_split($adresse);
		foreach ($chaine as $car) {
			if (in_array($car, $tableauPonctuation)) {
				if ( (!$car_prec_point) and ($nbr_point<4) ) {
					$resultat.= ( ($nbr_point<3) ? "." : "/" );
					$nbr_point+= 1;
					$car_prec_point = TRUE;
				}
			} elseif (in_array($car, $tableauChiffres)) {
				if ( ($nbr_point<=4) ) {
					$resultat.= $car;
					$car_prec_point = FALSE;
				}
			}
		}
	}
	return $resultat;
}

$ASCII_SPC_MIN = "àáâãäåæçèéêëìíîïðñòóôõöùúûüýÿžš";
$ASCII_SPC_MAX = "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖÙÚÛÜÝŸŽŠ";
function str2upper($text) {
    global $ASCII_SPC_MIN,$ASCII_SPC_MAX;
    return strtr(strtoupper($text),$ASCII_SPC_MIN,$ASCII_SPC_MAX);
}
function str2lower($text) {
    global $ASCII_SPC_MIN,$ASCII_SPC_MAX;
    return strtr(strtolower($text),$ASCII_SPC_MAX,$ASCII_SPC_MIN);
}
function ucsmart($text) {
    global $ASCII_SPC_MIN;
    return preg_replace(
        '/([^a-z'.$ASCII_SPC_MIN.']|^)([a-z'.$ASCII_SPC_MIN.'])/e',
        '"$1".str2upper("$2")',
        str2lower($text)
    );
}
?>