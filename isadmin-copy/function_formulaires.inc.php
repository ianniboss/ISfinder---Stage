<?PHP

/**
 * guillemet retourne une chaine dont les caractères ' sont remplacés par &acute;
 **/
function guillemet($chaine) {	return ( (is_string($chaine)) ? str_replace("'", "&acute;",$chaine) : $chaine );	}

// Crée la balise de début de formulaire
function formBaliseTop($nom="formulaire", $action=NULL) {
	if (is_null($action)) {	$action = $_SERVER['PHP_SELF'];	}
	return "<form action='".$action."' name='".$nom."'>";
}
// Crée la balise de fin de formulaire
function formBaliseBottom() {
	return "</form>";
}
// Crée un bouton de formulaire renvoyant $_POST[$nom]=$valeur, survol=$texte, style=$style
function formBouton($nom='action', $valeur='OK', $texte=null, $style="") {	return creerFormBouton($nom, $valeur, $texte, $style);	}
// Crée un champ texte renvoyant $_POST[$nom]=valeur saisie, par défaut $defaut, survol=$info, largeur=$largeur caractères, modifiable si $actif=TRUE
function formTexte($nom, $info, $defaut, $largeur=20, $actif=false) {	 return champTexte($nom, $info, $defaut, $largeur, $actif);	}
// Crée un champ password renvoyant $_POST[$nom]=valeur saisie, par défaut "", survol=$info, largeur=$largeur caractères, modifiable si $actif=TRUE
function formPasse($nom, $info, $largeur=20, $actif=false) {             return champTexte($nom, $info, "", $largeur, $actif, "password");	}
// Crée un champ email (sorte de texte) renvoyant $_POST[$nom]=valeur saisie, par défaut $defaut, survol=$info, largeur=$largeur caractères, modifiable si $actif=TRUE
function formEmail($nom, $info, $defaut, $largeur=20, $actif=false) {	 return champTexte($nom, $info, $defaut, $largeur, $actif, "email");	}
// Crée un champ tel (sorte de texte) renvoyant $_POST[$nom]=valeur saisie, par défaut $defaut, survol=$info, largeur=$largeur caractères, modifiable si $actif=TRUE
function formTel($nom, $info, $defaut, $largeur=20, $actif=false) {	 return champTexte($nom, $info, $defaut, $largeur, $actif, "tel");	}
// Crée un champ url (sorte de texte) renvoyant $_POST[$nom]=valeur saisie, par défaut $defaut, survol=$info, largeur=$largeur caractères, modifiable si $actif=TRUE
function formUrl($nom, $info, $defaut, $largeur=20, $actif=false) {	 return champTexte($nom, $info, $defaut, $largeur, $actif, "url");	}
// Crée un champ search (sorte de texte) renvoyant $_POST[$nom]=valeur saisie, par défaut $defaut, survol=$info, largeur=$largeur caractères, modifiable si $actif=TRUE
function formSearch($nom, $info, $defaut, $largeur=20, $actif=false) {	 return champTexte($nom, $info, $defaut, $largeur, $actif, "search");	}
// Crée un champ number (sorte de texte) renvoyant $_POST[$nom]=valeur saisie, par défaut $defaut, survol=$info, largeur=$largeur caractères, modifiable si $actif=TRUE
// Par rapport aux autres types de champ texte, on peut spécifier des valeurs mini et maxi, et un pas d'incrément
function formNumber($nom, $info, $defaut, $min=NULL, $max=NULL, $step=1, $actif=false) { return champTexte($nom, $info, $defaut, 10, $actif, "search", $min, $max, $step);	}
// Crée un champ number (sorte de texte) renvoyant $_POST[$nom]=valeur saisie, par défaut $defaut, survol=$info, largeur=$largeur caractères, modifiable si $actif=TRUE
// Par rapport aux autres types de champ texte, on peut spécifier des valeurs mini et maxi, et un pas d'incrément
function formRange($nom, $info, $defaut, $min=0, $max=100, $step=1, $actif=false) { return champTexte($nom, $info, $defaut, 10, $actif, "search", $min, $max, $step);	}

