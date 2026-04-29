<?PHP
/**
 * Retourne le contenu de la variable POST si elle existe, 
 	sinon le contenu de la variable GET si elle existe, 
	sinon le contenu de la variable SESSION si elle existe, 
	sinon la valeur de $defaut, qui est la valeur NULL par défaut
 * */
function getVar($variable, $defaut = NULL) {
	return ( (isset($_POST[$variable])) ? $_POST[$variable] : ( (isset($_GET[$variable])) ? $_GET[$variable] : getSession($variable, $defaut) ) );
}
/**
 * Retourne le contenu de la variable FILE si elle existe ET que le champ "name" n'est pas vide, sinon la valeur NULL.
   Si $variable n'est pas indiqué, retourne la variable FILES, amputée des lignes dont le champ "name" est vide : l'index sera le nom de la variable.
 * */
function getFichier($variable = NULL) {
	function expliqueErreur($erreur) {
		switch($erreur) {
			case UPLOAD_ERR_OK:			return "";																							break;
			case UPLOAD_ERR_INI_SIZE:	return "Fichier trop volumineux (limitée par PHP) : réduisez sa taille";							break;
			case UPLOAD_ERR_FORM_SIZE:	return "Fichier trop volumineux (limitée par le formulaire) : réduisez sa taille";					break;
			case UPLOAD_ERR_PARTIAL:	return "Téléchargement du fichier incomplet : si cela se reproduit, contactez l'administrateur";	break;
			case UPLOAD_ERR_NO_FILE:	return "Aucun fichier téléchargé : si cela se reproduit, contactez l'administrateur";				break;
			case UPLOAD_ERR_NO_TMP_DIR:	return "Dossier temporaire manquant : contactez l'administrateur";									break;
			case UPLOAD_ERR_CANT_WRITE:	return "Dossier temporaire en lecture seule : contactez l'administrateur";							break;
			case UPLOAD_ERR_EXTENSION:	return "Téléchargement bloqué par une extension PHP : contactez l'administrateur";					break;
			default:					return "Erreur inconnue (n°".$erreur.") au téléchargement de fichier";								break;
		}
	}
	if (is_null($variable)) {	// Variable vide, renvoi tous les fichiers
		$listeFichiersNonVides = array();
		if (count($_FILES)>0) {	// Il y a des fichiers !
			foreach ($_FILES as $nomVariable=>$unFichier) {
				if ($unFichier["name"]!="") {
					$listeFichiersNonVides[$nomVariable] = $unFichier;
					$unFichier['erreur'] = expliqueErreur($unFichier['error']);
				}
			}
		}
		return ( ($listeFichiersNonVides==array()) ? NULL : $listeFichiersNonVides );
	} else {
		$retour = NULL;
		if (isset($_FILES[$variable])) {
			$unFichier = $_FILES[$variable];
			if ($unFichier["name"]!="") {
				$retour = $unFichier;
				$unFichier['erreur'] = expliqueErreur($unFichier['error']);
			} else {
				$retour = NULL;
			}
		}
		return $retour;
	}
}
/**
 * Retourne VRAI si le fichier indiqué est une image, FALSE sinon (ou s'il n'existe pas)
 **/
function estImage($fichier) {
	$extensions_valides = array( 'jpg' , 'jpeg' , 'gif' , 'png' );
	return (in_array(strtolower(substr(strrchr($fichier, '.'), 1)),$extensions_valides));
}

/**
 * Retourne le contenu de la variable SESSION si elle existe, sinon la valeur de $defaut, qui est la valeur NULL par défaut
 * Ajoute la constante PREFIXE_SESSION
 * Retourne la valeur par défaut si la variable n'est pas indiquée ou si elle n'existe pas
 * */
function getSession($variable = NULL, $defaut = NULL) {
	$retour = $defaut;
	if (!is_null($variable)) {
		$variable = strtoupper($variable);
		if (isset($_SESSION[PREFIXE_SESSION.$variable])) {
			$retour = $_SESSION[PREFIXE_SESSION.$variable];
		}
	}
	return $retour;
}
/**
 * Crée une variable SESSION, la vide si $valeur n'est pas indiquée
 * Ne fait rien si la variable n'est pas indiquée
 * */
