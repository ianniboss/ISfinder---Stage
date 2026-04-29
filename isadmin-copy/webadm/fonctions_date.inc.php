<?php
/**
 * DDDDD    AA   TTTTTT EEEEEE
 * D    D  A  A    TT   EE
 * D    D AAAAAA   TT   EEEEE
 * D    D A    A   TT   EE
 * DDDDD  A    A   TT   EEEEEE
 **/

function texteVersHTML($texte) {
	return addslashes($texte);
}
function texteDepuisHTML($texte) {
	return stripcslashes($texte);
}
 
define('ACCENTS_HTML','html');
define('ACCENTS','accents');
define('SANS_ACCENTS','aucun');
/**
 * Convertit une chaine en modifiant les accents :
 * @param string $texte Texte &agrave; modifier
 * @param string $source mot cl&eacute; indiquant le type de texte envoyer (par d&eacute;faut, des caract&egrave;res accentu&eacute;s)
 * @param string $resultat mot cl&eacute; indiquant le type de texte &agrave; retourner (par d&eacute;faut, du code HTML)
 * Les mots cl&eacute;s sont : accents ; aucun ; html (insensible &agrave; la casse !).
 **/
function strAccents($texte, $source=ACCENTS, $cible=ACCENTS_HTML) {
	$tableauxAccents = array(ACCENTS=>array('"','&','<','>','œ','¡','¢','£','¤','¥','¦','§','¨','©','ª','«','¬','­', '®','¯',
						'°','±','²','³',"'",'µ','·','¸','¹','º','»','¼','½','¾','¿','À','Á','Â','Ã','Ä',
						'Å','Æ','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ð','Ñ','Ò','Ó','Ô','Õ','Ö','×','Ø',
						'Ù','Ú','Û','Ü','Ý','Þ','ß','à','á','â','ã','ä','å','æ','ç','è','é','ê','ë','ì',
						'í','î','ï','ð','ñ','ò','ó','ô','õ','ö','÷','ø','ù','ú','û','ü','ý','þ','ÿ'),
				 SANS_ACCENTS=>array('"','et','<','>','oe','¡','¢','£','¤','¥','¦','§','¨','(c)','ª','«','¬','­','(R)',
						     '¯','°','+/-','²','³',"'",'µ','·','¸','¹','º','»','1/4','1/2','3/4','¿','A','A',
						     'A','A','A','A','AE','C','E','E','E','E','I','I','I','I','D','N','O','O','O','O',
						     'O','x','0','U','U','U','U','Y','Þ','B','a','a','a','a','a','a','ae','c','e','e','e',
						     'e','i','i','i','i','o','n','o','o','o','o','o','/','0','u','u','u','u','y','þ','y'),
				 ACCENTS_HTML=>array('&quot;','&amp;','&lt;','&gt;','&oelig;','&iexcl;','&cent;','&pound;','&curren;',
						     '&yen','&brvbar;','&sect;','&uml;','&copy;','&ordf;','&laquo;','&not;','&shy;',
						     '&reg;','&masr;','&deg;','&plusmn;','&sup2;','&sup3;','&acute;','&micro;','&middot;',
						     '&cedil;','&sup1;','&ordm;','&raquo;','&frac14;','&frac12;','&frac34;','&iquest;',
						     '&Agrave;','&Aacute;','&Acirc;','&Atilde;','&Auml;','&Aring;','&Aelig','&Ccedil;',
						     '&Egrave;','&Eacute;','&Ecirc;','&Euml;','&Igrave;','&Iacute;','&Icirc;','&Iuml;',
						     '&eth;','&Ntilde;','&Ograve;','&Oacute;','&Ocirc;','&Otilde;','&Ouml;','&times;',
						     '&Oslash;','&Ugrave;','&Uacute;','&Ucirc;','&Uuml;','&Yacute;','&thorn;','&szlig;',
						     '&agrave;','&aacute;','&acirc;','&atilde;','&auml;','&aring;','&aelig;','&ccedil;',
						     '&egrave;','&eacute;','&ecirc;','&euml;','&igrave;','&iacute;','&icirc;','&iuml;',
						     '&eth;','&ntilde;','&ograve;','&oacute;','&ocirc;','&otilde;','&ouml;','&divide;',
						     '&oslash;','&ugrave;','&uacute;','&ucirc;','&uuml;','&yacute;','&thorn;','&yuml;'));
	if ($source==SANS_ACCENTS) {
		$retour = $texte;
	} else {
		$retour = str_replace($tableauxAccents[$source], $tableauxAccents[$cible] ,$texte, $nombre);
	}
	return($retour);
}

