<?php
require_once('entete_nohtml.inc.php');
require_once('menu.inc.php');
require_once('entete_html.inc.php');
?>
  <div class="content">
<?php
	$mode = urldecode(getVar('mode', ''));
	switch ($AUTORISATION) {
		case 1:	// Visiteur simple
			$mode = MODE_LISTE_ANNUAIRE;
			break;
		case 2: case 3: // Gestionnaire ou Admin... rien de spécial
			break;
		default:
			die ("vous n'êtes pas autorisé à utiliser ce site !");
			break;
	}
	// Si le mode n'est pas reconnu, ou si l'utilisateur n'est pas habilité, bascule sur le mode annuaire
	if ( (!in_array($mode, array(MODE_LISTE_DEMANDES, MODE_LISTE_FIN_ACTIVITE, MODE_LISTE_SUPPRIME, MODE_LISTE_AUTORISATION))) or ($AUTORISATION==1) ) {
		$mode = MODE_LISTE_ANNUAIRE;
	}
	$modeDateSuppression = FALSE;	// Si vrai, affichera la date de suppression !
	$modeAutorisation = FALSE;		// Si vrai, affichera le niveau d'autorisation au lieu du statut, le certificat au lieu des noms et prénoms, et le mail du certificat au lieu du mail
	$triSupplementaire = "";
	switch ($mode) {
		case MODE_LISTE_ANNUAIRE:	// Définition des infos pour le mode annuaire (par défaut)
			$modeTitre = "Liste de l'annuaire";
			$modeSelection = '( ( comptes.id_statut IN (2,3)) )';	// Comptes infos publiés et entrées d'annuaire
			$modeSelection.= ' AND ( comptes.date_arrivee<=NOW() )';					// ET date_arrivee passée
			$modeSelection.= ' AND ( comptes.date_depart>=NOW() OR comptes.date_depart IS NULL )';	// ET date_depart absente ou future
			$modeSelection.= ' AND ( comptes.date_suppression>=NOW() OR comptes.date_suppression IS NULL )';	// ET date_suppression absente ou future
		break;
		case MODE_LISTE_DEMANDES:	// Définitions pour le mode demandes (liste les comptes pour lesquels une demande est en cours !
			$modeTitre = "Demandes en cours";
			$modeSelection = '( comptes.id_statut IN (1,4,6,8,9) )';
			$triSupplementaire = "comptes.id_statut";
		break;
		case MODE_LISTE_FIN_ACTIVITE:	// Liste les comptes en fin d'activité, donc plus sur l'annuaire mais sans signalement...
			$modeTitre = "Comptes en fin d'activité";
			$modeSelection = '( comptes.id_statut IN (2,3) ) AND ( comptes.date_depart<NOW() )';
                        $modeDateSuppression = TRUE;
		break;
		case MODE_LISTE_SUPPRIME:	// Liste les compte suspendus ou supprimés, pour permettre suppression définitive, demander réactivation, proposer de changer date_depart et réactiver !
			$modeTitre = "Comptes suspendus ou supprimés";
			$modeSelection = '( (comptes.id_statut IN (5,7)) or (comptes.date_suppression<NOW()) )';
			$modeDateSuppression = TRUE;
		break;
		case MODE_LISTE_AUTORISATION:	// Liste des comptes qui sont liés à une autorisation !
			$modeTitre = "Autorisations";
			$modeSelection = '( autorisation.id_niveau IS NOT NULL )';
			$modeAutorisation = TRUE;
			$triSupplementaire = 'autorisation.id_niveau';
		break;
	}
	// Gestion des actions !!!
	$action = getVar('action', '');
	if (!in_array($action, array('supprime', 'revalide'))) {	$action = "";	}
	$idvar = getVar('id', 0);
	if ($idvar>0) {
		$infoCompte = sqlTrouve('comptes', 'id='.$idvar);
		if (!is_array($infoCompte)) {	$idvar = 0;	}
	}
	if ( ($idvar>0) and ($action!="") ) {	// une action est demandée sur un compte !
		if ($AUTORISATION==2) { 	// Admins
			if ($action=='supprime') {
				sqlUpdate('comptes', 'id='.$idvar, array('id_statut'=>'7', 'date_suppression'=>date("Y-m-d")));
			} elseif ($action=='revalide') {
				if (!in_array($une['id_statut'], array(1,2,3))) {
					if ( (is_null($une['login'])) or ($une['login']=="") ) {	// Login inexistant : simple entrée d'annuaire !
						sqlUpdate('comptes', 'id='.$idvar, array('id_statut'=>'2', 'annuaire'=>'1'));
					} else {	// Login indiqué, compte info !!
						sqlUpdate('comptes', 'id='.$idvar, array('id_statut'=>'3'));
					}
				}
			}
		} elseif ($AUTORISATION==3) { 	// Gestionnaires
			if ($action=='supprime') {
				if (in_array($une['id_statut'], array(2,3,5))) {
					sqlUpdate('comptes', 'id='.$idvar, array('id_statut'=>'6', 'mail_demandeur'=>$AUTORISATION_MAIL));
				}
			}
		}
	}
	// Gestion des tris !
	$liste1 = array('statut'=>"comptes.id_statut", 'certificat'=>"autorisation.certificat", 'login'=>"comptes.login", 
            'nom'=>"comptes.nom", 'prenom'=>"comptes.prenom", 'email'=>"comptes.email", 'job'=>"emploi_categorie.categorie, emploi.job", 
            'appartenance'=>"appartenance.appartenance", 'date_arrivee'=>"comptes.date_arrivee", 'date_depart'=>"comptes.date_depart", 
            'date_suppression'=>"comptes.date_suppression");
	foreach ($liste1 as $cle=>$valeur) {
		$vals = explode(',', $valeur);
		foreach ($vals as &$v) {	$v = $v.' desc';	}
		unset($v);
		$liste2[$cle.'I'] = implode(', ', $vals);
	}
	$listeTris = $liste1+$liste2;   // Anciennement, j'utilisais array_merge, mais cette fonctionne réindexe le tableau alros que j'utilise les index !
	$sort = getVar('sort', 'nom');
        $groupe = getVar('groupe', ''); // = rien si pas de regroupement pas équipe ; = equipe si regroupé par équipe...
	$tri = ( array_key_exists($sort, $listeTris) ? $listeTris[$sort] : "" );
	$requete = 'SELECT comptes.id as id, comptes.id_statut as id_statut, comptes_statut.statut as statut, comptes.annuaire as annuaire, ';
	if ($modeAutorisation) {	$requete.= 'CASE WHEN autorisation.id_niveau IS NULL THEN comptes.email ELSE autorisation.mail END';	}
	else {				$requete.= 'comptes.email';	}
	$requete.= ' as email,';
	$requete.= ' comptes.login as login, comptes.nom as nom, comptes.prenom as prenom,';
        if ($groupe=="equipe") {    $requete.= ' reseau.labos.nom as labo, reseau.equipes.equipe as equipe, Labo.responsabilites.responsabilite as responsabilite,';  }
	$requete.= ' comptes.poste as poste, emploi_categorie.categorie as emploi_categorie, emploi.job as job,';
	$requete.= ' appartenance.appartenance as appartenance, comptes.date_arrivee as date_arrivee,';
	$requete.= ' comptes.date_depart as date_depart, comptes.date_suppression as date_suppression,';
	$requete.= ' CASE WHEN autorisation.id_niveau IS NULL THEN 1 ELSE autorisation.id_niveau END as id_autorisation,';
	$requete.= ' CASE WHEN autorisation.certificat IS NULL THEN "n-a" ELSE autorisation.certificat END as certificat,';
	$requete.= ' CASE WHEN autorisation_niveau.description IS NULL THEN "Non identifiable" ELSE autorisation_niveau.description END as autorisation,';
        $requete.= ' login as login';
	$requete.= ' FROM `comptes`';
	$requete.= ' LEFT OUTER JOIN comptes_statut ON comptes_statut.idx=comptes.id_statut';
        if ($groupe=="equipe") {
            $requete.= ' LEFT OUTER JOIN comptes_equipes ON comptes_equipes.id_compte=comptes.id';
            $requete.= ' LEFT OUTER JOIN reseau.equipes ON reseau.equipes.idx=comptes_equipes.id_equipe';
            $requete.= ' LEFT OUTER JOIN reseau.labos ON reseau.labos.idx=reseau.equipes.id_labo';
            $requete.= ' LEFT OUTER JOIN responsabilites ON responsabilites.idx=comptes_equipes.id_responsabilite';
        }
	$requete.= ' LEFT OUTER JOIN emploi ON emploi.id_job=comptes.id_job';
	$requete.= ' LEFT OUTER JOIN emploi_categorie ON emploi_categorie.idx=emploi.id_categorie';
	$requete.= ' LEFT OUTER JOIN appartenance ON appartenance.idx=comptes.id_appartenance';
	$requete.= ' LEFT OUTER JOIN autorisation ON LOWER(autorisation.mail)=LOWER(comptes.email)';
	$requete.= ' LEFT OUTER JOIN autorisation_niveau ON autorisation_niveau.idx=autorisation.id_niveau';
	$requete.= ' WHERE '.$modeSelection;
	if ($tri.$triSupplementaire!="") {
		$requete.= ' ORDER BY ';
                if ($groupe=="equipe") {
                    $requete.= 'reseau.labos.nom, reseau.equipes.equipe, ';
                }
		if ($triSupplementaire!="") {	$requete.= $triSupplementaire; }
		if ($tri!="") {	$requete.= (($triSupplementaire!="")?", ":"").$tri; }
	}
	$listePersonnes = sqlRequete($requete);
        // On crée un autre tableau d'index comptes.id, de valeur "nombre d'équipes liées"...
	$requete = 'SELECT comptes.id as id, COUNT(comptes_equipes.id_equipe) as nombre';
	$requete.= ' FROM comptes LEFT OUTER JOIN comptes_equipes ON comptes_equipes.id_compte=comptes.id';
        $requete.= ' GROUP BY comptes.id ORDER BY comptes.id';
        $listeNombreEquipesParPersonne = sqlRequete($requete);
        $nbEquipesParPersonnes = array();
        foreach ($listeNombreEquipesParPersonne as $une) {   $nbEquipesParPersonnes[$une['id']] = $une['nombre'];   } 
	echo("<h1> ".$modeTitre." </h1>");
	echo("<h2> Tous laboratoires - ");
	if (!is_array($listePersonnes)) {	echo("Aucune ligne </h2>");	} else {	// Affichage :	
		echo(count($listePersonnes)." Ligne".(count($listePersonnes)>1?"s":"")." </h2>");
		echo("<table class='liste' width='100%'>");
                if ($groupe!="") {  // On affiche le bouton pour dégroupper les équipes
                        //echo('<a class="bouton" href="?mode='.$mode.'&groupe=">Dégrouper les équipes</a>');
                        echo( lienBouton("Dégrouper", "?mode=".$mode."&groupe=", "Affiche la liste sans regrouper par équipe", TRUE) );
                }
                    // On mets les entêtes dans une variable parce qu'on peut devoir les afficher plusieurs fois en cas de regroupement par équipe.
                $entete = '<tr>';
		$entete.= '<th> &nbsp; </th>';
		if ($modeAutorisation) {
			$entete.= '<th> Autorisation </th>';
			$entete.= '<th> <a href="?mode='.$mode.'&sort=certificat'.($sort=='certificat'?'I':'').'&groupe='.$groupe.'">Nom</a>&nbsp;';
                        $entete.= ($sort=='certificat'?'<img src="images/vers_bas.png" />':($sort=='certificatI'?'<img src="images/vers_haut.png" />':'')).' </th>';
		} else {
			$entete.= '<th> <a href="?mode='.$mode.'&sort=statut'.($sort=='statut'?'I':'').'&groupe='.$groupe.'">Statut</a>&nbsp;';
			$entete.= ($sort=='statut'?'<img src="images/vers_bas.png" />':($sort=='statutI'?'<img src="images/vers_haut.png" />':'')).' </th>';
			$entete.= '<th> <a href="?mode='.$mode.'&sort=login'.($sort=='login'?'I':'').'&groupe='.$groupe.'">Login</a>&nbsp;';
			$entete.= ($sort=='login'?'<img src="images/vers_bas.png" />':($sort=='loginI'?'<img src="images/vers_haut.png" />':'')).' </th>';
			$entete.= '<th> <a href="?mode='.$mode.'&sort=nom'.($sort=='nom'?'I':'').'&groupe='.$groupe.'">Nom</a>&nbsp;';
			$entete.= ($sort=='nom'?'<img src="images/vers_bas.png" />':($sort=='nomI'?'<img src="images/vers_haut.png" />':'')).' </th>';
			$entete.= '<th> <a href="?mode='.$mode.'&sort=prenom'.($sort=='prenom'?'I':'').'&groupe='.$groupe.'">Prénom</a>&nbsp;';
			$entete.= ($sort=='prenom'?'<img src="images/vers_bas.png" />':($sort=='prenomI'?'<img src="images/vers_haut.png" />':'')).' </th>';
		}
		$entete.= '<th> <a href="?mode='.$mode.'&sort=email'.($sort=='email'?'I':'').'&groupe='.$groupe.'">E-mail</a>&nbsp;';
		$entete.= ($sort=='email'?'<img src="images/vers_bas.png" />':($sort=='emailI'?'<img src="images/vers_haut.png" />':'')).' </th>';
                if ($groupe=="") {  // On ne mettra le groupe dans l'entête que si on ne regroupe pas par labo/équipe
                        //$entete.= '<th>Equipe <a class="bouton" href="?mode='.$mode.'&groupe=equipe">Grouper</a> </th>';
                        $entete.= '<th>Equipe '.lienBouton("Grouper", '?mode='.$mode.'&groupe=equipe', "Affiche la liste en regroupant par équipe", TRUE). ' </th>';
                }
		$entete.= '<th> <a href="?mode='.$mode.'&sort=job'.($sort=='job'?'I':'').'groupe='.$groupe.'">Fonction</a>&nbsp;';
		$entete.= ($sort=='job'?'<img src="images/vers_bas.png" />':($sort=='jobI'?'<img src="images/vers_haut.png" />':'')).' </th>';
		$entete.= '<th> <a href="?mode='.$mode.'&sort=appartenance'.($sort=='appartenance'?'I':'').'&groupe='.$groupe.'">Appartenance</a>&nbsp;';
		$entete.= ($sort=='appartenance'?'<img src="images/vers_bas.png" />':($sort=='appartenanceI'?'<img src="images/vers_haut.png" />':'')).' </th>';
		$entete.= '<th> T&eacute;l. </th>';
		$entete.= '<th> <a href="?mode='.$mode.'&sort=date_arrivee'.($sort=='date_arrivee'?'I':'').'&groupe='.$groupe.'">Date d&acute;arrivée</a>&nbsp;';
		$entete.= ($sort=='date_arrivee'?'<img src="images/vers_bas.png" />':($sort=='date_arriveeI'?'<img src="images/vers_haut.png" />':'')).' </th>';
		$entete.= '<th> <a href="?mode='.$mode.'&sort=date_depart'.($sort=='date_depart'?'I':'').'&groupe='.$groupe.'">Date de départ</a>&nbsp;';
		$entete.= ($sort=='date_depart'?'<img src="images/vers_bas.png" />':($sort=='date_departI'?'<img src="images/vers_haut.png" />':'')).' </th>';
		if ($modeDateSuppression) {
			$entete.= '<th> <a href="?mode='.$mode.'&sort=date_suppression'.($sort=='date_suppression'?'I':'').'&groupe='.$groupe.'">Date de suppression</a>&nbsp;';
			$entete.= ($sort=='date_suppression'?'<img src="images/vers_bas.png" />':($sort=='date_suppressionI'?'<img src="images/vers_haut.png" />':'')).' </th>';
		}
		$entete.= '</tr>';
                if ($groupe=="equipe") { // Si on groupe par équipe, on a besoin de garder une trace du labo et de l'équipe affichée au dessus...
                    $exLabo = "";
                    $exEquipe = "";
                } else {    // Si on ne regroupe pas, on affiche l'entête UNE fois au début !
                    echo($entete);
                }
		foreach($listePersonnes as $une) {
                    if ($groupe=="equipe") {    // Si on groupe par équipe, on regarde si on doit afficher le noms du labo et de l'équipe et les entêtes...
                        if ( ($exLabo!=$une['labo']) or ($exEquipe!=$une['equipe']) ) { // Il y a un changement de labo ou d'équipe avec la ligne précédente, on l'affiche ainsi que l'entête
                            echo('</table><h3>'.$une['labo'].' - '.$une['equipe'].'</h3><table class="liste" width="100%">'.$entete);
                            $exLabo = $une['labo'];
                            $exEquipe = $une['equipe'];
                        }
                    }
                    if ($groupe=="") {  // On récupère la liste des équipes seulement si on ne groupe pas par équipe, car sinon elles sont déjà dans la liste !
                        $requeteEquipes = 'SELECT reseau.labos.nom as labo, reseau.equipes.equipe as equipe, Labo.responsabilites.responsabilite as responsabilite,'
                                . ' CONCAT_WS(" - ",reseau.labos.nom,reseau.equipes.equipe) as labo_equipe'
                                . ' FROM Labo.comptes_equipes'
                                . ' LEFT OUTER JOIN reseau.equipes ON reseau.equipes.idx=Labo.comptes_equipes.id_equipe'
                                . ' LEFT OUTER JOIN reseau.labos ON reseau.labos.idx=reseau.equipes.id_labo'
                                . ' LEFT OUTER JOIN Labo.responsabilites ON Labo.responsabilites.idx=Labo.comptes_equipes.id_responsabilite'
                                . ' WHERE comptes_equipes.id_compte='.$une['id'];
                        $listeCompteEquipes = sqlRequete($requeteEquipes);
                        if (is_array($listeCompteEquipes)) {
                            $equipe = "";
                            foreach($listeCompteEquipes as $une_eq) {
                                $equipe = ($equipe!=""?"<br />":"").$une_eq['labo_equipe'];
                            }
                        } else {  $equipe = "-";  }
                    }
                    echo('<tr><td>');
                    $rowspan = ( $nbEquipesParPersonnes[$une['id']]>1 ? ' rowspan="'.$nbEquipesParPersonnes[$une['id']].'"' : '' );
                    // Afficher les boutons !
                    if ($AUTORISATION==2) {		// pour admins, autorise TOUJOURS modification !
                            echo("<a href='compte.php?id=".$une["id"]."' title='Modifier'><img src='images/act_ajouter.png' border='0' /></a>");
                            if ($une['id_statut']!=7) {	// Autorise de supprimer le compte dirtectement (vers id_statut = 7) !
                                    echo("<br /><a href='?mode=".$mode."&id=".$une["id"]."&action=supprime' title='Supprimer le compte directement'><img src='images/act_supprimer.png' border='0' /></a>");
                            }
                            if ( (!in_array($une['id_statut'], array(1,2,3,9))) 
                            and ($une['date_suppression']<=date("Y-m-d")) 
                            and ($une['date_depart']<=date("Y-m-d")) ) {	// Autorise de revalider le compte dirtectement (vers id_statut = 3) !
                                    echo("<a href='?mode=".$mode."&id=".$une["id"]."&action=revalide' title='Revalider le compte directement'><img src='images/act_valider.png' border='0' /></a>");
                            }
                    } elseif ($AUTORISATION==3) {	// pour gestionnaires
                            if (in_array($une['id_statut'], array(1,2,3,5,7))) {	// permet modification sur comptes en attente, actifs et entrées d'annuaire
                                    echo("<a href='compte.php?id=".$une["id"]."' title='Modifier'><img src='images/act_ajouter.png' border='0' /></a>");
                            }
                    }
                    echo('</td>');
                    if ($modeAutorisation) {
                            echo('<td> '.$une["autorisation"].' </td>');
                            echo('<td> '.$une["certificat"].' </td>');
                    } else {
                            echo('<td> '.$une["statut"].' </td>');
                            echo('<td> '.$une["login"].' </td>');
                            echo('<td> '.$une["nom"].' </td>');
                            echo('<td> '.$une["prenom"].' </td>');
                    }
                    echo('<td> '.$une["email"].' </td>');
                    if ($groupe=="") {  //  On affiche le labo et l'équipe que si on ne regroupe pas
                            echo('<td> '.$equipe.' </td>');
                    }
                    echo('<td> '.$une["emploi_categorie"].' - '.$une["job"].' </td>');
                    echo('<td> '.$une["appartenance"].' </td>');
                    echo('<td> '.$une["poste"].' </td>');
                    echo('<td> '.dateConv($une["date_arrivee"], DATE_FORMAT_HUMAIN).' </td>');
                    echo('<td> '.(is_null($une["date_depart"])?"-":dateConv($une["date_depart"], DATE_FORMAT_HUMAIN)).' </td>');
                    if ($modeDateSuppression) {	echo('<td> '.(is_null($une["date_suppression"])?"-":dateConv($une["date_suppression"], DATE_FORMAT_HUMAIN)).' </td>');	}	
                    echo('</tr>');
		}
		echo('</table>');
	}
?>
    <!-- end .content --></div>
<?php
require_once('pied.inc.php');
?>
