<?php
$Root = "";
if (!defined('DB_server')) {	define('DB_server', "lezat.ibcg.biotoul.fr");	}
if (!defined('DB_user')) {		define('DB_user',"gestannuaire");	}
if (!defined('DB_password')) {	define('DB_password',"aE3vUOKqHJe6ayti");	}
if (!defined('DB_bdd')) {		define('DB_bdd', "Labo");	}
require_once($Root.'function_variables.inc.php');
require_once($Root.'function_sql.inc.php');
require_once($Root.'function_controles.inc.php');
require_once($Root.'function_formulaires.inc.php');
define('DATE_VIERGE', "0000-00-00");
define('NON_INDIQUE', "(non indiqué)");
date_default_timezone_set("Europe/Paris");
define('PREFIXE_SESSION', "intranet_IBCG_");
	$modeSelection = '( ( comptes.id_statut IN (2,3)) ) AND ( comptes.annuaire=1 )';	// Comptes infos publiés et entrées d'annuaire
	$modeSelection.= ' AND ( comptes.date_arrivee<=NOW() )';					// ET date_arrivee passée
	$modeSelection.= ' AND ( comptes.date_depart>=NOW() OR comptes.date_depart IS NULL )';	// ET date_depart absente ou future
	$modeSelection.= ' AND ( comptes.date_suppression>=NOW() OR comptes.date_suppression IS NULL )';	// ET date_suppression absente ou future
	if ($selectionLabo!=$tab[0]) {
		$modeSelection.= ' AND reseau.labos.nom="'.$selectionLabo.'"';
	}	
	// Gestion des tris !
	$liste1 = array('nom'=>"comptes.nom", 'prenom'=>"comptes.prenom", 'email'=>"comptes.email",
					'equipe'=>"reseau.labos.nom, reseau.equipes.equipe");
	foreach ($liste1 as $cle=>$valeur) {
		$vals = explode(',', $valeur);
		foreach ($vals as &$v) {	$v = $v.' desc';	}
		unset($v);
		$liste2[$cle.'I'] = implode(', ', $vals);
	}
	$listeTris = array_merge($liste1, $liste2);
	global $sort;
	$tri = ( array_key_exists($sort, $listeTris) ? $listeTris[$sort] : "" );
	$requete = 'SELECT comptes.email as email,';
	$requete.= ' comptes.nom as nom, comptes.prenom as prenom, reseau.labos.nom as labo, reseau.equipes.equipe as equipe,';
	$requete.= ' comptes.poste as poste FROM `comptes`';
	$requete.= ' LEFT OUTER JOIN reseau.equipes ON reseau.equipes.idx=comptes.id_equipe';
	$requete.= ' LEFT OUTER JOIN reseau.labos ON reseau.labos.idx=reseau.equipes.id_labo';
	$requete.= ' WHERE '.$modeSelection;
	if ($tri!="") {
		$requete.= ' ORDER BY '.$tri;
	}
	$listePersonnes = sqlRequete($requete);
	if (!is_array($listePersonnes)) {	echo("Aucune ligne");	} else {	// Affichage :	
		echo(count($listePersonnes)." Ligne".(count($listePersonnes)>1?"s":"")." </h2>");	var_dump($sort);
		echo("<table class='liste'>");
		echo('<tr>');
		echo('<th> <a style="font-size:12px;" href="?sort=nom'.($sort=='nom'?'I':'').'">Nom</a>&nbsp;');
			echo(($sort=='nom'?'<img width="10px" height="10px" src="http://secure.ibcg.biotoul.fr/gestweb/images/vers_bas.png" />':($sort=='nomI'?'<img width="10px" height="10px" src="http://secure.ibcg.biotoul.fr/gestweb/images/vers_haut.png" />':'')).' </th>');
		echo('<th> <a style="font-size:12px;" href="?sort=prenom'.($sort=='prenom'?'I':'').'">Pr&eacute;nom</a>&nbsp;');
			echo(($sort=='prenom'?'<img width="10px" height="10px" src="http://secure.ibcg.biotoul.fr/gestweb/images/vers_bas.png" />':($sort=='prenomI'?'<img width="10px" height="10px" src="http://secure.ibcg.biotoul.fr/gestweb/images/vers_haut.png" />':'')).' </th>');
		echo('<th> <a style="font-size:12px;" href="?sort=email'.($sort=='email'?'I':'').'">E-mail</a>&nbsp;');
			echo(($sort=='email'?'<img width="10px" height="10px" src="http://secure.ibcg.biotoul.fr/gestweb/images/vers_bas.png" />':($sort=='emailI'?'<img width="10px" height="10px" src="http://secure.ibcg.biotoul.fr/gestweb/images/vers_haut.png" />':'')).' </th>');
		echo('<th> <a style="font-size:12px;" href="?sort=equipe'.($sort=='equipe'?'I':'').'">'.( $selectionLabo==$tab[0]?'Labo &amp; ':'' ).'Equipe</a>&nbsp;');
			echo(($sort=='equipe'?'<img width="10px" height="10px" src="http://secure.ibcg.biotoul.fr/gestweb/images/vers_bas.png" />':($sort=='equipeI'?'<img width="10px" height="10px" src="http://secure.ibcg.biotoul.fr/gestweb/images/vers_haut.png" />':'')).' </th>');
		echo('<th><p style="font-size:12px;"> Poste </p></th>');
		echo('</tr>');
		foreach($listePersonnes as $une) {
			echo('<tr>');
			echo('<td> '.$une["nom"].' </td>');
			echo('<td> '.$une["prenom"].' </td>');
			echo('<td> '.$une["email"].' </td>');
			echo('<td> '.($selectionLabo==$tab[0]?$une["labo"].' - ':'').$une["equipe"].' </td>');
			echo('<td> '.$une["poste"].' </td>');
			echo('</tr>');
		}
		echo('</table>');
	}
?>