/**
 * Renvoie la date du jour au format SQL
 */
function maintenant($complete=FALSE) {
	return date("Y-m-d".( $complete ? " H:i:s" : "" ));
}
 
define('DATE_ANNEE','annee');
define('DATE_MOIS','mois');
define('DATE_JOUR','jour');
define('DATE_HEURE','heure');
define('DATE_MINUTE','minute');
define('DATE_SECONDE','seconde');
define('DATE_FORMAT_JMA','jma_long');
define('DATE_FORMAT_SQL','sql_long');
define('DATE_FORMAT_PHP','php_long');
define('DATE_FORMAT_JMA_COURT','jma_court');
define('DATE_FORMAT_SQL_COURT','sql_court');
define('DATE_FORMAT_PHP_COURT','php_court');
define('DATE_SQL_COURT','Y-m-d');
define('DATE_SQL','Y-m-d H:i:s');
define('DATE_SEULE','date_seule');
define('HEURE_SEULE','heure_seule');

/**
 * Convertit une date depuis un tableau si $Data est un tableau, une date SQL si $Data est une chaine, une date PHP sinon.
 * Les valeurs non indiqu&eacute;es sont remplac&eacute;es par la date $Defaut si elle est indiqu&eacute;e, la date du jour si NULL
 * @param	var	$Data		Date &agrave; convertir, peut &ecirc;tre indiqu&eacute;e sous la forme d'un tableau associatif (is_array),
 * 					au format SQL (is_string) ou PHP (is_numeric ou is_null, par d&eacute;faut)
 * @param	string	$FormatSortie	Format de sortie, &agrave; choisir parmi : DATE_FORMAT_JMA, DATE_FORMAT_SQL, DATE_FORMAT_PHP
 * @param	var	$Defaut		Date par d&eacute;faut, peut &ecirc;tre indiqu&eacute;e sous la forme d'un tableau associatif (is_array),
 * 					au format SQL (is_string) ou PHP (is_numeric, par d&eacute;faut) ;
 * 					toute valeur non indiqu&eacute;e sera remplac&eacute;e par la date courante.
 * Sans aucun param&egrave;tre : retourne la date du jour dans un tableau associatif ('annee','mois','jour','heure','minute','seconde')
 * Fonctionnement :
 * 1 - Met la valeur par d&eacute;faut de la date au format JMA (tableau associatif), en utilisant dateConvert r&eacute;cursivement.
 * 2 - Met la valeur de la date indiqu&eacute;e au format JMA, selon que c'est une chaine (SQL), un nombre (PHP) ou un tableau associatif (JMA) ;
 * 	dans ce dernier cas, recherche une correspondance de cl&eacute; pouvant correspondre.
 * 3 - Modifie les valeurs de chaque &eacute;l&eacute;ment de date pour supporter les dates incoh&eacute;rentes (&eacute;viter de passer des dates incoh&eacute;rentes, tous les cas n'ont pas &eacute;t&eacute; test&eacute;s)
 * 4 - Retourne un r&eacute;sultat correspondant au format de sortie indiqu&eacute; : DATE_FORMAT_JMA, DATE_FORMAT_SQL, DATE_FORMAT_PHP, DATE_FORMAT_JMA_COURT, DATE_FORMAT_SQL_COURT ou DATE_FORMAT_PHP_COURT
 **/