/**
 * champID retourne un champ contenant une variable cachée et sa valeur
 * @param string $nom		Nom de la variable POST du formulaire, par défaut "deja_vu"
 * @param string $defaut	Valeur de la variable, par défaut chaine vide, sauf si le nom est "deja_vu" "deja_vu"
 **/
function champID($nom=NULL, $defaut=NULL) {
	if (is_null($nom)) {	$nom = 'deja_vu';	}
	if ($nom=='deja_vu') {	$defaut = $nom;		}
	if (is_null($defaut)) {	$defaut = "";		}
	$retour = "<input type='hidden' name='".$nom."' value='".guillemet($defaut)."' />";
	return $retour;
}

/**
 * champTexte retourne un champ de type de texte
 * @param string $nom		Nom de la variable POST du formulaire
 * @param string $info		Informations affichées au survol
 * @param string $defaut	Valeur par défaut
 * @param integer $largeur	Largeur du champ en caractères
 * @param boolean $actif	Indique si le champs est modifiable ou non
 * @param boolean $type         Indique le type de champ, text par défaut, peut être aussi égal à tel, url, email, search
 **/
function champTexte($nom, $info, $defaut, $largeur=20, $actif=false, $type="text", $min=NULL, $max=NULL, $step=NULL) {
	$info = guillemet($info);	
	$defaut = guillemet($defaut);
        if (!in_array($type, array("tel", "url", "email", "search", "number", "range", "password"))) {  $type = "text";  }
	$retour = "<span class='formTexte' alt='".$info."' title='".$info."'>";
	if ($actif) {
		$retour.= "<input type='".$type."' id='".$nom."' name='".$nom."' size='".$largeur;
		$retour.= "' alt='".$info."' title='".$info."' value='".$defaut."' />";
	} else {
		$retour.= champID($nom, $defaut).$defaut;
	}
	$retour.= "</span>";
	return $retour;
}

/**
 * champTexteLong retourne un champ de type de texte multilignes
 * @param string $nom		Nom de la variable POST du formulaire
 * @param string $info		Informations affichées au survol
 * @param integer $largeur	Largeur du champ en caractères
 * @param integer $hauteur	Hauteur du champ en lignes
 * @param string $defaut	Valeur par défaut
 * @param boolean $actif	Indique si le champs est modifiable ou non
 **/
function champTexteLong($nom, $info, $defaut, $largeur=20, $hauteur=5, $actif=false) {
	$info = guillemet($info);
	$defaut = guillemet($defaut);
	$retour = "<span class='formTexteArea' alt='".$info."' title='".$info."'>";
	if ($actif) {
		$retour.= "<textarea id='".$nom."' name='".$nom."' rows='".$hauteur."' cols='".$largeur;
		$retour.= "' alt='".$info."' title='".$info."' >".$defaut."</textarea>";
	} else {
		$retour.= champID($nom, $defaut).( $defaut=="" ? "" : $defaut );
	}
	$retour.= "</span>";
	return $retour;
}
$joursSemaine = array("Monday"=>"Lundi", "Tuesday"=>"Mardi", "Wednesday"=>"Mercredi",
				"Thursday"=>"Jeudi", "Friday"=>"Vendredi", "Saturday"=>"Samedi",
				"Sunday"=>"Dimanche", "Mon"=>"Lun", "Tue"=>"Mar", "Wed"=>"Mer",
				"Thu"=>"Jeu", "Fri"=>"Ven", "Sat"=>"Sam", "Sun"=>"Dim");
