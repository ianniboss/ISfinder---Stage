<?php
/**
 *	FFFFFF	 OOOO	RRRRR	M    M	U    U	L		  AA	IIIIII	RRRRR	EEEEEE	 SSSSS
 *	F		O    O	R    R	MM  MM	U    U	L		 A  A	  II	R    R	E		S
 *	FFFF	O    O	RRRRR	M MM M	U    U	L		 AAAA	  II	RRRRR	EEEE	 SSSSS
 *	F		O    O	R  R	M    M	U    U	L		A    A	  II	R  R	E	    	  S
 *	F		 OOOO	R   R	M    M	 UUUU	LLLLLL	A    A	IIIIII	R   R	EEEEEE	 SSSSS
 **/

/**
 * Cr&eacute;e la balise <FORM ...>
 **/
function commencerFormulaire($nomFormulaire="formulaire", $actionFormulaire=NULL, $tailleMaxFichiers=NULL) {
	if ( is_null($actionFormulaire) ) { $actionFormulaire = $_SERVER['PHP_SELF']; }
	$retour = '<div class="formulaire">';
	$retour.= '<form name="'.$nomFormulaire.'" action="'.$actionFormulaire.'" method="post" enctype="multipart/form-data">';
	if (is_numeric($tailleMaxFichiers)) {	$retour.= '<input type="hidden" name="MAX_FILE_SIZE" value="'.$tailleMaxFichiers.'" />'; }
//	$retour.= champID('deja_vu', 'deja_vu');
	return $retour;
}
/**
 * Ferme la balise ...</FORM>
 **/
function terminerFormulaire() {
	return '</form></div>';
}

/**
 * Cr&eacute; un champ de type Texte pour les ID de tables (non modifiables, masqu&eacute;s, mais permet de conserver les donn&eacute;es entre les validations de formulaire !)
 * @param string $nom_champ Nom du champ
 * @param string $valeur Valeur affect&eacute;e au champ
 */
function creerChampID($nom_champ,$valeur="null") {
	return '<input type="hidden"  name="'.$nom_champ.'"  value="'.$valeur.'">';
}
/**
 * Cr&eacute; un champ de type Texte
 * @param string $nom_champ Nom du champ
 * @param string $information Informations &agrave; afficher au survol de la souris sur le champ, par d&eacute;faut le nom du champ
 * @param string $defaut Valeur par d&eacute;faut du champ
 * @param string $largeur Largeur du champ pour l'affichage, par d&eacute;faut 20 caract&egrave;res
 */
function creerChampTexte($nom_champ, $information='', $defaut='', $largeur=0, $valide=false, $ckEditor=FALSE) {
	if ($ckEditor) {	// Remplacer par textarea d'une seule ligne visible
		$retour = creerChampTexteArea($nom_champ, $information, $defaut, $largeur, 1, TRUE);
	} else {
		if ($largeur==0) {	$largeur = 20;	}
		if ($information=='') {	$information = $nom_champ;	}
		$retour = '<input type="text" id="'.$nom_champ.'" name="'.$nom_champ.'" size="'.$largeur.'" alt="'.$information.'" title="'.$information.'"';
		if ($defaut!='') {	$retour.= ' value="'.$defaut.'"';	}
		if ($valide) {	$retour.= ' onChange="submit()"';	}
		$retour.= ' />';
	}
	return $retour;
}

/**
 * Cr&eacute; un champ de type Password
 * @param string $nom_champ Nom du champ
 * @param string $information Informations &agrave; afficher au survol de la souris sur le champ, par d&eacute;faut le nom du champ
 * @param string $largeur Largeur du champ pour l'affichage, par d&eacute;faut 20 caract&egrave;res
 */
function creerChampMotPasse($nom_champ,$information='',$largeur=0,$valide=false) {
	if ($largeur==0) {	$largeur = 20;	}
	if ($information=='') {	$information = $nom_champ;	}
	$retour = '<input type="password" id="'.$nom_champ.'" name="'.$nom_champ.'" size="'.$largeur.'" alt="'.$information.'" title="'.$information.'"';
	if ($valide) {	$retour.= ' onChange="submit()"';	}
	$retour.= ' />';
	return $retour;
}

