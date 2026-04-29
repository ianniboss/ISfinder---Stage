<?php
require_once('entete_nohtml.inc.php');
require_once('menu.inc.php');
define('ACTION_VALIDER', "Enregistrer");
define('ACTION_SUPPRIMER', "Supprimer");
define('ACTION_CONFIRME_SUPPRIMER', "Confirmer la suppression");
define('ACTION_RATTACHER_COMPTE', "Rattacher");
define('ACTION_DETACHER_COMPTE', "Détacher");
define('ACTION_MODIFIER_LIEN_COMPTE', "Modifier");

#
# On commence par jeter l'utilisateur s'il n'a pas le droit d'afficher cette page !
#
$messErr = array();
$messWarn = array();
$AFFICHER_FORMULAIRE = (in_array($AUTORISATION, array(2,3)));	// Si VRAI, affichera le formulaire
$MODIFIER_LIEN_COMPTE = FALSE;          // Si VRAI, permet de modifier le lien entre le compte et l'équipe
$MODIFIABLE = $AFFICHER_FORMULAIRE; // Si VRAI, permet de modifier les champs (sinon visu simple pour tous)
// Défini la valeur par défaut pour les nouveaux comptes
$defaut = array('equipe'=>"", 'email'=>"", 'id_labo'=>NULL, 'nom_machines'=>"", 'href'=>"", 'id_vlan'=>NULL, 'nom_AD'=>"");
// Si en modification d'un compte, modifie valeurs par défaut !
$idvar = getVar('id', 0);	// ($idvar==0) -> Ajout, sinon modification !
$listeMembres = array();
$listeMachines = array(); 
if ($idvar>0) {	// Si l'id equipe est indiqué
	$infoEquipe = sqlTrouve('reseau.equipes', 'idx='.$idvar);
	if (is_array($infoEquipe)) {	// Modifie les valeurs par défaut si l'équipe existe
		$defaut = $infoEquipe;
                $listeMembres = sqlSelect('comptes_equipes', 'id_equipe='.$idvar, NULL, NULL, 'id_compte');
	} else {	// Supprime l'id si l'équipe n'existe pas
		$idvar = 0;
	}
        $listeMachines = sqlSelect('reseau.machines', 'id_equipe='.$idvar, NULL, NULL, 'idx');
} 
if ($AFFICHER_FORMULAIRE) {	// Récupère les valeurs du formulaire !
	$equipe =                   trim(getVar('equipe', htmlspecialchars_decode($defaut["equipe"])));
	$email =                    str2lower(trim(getVar('email', $defaut["email"])));
	$id_labo =                  (int)getVar('id_labo', $defaut["id_labo"]);
	$nom_machines =             trim(getVar('nom_machines', $defaut["nom_machines"]));
	$href =                     trim(getVar('href', $defaut["href"]));
	$id_vlan =                  (int)getVar('id_vlan', $defaut["id_vlan"]);
	$nom_AD =                   trim(getVar('nom_AD', $defaut["nom_AD"]));
        $id_compte =                (int)getVar('id_compte', 0);   // Compte à supprimer
        if ($id_compte>0) { // Si un compte est indiqué, on l'oublie s'il n'existe pas
            $defautCompte = sqlTrouve('comptes', 'id='.$id_compte);
            if (!is_array($defautCompte)) {  $id_compte = 0;  }
            else {  $defautCompte = array('quotite'=>100, 'id_responsabilite'=>1);  }
        }
        $add_compte =               (int)getVar('add_compte', 0);  // Compte à lier, sera géré dans les actions
        $id_responsabilite =        (int)getVar('id_responsabilite', $defautCompte['id_responsabilite']);
        $quotite =                  (int)getVar('quotite', $defautCompte['quotite']);
          // Contenu du bouton pour supprimer l'équipe ! vide si équipe non supprimable (liée à des comptes ou machines)
        $BOUTON_SUPPRIME_CONFIRME = ( (($listeMembres!=array()) or ($listeMachines!=array())) ? "" : formBouton('action', ACTION_SUPPRIMER) ); 
// Effectue les actions simples !
	$action = getVar('action', "");
        if (($BOUTON_SUPPRIME_CONFIRME=="") and (in_array($action, array(ACTION_SUPPRIMER, ACTION_CONFIRME_SUPPRIMER)))) {   $action = "";  }
//            $REDIRIGER_VERS_PAGE = "password.php?id=".$idvar;   // Et on redirigera vers une autre page, avant le HTML
        switch ($action) {
            case ACTION_SUPPRIMER : // Vérifie si on peut supprimer l'équipe
                    $BOUTON_SUPPRIME_CONFIRME = formBouton('action', ACTION_CONFIRME_SUPPRIMER);
                break;
            case ACTION_CONFIRME_SUPPRIMER : // Supprime l'équipe
                    sqlDelete('reseau.equipes', 'idx='.$idvar);
                    $REDIRIGER_VERS_PAGE = "listEquipe.php";
                    $action = "";
                break;
            case ACTION_RATTACHER_COMPTE : // Rattache un compte
                    if (array_key_exists($add_compte, $listeMembres)) { // Le compte est déjà lié à l'équipe...
                        $messWarn[] = "Le compte indiqué est déjà lié à cette équipe.";
                        $add_compte = 0;
                    } else {    // Le compte n'est pas encore lié à l'équipe
                        $listeMembres[$id_compte] = array('id_compte'=>$add_compte, 'id_equipe'=>$idvar, 'quotite'=>100, 'id_responsabilite'=>1);
                        sqlInsert('comptes_equipes', $listeMembres[$id_compte]);   // Ajoute le lien entre équipe et compte
                        $id_compte = $add_compte;
                        $add_compte = 0;
                        $quotite = 100;
                        $id_responsabilite = 1;
                        $messWarn[] = "Le compte a été lié à cette équipe, vous pouvez modifier responsabilité et quotité.";
                        $MODIFIER_LIEN_COMPTE = TRUE;
                    }
                    $action = "";
                break;
            case ACTION_DETACHER_COMPTE : // Détache un compte
                    if ($id_compte>0) { // On s'assure quand même qu'un compte a été sélectionné dans la liste
                        if (!array_key_exists($id_compte, $listeMembres)) { // Le compte n'est déjà pas lié à l'équipe...
                            $messWarn[] = "Le compte indiqué n'est pas lié à cette équipe.";
                            $id_compte = 0;
                        } else {    // Le compte n'est pas encore lié à l'équipe
                            sqlDelete('comptes_equipes', 'id_compte='.$id_compte.' and id_equipe='.$idvar);        // Supprime le lien entre équipe et compte
                            $EXlisteMembres = $listeMembres;
                            $listeMembres = array();
                            foreach ($EXlisteMembres as $cle=>$valeur) {   if ($cle!=$id_compte) { $listeMembres[$cle] = $valeur; }  }
                            $MODIFICATION_EQUIPE = FALSE;   // On ne modifiera pas l'équipe qui vient d'être supprimée !
                        }	
                    }
                    $action="";
                break;
            case ACTION_VALIDER_LIEN_COMPTE :   // Valide le changement du lien avec un compte
                    if ($id_compte>0) {
                        sqlUpdate('comptes_equipes', 'id_equipe='.$idvar.' and id_compte='.$id_compte, array('id_responsabilite'=>$id_responsabilite, 'quotite'=>$quotite));
                        $MODIFIER_LIEN_COMPTE = FALSE;
                    }
                    $action = "";
                break;
            case ACTION_MODIFIER_LIEN_COMPTE :    // On demande à pouvoir modifier une équipe : changera juste l'affichage du formulaire !
                    $action = ""; 
                    $MODIFIER_LIEN_COMPTE = TRUE;
                break;
        }
// Vérifie et corrige les valeurs du formulaire
	if ($action==ACTION_VALIDER) {	// Tout d'abord, on fait uniquement les contrôles et corrections
		if ($equipe=="") {	$messErr[] = "Le nom d'équipe est obligatoire.";		}
		if ($id_labo==0) {	$messErr[] = "Le laboratoire est obligatoire.";		}
		if (count($messErr)>0) {	$action = "";	}	// On annule l'enregistrement s'il y a des erreurs...
	}
// Enregistrement des données, les contrôles sont faits !
	if ($action==ACTION_VALIDER) { 
		$donnees = array('equipe'=> htmlspecialchars($equipe, ENT_QUOTES, 'UTF-8', TRUE), 'email'=>$email, 'id_labo'=>$id_labo, 'nom_machines'=>$nom_machines, 'href'=>$href, 'id_vlan'=>$id_vlan, 'nom_AD'=>$nom_AD);
		if ($idvar>0) {	// Mise à jour demandée !
			if ( (array_merge(array('idx'=>(string)$idvar), $donnees))==$defaut ) {	// Les données n'ont pas été modifiées !
				$messWarn[] = "<strong>Mise à jour inutile.</strong>";
			} else {
				$result = sqlUpdate('reseau.equipes', 'idx='.$idvar, $donnees);
				if ( (is_int($result)) and ($result>0) ) {	// OK
					$messWarn[] = "<strong>Mise à jour effectuée.</strong>";
				} else {	// KO
					$messErr[] = "<strong>Echec de mise à jour.</strong>";
				}
			}
		} 
                else {	// Ajout
			$idvar = sqlInsert('reseau.equipes', $donnees);
			if ( (is_int($idvar)) and ($idvar>0) ) {	// OK
				$messWarn[] = "<strong>Ajout effectué.</strong>";
			} else {	// KO
				$messErr[] = "<strong>Echec d'enregistrement.</strong>";
			}
		}
	}
}
// On gère une éventuelle redirection
if (count($messErr)>0)  {   // S'il y a eu des erreurs d'enregistrement, on interdit la redirection vers changement de mot de passe
    $REDIRIGER_VERS_PAGE = NULL;  
}
if (!is_null($REDIRIGER_VERS_PAGE)) {    
	header('Location: '.$REDIRIGER_VERS_PAGE);      
	exit();        
}
// ZERO HTML Avant ce point !!!!
require_once('entete_html.inc.php');
echo('<div class="content">');
// Afficher les avertissements et erreurs :
echo("<div id='notifications' class='ecran'>");
if (count($messErr)>0) {
	echo("<div class='notification rouge'>");
	echo("<h2>Erreurs bloquantes :</h2><ul>");
	foreach ($messErr as $une) {	echo("<li>".$une."</li>");	}
	echo("<li><strong>L'enregistrement n'a pas été fait</strong> : veuillez corriger les erreurs !</li>");
	echo("</ul>");
	echo("</div>");
}
if (count($messWarn)>0) {
	echo("<div class='notification bleu'>");
	echo("<h3>Informations :</h3><ul>");
	foreach ($messWarn as $une) {	echo("<li>".$une."</li>");	}
	echo("</ul>");
	echo("</div>");
}
echo("</div>");
if ($AFFICHER_FORMULAIRE) {	// Affichage du formulaire
	$MODIFIABLE = ( ($AUTORISATION==2)			// 		Modifiable pour les administrateurs
                        || (in_array($id_statut, array(1,2,3,5,7))) );	// OU	les comptes sans statut obligeant une intervention des admins
	echo("<div class='boite'>");
	echo("<div><h1> ".($idvar==0?"Cré":"Modific")."ation d'une équipe </h1></div>");
	echo("<form method='post' action='' enctype='multipart/form-data'>");
		echo(champID());
		echo("<table>");
		// Identifiant
			if ($AUTORISATION==2) {	// Affichage uniquement pour administrateurs
				echo("<tr class='ecran'><th> ID </th><td> ".( ($idvar>0) ? $idvar : "<i>Nouveau</i>" )." </td></tr>");
                        } else {   echo(champID('id_cbi', $id_cbi));   }
		// Nom
			echo("<tr><th> Nom </th>");
			echo("<td> ".champTexte('equipe', 'Indiquez le nom (obligatoire !)', $equipe, 40, $MODIFIABLE)." </td></tr>");
		// Alias email
			echo("<tr><th> Alias Email </th>");
			echo("<td> ".champTexte('email', 'Indiquez l&acute;email (obligatoire !)', $email, 30, ($AUTORISATION==2) and $MODIFIABLE)." </td></tr>");
		// Labo
			echo("<tr><th> Laboratoire de rattachement </th><td>");
			$labosTous = sqlSelect('reseau.labos', 'reseau.labos.idx<>5', 'reseau.labos.nom');  // On exclu juste LE labo qui est une prévu pour les machines personnelles
			$listeLabos = array(0=>NON_INDIQUE);
			foreach ($labosTous as $un) {	$listeLabos[$un['idx']] = $un['nom'];	}
			echo( champListe('id_labo', 'Indiquez le laboratoire (obligatoire)', $id_labo, 1, $listeLabos, TRUE, $MODIFIABLE) );
			echo("</td></tr>");
		// Nom Machines
			echo("<tr><th> Nom usuel pour les machines </th>");
			echo("<td> ".champTexte('nom_machines', 'Indiquez le début usuel des noms de machines', $nom_machines, 30, ($AUTORISATION==2) and $MODIFIABLE)." </td></tr>");
		// href
			echo("<tr><th> Lien internet </th>");
			echo("<td> ".champTexte('href', 'Indiquez le lien vers la page web', $href, 60, $MODIFIABLE)." </td></tr>");
		// ID VLAN
			echo("<tr><th> VLAN dédié </th><td>");
			$vlanTous = sqlSelect('reseau.vlans', '', 'reseau.vlans.nom');  // On exclu juste LE labo qui est une prévu pour les machines personnelles
			$listeVlan = array(0=>NON_INDIQUE);
			foreach ($vlanTous as $un) {	$listeVlan[$un['idx']] = $un['nom']." - ".$un['desciption'];	}
			echo( champListe('id_vlan', 'Indiquez le VLAN dédié à cette équipe', $id_vlan, 1, $listeVlan, TRUE, ($AUTORISATION==2) and $MODIFIABLE) );
			echo("</td></tr>");
		// Nom Active Directory
			echo("<tr><th> Nom pour l'AD </th>");
			echo("<td> ".champTexte('nom_AD', 'Ce nom doit correspondre à celui défini dans l&acute;Active Directory', $nom_AD, 30, ($AUTORISATION==2) and $MODIFIABLE)." </td></tr>");
		// Membres
			echo("<tr><th rowspan='2'> Membres </th><td>");
                        // On récupère d'abore les comptes déjà liées au compte :
                        $requeteEquipe = 'SELECT comptes_equipes.id_compte as id_compte, comptes.nom as nom, comptes.prenom as prenom,';
                        $requeteEquipe.= ' comptes_statut.statut as statut, date_depart as date_depart, ';
                        $requeteEquipe.= ' comptes_equipes.quotite as quotite, responsabilites.responsabilite as responsabilite, responsabilites.idx as id_responsabilite';
                        $requeteEquipe.= ' FROM comptes_equipes LEFT OUTER JOIN reseau.equipes ON reseau.equipes.idx=comptes_equipes.id_equipe';
                        $requeteEquipe.= ' LEFT OUTER JOIN comptes ON comptes.id=comptes_equipes.id_compte';
                        $requeteEquipe.= ' LEFT OUTER JOIN reseau.labos ON reseau.labos.idx=reseau.equipes.id_labo';
                        $requeteEquipe.= ' LEFT OUTER JOIN responsabilites ON responsabilites.idx=comptes_equipes.id_responsabilite';
                        $requeteEquipe.= ' LEFT OUTER JOIN comptes_statut ON comptes_statut.idx=comptes.id_statut';
                        $requeteEquipe.= ' WHERE comptes_equipes.id_equipe='.$idvar;
                        $requeteEquipe.= '   AND id_statut<>7';         // Pas les comptes supprimés...
                        $requeteEquipe.= ' ORDER BY comptes.nom ;';
                        $listeMembres = sqlRequete($requeteEquipe, 'id_compte');
                        if ($MODIFIER_LIEN_COMPTE) { // On permet de modifier la responsabilité et la quotité, mais on affichera que les boutons Détacher et Valider les modifs
                                echo(" Modification du lien avec la personne : ".$listeMembres[$id_compte]['nom']." ".$listeMembres[$id_compte]['prenom'].champID('id_compte', $id_compte) );
                                    $quotite = $listeMembres[$id_compte]['quotite'];
                                    $id_responsabilite = $listeMembres[$id_compte]['id_responsabilite'];
                                    // Affiche la liste déroulante des responsabilité,
                                    $listeResponsabilites = sqlSelect('responsabilites', '', 'idx', NULL, 'idx');
                                    echo("<br />Responsabilite : ".champListe('id_responsabilite', 'Indiquez la responsabilité de la personne dans l&acute;équipe', $id_responsabilite, 1, $listeResponsabilites, FALSE, $MODIFIABLE) );
                                    // Affiche le champ de quotité (liste déroulante ? curseur ? texte ?)
                                    echo(" | Quotité : ".formNumber('quotite', 'Indiquez la quotité', $quotite, 0, 100, 5, $MODIFIABLE) );
                                    echo( "<br />".formBouton('action', ACTION_VALIDER_LIEN_COMPTE)."&nbsp;".formBouton('action', ACTION_DETACHER_COMPTE) );
                                echo('</td></tr>');
                                echo('<tr><td> Ajouter un membre : Vous pourrez ajouter un autre membre après avoir validé ou détaché celui ci-dessus...');
                                echo(champID('add_equipe', 0) );
                        } else {    // On permet de choisir l'équipe dans la liste ou d'en ajouter une, mais pas de modifier responsabilité et quoité !
                                echo(" Membres de l'équipe : ");
                                if (is_array($listeMembres)) {
                                    foreach ($listeMembres as $une) {
                                        $listeDeroulanteMembres[$une['id_compte']] = $une['nom'].' '.$une['prenom'].' - '.$une['quotite'].'%'
                                                .($une['id_responsabilite']!=1?' ('.$une['responsabilite'].')':'').' -> '.$une['statut'];
                                    }
                                }
                                if ($listeMembres!=array()) { // On affiche les équipes liées que s'il y en a !
                                    echo(champListe('id_compte', 'Choisissez le membre à modifier', $id_compte, 1, $listeDeroulanteMembres, FALSE, $MODIFIABLE) );
                                    echo( "<br />".formBouton('action', ACTION_MODIFIER_LIEN_COMPTE)."&nbsp;".formBouton('action', ACTION_DETACHER_COMPTE) );
                                } else {    // Pas d'équipe liée, on garde juste un id_equipe
                                    echo(" aucune".champID('id_compte', $id_compte));
                                }
                                echo('</td></tr>');
                                echo('<tr><td> Ajouter le membre : ');
                                // On affiche ensuite les membres PAS liés au compte, pour proposer de les ajouter
                                $requeteEquipe = 'SELECT comptes.id as id_compte, comptes.nom as nom, comptes.prenom as prenom,';
                                $requeteEquipe.= ' comptes_statut.statut as statut, comptes.date_depart as date_depart, ';
                                $requeteEquipe.= ' reseau.labos.nom as nom_labo';
                                $requeteEquipe.= ' FROM comptes';
                                $requeteEquipe.= ' LEFT OUTER JOIN reseau.labos ON reseau.labos.idx=comptes.id_labo';
                                $requeteEquipe.= ' LEFT OUTER JOIN comptes_statut ON comptes_statut.idx=comptes.id_statut';
                                $requeteEquipe.= ' LEFT OUTER JOIN comptes_equipes ON comptes_equipes.id_compte=comptes.id';
                                $requeteEquipe.= ' WHERE id_statut IN (1,2,3,8,9) AND date_depart>NOW()';
                                if ($listeMembres!=array()) {
                                    $requeteEquipe.= ' AND comptes_equipes.id_compte not in ('.implode(',', array_keys($listeMembres)).')';
                                }
                                $requeteEquipe.= ' ORDER BY reseau.labos.nom, comptes.nom ;';
                                $listeMembresExclus = sqlRequete($requeteEquipe, 'id_compte');
                                $listeDeroulanteMembresExclus = array('0'=>NON_INDIQUE);
                                $ex_labo = "";
                                foreach ($listeMembresExclus as $une) {
                                        if ($ex_labo!=$une['nom_labo']) {
                                            $listeDeroulanteMembresExclus['DISABLED'.$une['nom_labo']] = '---'.$une['nom_labo'];
                                            $ex_labo = $une['nom_labo'];
                                        }
                                        if (!array_key_exists($une['id_compte'], $listeMembres)) {
                                            $listeDeroulanteMembresExclus[$une['id_compte']] = $une['nom'].' '.$une['prenom'];
                                        }
                                }
                                echo(champListe('add_compte', 'Choisissez le membre à ajouter', $add_compte, 1, $listeDeroulanteMembresExclus, FALSE, $MODIFIABLE) );
                                            // Affiche le bouton pour valider l'AJOUT de l'équipe
                                echo(formBouton('action', ACTION_RATTACHER_COMPTE));
                                echo("<br />S'il manque la personne que vous voulez rajouter, contactez votre gestionnaire.");
                                echo("<br />Les comptes listés sont ceux qui doivent perdurer (exclu les comptes supprimés ou désactivés, ou en instance de l'être).");
                        }
			echo(" </td></tr>");
		// Boutons
			echo("<tr><th colspan='2'> ");
                        if (!$MODIFICATION_EQUIPE) {    // On n'affiche les boutons de validation du formulaire et d'impression que si on n'est pas en train de modifier responsabilité et quotité d'une équipe...
                            if ($MODIFIABLE) {	// Formulaire modifiable, permet de valider
                                    echo( creerFormBouton('action', ACTION_VALIDER) );
                            }
                            echo(" </th></tr></table>");
                        }
		echo("</form>");//	var_dump($_REQUEST);
	echo("</div>");
}

require_once('pied.inc.php');
?>
