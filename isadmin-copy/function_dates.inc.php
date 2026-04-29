<?PHP

/*	fonction qui prend une date au format PHP JJ/MM/AAAA pour la convertir au format SQL AAAA-MM-JJ
	renvoie la date convertie (string), FALSE si la chaine ne peut pas être convertie, NULL si la chaine est vide
 */
function dateVersBDD($date) {
	if (preg_match('#^[0-9]{1,2}/[0-9]{1,2}/[0-9]{2,4}$#', $date)==1) {	// date avec / considère le format JJ/MM/AAAA
		list($jour, $mois, $annee) = explode('/', $date, 3);
		if ($jour<1) {	$jour = 1; } elseif ($jour>31) {	$jour = 31; }
		if ($mois<1) {	$mois = 1; } elseif ($mois>12) {	$mois = 12; }
		if ($annee<100) {	$annee+=2000; }
		$retour = date("Y-m-d", mktime(0, 0, 0, $mois, $jour, $annee));
	} else {	$retour = $date; }
	return $retour;
}
/*	fonction qui prend une date au format SQL AAAA-MM-JJ pour la convertir au format PHP JJ/MM/AAAA
	renvoie la date convertie (string), FALSE si la chaine ne peut pas être convertie, NULL si la chaine est vide
 */
function dateDepuisBDD($date) {
	if (preg_match('#^[0-9]{4}-[0-9]{2}-[0-9]{2}$#', $date)==1) {	// date avec - considère le format AAAA-MM-JJ
		list($annee, $mois, $jour) = explode('-', $date, 3);
		$retour = date("d/m/Y", mktime(0, 0, 0, $mois, $jour, $annee));
	} else {	$retour = $date; }
	return $retour;
}
/*	fonction qui prend une date au format SQL AAAA-MM-JJ ou Humain JJ/MM/AAAA pour la convertir en date PHP (integer)
	renvoie un entier si OK, FALSE si la chaine ne peut pas être convertie, NULL si la chaine est vide.
	NB : cette fonction sert de base aux fonctions dateHumain et dateSQL !
 */
function datePHP($date) {
	$retour = NULL;
	if (is_string($date)) {	// chaine de caractère, essaye de reconnaitre le format de la date !
		if (preg_match('#^[0-9]{4}-[0-9]{2}-[0-9]{2}$#', $date)==1) {	// date avec - considère le format AAAA-MM-JJ
			list($annee, $mois, $jour) = explode('-', $date, 3);
			$retour = mktime(0, 0, 0, $mois, $jour, $annee);
		}
		elseif (preg_match('#^[0-9]{1,2}/[0-9]{1,2}/[0-9]{2,4}$#', $date)==1) {	// date avec / considère le format JJ/MM/AAAA
			list($jour, $mois, $annee) = explode('/', $date, 3);
			if ($jour<1) {	$jour = 1; } elseif ($jour>31) {	$jour = 31; }
			if ($mois<1) {	$mois = 1; } elseif ($mois>12) {	$mois = 12; }
			if ($annee<100) {	$annee+=2000; }
			$retour = mktime(0, 0, 0, $mois, $jour, $annee);
		}
		else {	$retour = FALSE;	}
	}
	elseif (is_int($date)) {	$retour = $date;	}
	return $retour;
}
/*	fonction qui prend une date pour la convertir en date SQL (AAAA-MM-JJ)
	les formats SQL (AAAA-MM-JJ), Humain (JJ/MM/AAAA) ou PHP (integer) sont supportés.
	renvoie un entier si OK, FALSE si la chaine ne peut pas être convertie, NULL si la chaine est vide.
 */
function dateSQL($date) {		return date("Y-m-d", datePHP($date));	}
/*	fonction qui prend une date pour la convertir en date Humain (JJ/MM/AAAA)
	les formats SQL (AAAA-MM-JJ), Humain (JJ/MM/AAAA) ou PHP (integer) sont supportés.
	renvoie un entier si OK, FALSE si la chaine ne peut pas être convertie, NULL si la chaine est vide
 */
function dateHumain($date) {	return date("d/m/Y", datePHP($date));	}
/*	fonction qui prend une date pour la convertir en date Humain (JJ/MM/AAAA)
	les formats SQL (AAAA-MM-JJ), Humain (JJ/MM/AAAA) ou PHP (integer) sont supportés.
	renvoie un entier si OK, FALSE si la chaine ne peut pas être convertie, NULL si la chaine est vide
 */
function dateHumainUS($date) {	return date("m/d/Y", datePHP($date));	}

/*	fonction qui prend une date/heure au format SQL AAAA-MM-JJ ou Humain JJ/MM/AAAA pour la convertir en date/heure PHP (integer)
	renvoie un entier si OK, FALSE si la chaine ne peut pas être convertie, NULL si la chaine est vide.
	NB : cette fonction sert de base aux fonctions dateHumain et dateSQL !
 */
