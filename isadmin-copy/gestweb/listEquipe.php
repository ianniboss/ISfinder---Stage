<?php
require_once('entete_nohtml.inc.php');
require_once('menu.inc.php');
require_once('entete_html.inc.php');
?>
  <div class="content">
<?php
	// Gestion des tris !
	$liste1 = array('equipe'=>"reseau.equipes.equipe", 'email'=>"reseau.equipes.email", 'nom_machines'=>"reseau.equipes.nom_machines",
                         'vlan'=>"reseau.vlans.nom", 'nom_AD'=>"reseau.equipes.nom_AD");
	foreach ($liste1 as $cle=>$valeur) {
		$vals = explode(',', $valeur);
		foreach ($vals as &$v) {	$v = $v.' desc';	}
		unset($v);
		$liste2[$cle.'I'] = implode(', ', $vals);
	}
	$listeTris = array_merge($liste1, $liste2);
	$sort = getVar('sort', 'nom');
        $groupe = getVar('groupe', ''); // = rien si pas de regroupement pas équipe ; = equipe si regroupé par équipe...
	$tri = ( array_key_exists($sort, $listeTris) ? $listeTris[$sort] : "" );
	$requete = 'SELECT reseau.equipes.idx as id, reseau.equipes.equipe as equipe, reseau.equipes.email as email, reseau.labos.nom as nom_labo,'
                . ' reseau.equipes.nom_machines as nom_machines, reseau.equipes.href as href, reseau.vlans.nom as nom_vlan, reseau.vlans.description as description_vlan,'
                . ' reseau.equipes.nom_AD as nom_AD ';
	$requete.= ' FROM reseau.equipes';
        $requete.= ' LEFT OUTER JOIN reseau.labos ON reseau.labos.idx=reseau.equipes.id_labo';
        $requete.= ' LEFT OUTER JOIN reseau.vlans ON reseau.vlans.idx=reseau.equipes.id_vlan';
	//$requete.= ' WHERE ';
	if ( ($groupe=="labo") or ($tri!="") ) {
		$requete.= ' ORDER BY ';
                $requete.= ( ($groupe=="labo") ? 'reseau.labos.nom' : '' );
                $requete.= ( ($groupe=="labo") and ($tri!="") ? ', ' : '' );
                $requete.= ( ($tri!="") ? $tri : '' );
	}
	$listeEquipes = sqlRequete($requete);
	echo("<h1> Liste des équipes </h1>");
        echo("<a href='comptEquipe.php' target='_self'>Ajouter une équipe</a>");
	echo("<h2> Tous laboratoires - ");
	if (!is_array($listeEquipes)) {	echo("Aucune ligne </h2>");	} else {	// Affichage :	
		echo(count($listeEquipes)." Ligne".(count($listeEquipes)>1?"s":"")." </h2>");
		echo("<table class='liste' width='100%'>");
                if ($groupe!="") {  // On affiche le bouton pour dégroupper les équipes
                        //echo('<a class="bouton" href="?mode='.$mode.'&groupe=">Dégrouper les équipes</a>');
                        echo( lienBouton("Dégrouper", "?groupe=", "Affiche la liste sans regrouper par labo", TRUE) );
                }
                    // On mets les entêtes dans une variable parce qu'on peut devoir les afficher plusieurs fois en cas de regroupement par équipe.
                $entete = '<tr>';
		$entete.= '<th> &nbsp; </th>';  // Colonne pour afficher les boutons...
                $entete.= '<th> <a href="?sort=equipe'.($sort=='equipe'?'I':'').'&groupe='.$groupe.'">Equipe</a>&nbsp;';
                $entete.= ($sort=='equipe'?'<img src="images/vers_bas.png" />':($sort=='equipeI'?'<img src="images/vers_haut.png" />':'')).' </th>';
		$entete.= '<th> <a href="?sort=email'.($sort=='email'?'I':'').'&groupe='.$groupe.'">Alias E-mail</a>&nbsp;';
		$entete.= ($sort=='email'?'<img src="images/vers_bas.png" />':($sort=='emailI'?'<img src="images/vers_haut.png" />':'')).' </th>';      
                if ($groupe=="") {  // On ne mettra le groupe dans l'entête que si on ne regroupe pas par labo
                        //$entete.= '<th>Equipe <a class="bouton" href="?mode='.$mode.'&groupe=equipe">Grouper</a> </th>';
                        $entete.= '<th>Labo '.lienBouton("Grouper", '?mode='.$mode.'&groupe=labo', "Affiche la liste en regroupant par laboratoire", TRUE). ' </th>';
                }
		$entete.= '<th> Axes </th>';
                $entete.= '<th> <a href="?sort=nom_machines'.($sort=='nom_machines'?'I':'').'&groupe='.$groupe.'">Nom de machines</a>&nbsp;';
                $entete.= ($sort=='nom_machines'?'<img src="images/vers_bas.png" />':($sort=='nom_machinesI'?'<img src="images/vers_haut.png" />':'')).' </th>';
		$entete.= '<th> Page web </th>';
                $entete.= '<th> <a href="?sort=vlan'.($sort=='vlan'?'I':'').'&groupe='.$groupe.'">VLAN dédié</a>&nbsp;';
                $entete.= ($sort=='vlan'?'<img src="images/vers_bas.png" />':($sort=='vlanI'?'<img src="images/vers_haut.png" />':'')).' </th>';
                $entete.= '<th> <a href="?sort=nom_AD'.($sort=='nom_AD'?'I':'').'&groupe='.$groupe.'">Nom pour l&acute;AD</a>&nbsp;';
                $entete.= ($sort=='nom_AD'?'<img src="images/vers_bas.png" />':($sort=='nom_ADI'?'<img src="images/vers_haut.png" />':'')).' </th>';
		$entete.= '</tr>';
                if ($groupe=="labo") { // Si on groupe par équipe, on a besoin de garder une trace du labo et de l'équipe affichée au dessus...
                    $exLabo = "";
                } else {    // Si on ne regroupe pas, on affiche l'entête UNE fois au début !
                    echo($entete);
                }
		foreach($listeEquipes as $une) {
                    if ($groupe=="labo") {    // Si on groupe par labo, on regarde si on doit afficher le nom du labo et les entêtes...
                        if ($exLabo!=$une['nom_labo']) { // Il y a un changement de labo avec la ligne précédente, on l'affiche ainsi que l'entête
                            echo('</table><h3>'.$une['nom_labo'].'</h3><table class="liste" width="100%">'.$entete);
                            $exLabo = $une['nom_labo'];
                        }
                    }
                    // On récupère la liste des axes pour l'équipe
                    $requeteAxes = 'SELECT reseau.axes.sigle as sigle, reseau.axes.nom as nom'
                            . ' FROM reseau.equipes_axes LEFT OUTER JOIN reseau.axes ON reseau.axes.idx=reseau.equipes_axes.id_axe'
                            . ' WHERE reseau.equipes_axes.id_equipe='.$une['id'];
                    $listeAxesEquipes = sqlRequete($requeteAxes);
                    if (is_array($listeAxesEquipes)) {
                        $axes = "";
                        foreach($listeAxesEquipes as $un_axe) {
                            $axes = ($axes!=""?"<br />":"")."<span title='".$un_axe['nom']."'>".$un_axe['sigle']."</span>";
                        }
                    } else {  $axes = "-";  }
                    echo('<tr><td>');
                    $rowspan = ( $nbEquipesParPersonnes[$une['id']]>1 ? ' rowspan="'.$nbEquipesParPersonnes[$une['id']].'"' : '' );
                    // Afficher les boutons !
                    if ($AUTORISATION==2) {		// pour admins, autorise  modification !
                            echo("<a href='comptEquipe.php?id=".$une["id"]."' title='Modifier'><img src='images/act_ajouter.png' border='0' /></a>");
                    }
                    echo('</td>');
                    echo('<td> '.$une["equipe"].' </td>');
                    echo('<td> '.$une["email"].' </td>');
                    if ($groupe=="") {  //  On affiche le labo que si on ne regroupe pas
                            echo('<td> '.$une["nom_labo"].' </td>');
                    }
                    echo('<td> '.$axes.' </td>');
                    echo('<td> '.$une["nom_machines"].' </td>');
                    echo('<td> '.$une["href"].' </td>');
                    echo('<td> '.$une["nom_vlan"].' - '.$une["description_vlan"].' </td>');
                    echo('<td> '.$une["nom_AD"].' </td>');	
                    echo('</tr>');
		}
		echo('</table>');
	}
?>
    <!-- end .content --></div>
<?php
require_once('pied.inc.php');
?>