$moisAnnee = array("1"=>"Janvier", "2"=>"F&eacute;vrier", "3"=>"Mars", "4"=>"Avril",
				"5"=>"Mai", "6"=>"Juin", "7"=>"Juillet", "8"=>"Ao&ucirc;t",
				"9"=>"Septembre", "10"=>"Octobre", "11"=>"Novembre",
				"12"=>"D&eacute;cembre", 1=>"Janvier", 2=>"F&eacute;vrier",
				3=>"Mars", 4=>"Avril", 5=>"Mai", 6=>"Juin", 7=>"Juillet",
				8=>"Ao&ucirc;t", 9=>"Septembre", 10=>"Octobre", 11=>"Novembre",
				12=>"D&eacute;cembre", "January"=>"Janvier",
				"February"=>"F&eacute;vrier", "March"=>"Mars", "April"=>"Avril", 
				"May"=>"Mai", "June"=>"Juin", "July"=>"Juillet",
				"August"=>"Ao&ucirc;t", "September"=>"Septembre",
				"October"=>"Octobre", "November"=>"Novembre",
				"December"=>"D&eacute;cembre", "Janu"=>"Janv", "Febr"=>"F&eacute;vr",
				"Marc"=>"Mars", "Apri"=>"Avri", "May"=>"Mai", "June"=>"Juin",
				"July"=>"Juil", "Augu"=>"Ao&ucirc;t", "Sept"=>"Sept",
				"Octo"=>"Octo", "Nove"=>"Nove", "Dece"=>"D&eacute;ce");
/**
 * champDate retourne un champ composé de 2 listes pour le jour et le mois, plus un texte pour l'année
 * @param string $nom			Nom de la variable POST du formulaire, servira de préfixe pour les 3 champs, auquel sera ajouté _jour, _mois et _annee
 * @param string $info			Informations affichées au survol (les 3 champs sont placés dans un DIV exprès)
 * @param string $defaut		Date par défaut, au format SQL, par exemple 2013-08-12 pour le 12 août 2013
 * @param boolean $actif		Indique si les champs sont modifiables ou non, false si 0, "0", "", false, null ; true dans les autres cas
 * @param integer $annee_bas	Indique l'année la plus basse admise, par défaut l'année en cours
 * @param integer $annee_haut	Indique l'année la plus haute admise, par défaut l'année en cours
 *								NB : Si $annee_bas==$annee-haut, l'année devra être saisie dans un champ de textes (prévoir alors un contrôle à postériori
 **//*
function champDate($nom, $info, $defaut, $actif=null, $annee_bas=null, $annee_haut=null) {
	global $joursSemaine;
	global $moisAnnee;
	$info = guillemet($info);
	$date = separeDate($defaut);
	if (!is_bool($actif)) {	// Gère les différentes valeurs possible de $actif
		if (is_numeric($actif)) {	$actif = ( $actif!=0 ); }
		if (is_string($actif)) {	$actif = ( ($actif!="0") and ($actif!="") ); }
		if (is_null($actif)) {		$actif = false; }
	}
	for($i=1 ; $i<=31 ; $i++) {	$listeJours[$i] = $i;	}
	for($i=1 ; $i<=12 ; $i++) {	$listeMois[$i] = $moisAnnee[$i];	}
	if (is_null($annee_bas)) {	$annee_bas = date("Y"); }
	if (is_null($annee_haut)) {	$annee_haut = date("Y"); }
	for($i=(integer) $annee_bas ; $i<=(integer) $annee_haut ; $i++) {	$listeAnnees[$i] = $i;	}
	$retour = "<div class='formDate' alt='".$info."' title='".$info."'>";
	$retour.= " (".$joursSemaine[date("l", mktime(0, 0, 0, $date['mois'], $date['jour'], $date['annee']))].") ";
	if ($actif) {
		$retour.= champListe($nom.'_jour', $info.( $info<>"" ? "" : "Date")." - Jour", $date['jour'], 1, $listeJours, false, true)." ";
		$retour.= champListe($nom.'_mois', $info.( $info<>"" ? "" : "Date")." - Mois", $date['mois'], 1, $listeMois, false, true)." ";
		if ($annee_bas==$annee_haut) {	// années identiques, champ de type texte...
			$retour.= champTexte($nom.'_annee', $info.( $info<>"" ? "" : "Date")." - Ann&eacute;e (4 chiffres)", $date['annee'], 6, true);
		} else {	// années différentes, champ de type liste
			$retour.= champListe($nom.'_annee', $info.( $info<>"" ? "" : "Date")." - Ann&eacute;e", $date['annee'], 1, $listeAnnees, false, true)." ";
		}
	} else {
		$retour.= $date['jour']." / ".$moisAnnee[$date['mois']]." / ".$date['annee'];
		$retour.= champID($nom.'_jour', $date['jour']).champID($nom.'_mois', $date['mois']).champID($nom.'_annee', $date['annee']);
	}
	$retour.= "</div>";
	return $retour;	// date("Y-m-d"); // Vers SQL
}*/
/**
 * champDate retourne un champ de date (HTML5 !)
 * @param string $nom			Nom de la variable POST du formulaire, servira de préfixe pour les 3 champs, auquel sera ajouté _jour, _mois et _annee
 * @param string $info			Informations affichées au survol (les 3 champs sont placés dans un DIV exprès)
 * @param string $defaut		Date par défaut, au format SQL, par exemple 2013-08-12 pour le 12 août 2013
 * @param boolean $actif		Indique si les champs sont modifiables ou non, false si 0, "0", "", false, null ; true dans les autres cas
 * @param integer $date_mini	Indique la date la plus basse admise, par défaut pas de limite
 * @param integer $date_maxi	Indique la date la plus haute admise, par défaut pas de limite
 *								NB : Si $annee_bas==$annee-haut, l'année devra être saisie dans un champ de textes (prévoir alors un contrôle à postériori
 **/