/**
 * Cr&eacute; un champ de type Texte multi ligne
 * @param string $nom_champ Nom du champ
 * @param string $information Informations &agrave; afficher au survol de la souris sur le champ, par d&eacute;faut le nom du champ
 * @param string $cols Largeur du champ pour l'affichage, par d&eacute;faut 20 caract&egrave;res
 * @param string $ligs Nombre de lignes du champ pour l'affichage, par d&eacute;faut 5 lignes
 * @param string $defaut Valeur par d&eacute;faut du champ
 */
function creerChampTexteArea($nom_champ,$information='',$defaut='',$cols=20,$ligs=5,$ckEditor=FALSE) {
	if ($information=='') {	$information = $nom_champ;	}
	$retour = '<textarea '.( ($ckEditor!=FALSE) ? 'class="ckeditor" ' : '' ).'id="'.$nom_champ.'" name="'.$nom_champ.'"';
	$retour.= ' alt="'.$information.'" title="'.$information.'" rows='.$ligs.' cols='.$cols.'>'.$defaut.'</TEXTAREA>';
	$retour.= ( ($ckEditor!=FALSE) ? '<script>CKEDITOR.replace( "'.$nom_champ.'" );</script>' : '' );
	return $retour;
}
 
/**
 * Cr&eacute;e une case &agrave; cocher.
 * TRES IMPORTANT POUR L'UTLILISATION : Une case &agrave; coch&eacute;e vide ne renvoie pas de variable, il faut donc cr&eacute;er un champ (dans l'exemple "deja_vu"),
 * afin de d&eacute;terminer si une case &agrave; coch&eacute;e est d&eacute;coch&eacute;e (inexistante) ou si c'est simplement le premier affichage du formulaire :
 * 	// Coch&eacute;e par d&eacute;faut, "" si non coch&eacute;e par d&eacute;faut...
 * 	$defautCaseCochee = "checked";
 * 	// On ne r&eacute;cup&egrave;re donc la valeur que si la variable deja_vu existe, sinon on prend la valeur par d&eacute;faut
 *	$champCaseCochee = (is_set('deja_vu') ? getVar('nom_case_&agrave;_cocher','') : $defautCaseCochee);
 *	// On affiche ensuite la case &agrave; cocher (le texte ensuite !)
 *	echo(creerCaseCochee('nom_case_&agrave;_cocher','checked',$champCaseCochee)." Case &agrave; cocher");
 *	// ET on veille &agrave; cr&eacute;er le champ cach&eacute; pour savoir si le formulaire a &eacute;t&eacute; vu, bien sur un seul champ suffit pour toutes les cases &agrave; cocher !
 *	echo(creerChampID('deja_vu','deja_vu'));
 * @param string	$nom_case	Nom donn&eacute; &agrave; la balise DIV (peut &ecirc;tre utilis&eacute; pour des feuilles de style).
 * @param array		$valeur		Valeur de la variable $nom_case si elle est coch&eacute;e.
 * @param boolean	$defaut		Valeur par d&eacute;faut.
 * @param boolean	$valide		true = provoque la validation du formulaire.
 * */
function creerCaseCochee($nom_case,$valeur,$defaut=false,$valide=true) {
	$retour= '<input id="'.$nom_case.'" type="checkbox" name="'.$nom_case.'" value="'.$valeur.'"';
	$retour.= ( $defaut?' checked':'' ).( $valide?' onclick="submit();"':'' ).' />';
	return($retour);
}