function dateConvert($Data=NULL, $FormatSortie=DATE_FORMAT_JMA, $Defaut=NULL) {
	// G&egrave;re la valeur par d&eacute;faut : prend la date du jour si elle n'est pas indiqu&eacute;e
	// $dateDefaut = ( (is_null($Defaut)) ? dateConvert(date(DATE_SQL)) : dateConvert($Defaut) );
	$dateJMA = array(DATE_JOUR=>NULL,DATE_MOIS=>NULL,DATE_ANNEE=>NULL,DATE_HEURE=>NULL,DATE_MINUTE=>NULL,DATE_SECONDE=>NULL);
	if (!is_null($Data)) {	// Null : date du jour, sinon lit la date...
		if (is_numeric($Data)) {	// Format PHP
			$Data = date(DATE_SQL,$Data);
		}
		if (is_array($Data)) {		// Format JMA (tableau associatif)
			// Recherche les valeurs correspondantes dans le tableau d'entr&eacute;e
			foreach ($Data as $cle=>$valeur) {
				if ( array_search(strtolower($cle),array(DATE_JOUR,'j','jour','d','day'))!==FALSE ) { $dateJMA[DATE_JOUR] = $valeur; }
				if ( array_search(strtolower($cle),array(DATE_MOIS,'m','mois','month'))!==FALSE ) { $dateJMA[DATE_MOIS] = $valeur; }
				if ( array_search(strtolower($cle),array(DATE_ANNEE,'a','annee','y','year'))!==FALSE ) { $dateJMA[DATE_ANNEE] = $valeur; }
				if ( array_search(strtolower($cle),array(DATE_HEURE,'h','heure','hour'))!==FALSE ) { $dateJMA[DATE_HEURE] = $valeur; }
				if ( array_search(strtolower($cle),array(DATE_MINUTE,'min','minute'))!==FALSE ) { $dateJMA[DATE_MINUTE] = $valeur; }
				if ( array_search(strtolower($cle),array(DATE_SECONDE,'s','sec','seconde','second'))!==FALSE ) { $dateJMA[DATE_SECONDE] = $valeur; }
			}
		}
		if (is_string($Data)) {		// Format SQL
			if (strlen($Data)>=4) {		$dateJMA[DATE_ANNEE] = substr($Data,0,4);	}
			if (strlen($Data)>=7) {		$dateJMA[DATE_MOIS] = substr($Data,5,2);	}
			if (strlen($Data)>=10) {	$dateJMA[DATE_JOUR] = substr($Data,8,2);	}
			if (strlen($Data)>=13) {	$dateJMA[DATE_HEURE] = substr($Data,11,2);	}
			if (strlen($Data)>=16) {	$dateJMA[DATE_MINUTE] = substr($Data,14,2);	}
			if (strlen($Data)>=18) {	$dateJMA[DATE_SECONDE] = substr($Data,17,2);	}
		}
	}
	// Remplace les valeurs par les valeurs en cours si elles ne sont pas indiqu&eacute;es
	if (is_null($dateJMA[DATE_JOUR])) {	$dateJMA[DATE_JOUR] = date("d");	}
	if (is_null($dateJMA[DATE_MOIS])) {	$dateJMA[DATE_MOIS] = date("m");	}
	if (is_null($dateJMA[DATE_ANNEE])) {	$dateJMA[DATE_ANNEE] = date("Y");	}
	if (is_null($dateJMA[DATE_HEURE])) {	$dateJMA[DATE_HEURE] = date("H");	}
	if (is_null($dateJMA[DATE_MINUTE])) {	$dateJMA[DATE_MINUTE] = date("i");	}
	if (is_null($dateJMA[DATE_SECONDE])) {	$dateJMA[DATE_SECONDE] = date("s");	}
	// Ram&egrave;ne les valeurs ans les valeur acceptables (&eacute;vite mois = 13, par exemple)
	if ($dateJMA[DATE_ANNEE]<1000) {	$dateJMA[DATE_ANNEE]+= 2000;		}
	while ($dateJMA[DATE_SECONDE]>=60) {	$dateJMA[DATE_MINUTE]++;	$dateJMA[DATE_SECONDE]-= 60;	}
	while ($dateJMA[DATE_MINUTE]>=60) {	$dateJMA[DATE_HEURE]++;		$dateJMA[DATE_MINUTE]-= 60;	}
	while ($dateJMA[DATE_HEURE]>=24) {	$dateJMA[DATE_JOUR]++;		$dateJMA[DATE_HEURE]-= 24;	}
	while ($dateJMA[DATE_JOUR]>nbJourMois($dateJMA[DATE_MOIS],$dateJMA[DATE_ANNEE])) {	$dateJMA[DATE_JOUR]-= nbJourMois($dateJMA[DATE_MOIS]);	$dateJMA[DATE_MOIS]+= 1;	}
	while ($dateJMA[DATE_MOIS]>12) {	$dateJMA[DATE_ANNEE]++;		$dateJMA[DATE_MOIS]-= 12;	}
	while ($dateJMA[DATE_SECONDE]<0) {	$dateJMA[DATE_MINUTE]--;	$dateJMA[DATE_SECONDE]+= 60;	}
	while ($dateJMA[DATE_MINUTE]<0) {	$dateJMA[DATE_HEURE]--;		$dateJMA[DATE_MINUTE]+= 60;	}
	while ($dateJMA[DATE_HEURE]<0) {	$dateJMA[DATE_JOUR]--;		$dateJMA[DATE_HEURE]+= 24;	}
	while ($dateJMA[DATE_JOUR]<1) {		$dateJMA[DATE_JOUR]+= nbJourMois($dateJMA[DATE_MOIS]);	$dateJMA[DATE_MOIS]-= 1;	}
	while ($dateJMA[DATE_MOIS]<1) {		$dateJMA[DATE_ANNEE]--;		$dateJMA[DATE_MOIS]+= 12;	}
	// Modifie le format de sortie si besoin :
	$datePHPCourt = mktime(0,0,0,$dateJMA[DATE_MOIS],$dateJMA[DATE_JOUR],$dateJMA[DATE_ANNEE]);
	$datePHP = mktime($dateJMA[DATE_HEURE],$dateJMA[DATE_MINUTE],$dateJMA[DATE_SECONDE],$dateJMA[DATE_MOIS],$dateJMA[DATE_JOUR],$dateJMA[DATE_ANNEE]);
	switch ($FormatSortie) {
		case DATE_FORMAT_JMA:		$resultat = $dateJMA;	break;
		case DATE_FORMAT_SQL:		$resultat = date(DATE_SQL,$datePHP);	break;
		case DATE_FORMAT_PHP:		$resultat = $datePHP;	break;
		case DATE_FORMAT_JMA_COURT:	$resultat = array(DATE_JOUR=>$dateJMA[DATE_JOUR], DATE_MOIS=>$dateJMA[DATE_MOIS], DATE_ANNEE=>$dateJMA[DATE_ANNEE]);	break;
		case DATE_FORMAT_SQL_COURT:	$resultat = date(DATE_SQL_COURT,$datePHP);	break;
		case DATE_FORMAT_PHP_COURT:	$resultat = $datePHPCourt;	break;
	}
	return $resultat;
}