function champDate($nom, $info, $defaut=null, $actif=null, $date_mini=null, $date_maxi=null) {
	global $joursSemaine;
	$info = guillemet($info);
	if (is_null($defaut)) {	$defaut = ''; }
	if (!is_bool($actif)) {	// Gère les différentes valeurs possible de $actif, pour qu'il soit forcément TRUE ou FALSE !
		if (is_numeric($actif)) {	$actif = ( $actif!=0 ); }
		if (is_string($actif)) {	$actif = ( ($actif!="0") and ($actif!="") ); }
		if (is_null($actif)) {		$actif = false; }
	}
	if (is_numeric($date_mini)) {	$date_mini = date("Y-m-d", mktime(0,0,0,1,1,$date_mini)); }	// date mini est un nombre, considère que c'est une année !
	if (is_numeric($date_maxi)) {	$date_maxi = date("Y-m-d", mktime(0,0,0,12,31,$date_maxi)); }	// date maxi est un nombre, considère que c'est une année !
	if ( (!is_null($date_mini)) and (!is_null($annee_haut)) and ( $date_maxi<$date_mini) ) {
		swapVar($date_mini, $date_maxi);
	}
	$retour = "<div class='formDate' alt='".$info."' title='".$info."'>";
	if ($defaut!='') {	$retour.= " (".$joursSemaine[date("l", strtotime($defaut))].") "; }
	if ($actif) {
		$retour.= "<input name='".$nom."' value='".$defaut."' type='date' ";
		$retour.= ($date_mini!=''?"min='".$date_mini."'":"").($date_maxi!=''?" max='".$date_maxi."'":"");
		$retour.= " /> <i>format JJ/MM/AAAA</i>";
	} else {
		$retour.= ($defaut==''?"-":$defaut) . champID($nom, $defaut);
	}
	$retour.= "</div>";
	return $retour;	// date("Y-m-d"); // Vers SQL
}

/**
 * dateMaintenant retourne la date en cours au format SQL
 * @param boolean $format_long	Indique si l'on doit retourner la date seule (FALSE, par défaut) ou avec l'heure (TRUE)
 **/
function dateMaintenant($format_long=FALSE) {	return date("Y-m-d". ($format_long?" H:i:s":"") );	}

