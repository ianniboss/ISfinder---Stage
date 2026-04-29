<?php
	$tri = $_SESSION['IBCG_INTRANET_TRI'];
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