/**
 * Cr&eacute;e un groupe de boutons Radios r&eacute;unis dans une balise DIV.
 * @param string	$nom_groupe	Nom donn&eacute; &agrave; la balise DIV (peut &ecirc;tre utilis&eacute; pour des feuilles de style).
 * @param array		$boutonsRadio	Tableau associatif dont les cl&eacute;s sont les valeurs des boutons,
 * 					et les valeurs sont les textes affich&eacute;s &agrave; la suite des boutons.
 * @param string	$defaut		Valeur du bouton s&eacute;lectionn&eacute; par d&eacute;faut.
 * @param string	$entreBoutons	Texte &agrave; mettre entre chaque bouton...
 * */
function creerBoutonRadio($nom_groupe,$boutonsRadio,$defaut='',$entreBoutons='') {
	$retour = '<div id="'.$nom_groupe.'" name="'.$nom_groupe.'">';
	$premier = true;
	foreach($boutonsRadio as $cle => $valeur) {
		if ($premier) {	$premier = false; }
		else {			$retour.= $entreBoutons; }
		$retour.= '<input id="'.$nom_groupe.'" type="radio" name="'.$nom_groupe.'" value="'.$cle.'"';
		$retour.= ( ($defaut==$cle)?' checked':'' ).' onclick="submit();" /> '.$valeur;
	}
	$retour.= '</div>';
	return($retour);
}

/**
 * Cr&eacute;e une liste d&eacute;roulante.
 * @param string  $nom_groupe 		Nom donn&eacute; &agrave; la balise SELECT (peut &ecirc;tre utilis&eacute; pour des feuilles de style).
 * @param array   $listeDeroulante	Tableau associatif dont les cl&eacute;s sont les valeurs des options,
 * 					et les valeurs sont les textes affich&eacute;s pour chaque option.
 * @param string  $defaut		Valeur de l'option s&eacute;lectionn&eacute;e par d&eacute;faut.
 * @param boolean $valideForm		Si true (par d&eacute;faut), la modification de la liste d&eacute;roulante provoque
	 				la validation du formulaire.
 * */
function creerListeDeroulante($nom_liste,$listeDeroulante,$defaut=NULL,$valide=true) {
	$retour = '<select id="'.$nom_liste.'" name="'.$nom_liste.'" size="1"'.( ($valide)?' onchange="submit();"':'' ).'>';
	foreach($listeDeroulante as $cle => $valeur) {
		$retour.= '<option value="'.$cle.'"'.( ($defaut==$cle)?' selected="selected"':'').'>'.$valeur.'</option>';
	}
	$retour.= '</select>';
	return($retour);
}

/**
 * Cr&eacute;e un groupe de 6 champs pour choisir une date, en acceptant une date comprise &agrave; partir d'une date, pour un certain intervalle de jours.
 * Le premier champ est une case &agrave; cocher, qui indique que la date ne doit pas &ecirc;tre prise en compte (null), cela &eacute;vite la saisie automatique de la date par d&eacute;faut.
 * Si la date de d&eacute;but est omise : la date du jour lui est affect&eacute;e.
 * L'intervalle de jours par d&eacute;faut est 366 (une ann&eacute;e en partant d'ann&eacute;es bisextiles...).
 * Si apr&egrave;s cela, la date de fin est ant&eacute;rieure &agrave; la date de d&eacute;but, elles sont invers&eacute;es.
 * Si on choisit une date impossible (31 f&eacute;vrier, par exemple), elle est retourn&eacute;e malgr&eacute; tout,
 * il faudra donc veiller &agrave; le g&eacute;rer dans le code PHP appelant cette fonction.
 * @param string	$prefixe_champ		Pr&eacute;fixe donn&eacute; aux champs _jour, _mois, _annee, _heure, _minute
 * 						par exemple, si $prefixe_champ vaut "ne_le", les donn&eacute;es r&eacute;cup&eacute;r&eacute;es par le
 * 						formulaire seront respectivements : "ne_le_jour", "ne_le_mois", "ne_le_annee", "ne_le_heure", "ne_le_minute".
 * 						Ils pourront &ecirc;tre utilis&eacute;s comme valeur par d&eacute;faut de cette fonction en utilisant :
 * 						mktime(getVar('ne_le_heure'),getVar('ne_le_minute'),0,getVar('ne_le_mois'),getVar('ne_le_jour'),getVar('ne_le_annee'));
 * @param string	$information		Informations indiqu&eacute;es au survol de la souris sur les champs.
 * @param array		$defaut			Valeurs par d&eacute;faut dans un tableau associ&eacute; contenant les 6 valeurs ; si elle est omise, il prend la date du jour.
 * @param timestamp $date_debut			Date de d&eacute;but, en timestamp UNIX (cr&eacute;&eacute; avec "mktime")
 * @param integer	$intervalle_jours	Intervalle de jours permettant le calcul de la date de fin ( date_debut+intervalle_jours )
 * @param integer	$intervalle_minute	Intervalle de minutes, par d&eacute;faut 5 pour afficher les minutes de 5 en 5 (r&eacute;duit la taille de la liste...)
 * 			
 * */