function dateHeurePHP($dateHeure) {
	$retour = NULL;
	if (is_string($dateHeure)) {	// chaine de caractère, essaye de reconnaitre le format de la date !
		list($date, $horaire) = explode(' ', $dateHeure, 2);
		// Gère les heures d'abord (besoin dans le mktime...)
		$chaines = explode(':', $horaire, 4);
		if ($chaines===FALSE) {	$chaines = array(0,0,0);	}
		list($heure, $minute, $seconde, $rebus) = $chaines;
		if (between($heure, 0, 23)==FALSE) {	$heure = 0;		}
		if (between($minute, 0, 59)==FALSE) {	$minute = 0;	}
		if (between($seconde, 0, 59)==FALSE) {	$seconde = 0;	}
		// Gère la date si lisible...
		if (preg_match('#^[0-9]{4}-[0-9]{2}-[0-9]{2}$#', $date)==1) {	// date avec - considère le format AAAA-MM-JJ
			list($annee, $mois, $jour) = explode('-', $date, 3);
			$retour = mktime($heure, $minute, $seconde, $mois, $jour, $annee);
		}
		elseif (preg_match('#^[0-9]{1,2}/[0-9]{1,2}/[0-9]{2,4}$#', $date)==1) {	// date avec / considère le format JJ/MM/AAAA
			list($jour, $mois, $annee) = explode('/', $date, 3);
			if ($jour<1) {	$jour = 1; } elseif ($jour>31) {	$jour = 31; }
			if ($mois<1) {	$mois = 1; } elseif ($mois>12) {	$mois = 12; }
			if ($annee<100) {	$annee+=2000; }
			$retour = mktime($heure, $minute, $seconde, $mois, $jour, $annee);
		}
		else {	$retour = FALSE;	}
	}
	elseif (is_int($dateHeure)) {	$retour = $dateHeure;	}
	return $retour;
}
/*	fonction qui prend une date/heure pour la convertir en date/heure SQL (AAAA-MM-JJ)
	les formats SQL (AAAA-MM-JJ), Humain (JJ/MM/AAAA) ou PHP (integer) sont supportés.
	renvoie un entier si OK, FALSE si la chaine ne peut pas être convertie, NULL si la chaine est vide.
 */
function dateHeureSQL($dateHeure) {		return date("Y-m-d H:i:s", dateHeurePHP($dateHeure));	}
/*	fonction qui prend une date/heure pour la convertir en date/heure Humain (JJ/MM/AAAA)
	les formats SQL (AAAA-MM-JJ), Humain (JJ/MM/AAAA) ou PHP (integer) sont supportés.
	renvoie un entier si OK, FALSE si la chaine ne peut pas être convertie, NULL si la chaine est vide
 */
function dateHeureHumain($dateHeure) {	return date("d/m/Y G:i:s", dateHeurePHP($dateHeure));	}
/*	fonction qui prend une date/heure pour la convertir en date/heure Humain (JJ/MM/AAAA)
	les formats SQL (AAAA-MM-JJ), Humain (JJ/MM/AAAA) ou PHP (integer) sont supportés.
	renvoie un entier si OK, FALSE si la chaine ne peut pas être convertie, NULL si la chaine est vide
 */
function dateHeureHumainUS($dateHeure) {	return date("m/d/Y G:i:s", dateHeurePHP($dateHeure));	}

/*	fonction qui prend une heure au format 00:00:00 pour la convertir en heure PHP (integer)
	renvoie un entier si OK, FALSE si la chaine ne peut pas être convertie, NULL si la chaine est vide.
	NB : cette fonction sert de base aux fonctions heureHumain et heureSQL !
 */ 
function heurePHP($heure) {
	$retour = NULL;
	if (is_string($heure)) {	// chaine de caractère, essaye de reconnaitre le format de la date !
		$chaines = explode(':', $heure, 4);
		if ($chaines!==FALSE) {	
			list($heure, $minute, $seconde, $rebus) = $chaines;
			if (between($heure, 0, 23)==FALSE) {	$heure = 0;		}
			if (between($minute, 0, 59)==FALSE) {	$minute = 0;	}
			if (between($seconde, 0, 59)==FALSE) {	$seconde = 0;	}
			$retour = mktime($heure, $minute, $seconde);
		} else {	$retour = FALSE;	}
	}
	elseif (is_int($heure)) {	$retour = $heure;	}
	return $retour;
}
/*	fonction qui prend une heure pour la convertir en heure SQL (00:00:00)
	renvoie une chaine si OK, FALSE si la chaine ne peut pas être convertie, NULL si la chaine est vide
	la différence avec la fonction heureHumain est que l'heure comporte forcément 2 chiffres
 */
function heureSQL($dateHeure) {		return date("H:i:s", heurePHP($dateHeure));	}
/*	fonction qui prend une heure pour la convertir en heure Humain (0:00:00)
	renvoie une chaine si OK, FALSE si la chaine ne peut pas être convertie, NULL si la chaine est vide
	la différence avec la fonction heureSQL est que l'heure peut ne comporter qu'un seul chiffre
 */
function heureHumain($dateHeure) {	return date("G:i:s", heurePHP($dateHeure));	}

?>