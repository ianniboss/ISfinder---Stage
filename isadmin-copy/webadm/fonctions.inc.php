<?php

function erreur($var="",$parentheses=true,$echo=true) {
	$retour = '';
	$tab1 = array('<','>');
	$tab2 = array('&lt;','&gt;');
	if (is_null($var)) {	$retour = "<i>NULL</i>";	}
	if (is_bool($var)) {	$retour = "<strong>Boolean</strong>:".($var?"VRAI":"FAUX");	}
	if (is_int($var)) {	$retour = "<strong>Int</strong>:".$var;	}
	if (is_integer($var)) {	$retour = "<strong>Integer</strong>:".$var;	}
	if (is_string($var)) {	$retour = "<strong>String</strong>:".str_replace($tab1,$tab2,$var);	}
	if (is_array($var)) {
		$premier = true;
		foreach ($var as $cle=>$val) {
			if ($premier==true) { $premier=false; } else { $retour.=", "; }
			$retour.= erreur($cle,false,false)."=>".erreur($val,false,false);
		}
		$retour = "[".$retour."]";
	} else {
		if ($parentheses) {	$retour = "(".$retour.")";	}
	}
	if ($retour=='') {	$retour = "<strong>autre</strong>:".$var;	}
	if ($echo) {	echo($retour);	} else {	return($retour);	}
}

include_once('fonctions_variables.inc.php');
include_once('fonctions_sql.inc.php');
include_once('fonctions_date.inc.php');
include_once('fonctions_form.inc.php');
include_once('fonctions_fichiers.inc.php');
include_once('fonctions_ldap.inc.php');
include_once('fonctions_calc.inc.php');
include_once('fonctions_formatage.inc.php');

/** Renvoie la traduction du texte, en fonction du mot cl&eacute; !
 * Si le mot cl&eacute; est un nombre, recherche l'id_traduction.
 * Si la traduction n'existe pas ou n'est pas traduite dans la langue, renvoie le mot cl&eacute; entre crochets !
 * Pour les utilisateurs identifi&eacute;s, permet de rajouter le mot cl&eacute; dans la table 'traduction'...
 * Si la variable $sans_bouton vaut TRUE : n'affiche pas le bouton
 **/
function Traduire($id_traduction) {
	// Cherche la traduction par l'ID ou le MOT CLE
	$condition = ( (is_numeric($mot_cle)) ? 'id_traduction='.$id_traduction : 'mot_cle="'.$id_traduction.'"' );
	$traduction = sqlTrouve('traduction', $condition);
	// R&eacute;cup&egrave;re la traduction si elle existe
    $retour = ( (is_null($traduction)) ? '' : $traduction['mot_'.getSession('Page_Langue')] );
	if ($retour=='') {  // La traduction n'existe pas OU est vide, mets le mot_cle entre crochets...
		$retour = '['.$mot_cle.']';
	}
	return $retour;
}
/** Effectue un echo de la fonction Traduire....
 **/
function echoTraduire($mot_cle, $sans_bouton=FALSE) {	echo(Traduire($mot_cle, $sans_bouton));	}

/** Renvoie la traduction d'un paragraphe, en fonction du num&eacute;ro du paragraphe !
 * Si le num&eacute;ro du paragraphe n'existe pas, ou si le texte est vide, retourne le num&eacute;ro entre crochets et en rouge !
 * Pour les utilisateurs identifi&eacute;s, permet de rajouter le paragraphe  dans la table 'traduction'...
 **/
function Paragraphe($id_paragraphe, $balise="<p>") {
    $page_source = nomFichier($_SERVER['PHP_SELF']);
    $retour = '';
	$paragraphe = sqlTrouve('paragraphes','id_paragraphe='.$id_paragraphe);
	if (is_null($paragraphe)) {	// Le paragraphe n'existe pas...
		$retour = "<em>(Paragraphe inexistant : cr&eacute;ez-le !)</em>";
	} else {	// Le paragraphe existe, ajoute le texte
		$retour = texteDepuisHTML($paragraphe['texte_'.getSession('Page_Langue')]);
		if ($retour=='') {      // SI le paragraphe est vide, le dit
			$retour = "<em>(Contenu vide : Paragraphe n°".$id_paragraphe.")</em>";
		}
	}
	// Ajoute une ancre au d&eacute;but (permettra d'atteindre le paragraphe depuis la page de recherche)
	$retour = '<a href="#paragraphe_'.$id_paragraphe.'"></a>'.$retour;
	switch ($balise) {
		case "<p>" : $retour = '<p>'.$retour.'</p>'; break;
		case "<br>" : $retour = $retour.'<br />'; break;
	}
	return $retour;
}
/** Effectue un echo de la fonction Paragraphe.... **/
function echoParagraphe($id_paragraphe, $balise="<p>") {	echo(Paragraphe($id_paragraphe, $balise));	}