/**
 * assembleDate retourne la date indiquée au format SQL
 * @param integer $dateAnnee	Année à utiliser (par défaut l'année en cours est utilisée)
 * @param integer $dateJour	Jour à utiliser (par défaut le premier jour du mois)
 * @param integer $dateMois	Mois à utiliser (par défaut le mois de janvier)
 **/
function assembleDate($dateAnnee=NULL, $dateMois=1, $dateJour=1) {
	return date("Y-m-d", mktime(0, 0, 0, $dateMois, $dateJour, ( is_null($dateAnnee) ? date("Y") : $dateAnnee )));
}

/**
 * assembleHeure retourne l'heure indiquée au format SQL
 * @param integer $dateHeure	Heure à utiliser (par défaut 0)
 * @param integer $dateMinute	Minutes à utiliser (par défaut 0)
 * @param integer $dateSeconde	Secondes à utiliser (par défaut 0)
 **/
function assembleHeure($dateHeure=0, $dateMinute=0, $dateSeconde=0) {	return date("H:i:s", mktime($dateHeure, $dateMinute, $dateSeconde));	}

/**
 * assembleDate retourne la date et l'heure indiquées au format SQL
 * @param integer $dateAnnee	Année à utiliser (par défaut l'année en cours est utilisée)
 * @param integer $dateJour	Jour à utiliser (par défaut le premier jour du mois)
 * @param integer $dateMois	Mois à utiliser (par défaut le mois de janvier)
 * @param integer $dateHeure	Heure à utiliser (par défaut 0)
 * @param integer $dateMinute	Minutes à utiliser (par défaut 0)
 * @param integer $dateSeconde	Secondes à utiliser (par défaut 0)
 **/
function assembleDateHeure($dateAnnee, $dateMois, $dateJour, $dateHeure=0, $dateMinute=0, $dateSeconde=0) {
	return date("Y-m-d H:i:s", mktime($dateHeure, $dateMinute, $dateSeconde, $dateMois, $dateJour, $dateAnnee));
}

/**
 * separeDate retourne la date et l'heure indiquées dans un tableau associatif, les valeurs sont converties en integer ;
 * Les noms des champs sont : annee, mois, jour, heure, minute & seconde
 * @param string $date	Date au format SQL
 **/
function separeDate($date) {
	$resultat = array(	'annee'=>	( strlen($date)>=4 ? intval(substr($date,0,4)) : NULL ),
						'mois'=>	( strlen($date)>=7 ? intval(substr($date,5,2)) : NULL ),
						'jour'=>	( strlen($date)>=10 ? intval(substr($date,8,2)) : NULL ),
						'heure'=>	( strlen($date)>=13 ? intval(substr($date,11,2)) : NULL ),
						'minute'=>	( strlen($date)>=16 ? intval(substr($date,14,2)) : NULL ),
						'seconde'=>	( strlen($date)>=19 ? intval(substr($date,17,2)) : NULL ));
	return $resultat;
}

/**
 * champListe retourne un champ de type de liste déroulante
 * ATTENTION, les éléments dont la clé commence par DISABLED, ne seront pas sélectionnables !
 * @param string	$nom		Nom de la variable POST du formulaire
 * @param string	$info		Informations affichées au survol
 * @param string	$defaut		Valeur par défaut
 * @param integer	$hauteur	Hauteur du champ en lignes, 1 par défaut
 * @param array		$valeurs	Tableau associatif contenant la liste des options ;
 * 				La clé étant la valeur, et la valeur le texte affiché ;
 * 				Si la valeur par défaut n'est pas dans la liste des valeurs, le choix (aucun) est ajouté dans la liste.
 * A DEVELOPPER	Le tableau peut être un tableau de tableaux : auquel cas le premier niveau de tableau servira de balise <OPTGROUP...>, le second niveau servira de valeurs.
 * @param boolean	$valide		Indique si le changement dans la sélecton valide le formulaire (par défaut NON)
 * @param boolean	$actif		Indique si le champs est modifiable (par défaut NON)
 **/