function creerChampDateHeure($prefixe_champs, $information='', $defaut=NULL, $date_debut=NULL, $intervalle_jours=0, $intervalle_minute=5) {
	if (is_null($defaut)) {
		$defaut['inconnue'] = true;
		$defaut['annee'] = date("Y");
		$defaut['mois'] = date("n");
		$defaut['jour'] = date("j");
		$defaut['heure'] = date("G");
		$defaut['minute'] = date("i");
	}
	if (is_null($date_debut)) {	$date_debut = time();	}
	if ($intervalle_jours==0) {	$intervalle_jours = 366;	}
	$date_fin = mktime(23,59,59,date("n",$date_debut),date("j",$date_debut)+$intervalle_jours,date("Y",$date_debut));
	$listeAnnees = array(date('Y')=>date('Y'),(date('Y')+1)=>(date('Y')+1));
	$listeMois = array(1=>'janvier', 2=>'f&eacute;vrier', 3=>'mars', 4=>'avril', 5=>'mai', 6=>'juin', 7=>'juillet', 8=>'aout',
			   9=>'septembre', 10=>'octobre', 11=>'novembre', 12=>'decembre');
	$jourSemaine = array(1=>'lundi', 2=>'mardi', 3=>'mercredi', 4=>'jeudi', 5=>'vendredi', 6=>'samedi', 7=>'dimanche');
	for($i=1;$i<=31;$i++) {	$listeJours[$i] = $i;	}
	for($i=0;$i<24;$i++) {	$listeHeures[$i] = $i;	}
	for($i=0;$i<60;$i++) {	$listeMinutes[$i] = $i;	}
	$boutonsRadio = array ('non' => 'indiqu&eacute;e ci-dessous', 'oui' => 'inconnue (&agrave; d&eacute;finir ult&eacute;rieurement)');
	$retour = creerBoutonRadio($prefixe_champs.'Inconnu',$boutonsRadio,$defaut['inconnue']);
	$retour.= ' '.$jourSemaine[date("N",mktime(0,0,0,$defaut['mois'],$defaut['jour'],$defaut['annee']))];
	$retour.= ' '.creerListeDeroulante($prefixe_champs.'Jour',$listeJours,$defaut['jour']);
	$retour.= ' '.creerListeDeroulante($prefixe_champs.'Mois',$listeMois,$defaut['mois']);
	$retour.= ' '.creerListeDeroulante($prefixe_champs.'Annee',$listeAnnees,$defaut['annee']);
	$retour.= ' '.creerListeDeroulante($prefixe_champs.'Heure',$listeHeures,$defaut['heure']);
	$retour.= ' '.creerListeDeroulante($prefixe_champs.'Minute',$listeMinutes,$defaut['minute']);
	return $retour;
}

/**
 * Cr&eacute;e un champ de type texte pour indiquer le nom du fichier, affiche le bouton Parcourir qui permet d'uploader le fichier...
 * @param string	$nomFichier		Nom de la variable $_FILES[NOM]
 * @param string	$defaut			Valeur par défaut (nom du fichier s'il existe, NULL sinon)
 * @param numeric	$largeurImage	Largeur de l'image affichée en pixels
 * */