/** Renvoie une photo, en fonction du num&eacute;ro de la photo, accepte aussi un $id sous la forme d'un tableau de valeurs tel que return&eacute; par les fonctions sqlSelect et sqlTrouve !
 * Si la photo n'existe pas, retourne le num&eacute;ro entre crochets et en rouge !
 * Pour les utilisateurs identifi&eacute;s, permet de rajouter la photo dans la table 'galerie'...
 * Si la taille maxi est NULL ou non indiqu&eacute;e, on ne r&eacute;duit pas la photo !
 **/
function Photo($id, $taille_maximum=NULL) {
    $page_source = nomFichier($_SERVER['PHP_SELF']);
    $retour = '';
    $photo = NULL;
    $maintenant = dateConvert(NULL,DATE_FORMAT_SQL);
    $defaut = array('horodateur_creation'=>$maintenant, 'horodateur_modification'=>$maintenant,
                    'photo'=>'', 'titre_fr'=>'', 'titre_en'=>'', 'description_fr'=>'', 'description_en'=>'',
                    'copyright'=>'', 'surimpression_logo'=>0);
    if (is_array($id)) {    // tableau pass&eacute; en param&egrave;tre
        if (array_key_exists('id_photo', $id)) {    // Si l'id_photo est indiqu&eacute;,ignore les donn&eacute;es du tableau $id
            $id = $id['id_photo'];
        } else {    // Si l'id_photo n'est pas indiqu&eacute;, utilise les donn&eacute;es du tableau $id comme donn&eacute;es &agrave; afficher mais ne touche pas &agrave; la base de donn&eacute;es
            foreach ($defaut as $cle=>$valeur) {    // Pour chaque valeur par d&eacute;faut...
                if (!array_key_exists($cle, $id)) {     // .... si la valeur n'existe pas dans le tableau $id, prend la valeur par d&eacute;faut
                    $id[$cle] = $valeur;
                }
            }
            $photo = $id;
        }
    }
    if (is_numeric($id)) {  // valeur num&eacute;rique pass&eacute;e en param&egrave;tre OU tableau incluant la valeur de id_photo (voir 11 lignes au dessus) :
        $photo = sqlTrouve('galerie', 'id_photo='.$id);     // Cherche la photo dans la base de donn&eacute;es
        if (!is_array($photo)) {    // Si la photo est introuvable, prend les valeurs par d&eacute;faut
            $photo = $defaut;
        }
    }
    $fichierGrand = $photo['photo'];
    if ( (file_exists($fichierGrand)) and (extensionFichierValide($fichierGrand, FICHIER_IMAGE)) ) {   // Le fichier existe ET est une image : on l'affiche !!!
        $retour = '<div class="image_galerie">';
        $image = '<img src=';
        if (is_null($taille_maximum)) {     // La photo doit &ecirc;tre affich&eacute;e en grand !
            $image.= '"'.$fichierGrand.'"';
        } else {    // La photo doit &ecirc;tre r&eacute;duite
            $fichierMiniature = imageThumb($fichierGrand, NULL, $taille_maximum);
            $taille_image = getimagesize($fichierGrand);
            if ($fichierMiniature===FALSE) {    // La miniature n'a pas &eacute;t&eacute; cr&eacute;&eacute;e
                $image.= '"'.$fichierGrand.'" width="'.$taille_maximum.'"';
            } else {    // La miniature a &eacute;t&eacute; cr&eacute;&eacute;e
                $image.= '"'.$fichierMiniature.'"';
            }
        }
        $image.= ' alt="'.$photo['titre_'.$Page_Langue].'"';
        $image.= ' title="'; 
        if (is_null($taille_maximum)) { // La photo est affich&eacute;e en grand
            $image.= 'Cliquer pour revenir &agrave; la liste..." />'; 
            $retour.= '<a href="'.$lien_sur_photo.'" target="_self">'.$image.'</a>';
        } else {    // La photo peut &ecirc;tre r&eacute;duite   
            if ($taille_image>$taille_maximum) {    // L'image peut &ecirc;tre agrandie et elle n'est pas affich&eacute;e r&eacute;duite
                $image.= 'Cliquer pour agrandir..." />';
                $retour.= '<a href="'.$lien_sur_photo.'" target="_self">'.$image.'</a>';
            } else {    // L'image d'origine a une taille inf&eacute;rieure &agrave; la taille affich&eacute;e, inutile de proposer l'agrandissement...
                $image.= 'Agrandissement impossible" />';
                $retour.= $image;
            }
        }
        $retour.= '<p alt="'.$photo['description_'.$Page_Langue].'<br />&copy;&nbsp;'.$photo['copyright'].'"> '.$photo['titre_'.$Page_Langue].' </p>';
        $retour.= '</div>';
    }
    return $retour;
}
/** Effectue un echo de la fonction Photo.... **/
function echoPhoto($id, $taille_maximum=NULL) {	echo(Photo($id, $taille_maximum, $lien_sur_photo));	}