function setSession($variable = NULL, $valeur = NULL) {
	if (!is_null($variable)) {
		if (is_null($valeur)) {	$valeur = ''; }
		$_SESSION[PREFIXE_SESSION.strtoupper($variable)] = $valeur;
	}
}
/**
 * Supprime toutes les variables de session préfixées par PREFIXE_SESSION
 * Si $filtre est indiqué, ne supprime que les variables de session dont le nom contient le filtre
 * */
function flushSession($filtre=NULL) {
	foreach($_SESSION as $cle=>$valeur) {
		if (substr($cle,0,strlen(PREFIXE_SESSION))==PREFIXE_SESSION) {	// prefixe de session présent !
			if ( (is_null($filtre)) or (strpos($cle, $filtre)!==false) ) {	// chaine de filtrage absente ou trouvée
				$_SESSION[$cle] = '';
			}
		}
	}
}

/**
 * échange les valeurs des deux variables indiquées
 * retourne VRAI si l'échange a pu se faire
 * retourne FALSE si l'échange n'a pas pu se faire, ou si les deux valeurs sont strictement identiques (type et valeur)
 **/
function swapVar(&$var1, &$var2) {
	if ($var1!==$var2) {
		$ex_var1 = $var1;
		$ex_var2 = $var2;
		$var1 = $ex_var2;
		$var2 = $ex_var1;
		$retour = ( ($var1!=$ex_var1) || ($var2!=$ex_var2) );
	} else {	$retour = FALSE; }
	return $retour;
}

/**
 * renvoie la valeur TRUE si la valeur est incluse entre le minimum et le maximum
 * @param	variable	$valeur				Valeur à comparer
 * @param	variable	$minimum			Valeur minimum, par défaut 0 si la valeur maximum est un nombre, "" si la valeur maximum est une chaine
 * @param	variable	$maximum			Valeur maximum, obligatoire.
 * @param	boolean		$valeurs_incluses	TRUE (par défaut : la fonction retournera TRUE si $valeur==$minimum ou $valeur==$maximum
 *		NOTE :	Si une seule valeur est indiquée, elle est réputée être le maximum ; Dans ce cas, la valeur sera comparée en fonction du type de $maximum.
 *				Si $minimum est un tableau, $maximum et $valeurs_incluses seront ignorés, et la fonction in_array sera employée pour définir la valeur de retour
 *				Les noms $minimum et $maximum ne sont qu'indicatifs, les valeurs peuvent être inversées.
 *				Si les types de $minimum et $maximum ne peuvent pas être comparés à $valeur, la fonction renverra un résultat bizarre.
 *				Cas particulier : Si $valeur est NULL ou booléen, renvoi la valeur FALSE (ne peut pas être inclu si pas du bon type !)
 **/
function estInclu($valeur, $minimum, $maximum=NULL, $valeurs_incluses=NULL) {
	if ( (is_null($valeur)) or (is_bool($valeur)) ) {	return FALSE;	}
	if (is_bool($maximum)) {	// $maximum est boolean, elle correspond donc en fait à $valeurs_incluses !
		$valeurs_incluses = $maximum;
		$maximum = NULL;
	}
	if (is_null($valeurs_incluses)) {	$valeurs_incluses = TRUE;	}
	if (is_array($minimum)) {	// on a indiqué un tableaux... on emploie la fonction in_array dans laquelle $maximum est réputé être $strict, et on ignore $valeurs_incluses...
		if (is_null($maximum)) {	$maximum = FALSE;	}
		return in_array($valeur, $minimum, $maximum);
	} else {	// $minimum n'est pas un tableau
		if (is_null($maximum)) {	// $maximum n'est pas indiqué, $minimum est donc le maximum !
			$maximum = $minimum;
			if (is_int($maximum)) {	$minimum = 0;	} else {	$minimum = "";	}
		}
		if ($minimum>$maximum) {	swapVar($minimum, $maximum);	}
		return ( (($valeurs_incluses) and ($valeur>=$minimum) and ($valeur<=$maximum)) or ((!$valeurs_incluses) and ($valeur>$minimum) and ($valeur<$maximum)) );
	}
}