function creerChampFichier($nomFichier, $defaut=NULL, $largeurImage=NULL) {
	//$retour = '<input type="hidden" name="MAX_FILE_SIZE" value="2100000" />';
	$Root = $GLOBALS['Root'];
	if (!is_numeric($largeurImage)) {	$largeurImage = 190;	}
	$title = ( (is_null($defaut)) ? "ajouter une" : "remplacer cette" );
	$retour = ( (is_null($defaut)) ? "<i>(pas de fichier enregistr&eacute;)</i>" : '<img src="'.$Root.$defaut.'" width="'.$largeurImage.'" />' );
	$retour.= '<br /><input type="file" name="'.$nomFichier.'" alt="'.ucfirst($title).' image..." title="Cliquez pour '.$title.' image" />';
	return $retour;
}

/**
 * Récupère les infos d'un fichier uploadé dans un formulaire, et le déplace à l'endroit voulu !
 * Retourne le nom du fichier si tout se passe bien, un code d'erreur numérique sinon.
 * Liste des erreurs :
 *		- Toutes les erreurs prévues dans les "File Upload Error Code", plus :
 *		- UPLOAD_ERR_EXTENSION_INVALIDE (100) : extension invalide
 * $champLogo = getFichier('champLogo', 'logo_labo', '', TYPE_FICHIER_IMAGE, 'upload', TRUE);
 * if (is_numeric($champLogo)) {	echo('ERREUR '.$champLogo.' !!!'); }
 * elseif (is_string($champLogo)) {	echo('Fichier '.$champLogo.' uploadé !'); }
 **/
define('TYPE_FICHIER_IMAGE', 'IMAGE');
define('TYPE_FICHIER_TEXTE', 'TEXTE');
define('TYPE_FICHIER_CALC', 'CALC');
define('TYPE_FICHIER_PRESENT', 'PRESENT');
define('TYPE_FICHIER_BDD', 'BDD');
define('TYPE_FICHIER_PDF', 'PDF');
define('TYPE_FICHIER_WEB', 'WEB');
define('UPLOAD_ERR_EXTENSION_INVALIDE', 100);
$TYPES_FICHIERS = array(	TYPE_FICHIER_IMAGE=>	array('jpg', 'jpeg', 'png', 'gif'),
							TYPE_FICHIER_TEXTE=>	array('txt', 'doc', 'docx'),
							TYPE_FICHIER_CALC=>		array('xls', 'xlsx'),
							TYPE_FICHIER_PRESENT=>	array('ppt', 'pps', 'pptx', 'ppsx'),
							TYPE_FICHIER_BDD=>		array('sql'),
							TYPE_FICHIER_PDF=>		array('pdf'),
							TYPE_FICHIER_WEB=>		array('htm', 'html', 'php', 'cgi'));