/**
 * Retourne une News compl&egrave;te, pr&ecirc;te &agrave; afficher, dans son cadre, dans la langue indiqu&eacute;e. Si la langue ne correspond &agrave; aucune langue traduite, utilise la langue par d&eacute;faut
 * Si $news est un num&eacute;ro, l'utilise comme id_news, si c'est un tableau, prends les valeurs directement
 * $afficher_bouton_modifier = TRUE : permet l'affichage du bouton modifier (mais il ne s'affiche de toute fa&ccedil;on que si les modifications en navigation sont actves ! )
 * */
function afficherNews($news, $langue = NULL) {
	$Page_Langue = $GLOBALS['Page_Langue'];
	$retour = '';
    if (is_null($langue)) { $langue = $Page_Langue; }
    if (is_numeric($news)) { // $uneNews est consid&eacute;r&eacute; comme l'ID_NEWS, prend dans la base de donn&eacute;es...
        $uneNews = sqlTrouve('news',$news);
    } else {
        $uneNews = $news;
    }
    if (is_array($uneNews)) {
		$retour.= '<div class="nouvelle">';
		if (!is_null($uneNews['illustration'])) {
			$retour.= '<div class="image"><img src="'.$uneNews['illustration'].'" /></div>';
		}
		$retour.= '<div class="texte"><h1>'.$uneNews['titre_'.$Page_Langue].'</h1>';
		if (!is_null($uneNews['description_'.$Page_Langue])) {
			$retour.= '<p>'.$uneNews['description_'.$Page_Langue].'</p>';
		}
		$retour.= '</div>';
		if ($uneNews['lien']!='') {
			$retour.= '<p class="savoir_plus"><a href="'.$uneNews['lien'].'" target="">'.Traduire('suite_par_la').'</a></p>';
		}
		$retour.= '</div>';
    }
    return($retour);
}

/**
 * Retourne les titres H1 et H2 qui se suivent !
 * On autorise les recherches que si la page a un titre (condition pour qu'elle soit index&eacute;e !)
 **/
function Titres($h1=NULL, $h2=NULL) {	// Aller chercher les traductions directement si elles existent...
	if (is_string($h1)) {	if (is_array(sqlTrouve('traduction', 'mot_cle="'.$h1.'"'))) {	$h1 = Traduire($h1); } }
	if (is_string($h2)) {	if (is_array(sqlTrouve('traduction', 'mot_cle="'.$h2.'"'))) {	$h2 = Traduire($h2); } }
	if (is_null($h1)) {	$h1 = '&nbsp;';	}
	if (is_null($h2)) {	$h2 = '&nbsp;';	}
	elseif (strlen($h2)>=1) {
		// Ne tient pas compte de l'ancre potentiellement ajout&eacute;e par la fonction traduire()...
		$posMajuscule = ( stripos($h2, "</a>")===FALSE ? 0 : stripos($h2, "</a>")+4 );
		$h2 = substr($h2, 0, $posMajuscule).'<strong>'.substr($h2,$posMajuscule,1).'</strong>'.substr($h2,$posMajuscule+1);	// pour mettre l'initiale en gras
	}
	return '<div class="titres2"><h2> '.$h2.' </h2><h1> '.$h1.' </h1></div>';
}
function echoTitres($h1=NULL, $h2=NULL) {	echo(Titres($h1, $h2));	}

?>