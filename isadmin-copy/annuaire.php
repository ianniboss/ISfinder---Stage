<?php
$Root = "";
require_once('entete.inc.php');
require_once('menu.inc.php');
?>
  <div class="content">
  	<?php
		$choix = '<form action="'.$_SERVER['PHP_SELF'].'" method="post"><select id="labo" name="labo" autofocus onchange="submit();">';
		$tab = array(0=>"Tous", "CBI", "LBME", "LMGM");
		$selectionLabo = getVar("labo", $tab[0]);	
		if (!in_array($selectionLabo, $tab) ) {		$selectionLabo = "";	}
		foreach ($tab as $t) {	$choix.= '<option value="'.$t.'"'.($selectionLabo==$t?' selected':'').'>'.$t.'</option>';	}
		$choix.= '</select></form>';
		
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
	$sort = getVar('sort', 'nom');
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
	echo("<h2> ".( $selectionLabo!=$tab[0]?"Laboratoire ".$selectionLabo:"Tous laboratoires" )." - ");
	if (!is_array($listePersonnes)) {	echo("Aucune ligne </h2>".$choix);	} else {	// Affichage :	
		echo(count($listePersonnes)." Ligne".(count($listePersonnes)>1?"s":"")." </h2>".$choix);
		echo("<table class='liste'>");
		echo('<tr>');
		echo('<th> <a href="?labo='.$labo.'&sort=nom'.($sort=='nom'?'I':'').'">Nom</a>&nbsp;');
			echo(($sort=='nom'?'<img src="images/vers_bas.png" />':($sort=='nomI'?'<img src="images/vers_haut.png" />':'')).' </th>');
		echo('<th> <a href="?labo='.$labo.'&sort=prenom'.($sort=='prenom'?'I':'').'">Prénom</a>&nbsp;');
			echo(($sort=='prenom'?'<img src="images/vers_bas.png" />':($sort=='prenomI'?'<img src="images/vers_haut.png" />':'')).' </th>');
		echo('<th> <a href="?labo='.$labo.'&sort=email'.($sort=='email'?'I':'').'">E-mail</a>&nbsp;');
			echo(($sort=='email'?'<img src="images/vers_bas.png" />':($sort=='emailI'?'<img src="images/vers_haut.png" />':'')).' </th>');
		echo('<th> <a href="?labo='.$labo.'&sort=equipe'.($sort=='equipe'?'I':'').'">'.( $selectionLabo==$tab[0]?'Labo &amp; ':'' ).'Equipe</a>&nbsp;');
			echo(($sort=='equipe'?'<img src="images/vers_bas.png" />':($sort=='equipeI'?'<img src="images/vers_haut.png" />':'')).' </th>');
		echo('<th>Poste</th>');
		echo('</tr>');
		foreach($listePersonnes as $une) {
			echo('<tr>');
			echo('<td> '.html_entity_decode($une["nom"]).' </td>');
			echo('<td> '.html_entity_decode($une["prenom"]).' </td>');
			echo('<td> '.$une["email"].' </td>');
			echo('<td> '.($selectionLabo==$tab[0]?$une["labo"].' - ':'').$une["equipe"].' </td>');
			echo('<td> '.$une["poste"].' </td>');
			echo('</tr>');
		}
		echo('</table>');
	}
?>
    <!-- end .content --></div>
<?php
require_once('pied.inc.php');
?>