function getFichier($fichier, $nomFichier="", $prefixe="", $typeAccepte=TYPE_FICHIER_IMAGE, $dossierDestination="img/upload", $ecrase=TRUE) {
	global $TYPES_FICHIERS;
	if ( !array_key_exists($typeAccepte, $TYPES_FICHIERS) ) {	$typeAccepte = TYPE_FICHIER_IMAGE; }
	if ($nomFichier=="") {	$nomFichier = $fichier; }
	if ( ($fichier!="") and (isset($_FILES[$fichier])) ) {	// Variable existe, on continu
		if ($_FILES[$fichier]['error']==UPLOAD_ERR_OK) {	// Fichier uploadé, on continu
			if ($_FILES[$fichier]['size']<=getVar('MAX_FILE_SIZE', 2000000)) {	// Taille correcte, continue
				$ext = strtolower( substr( strrchr($_FILES[$fichier]['name'], '.')  ,1)  );
				var_dump($TYPES_FICHIERS);
				if ( in_array($ext, $TYPES_FICHIERS[$typeAccepte]) ) {	// extension OK, on continu
					if (in_array($ext, $TYPES_FICHIERS[TYPE_FICHIER_WEB])) {
						// On modifie l'extension si elle peut être dangereuse pour le site web
						$ext = "file_".$ext;
					}
					if ($prefixe!="") {	$prefixe.= "_"; }
					// On cherche si le nom de fichier est déjà présent
					$nom = $prefixe.$nomFichier.".".$ext;
					if (file_exists($dossierDestination.'/'.$nom)) {	// fichier existe déjà !
						if ($ecrase==TRUE) {	// doit écraser, donc supprime le fichier si possible
							if (!unlink($dossierDestination.'/'.$nom)) {	$ecrase = FALSE; }
						}
						if ($ecrase==FALSE) {	// on écrase pas, donc on cherche un nom de fichier non existant
							$index = 1;
							while (file_exists($dossierDestination.'/'.$prefixe.$nomFichier."_".$index.".".$ext)) {	$index+= 1; }
							$nom = $prefixe.$nomFichier."_".$index.".".$ext;
						}
					}
					$resultat = move_uploaded_file($_FILES[$fichier]['tmp_name'], $dossierDestination.'/'.$nom);
					if ($resultat) {	// OK, renvoi le nom du fichier (sans dossier destination, connu
						return($nom);
					} else {	// Peut pas écrire...
						return(UPLOAD_ERR_CANT_WRITE);
					}
				} else {	// extension invalide
					return(UPLOAD_ERR_EXTENSION_INVALIDE);
				}
			} else {	// Taille trop grande
				return(UPLOAD_ERR_FORM_SIZE);
			}
		} else {	// Erreur d'upload, on interromp !
			return($_FILES[$fichier]['error']);
		}
	} else {	// Variable n'existe pas, on stoppe
		return(UPLOAD_ERR_NO_FILE);
	}
}

/**
 * Cr&eacute;e un groupe de 4 champs pour choisir une date, en acceptant une date comprise &agrave; partir d'une date, pour un certain intervalle de jours.
 * Le premier champ est une case &agrave; cocher, qui indique que la date ne doit pas &ecirc;tre prise en compte (null), cela &eacute;vite la saisie automatique de la date par d&eacute;faut.
 * Si la date de d&eacute;but est omise : la date du jour lui est affect&eacute;e.
 * L'intervalle de jours par d&eacute;faut est 366 (une ann&eacute;e en partant d'ann&eacute;es bisextiles...).
 * Si apr&egrave;s cela, la date de fin est ant&eacute;rieure &agrave; la date de d&eacute;but, elles sont invers&eacute;es.
 * Si on choisit une date impossible (31 f&eacute;vrier, par exemple), elle est retourn&eacute;e malgr&eacute; tout,
 * il faudra donc veiller &agrave; le g&eacute;rer dans le code PHP appelant cette fonction.
 * @param string	$prefixe_champ		Pr&eacute;fixe donn&eacute; aux champs _jour, _mois, _annee, _heure, _minute
 * 						par exemple, si $prefixe_champ vaut "ne_le", les donn&eacute;es r&eacute;cup&eacute;r&eacute;es par le
 * 						formulaire seront respectivements : "ne_le_jour", "ne_le_mois", "ne_le_annee".
 * @param string	$information		Informations indiqu&eacute;es au survol de la souris sur les champs.
 * @param array		$defaut			Valeurs par d&eacute;faut dans un tableau associ&eacute; contenant les 6 valeurs ; si elle est omise, il prend la date du jour.
 * @param timestamp $date_debut			Date de d&eacute;but, en timestamp UNIX (cr&eacute;&eacute; avec "mktime")
 * @param integer	$intervalle_jours	Intervalle de jours permettant le calcul de la date de fin ( date_debut+intervalle_jours )
 * @param integer	$intervalle_minute	Intervalle de minutes, par d&eacute;faut 5 pour afficher les minutes de 5 en 5 (r&eacute;duit la taille de la liste...)
 * 			
 * */