function champListe($nom, $info, $defaut, $hauteur=1, $valeurs=array("0"=>'NON', "1"=>'OUI'), $valide=false, $actif=false) {
	$retour = '<span class="formListe" alt="'.$info.'" title="'.$info.'">';
	if ($actif) {
		$retour.= '<select id="'.$nom.'" name="'.$nom.'" size="'.$hauteur.'" '.( $valide ? 'onchange="submit();"' : '' ).'>';
		if (!array_key_exists($defaut, $valeurs)) {	// Ajoute une ligne indiquant de faire un choix, si la valeur par défaut n'est pas dans la liste
			$retour.= '<option value="NULL" selected="selected">(choisissez...)</option>';
		}
		foreach ($valeurs as $cle=>$valeur) {
			$retour.= '<option value="'.$cle.'" '.( substr($cle,0,8)=="DISABLED" ? "disabled" : "" ).( $cle==$defaut ? 'selected="selected"' : '' ) .'>'.$valeur.'</option>';
		}
		$retour.= '</select>';
	} else {
		$retour.= champID($nom, $defaut).( array_key_exists($defaut,$valeurs) ? $valeurs[$defaut] : '(non d&eacute;fini)' );
	}
	$retour.= '</span>';
	return $retour;
}

/**
 * Retourne un bouton SUBMIT de formulaire
 * @param string $nom 		Nom de la variable créée par le bouton, par défaut 'action'
 * @param string $valeur 	Valeur de la variable créée par le bouton, par défaut 'OK'
 * @param string $texte 	Texte affiché au survol du bouton par la souris, par défaut le même que la valeur du bouton.
 * @param string $image		Image pour remplacer le texte affiché sur le bouton, doit être placé dans le dossier IMG
 */
function creerFormBouton($nom='action', $valeur='OK', $texte=null, $style="") {
	if (is_null($texte)) {	$texte = $valeur; }
	if ($style!='') {		$style = 'class="'.$style.'"' ; }
	$retour = '<input type="submit" id="'.$nom.'" name="'.$nom.'" value="'.$valeur.'" title="'.$texte.'" alt="'.$texte.'" '.$style.' />';
	return($retour);
}

/**
 * Crée une case à cocher.
 * TRES IMPORTANT POUR L'UTLILISATION : Une case à cochée vide ne renvoie pas de variable, il faut donc créer un champ (dans l'exemple "deja_vu"),
 * afin de déterminer si une case à cochée est décochée (inexistante) ou si c'est simplement le premier affichage du formulaire :
 * 	// Cochée par défaut, "" si non cochée par défaut...
 * 	$defautCaseCochee = "checked";
 * 	// On ne récupère donc la valeur que si la variable deja_vu existe, sinon on prend la valeur par défaut
 *	$champCaseCochee = (isset($deja_vu) ? getVar('nom_case_à_cocher','') : $defautCaseCochee);
 *	// On affiche ensuite la case à cocher (le texte ensuite !)
 *	echo(creerCaseCochee('nom_case_à_cocher','checked',$champCaseCochee)." Case à cocher");
 *	// ET on veille à créer le champ caché pour savoir si le formulaire a été vu, bien sur un seul champ suffit pour toutes les cases à cocher !
 *	echo(creerChampID('deja_vu','deja_vu'));
 * @param string	$nom_case	Nom donné à la balise DIV (peut être utilisé pour des feuilles de style).
 * @param array		$valeur		Valeur de la variable $nom_case si elle est cochée.
 * @param boolean	$defaut		Valeur par défaut.
 * @param boolean	$valide		true = provoque la validation du formulaire.
 * @param boolean	$disabled	true = interdit de modifier la valeur de la case à cocher
 * */
function creerCaseCochee($nom_case, $valeur, $defaut=false, $valide=true, $disabled=false) {
	$retour= '<input id="'.$nom_case.'" type="checkbox" name="'.$nom_case.'" value="'.$valeur.'"'.( ($defaut) ? ' checked' : '' );
	$retour.= ( ($valide) ? ' onclick="submit();"' : '' ).( ($disabled) ? ' disabled="disabled"' : '' ).' />';
	return($retour);
}