/**
 * Retourne le nombre de jour dans le mois indiqu&eacute;
 * Si le mois et l'ann&eacute;e ne sont pas indiqu&eacute;s, utilise le mois et l'ann&eacute;e en cours
 * */
function nbJourMois($mois=NULL,$annee=NULL) {
	if (!is_numeric($mois)) {	$mois = date("n");	}
	if (!is_numeric($annee)) {	$annee = date("y");	}
	while ($mois<1) {	$annee--;	$mois+= 12;	}
	while ($mois>12) {	$annee++;	$mois-= 12;	}
	switch ($mois) {
		case 4:		case 6: case 9: case 11: $retour = 30;		break;
		case 2:		$retour = ( (($annee % 4)==0) ? 29 : 28 );	break;
		default:	$retour = 31; break;
	}
	return $retour;
}

/**
 * Retourne le nombre de jour dans l'ann&eacute;e indiqu&eacute;e
 * Si l'ann&eacute;e n'est pas indiqu&eacute;e, utilise l'ann&eacute;e en cours
 * */
function nbJourAnnee($annee=NULL) {
	if (!is_numeric($annee)) {	$annee = date("y");	}
	$retour = ( (($annee % 4)==0) ?	366 : 365 );
	return $retour;
}

/**
 * Renvoie une date au format Fran&ccedil;ais : remplace tous les mois et jours US par leur version FR
 **/
function dateFR($date) {
	return str_replace($GLOBALS['US'],$GLOBALS['FR'],$date);
}

// Retourne une chaine de caract&egrave;re sous la forme vendredi 5 mars 2012,  
function dateSQLLisible($dateSQL) {
	$retour = date("l j F Y",strtotime($dateSQL));
	if ( getSession('Page_Langue')=='fr' ) {
		$retour = dateFR($retour);
	}
	return $retour;
}
// Retourne une chaine de caract&egrave;re sous la forme "H:mm",  
function heureSQLLisible($dateSQL) {
	return date("G:i",strtotime($dateSQL));
}
// Retourne une chaine de caract&egrave;re sous la forme vendredi 5 mars 2012, H:mm
function horodatageSQLLisible($dateSQL) {
	$retour = date("l j F Y, G:i",strtotime($dateSQL));
	if ( getSession('Page_Langue')=='fr' ) {	$retour = dateFR($retour); }
	return $retour;
}

?>