/**
 * convertit une date dans un autre format.
 * retourne un nombre pour un format PHP, une chaine dans les autres cas.
 * @param	int		$date		Date au format PHP
 *			string				Date dans un autre format
 *								La valeur par défaut est la date courante du système.
 * @param	string	$format		Format sous la forme d'une chaine ou d'une valeur definie ci-dessous,
 *								Si la valeur n'est pas reconnue, la valeur par défaut (qui est DATE_FORMAT_SQL) est utilisée.
 * @param	boolean	$complete	Indique si la date renvoyée doit contenir les heures, par défaut NON.
 **/
define('DATE_FORMAT_PHP', "PHP");
define('DATE_FORMAT_SQL', "SQL");
define('DATE_FORMAT_HUMAIN', "Humain");
function dateConv($date=NULL, $format=NULL, $complete=FALSE) {	
	if (!in_array($format, array(DATE_FORMAT_PHP, DATE_FORMAT_HUMAIN))) {	// Si format différent nommément PAS indiqué : format par défaut !
		$format = DATE_FORMAT_SQL;
	}
	if (is_null($date)) {	// date non indiquée, prend la date du jour
		list($annee, $mois, $jour, $heure, $minute, $seconde) = explode('-', date("Y-m-d-H-i-s"));
	}
	elseif (is_int($date)) {	// Date est un nombre, considère que c'est une date PHP
		list($annee, $mois, $jour, $heure, $minute, $seconde) = explode('-', date("Y-m-d-H-i-s", $date));
	}	
	elseif (is_string($date)) {	// La date est une chaine NON VIDE
		$jour = 1;
		$mois = 1;
		$annee = date("Y");
		$explosion = explode(' ', $date, 2);
		$un = $explosion[0];
		$deux = ( (count($explosion)>1) ? $explosion[1] : "" );
		if (strpos($un, '/')!==FALSE) {	// Format humain
			$explosion = explode('/', $un, 3);
			$jour = $explosion[0];
			$mois = ( (count($explosion)>1) ? $explosion[1] : "" );
			$annee = ( (count($explosion)>2) ? $explosion[2] : "" );
		} elseif (strpos($un, '-')!==FALSE) {	// Format SQL
			$explosion = explode('-', $un, 3);
			$annee = $explosion[0];
			$mois = ( (count($explosion)>1) ? $explosion[1] : "" );
			$jour = ( (count($explosion)>2) ? $explosion[2] : "" );
		}
		$explosion = explode(':', $deux, 3);
		$heure = $explosion[0];
		$minute = ( (count($explosion)>1) ? $explosion[1] : "" );
		$seconde = ( (count($explosion)>2) ? $explosion[2] : "" );
	}
	if (!estInclu($jour, 1, 31)) {		$jour = 1;		}
	if (!estInclu($mois, 1, 12)) {		$mois = 1;		}
	if (!estInclu($heure, 0, 23)) {		$heure = 0;		}
	if (!estInclu($minute, 0, 59)) {	$minute = 0;	}
	if (!estInclu($seconde, 0, 59)) {	$seconde = 0;	}
	$datePHP = mktime((int)$heure, (int)$minute, (int)$seconde, (int)$mois, (int)$jour, (int)$annee);
	switch ($format) {
		case DATE_FORMAT_PHP:		return $datePHP;											break;
		case DATE_FORMAT_SQL:		return date("Y-m-d".( $complete?" H:i:s":"" ), $datePHP);	break;
		case DATE_FORMAT_HUMAIN:	return date("d/m/Y".( $complete?" H:i:s":"" ), $datePHP);	break;
	}
}

/* Cette fonction renvoie un tableau dans tous les cas, quelque soit l'entrée...
 * Si $variable est un tableau, elle est retournée sans modification, si ce n'est pas un tableau, c'est array($variable) qui est retourné.
 */
function all2array($variable) {
    return (is_array($variable)?$variable:array($variable)); 
}

?>