function creerChampDate($prefixe_champs, $information='', $defaut=NULL, $date_debut=NULL, $intervalle_jours=0) {
	if (is_null($defaut)) {	$defaut = array('annee'=>NULL, 'mois'=>NULL, 'jour'=>NULL);	}
	if (is_null($defaut['annee'])) {	$defaut['annee'] = date("Y");	}
	if (is_null($defaut['mois'])) {		$defaut['mois'] = date("n");	}
	if (is_null($defaut['jour'])) {		$defaut['jour'] = date("j");	}
	if (is_null($date_debut)) { $date_debut = time(); }
	if ($intervalle_jours==0) { $intervalle_jours = 366; }
	$date_fin = mktime(23,59,59,date("n",$date_debut),date("j",$date_debut)+$intervalle_jours,date("Y",$date_debut));
	for($i=date('Y',$date_debut);$i<=date('Y',$date_fin);$i++) {	$listeAnnees[$i] = $i;	}
	$listeMois = array(1=>'janvier', 2=>'f&eacute;vrier', 3=>'mars', 4=>'avril', 5=>'mai',	6=>'juin', 7=>'juillet',
			   8=>'aout', 9=>'septembre', 10=>'octobre', 11=>'novembre', 12=>'decembre');
	$jourSemaine = array(1=>'lundi', 2=>'mardi', 3=>'mercredi', 4=>'jeudi',	5=>'vendredi', 6=>'samedi', 7=>'dimanche');
	for($i=1;$i<=31;$i++) {	$listeJours[$i] = $i;	}
	$retour = $jourSemaine[date("N",mktime(0,0,0,$defaut['mois'],$defaut['jour'],$defaut['annee']))];
	$retour.= ' '.creerListeDeroulante($prefixe_champs.'Jour',$listeJours,$defaut['jour']);
	$retour.= ' '.creerListeDeroulante($prefixe_champs.'Mois',$listeMois,$defaut['mois']);
	$retour.= ' '.creerListeDeroulante($prefixe_champs.'Annee',$listeAnnees,$defaut['annee']);
	return $retour;
}

/**
 * Cr&eacute;e un groupe de 2 champs pour choisir une heure.
 * @param string	$prefixe_champ		Pr&eacute;fixe donn&eacute; aux champs _heure, _minute
 * 						par exemple, si $prefixe_champ vaut "ne_a", les donn&eacute;es r&eacute;cup&eacute;r&eacute;es par le
 * 						formulaire seront respectivements : "ne_aHeure", "ne_aMinute".
 * 						Ils pourront &ecirc;tre utilis&eacute;s comme valeur par d&eacute;faut de cette fonction en utilisant :
 * 							mktime(getVar('ne_aHeure'),getVar('ne_aMinute'));
 * @param string	$information		Informations indiqu&eacute;es au survol de la souris sur les champs.
 * @param array		$defaut				Valeurs par d&eacute;faut dans un tableau associ&eacute; contenant les 2 valeurs ; si elle est omise, il prend l'heure actuelle.
 * @param integer	$intervalle_minute	Intervalle de minutes, par d&eacute;faut 5 pour afficher les minutes de 5 en 5 (r&eacute;duit la taille de la liste...)
 * 			
 * */
function creerChampHeure($prefixe_champs,$information='',$defaut=NULL,$intervalle_minute=5) {
	if (is_null($defaut)) {	$defaut = array('heure'=>NULL, 'minute'=>NULL); }
	if (is_null($defaut['heure'])) {	$defaut['heure'] = date("G");	}
	if (is_null($defaut['minute'])) {	$defaut['minute'] = date("i");	}
	for($i=0;$i<24;$i++) {	$listeHeures[$i] = $i;	}
	for($i=0;$i<60;$i=$i+$intervalle_minute) {	$listeMinutes[$i] = $i;	}
	$retour = creerListeDeroulante($prefixe_champs.'Heure',$listeHeures,$defaut['heure']);
	$retour.= ' '.creerListeDeroulante($prefixe_champs.'Minute',$listeMinutes,$defaut['minute']);
	return $retour;
}