/**
 * Cré un champ de type Texte pour les ID de tables (non modifiables, masqués, mais permet de conserver les données entre les validations de formulaire !)
 * @param string $nom_champ Nom du champ
 * @param string $valeur Valeur affectée au champ
 */
function creerChampID($nom_champ, $valeur="null") {
	return '<input type="hidden"  name="'.$nom_champ.'"  value="'.$valeur.'">';
}

/**
 * Crée un groupe de boutons Radios réunis dans une balise DIV.
 * @param string	$nom_groupe	Nom donné à la balise DIV (peut être utilisé pour des feuilles de style).
 * @param array		$boutonsRadio	Tableau associatif dont les clés sont les valeurs des boutons,
 * 					et les valeurs sont les textes affichés à la suite des boutons.
 * @param string	$defaut		Valeur du bouton sélectionné par défaut.
 * @param string	$entreBoutons	Texte à mettre entre chaque bouton...
 * */
function creerBoutonRadio($nom_groupe,$boutonsRadio,$defaut='',$entreBoutons='') {
	$retour = '<div id="'.$nom_groupe.'" name="'.$nom_groupe.'">';
	$premier = true;
	foreach($boutonsRadio as $cle => $valeur) {
		if ($premier) {
			$premier = false;
		} else {
			$retour.= $entreBoutons;
		}
		$retour.= '<input id="'.$nom_groupe.'" type="radio" name="'.$nom_groupe.'" value="'.$cle.'"';
		if ($defaut==$cle) {	$retour.= ' checked';	}
		$retour.= ' onclick="submit();" /> '.$valeur;
	}
	$retour.= '</div>';
	return($retour);
}

/**
 * Crée un champ de type fichier
 * @param string	$nom		Nom donné au fichier, par  défaut "fichier"
 * @param int		$largeur	Largeur du champ
 *
 * */
function champFichier($nom=NULL, $largeur=NULL) {
	if (is_null($nom)) {		$nom = "fichier";	}
	if (is_null($largeur)) {	$largeur = 20;		}
	$retour = "<input name='".$nom."' type='file' size='".$largeur."'>";
	return $retour;
}


/**
 * Retourne un bouton BUTTON, donc hors formulaire
 * @param	string	$texte 			Texte affich&eacute; au survol du bouton par la souris, par d&eacute;faut le m&ecirc;me que la valeur du bouton, si cette valeur est indiqu&eacute;e.
 * @param	string	$lien 			Lien vers lequel le bouton renvoi, par défaut #
 * @param	string	$informations	Description &agrave; afficher au survol du bouton
 * @param	boolean	$meme_page		Indique si le lien doit renvoyer vers la page actuelle ou une nouvelle, par défaut cela dépend de la présence ou non de "http" au début du lien
 			string					Indique ou doit aller le lien, selon le texte indiqué : self, top, blank et new seront précédés de '_'
 */
function lienBouton($texte, $lien, $informations=NULL, $meme_page=NULL) {
	if ( (!is_string($lien)) || ($lien=='') ) {	$lien = '#'; }
	if (is_null($informations)) {
		$informations = '';
	} else {
		$informations = str_replace("'", "&acute;", $informations);
		if ($informations=='') {	$informations = $nom; }
	}
	if (is_string($meme_page)) {
		$meme_page = strtolower($meme_page);
		$cible = (in_array($meme_page, array('self', 'top', 'blank', 'new'))?'_':'').$meme_page;
	}
	else {
		if (!is_bool($meme_page)) {	$meme_page = (substr($lien, 0, 4)!='http'); }
		$cible = ( ($meme_page) ? '_self' : '_blank' );
	}
	$retour = '<a href="'.$lien.'" target="'.$cible.'"><input type="button" value="'.$texte.'" title="'.$informations.'" alt="'.$informations.'" /></a>';
	return $retour;
}