/**
 * Retourne un bouton SUBMIT de formulaire
 * @param string $nom 		Nom de la variable cr&eacute;&eacute;e par le bouton, par d&eacute;faut 'action'
 * @param string $valeur 	Valeur de la variable cr&eacute;&eacute;e par le bouton, par d&eacute;faut 'OK'
 * @param string $texte 	Texte affich&eacute; au survol du bouton par la souris, par d&eacute;faut le m&ecirc;me que la valeur du bouton.
 * @param string $image		Image pour remplacer le texte affich&eacute; sur le bouton, doit &ecirc;tre plac&eacute; dans le dossier IMG
 */
function creerFormBouton($nom='action', $valeur='OK', $texte=null, $style="") {
	if (is_null($texte)) { $texte = $valeur; }
	if ($style!='') { $style = 'class="'.$style.'"' ; }
	$retour = '<input type="submit" id="'.$nom.'" name="'.$nom.'" value="'.$valeur.'" title="'.$texte.'" alt="'.$texte.'" '.$style.' />';
	return($retour);
}

/**
 * Retourne un bouton BUTTON, donc hors formulaire
 * @param string $texte 	Texte affich&eacute; au survol du bouton par la souris, par d&eacute;faut le m&ecirc;me que la valeur du bouton, si cette valeur est indiqu&eacute;e.
 * @param string $lien 		Lien vers lequel le bouton renvoi
 * @param string $description	Description &agrave; afficher au survol du bouton
 */
function creerLienBouton($texte, $lien, $description=NULL, $meme_page=TRUE) {
	if (is_null($description)) {	$description = $texte;	}
	$cible = ( ($meme_page) ? '_self' : '_blank' );
	$retour = '<input type="button" value="'.$texte.'" title="'.$description.'" alt="'.$description.'" />';
	if ($lien!="") {	$retour = '<a href="'.$lien.'" target="'.$cible.'">'.$retour.'</a>';	}
	return $retour;
}
/**
 * Retourne un bouton BUTTON hors formulaire, conditionn&eacute; par l'acceptation d'afficher les boutons en cours de navigation
**/
function creerNavigationBouton($texte, $lien, $description=NULL) {
	if (getSession('meti_modifications_en_navigation')=='1') { return creerLienBouton($texte, $lien, $description); }
}


/**
 * V&eacute;rifie si la chaine $texte est une heure valide, renvoi TRUE si oui, FALSE sinon.
 **/
function estHeure($texte) {
	$retour = false;
	if ($texte!="") {	
		if (preg_match("/[0-9]:[0-5][0-9]/",$texte)==1) {	// correspond &agrave; 1 ou 2 chiffres, suivis de ":", suivi d'un ou deux chiffres : doit v&eacute;rifier que le premier groupe de chiffre est inf&eacute;rieur &agrave; 24 et que le second est inf&eacute;rieur &agrave; 60 :
			$heure = substr($texte,0,strpos($texte,':'));
			$minute = substr($texte,strpos($texte,':')+1);
			$retour = true;
			if ($heure>=24) {	$retour = false;	}
			if ($minute>=60) {	$retour = false;	}
		}
	}
	return $retour;
}

/**
 * V&eacute;rifie si la chaine $texte est une adresse mail valide, renvoi TRUE si oui, FALSE sinon.
 **/
function estMail($texte) {
	$retour = false;
	if ($texte!="") {	
		if (preg_match("/[a-zA-Z0-9-_.]*@[a-zA-Z0-9-_.]*[a-zA-Z]/",$texte)==1) {
			$position = strpos($texte,'@');
			$adresse = substr($texte,0,$position);
			$serveur = substr($texte,$position+1);
			$retour = true;
			if ( (substr($adresse,0,1)=='.') || (substr($adresse,$position-1,1)=='.') || (substr($adresse,$position+1,1)=='.') ) { $retour = false; }
		}
	}
	return $retour;
}
?>