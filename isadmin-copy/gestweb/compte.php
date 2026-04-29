<?php
require_once('entete_nohtml.inc.php');
require_once('menu.inc.php');
define('ACTION_VALIDER', "Enregistrer");
define('ACTION_AJOUTER_EQUIPE', "Ajouter");
define('ACTION_MODIFIER_EQUIPE', "Modifier");
define('ACTION_VALIDER_EQUIPE', "Valider");
define('ACTION_DETACHER_EQUIPE', "Détacher");
define('ACTION_INSCRIPTION_SEMINAIRE', "Inscription");
define('ACTION_DESINSCRIPTION_SEMINAIRE', "Descinscription");
require_once('../function_mail.inc.php');

#
# On commence par jeter l'utilisateur s'il n'a pas le droit d'afficher cette page !
#
$messErr = array();
$messWarn = array();
$AFFICHER_FORMULAIRE = (in_array($AUTORISATION, array(2,3)));	// Si VRAI, affichera le formulaire
$MODIFIABLE = $AFFICHER_FORMULAIRE;								// Si VRAI, permet de modifier les champs (sinon visu simple pour tous)
$mode = urldecode(getVar('mode', MODE_COMPTE));
if (!in_array($mode, array(MODE_COMPTE, MODE_ANNUAIRE))) {	$mode = MODE_COMPTE;	}	// Par défaut, on gère un compte informatique
// Défini la valeur par défaut pour les nouveaux comptes
$defaut = array('id_statut'=>( ($mode==MODE_COMPTE) ? 1 : 2 ), 'annuaire'=>"1", 'nom'=>"", 'prenom'=>"", 'id_labo'=>$AUTORISATION_ID_LABO,
		'id_job'=>0, 'id_appartenance'=>0, 'poste'=>"", 'email'=>"", 'email_perso'=>"", 'lieu'=>"", 
                'date_arrivee'=>date("Y-m-d"), 'date_depart'=>NULL, 'date_creation'=>date("Y-m-d H:i:s"), 'date_suppression'=>NULL, 
                'login'=>NULL, 'nvx_membres'=>"0", 'mail_demandeur'=>$AUTORISATION_MAIL, 'id_cbi'=>NULL);
// Si en modification d'un compte, modifie valeurs par défaut !
$idvar = getVar('id', 0);	// ($idvar==0) -> Ajout, sinon modification !
$equipes_liees = NULL;
$listeIdEquipe = array();
if ($idvar>0) {	// Si l'id compte est indiqué
	$infoCompte = sqlTrouve('comptes', 'id='.$idvar);
	if (is_array($infoCompte)) {	// Modifie les valeurs par défaut si le compte existe
		$defaut = $infoCompte;
                $infoEquipes = sqlSelect('comptes_equipes', 'id_compte='.$idvar);
                if (is_array($infoEquipes)) {
                    $equipes_liees = array();
                    foreach ($infoEquipes as $uneEquipe) {
                        $listeIdEquipe[] = $uneEquipe['id_equipe'];
                        $equipes_liees[$uneEquipe['id_equipe']] = $uneEquipe;
                    }
                }
	} else {	// Supprime l'id si le compte n'existe pas
		$idvar = 0;
	}
} 
if ($AFFICHER_FORMULAIRE) {	// Récupère les valeurs du formulaire !
	$id_statut =                (int)getVar('id_statut', $defaut['id_statut']);
	$mode =                     ( $id_statut==2 ? MODE_ANNUAIRE : MODE_COMPTE );	// change le mode selon le statut !!!
	$id_cbi =                   (int)getVar('id_cbi', $defaut["id_cbi"]);
	$nom =                      ucsmart(trim(getVar('nom', htmlspecialchars_decode($defaut["nom"]))));
	$prenom =                   ucsmart(trim(getVar('prenom', htmlspecialchars_decode($defaut["prenom"]))));
	$id_labo =                  (int)getVar('id_labo', $defaut["id_labo"]);
	$id_equipe =                (int)getVar('id_equipe', (is_null($equipes_liees)?0:$listeIdEquipe[0]));  // id de l'équipe à modifier, celui du compte si non indiqué, sinon remplacé par celui du formulaire, 0 si aucune équipe !
        $equipe_responsabilite =    (int)getVar('equipe_responsabilite', (is_null($equipes_liees)?1:$equipes_liees[$id_equipe]['id_responsabilite']));    // Responsabilite de la nouvelle équipe, ou de l'équipe à modifier
        $equipe_quotite =           (int)getVar('equipe_quotite', (is_null($equipes_liees)?100:$equipes_liees[$id_equipe]['quotite']));        // Quotite de la nouvelle équipe, ou de l'équipe à modifier
        $add_equipe =               (int)getVar('add_equipe', 0);      // id de l'équipe à ajouter
        if ( (is_array($equipes_liees)) and (array_key_exists($add_equipe, $equipes_liees)) ) {    $add_equipe = 0;    }   // Ignore $add_equipe si l'équipe est déjà liée
	$poste =                    nettoieTelephone(trim(getVar('poste', $defaut["poste"])));
	$id_job =                   (int)getVar('id_job', $defaut["id_job"]);
	$id_appartenance =          (int)getVar('id_appartenance', $defaut['id_appartenance']);
	$email =                    str2lower(trim(getVar('email', $defaut["email"])));
	$email_perso =              str2lower(trim(getVar('email_perso', $defaut["email_perso"])));
	$lieu =                     trim(getVar('lieu', $defaut["lieu"]));
	$date_arrivee =             getVar('date_arrivee', $defaut["date_arrivee"]);
	if ($date_arrivee==DATE_VIERGE) {	// Si date arrivée non indiquée ou 0000-00-00, la remplace par date de création
		$date_arrivee = dateConv($defaut['date_creation'], DATE_FORMAT_SQL);
		$messWarn[] = "La date d'arrivée était invalide : elle a été remplacée par la date de création.";
	}
	$date_depart =              getVar('date_depart', $defaut["date_depart"]); 
	if ( ( (is_null($date_depart)) or ($date_depart==DATE_VIERGE) ) and ($date_depart!="") ) {
		$date_depart = "";
		$messWarn[] = "La date de départ était invalide : elle a été supprimée.";
	}
	$date_creation =            $defaut["date_creation"];
	$date_suppression =         $defaut["date_suppression"];
	$deja_vu =                  getVar('deja_vu');	//	var_dump($deja_vu);
	$nvx_membres =              ( (isset($deja_vu)) ? getVar('nvx_membres', '') : ( $defaut["nvx_membres"]=="1" ? "checked" : "" ) );
	$annuaire =                 ( (isset($deja_vu)) ? getVar('annuaire', '') : ( $defaut["annuaire"]=="1" ? "checked" : "" ) );
	$inscriptionListeSeminaire =	getVar('inscriptionListeSeminaire', ( in_array($id_labo, array(1,2)) ? "checked" : "" ) );
	$login =                    ( $AUTORISATION==2 ? ( getVar('login', $defaut['login']) ) : $defaut['login'] );	// Seuls les admins peuvent modifier le login
	$mail_demandeur =           getVar('mail_demandeur', $defaut['mail_demandeur']);
// Effectue les actions simples !
	$action = getVar('action', "");
/*        if ($action==ACTION_MODIFIER_PASSWORD) {    // Si on demande à modifier le mot de passe du compte...
            $action = ACTION_VALIDER;                   // On enregistrera d'abord comme une validation classique !
            $REDIRIGER_VERS_PAGE = "password.php?id=".$idvar;   // Et on redirigera vers une autre page, avant le HTML
        } else {                                     // ... Si on ne le demande pas, la variable reste NULL
            $REDIRIGER_VERS_PAGE = NULL;
        }*/
        $MODIFICATION_EQUIPE = FALSE;   // Si TRUE, provoquera l'affichage des champs pour modifier la quotite et la responsabilite
        $equipeSupprimee = NULL;        // Indique l'id_equipe qui vient d'être détachée du compte, provoquera envoi d'un mail au responsable dans certains cas (id_statut IN [])
        $equipeAjoutee = NULL;          // Indique l'id_equipe qui vient d'être ajoutée au compte, provoquera envoi d'un mail au responsable dans certains cas
        switch ($action) {
            case ACTION_AJOUTER_EQUIPE :
            case ACTION_VALIDER :  // Ajoute l'équipe indiquée par add_equipe
                    if ( ($idvar>0) and ($add_equipe>0) ) {   // Ajoute l'équipe
                        // On vérifie d'abord que le lien n'existe pas...
                        $lien_existe = sqlSelect('comptes_equipes', 'id_compte='.$idvar.' and id_equipe='.$add_equipe);
                        if (is_array($lien_existe)) {   // Rien à créer, le lien est déjà établi entre l'équipe et le compte (peut arriver si on actualise la page après ajout...)
                            $id_equipe = $add_equipe;                               // Permet de modifier les infos sur cette équipe pour ce compte
                            $add_equipe = 0;                                        // Vide la liste déroulante d'AJOUT
                            $messWarn[] = "L'équipe indiquée est déjà liée à ce compte";
                        } else {
                            $donneesComptesEquipes = array('id_compte'=>$idvar, 'id_equipe'=>$add_equipe, 'quotite'=>100, 'id_responsabilite'=>1);
                            sqlInsert('comptes_equipes', $donneesComptesEquipes);   // Ajoute le lien entre équipe et compte
                            $id_equipe = $add_equipe;                               // Sélectionne l'équipe ajoutée par défaut !
                            $add_equipe = 0;                                        // Vide la liste déroulante d'AJOUT
                            $equipe_quotite = 100;                                  // Indique une quotité de 100 par défaut
                            $equipe_responsabilite = 1;                             // Vide la liste déroulante de modification de responsaibilité
                            $messWarn[] = "L'équipe indiquée a été liée à ce compte, vous pouvez modifier le niveau de responsabilité et la quotité.";
                            if ($action!=ACTION_VALIDER) {  $MODIFICATION_EQUIPE = TRUE;   }        // On modifiera les infos si on ne valide pas le formulaire entier
                            $equipes_liees[$id_equipe] = array('id_equipe'=>$id_equipe, 'quotite'=>100, 'id_responsabilite'=>1);
                        }
                    }
                    if ($action!=ACTION_VALIDER) {  $action = "";  }
                break;
            case ACTION_VALIDER_EQUIPE :       // Modifie l'équipe sélectionnée par $id_equipe
                    if ($id_equipe>0) { // Un $id_equipe est indiqué, on commence par vérifier si le lien existe bien
                        $condition = 'id_compte='.$idvar.' and id_equipe='.$id_equipe;
                        $lien_existe = sqlSelect('comptes_equipes', $condition);
                        if (is_array($lien_existe)) {   // Le lien existe bien, on modifie les infos...
                            $add_equipe = 0;                                        // Vide la liste déroulante d'AJOUT : on ne peut pas modifier une équipe ET en ajouter une en même temps !
                            $donneesComptesEquipes = array('quotite'=>$equipe_quotite, 'id_responsabilite'=>$equipe_responsabilite);
                            sqlUpdate('comptes_equipes', $condition, $donneesComptesEquipes);
                            $messWarn[] = "Le lien entre cette équipe et ce compte ont été mis à jour.";
                            $equipes_liees[$id_equipe] = array('id_equipe'=>$id_equipe, 'quotite'=>$equipe_quotite, 'id_responsabilite'=>$equipe_responsabilite);
                        } else {
                            $donneesComptesEquipes = array('id_compte'=>$idvar, 'id_equipe'=>$id_equipe, 'quotite'=>$equipe_quotite, 'id_responsabilite'=>$equipe_responsabilite);
                            sqlInsert('comptes_equipes', $donneesComptesEquipes);   // Ajoute le lien entre équipe et compte
                            $add_equipe = 0;                                        // Vide la liste déroulante d'AJOUT
                            $messWarn[] = "L'équipe indiquée a été liée à ce compte, les informations ont été mises à jour.";
                            $equipes_liees[$id_equipe] = array('id_equipe'=>$id_equipe, 'quotite'=>100, 'id_responsabilite'=>1);
                        }
                    }
                    $action = ""; // On supprime l'action sauf si on valide 
                break;
            case ACTION_DETACHER_EQUIPE :   // On détache une équipe du compte !
                // On vérifie d'abord si le lien existe ou pas...
                $condition = 'id_compte='.$idvar.' and id_equipe='.$id_equipe;
                $lien_existe = sqlSelect('comptes_equipes', $condition);
                if (!is_array($lien_existe)) {   // Rien à supprimer, le lien n'existe pas entre l'équipe et le compte (pourrait arriver si on actualise la page après ajout...)
                    $messWarn[] = "L'équipe n'est pas rattachée à ce compte";
                } else {
                    sqlDelete('comptes_equipes', $condition);        // Supprime le lien entre équipe et compte
                    $EXequipes_liees = $equipes_liees;
                    $equipes_liees = array();
                    foreach ($EXequipes_liees as $cle=>$valeur) {   if ($cle!=$id_equipe) { $equipes_liees[$cle] = $valeur; }  }
                    $MODIFICATION_EQUIPE = FALSE;   // On ne modifiera pas l'équipe qui vient d'être supprimée !
                }	
                $action="";
                break;
            case ACTION_MODIFIER_EQUIPE :    // On demande à pouvoir modifier une équipe : changera juste l'affichage du formulaire !
                    $action = ""; 
                    $MODIFICATION_EQUIPE = TRUE;
                break;
            case MAIL_SEMINAIRE_INSCRIPTION:
            case MAIL_SEMINAIRE_DESINSCRIPTION:
                    if (envoyerMail($action, 'sympa@univ-tlse3.fr', $email, array('nom'=>$nom, 'prenom'=>$prenom) )) {
                                $messWarn[] = "Le message a été envoyé au gestionnaire de liste.";
                    } else {
                                $messWarn[] = "Le message n'a pas pu être envoyé au gestionnaire de liste.";
                    }
                    $action = "";
                break;
        }
// Avertir pour publication annuaire !	
	if ($id_labo>0) {
		if ( (in_array($id_labo, array(1,2,8))) and ($annuaire!="checked") ) {
			$messWarn[] = "Etes-vous certain de ne pas vouloir faire figurer cette personne sur l'annuaire IBCG ?";
		} elseif ( (!in_array($id_labo, array(1,2,8))) and ($annuaire=="checked") ) {
			$messWarn[] = "Etes-vous certain de vouloir faire figurer cette personne sur l'annuaire IBCG ?";
		}
	}
// Mise à jour de l'adresse mail si besoin
	if ( ($email=="") and ($nom!="") and ($prenom!="") and ($id_statut<>2) ) {	// compte avec mail vide mais nom et prenom remplis...
		$email = str_replace(' ','-',nettoieAccents(strtolower($prenom.".".$nom)))."@".MAIL_SERVEUR_DEFAUT;
		$messWarn[] = "L'adresse mail a été modifiée, veuillez la vérifier !";
	}
// Correction du login
	if ( ($mode==MODE_COMPTE) and ($AUTORISATION==2) 
	and ( ($login=="") or (is_null($login)) ) and ($nom!="") and ($prenom!="") ) {	// Uniquement si on crée un compte info, qu'on est admin, qu'il n'est pas indiqué et qu'on a le prenom et le nom !
		$login = substr(nettoieEspace(nettoieAccents(strtolower($prenom))),0,1).substr(nettoieEspace(nettoieAccents(strtolower($nom))),0,11);   // initiale du prénom + 11 caractères du nom
		$messWarn[] = "Le login a été modifié, veuillez le vérifier !";
	}
	if ($login!="")	{
		$conditions = '(LOWER(login)="'.strtolower(htmlentities($login, ENT_QUOTES, "UTF-8")).'")';
		$conditions.= ( ($idvar==0) ? '' : ' AND (id<>'.$idvar.')' );
		$conditions.= ' AND (id_statut IN (1,3,4,6,8))';
		$autreCompte = sqlTrouve('comptes', $conditions);
		if (is_array($autreCompte)) {
			$messErr[] = "Il existe un autre compte avec le même login : <a href='?id=".$autreCompte['id']."' target='_self'>Modifier l'autre compte</a>";
		}
	}
// Vérifie et corrige les valeurs du formulaire
	if ($action==ACTION_VALIDER) {	// Tout d'abord, on fait uniquement les contrôles et corrections
		if ($nom=="") {		$messErr[] = "Le nom est obligatoire.";		}
		if ($prenom=="") {	$messErr[] = "Le prénom est obligatoire.";	}
		if ( ($nom!="") and ($prenom!="") )	{
			$conditions = ' id_statut<>7';
			$conditions.= ' AND UPPER(nom)="'.strtoupper(htmlentities($nom, ENT_QUOTES, "UTF-8")).'"';
			$conditions.= ' AND UPPER(prenom)="'.strtoupper(htmlentities($prenom, ENT_QUOTES, "UTF-8")).'"';
			$conditions.= ( ($idvar==0) ? '' : ' AND id<>'.$idvar );
			$autreCompte = sqlTrouve('comptes', $conditions);
			if (is_array($autreCompte)) {
				$lienAutre = "<a href='?mode=".$mode."&id=".$autreCompte['id']."' target='_self'>Modifier l'autre compte</a>";
				switch ($autreCompte['id_statut']) {	// personnalise le message selon l'id_statut de l'autre compte...
					case 1 :    $mess = "Une demande de compte est en cours à ce nom";                      break;
					case 2 :    $mess = "Une entrée d'annuaire existe à ce nom";                            break;
					case 3 :    $mess = "Un compte actif existe déjà à ce nom";                             break;
					case 4 :    $mess = "Un compte au même nom est en cours de suspension";                 break;
					case 5 :    $mess = "Un compte suspendu existe à ce nom";                               break;
					case 6 :    $mess = "Un compte au même nom est en cours de suppression";                break;
					case 8 :    $mess = "Un compte au même nom est en cours de revalidation";               break;
                                        case 9 :    $mess = "Un compte au même nom est en attente de saisie du mot de passe";   break;
				}
				if ( ($AUTORISATION==2) or (in_array($id_statut, array(2,3,5,7))) ) {	// Pour les admins et les id_statut modifiables par les gestionnaires, on affiche un avertissement
					$messWarn[] = $mess." : ".$lienAutre;
				} else {
					$messErr[] = $mess." : contactez le service informatique !";
				}
			}
		}
		if ( ( (is_null($id_cbi)) or ($id_cbi==0) ) and ($idvar>0) and ($id_labo>0) )	{ // Modifie l'identifiant CBI si besoin
			if (in_array($id_labo, array(1,2,3,8))) {	$id_cbi = $idvar+40000;	}
		}
		if ( ($id_cbi>0) and ($idvar>0) ) {
			$conditions = ' id_cbi='.$id_cbi.' AND id<>'.$idvar;
			$autreCompte = sqlTrouve('comptes', $conditions);
			if (is_array($autreCompte)) {
				$lienAutre = "<a href='?mode=".$mode."&id=".$autreCompte['id']."' target='_self'>Modifier l'autre compte</a>";
				$messErr[] = "L'identifiant CBI existe déjà : modifiez-le, ou bien modifiez l'autre compte !";
			}
		}   // $equipes_liees[$uneEquipe['id_equipe']] = $uneEquipe;
                if ( ($add_equipe==0) and (is_null($equipes_liees)) ) {	// Aucune équipe à lier, ni déjà liée
                        if ($id_labo<>6) {  $messErr[] = "L'équipe est obligatoire.";   }
		}
		if ($id_labo==0) {	// Labo pas indiqué
			$messErr[] = "Le laboratoire est obligatoire.";
		}
		if ($id_job==0) {	// Impose la saisie de l'emploi
			$messErr[] = "La fonction ou type de stage est obligatoire.";	}
		else {	// Si emploi indiqué ...
			$listeEmploi = sqlSelect('emploi', 'id_job='.$id_job.' and temporaire>=1');
			if (is_array($listeEmploi)) {	// si emploi temporaire...
				$infoEmploi = $listeEmploi[0];
				if ($date_depart=="") {	// date de départ non indiquée, mais obligatoire !
                                      $messErr[] = "La date de départ est obligatoire pour les personnels non permanents.";
				}
				if ( ($infoEmploi['temporaire']<=3) and ($email_perso=="") ) {	// date de départ non indiquée, mais obligatoire !
					$messErr[] = "L'adresse mail personnelle est obligatoire pour les personnels qui restent moins de 3 mois.";
				}
			}
		}
                if ( ($date_depart<>"") and (in_array($id_statut,array(1,3,8,9))) and ((dateConv($date_depart, DATE_FORMAT_SQL))<=dateConv(NULL, DATE_FORMAT_SQL)) ) {	// Impose date de départ > date du jour pour création ou revalidation !
                        $messErr[] = "La date de départ ne peut pas être passée pour revalider le compte. ";
                }        
		if ($id_appartenance==0) {	// Impose la saisie de l'emploi
			$messErr[] = "L'appartenance à un organisme est obligatoire.";
		}
		if ($poste=="") {		$messErr[] = "Le numéro de téléphone est obligatoire.";		}
		if ($lieu=="") {		$messWarn[] = "Pensez à indiquer le lieu dès que vous aurez l'information.";		}
		if (count($messErr)>0) {	$action = "";	}	// On annule l'enregistrement s'il y a des erreurs...
	}
// Enregistrement des données, les contrôles sont faits !
	if ($action==ACTION_VALIDER) {
		$donnees = array('id_statut'=>$id_statut, 'annuaire'=>( ($annuaire=="checked") ? "1" : "0" ),
				'nom'=>$nom, 'prenom'=>$prenom, 
				'id_labo'=>$id_labo, 'id_job'=>$id_job, 'id_appartenance'=>$id_appartenance,
				'poste'=>$poste, 'email'=>$email, 'email_perso'=>$email_perso, 'lieu'=>$lieu, 
                                'date_arrivee'=>dateConv($date_arrivee, DATE_FORMAT_SQL),
				'date_depart'=>( $date_depart=="" ? NULL : dateConv($date_depart, DATE_FORMAT_SQL) ),
				'date_creation'=>date("Y-m-d H:i:s"), 'date_suppression'=>$date_suppression,
				'login'=>( ($login=="") ? NULL : $login ), 'id_cbi'=>( ($id_cbi==0) ? NULL : $id_cbi ),
				'nvx_membres'=>( ($nvx_membres=="checked") ? "1" : "0" ), 'mail_demandeur'=>$mail_demandeur);
		if ($idvar>0) {	// Mise à jour demandée !
			// Récupère le statut et l'équipe tels qu'ils sont enregistrés AVANT mise à jour
			$infoEnregistrees = sqlTrouve('comptes', 'id='.$idvar);	
			$statutEnregistre = $infoEnregistrees['id_statut'];
			$equipeEnregistre = $infoEnregistrees['id_equipe'];
			if ($id_statut<>$statutEnregistre) {
				if (in_array($id_statut, array(1,2,3,9))) {	// Pour les comptes demandés, actifs et les entrées d'annuaire
					$donnees['date_suppression'] = NULL;		// On supprime la date de suppression
				} elseif ($id_statut==7) {					// Pour les comptes supprimés
					$donnees['date_suppression'] = date("Y-m-d");	// on indique la date de suppression
				}
				if ( (in_array($id_statut, array(5,7)))	// Pour les comptes suspendus et supprimés...
				and ( ($donnees['date_depart']>date("Y-m-d")) or (is_null($donnees['date_depart'])) ) ) {	//... sans date de départ indiquée ou date future
					$donnees['date_depart'] = date("Y-m-d");	// On indique la date de départ
				}
			}
			if ( (array_merge(array('id'=>(string)$idvar), $donnees))==$defaut ) {	// Les données n'ont pas été modifiées !
				$messWarn[] = "<strong>Mise à jour inutile.</strong>";
			} else {
				$result = sqlUpdate('comptes', 'id='.$idvar, $donnees);
				if ( (is_int($result)) and ($result>0) ) {	// OK
					$messWarn[] = "<strong>Mise à jour effectuée.</strong>";
					$infoLabo = sqlTrouve('reseau.labos', 'idx='.$id_labo);
					$infoEquipe = ($id_equipe>0?sqlTrouve('reseau.equipes', 'idx='.$id_equipe):array('equipe'=>"aucune"));
					$infoExEquipe = ($equipeEnregistre>0?sqlTrouve('reseau.equipes', 'idx='.$equipeEnregistre):array('equipe'=>"aucune"));
					$infoEmploi = sqlTrouve('emploi', 'id_job='.$id_job);
					$infoEmploiCategorie = sqlTrouve('emploi_categorie', 'idx='.$infoEmploi['id_categorie']);	//var_dump($id_appartenance);
					$infoAppartenance = sqlTrouve('appartenance', 'idx='.$id_appartenance);						//var_dump($infoAppartenance);
					if ($id_statut<>$statutEnregistre) {	// Gestion des envois de mails quand modifications des statuts...$demande = "création";
						$lienCompte = SITE_RACINE."compte.php?id=".$idvar;
						$lienListe = SITE_RACINE."liste.php?mode=DemandesActuelles";
						switch ($statutEnregistre) {	// Réglage d'une variable pour informer sur l'ancien statut du compte
                                                        case 1 : $etatPrecedent = "Demande d'ouverture en cours";                                	break;
                                                        case 2 : $etatPrecedent = "Entrée d'annuaire";                                               	break;
                                                        case 3 : $etatPrecedent = "Compte actif";                                                	break;
                                                        case 4 : $etatPrecedent = "Demande de suspension en cours";	$demande = "suspension";	break;
                                                        case 5 : $etatPrecedent = "Compte suspendu";                             			break;
                                                        case 6 : $etatPrecedent = "Demande de suppression en cours";	$demande = "suppression";	break;
                                                        case 7 : $etatPrecedent = "Compte supprimé";                                     		break;
                                                        case 8 : $etatPrecedent = "Demande de revalidation en cours";	$demande = "revalidation";	break;
                                                        case 9 : $etatPrecedent = "Compte créé par le service Info, mot de passe pas à saisir";     break;
						}
						switch ($id_statut) {	// Switch selon le nouveau statut
							case 1:	break;	// Demande de création : IMPOSSIBLE car on est sur une modification...
							case 2:	// -> Annuaire	---> Passage vers annuaire : MAIL AU SERVICE INFO CAR SUPPRESSION DE COMPTE INFO
                                                            if ($statutEnregistre==1) { // Cas spécifique d'une demande de compte qu'on ne doit pas créer...
                                                                if ($email_perso!="") {
                                                                    $messWarn[] = "Le mail perso est impératif pour une entrée d'entrée d'annuaire !";
                                                                    $annuaire = "checked";
                                                                }
                                                                $messWarn[] = "Veillez à rediriger les mails ibcg vers son adresse personnelle !";
                                                            } else {    // Cas général d'un compte qui existait !
                                                                if (envoyerMail(MAIL_CONTENU_DEMANDE_SUPPRESSION, MAIL_SERVICE_INFO, $AUTORISATION_MAIL, 
                                                                    array('prenom'=>$prenom, 'nom'=>$nom, 'equipe'=>$infoEquipe['equipe'], 'labo'=>$infoLabo['nom']))) {
                                                                            $messWarn[] = "Un message a été envoyé au service info pour qu'ils suppriment le compte.";
                                                                } else {
                                                                            $messErr[] = "Le message n'a pas pu être envoyé au service info : contactez-les !";
                                                                }
                                                            }
							break;
							case 4:	// -> Demande de suspension
							case 6:	// -> Demande de suppression	---> Suspension ou suppression, demandée : MAIL AU SERVICE INFO CAR SUPPRESSION A FAIRE
                                                            if (envoyerMail(MAIL_CONTENU_DEMANDE_SUPPRESSION, MAIL_SERVICE_INFO, $AUTORISATION_MAIL,
                                                                array('prenom'=>$prenom, 'nom'=>$nom, 'equipe'=>$infoEquipe['equipe'], 'labo'=>$infoLabo['nom'], 'demande'=>$demande, 'date_depart'=>$date_depart, 'lien_compte'=>$lienCompte))) {
									$messWarn[] = "Un message a été envoyé au service info pour qu'ils suppriment le compte.";
                                                            } else {
									$messErr[] = "Le message n'a pas pu être envoyé au service info : contactez-les !";
                                                            }
							break;
							case 8:	// Demande de revalidation : MAIL AU SERVICE INFO CAR SUPPRESSION A FAIRE
                                                                if (envoyerMail(MAIL_CONTENU_REVALIDATION, MAIL_SERVICE_INFO, $AUTORISATION_MAIL,
                                                                    array('prenom'=>$prenom, 'nom'=>$nom, 'equipe'=>$infoEquipe['equipe'], 'labo'=>$infoLabo['nom'], 'lien_compte'=>$lienCompte))) {
									$messWarn[] = "Un message a été envoyé au service info pour qu'ils revalident le compte.";
								} else {
									$messErr[] = "Le message n'a pas pu être envoyé au service info : contactez-les !";
								}
							break;
							case 3:	//  Compte valide
								switch ($statutEnregistre) {
                                                                        case 1: case 2: case 9:		// Compte juste créé, ou entrée d'annuaire transformée : mail au titulaire du compte
										// Mail pour inscription séminaire si demandé
										if ($inscriptionListeSeminaire=="checked") {	// Inscription à la liste des séminaires
                                                                                        if (envoyerMail(MAIL_SEMINAIRE_INSCRIPTION, "sympa@univ-tlse3.fr", $email, array('prenom'=>$prenom, 'nom'=>$nom))) {
												$messWarn[] = "Le compte a été abonné à la liste de cbi.int-seminars@univ-tlse3.fr.";
											} else {
												$messWarn[] = "Le compte n'a pas pu être abonné à la liste de cbi.int-seminars@univ-tlse3.fr !";
												$inscriptionListeSeminaire = "";
											}
										}
										if (envoyerMail(MAIL_CONTENU_VALIDATION, $email, MAIL_SERVEUR_INFO,
                                                                                    array('prenom'=>$prenom, 'nom'=>$nom, 'date_arrivee'=>$date_arrivee, 'date_depart'=>$date_depart, 'inscription'=>$inscriptionListeSeminaire=="checked"))) {
											$messWarn[] = "Un message a été envoyé au titulaire du compte.";
										} else {
											$messErr[] = "Le message n'a pas pu être envoyé au titulaire : vérifiez !";
										}
                                                                                // Gère l'envoi du mail à technik et aux assistants de prévention
                                                                                $infoLabo = sqlTrouve('reseau.labos', 'idx='.$id_labo);
                                                                                //$infoEquipe = ( $id_equipe>0 ? sqlTrouve('reseau.equipes', 'idx='.$id_equipe) : array('equipe'=>"(Non renseigné)") );
                                                                                $infoEmploi = sqlTrouve('emploi', 'id_job='.$id_job);
                                                                                $infoEmploiCategorie = sqlTrouve('emploi_categorie', 'idx='.$infoEmploi['id_categorie']);	//var_dump($id_appartenance);
                                                                                $infoAppartenance = sqlTrouve('appartenance', 'idx='.$id_appartenance);						//var_dump($infoAppartenance);
                                                                                $options = array('prenom'=>$prenom, 'nom'=>$nom, 'date_arrivee'=>$date_arrivee, 'date_depart'=>$date_depart, 
                                                                                        'equipe'=>$infoEquipe['equipe'], 'labo'=>$infoLabo['nom'], 'poste'=>$poste, 'job'=> $infoEmploi['job'],
                                                                                        'categorie'=>$infoEmploiCategorie['categorie'], 'appartenance'=>$infoAppartenance['appartenance'],
                                                                                        'mail'=>$email);
                                                                                if (in_array($id_labo, array(1,2,8))) { // Envoi uniquement si labo LBME, LMGM ou CBI !
                                                                                    if ( envoyerMail(MAIL_CONTENU_AP, "Assistants de Prevention <ibcg.ap@ibcg.biotoul.fr>", MAIL_SERVEUR_INFO, $options) ) {
                                                                                        // [, , , poste, job, categorie, appartenance, , ]
                                                                                            $messWarn[] = "Un message a été envoyé aux assistants de prévention pour les prévenir.";
                                                                                    } else {
                                                                                            $messErr[] = "Le message n'a pas pu être envoyé aux assistants de prévention : veuillez les prévenir par un autre moyen !";
                                                                                    }
                                                                                }
                                                                                if (in_array($id_labo, array(1,2,8))) { // Envoi uniquement si labo LBME, LMGM, CBI, CBD, LBCMCP ou CRCA !
                                                                                    if ( envoyerMail(MAIL_CONTENU_TECHNIK, "Service Technique <technik@ibcg.biotoul.fr>", MAIL_SERVEUR_INFO, $options ) ) {
                                                                                            $messWarn[] = "Un message a été envoyé au service technique pour la distribution éventuelle d'un badge.";
                                                                                    } else {
                                                                                            $messErr[] = "Le message n'a pas pu être envoyé au service technique : veuillez les prévenir par un autre moyen !";
                                                                                    }
                                                                                }
                                                                                if ( envoyerMail(MAIL_CONTENU_RH, "Marie Pelletier <Marie.Pelletier@univ-tlse3.fr>", MAIL_SERVEUR_INFO, $options ) ) {
                                                                                        $messWarn[] = "Un message a été envoyé au gestionnaire RH.";
                                                                                } else {
                                                                                        $messErr[] = "Le message n'a pas pu être envoyé au gestionnaire RH : veuillez la prévenir par un autre moyen !";
                                                                                }
									break;
									case 5: case 7: case 8:	// Revalidation d'un compte suspendu ou supprimé : mail au titulaire
										if (envoyerMail(MAIL_CONTENU_REVALIDATION, $email, MAIL_SERVEUR_INFO, array('prenom'=>$prenom, 'nom'=>$nom, 'date_depart'=>$date_depart)) ) {
											$messWarn[] = "Un message a été envoyé au titulaire du compte.";
										} else {
											$messErr[] = "Le message n'a pas pu être envoyé au titulaire : vérifiez !";
										}
									break;
									case 4: case 6: // Annulation d'une demande de suspension ou suppression : mail au service info
                                                                                if (envoyerMail(MAIL_CONTENU_ANNULE_DEMANDE, MAIL_SERVICE_INFO, $AUTORISATION_MAIL,
                                                                                    array('prenom'=>$prenom, 'nom'=>$nom, 'equipe'=>$infoEquipe['equipe'], 'labo'=>$infoLabo['nom'], 'demande'=>$demande, 'lien_compte'=>$lienCompte))) {
											$messWarn[] = "Un message a été envoyé au service info pour qu'ils sachent que le compte doit rester actif.";
										} else {
											$messErr[] = "Le message n'a pas pu être envoyé au service info : contactez-les !";
										}  
									break;
								}
							break;
							case 5:	// Suspendu : RIEN
							break;
							case 7:	// Supprimé : RIEN
							break;
                                                        case 9: // Créé informatiquement, mais pas de mot de passe saisi... : RIEN
                                                        break;
						}	
					}	// Fin de gestion des envois de mails en cas de changement de statut.
				} else {	// KO
					$messErr[] = "<strong>Echec de mise à jour.</strong>";
				}
			}
		} 
                else {	// Ajout
			$donnees['id_statut'] = ( $mode==MODE_COMPTE ? 1 : 2 );
			$result = sqlInsert('comptes', $donnees);
			if ( (is_int($result)) and ($result>0) ) {	// OK
				$messWarn[] = "<strong>Ajout effectué.</strong>";
				$idvar = $result;
				if ($donnees['id_statut']==1) {	// On a ajouté un compte : Envoi du mail aux administrateurs...
					if ($inscriptionListeSeminaire=="checked") {	// Demande d'abonnement à la liste cbi.int-seminars@univ-tlse3.fr
						$messWarn[] = "Le compte sera abonné à la liste pour la diffusion d'informations sur les séminaires organisés par le CBI.";
					}
					$lienCompte = "https://secure.ibcg.biotoul.fr/gestweb/compte.php?id=".$idvar."&inscriptionListeSeminaire=".$inscriptionListeSeminaire;
					$infoLabo = sqlTrouve('reseau.labos', 'idx='.$id_labo);
					$infoEquipe = sqlTrouve('reseau.equipes', 'idx='.$add_equipe);
					$infoEmploi = sqlTrouve('emploi', 'id_job='.$id_job);
					$infoEmploiCategorie = sqlTrouve('emploi_categorie', 'idx='.$infoEmploi['id_categorie']);	//var_dump($id_appartenance);
					$infoAppartenance = sqlTrouve('appartenance', 'idx='.$id_appartenance);	
                                        $options =  array('valideur'=>getenv('SSL_CLIENT_S_DN_CN'), 'equipe'=>$infoEquipe['equipe'], 'poste'=>$poste, 'date_arrivee'=>$date_arrivee, 
                                                      'date_depart'=>$date_depart, 'appartenance'=>$infoAppartenance['appartenance'], 'labo'=>$infoLabo['nom'], 'lien'=>$lienCompte,
                                                      'job'=>$infoEmploi['job'], 'job_categorie'=>$infoEmploiCategorie['categorie'], 'nom'=>$nom, 'prenom'=>$prenom,
                                                      'annuaire'=>$annuaire);
					if ( envoyerMail(MAIL_CONTENU_DEMANDE_NOUVEAU, MAIL_SERVICE_INFO, $AUTORISATION_MAIL, $options) ) {
						$messWarn[] = "Un message a été envoyé au service info pour qu'ils créent le compte.";
					} else {
						$messErr[] = "Le message n'a pas pu être envoyé au service info : contactez-les !";
					}
				} else {	// Simple ajout à l'annuaire !
					$messWarn[] = "L'entrée d'annuaire a été ajoutée.";
					$MODIFIABLE = ($AUTORISATION==2);	// En cas d'ajout, le formulaire reste modifiable uniquement pour les administrateurs !
				}
			} else {	// KO
				$messErr[] = "<strong>Echec d'enregistrement.</strong>";
			}
		}
                // Gère l'éventuel ajout d'une équipe :
                if ($add_equipe>0) {    // Pour le cas d'un compte juste créé, pas encore lié à une équipe, on ajoute le lien avec l'équipe...
                        $donneesComptesEquipes = array('id_compte'=>$idvar, 'id_equipe'=>$add_equipe, 'quotite'=>100, 'id_responsabilite'=>1);
                        sqlInsert('comptes_equipes', $donneesComptesEquipes);   // Ajoute le lien entre équipe et compte
                        $id_equipe = $add_equipe;                               // Sélectionne l'équipe ajoutée par défaut !
                        $add_equipe = 0;                                        // Vide la liste déroulante d'AJOUT
                        $equipe_quotite = 100;                                  // Indique une quotité de 100 par défaut
                        $equipe_responsabilite = 1;                             // Vide la liste déroulante de modification de responsaibilité
                        $messWarn[] = "L'équipe indiquée a été liée à ce compte, vous pouvez modifier le niveau de responsabilité et la quotité.";
                        $MODIFICATION_EQUIPE = TRUE;           // On permet de modifier les infos si on ne valide pas le formulaire entier
                        // On ajoute l'équipe dans la liste des équipes liées
                        $equipes_liees[$id_equipe] = array('id_equipe'=>$id_equipe, 'quotite'=>100, 'id_responsabilite'=>1);
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
	echo("<div class='impression'><h3> Demande de création d'un compte sur le réseau informatique de l'IBCG <br />");
	echo("<i>(à remettre avec votre signature au service informatique)</i> </h3></div>");
	echo("<div class='ecran'><h1> ".($idvar==0?"Cré":"Modific")."ation d'un".($mode==MODE_COMPTE?" compte":"e entrée d'annuaire")." </h1></div>");
	echo("<form method='post' action='' enctype='multipart/form-data'>");
		echo(champID());
		echo("<table>");
		// Identifiant
			if ($AUTORISATION==2) {	// Affichage uniquement pour administrateurs
				echo("<tr class='ecran'><th> ID </th><td> ");
				if ($idvar>0) {
					echo( $idvar." - Identifiant pour le site cbi-toulouse.fr : " );
					echo( champTexte('id_cbi', 'Identifiant pour le site cbi-toulouse.fr', $id_cbi, 5, $MODIFIABLE) );
					echo("<br />Ne PAS modifier sans accord de webAdminCBI@ibcg.biotoul.fr !");
				} else {
					echo("<i>Nouveau</i>");
				}
				echo(" </td></tr>");
                        }
                        else {   echo(champID('id_cbi', $id_cbi));   }
		// Statut
			$infoStatut = sqlTrouve('comptes_statut', 'idx='.$id_statut);
			echo("<tr class='ecran'><th> Statut </th><td> ");
			$listeStatutsDisponibles = NULL;	// Par défaut, on ne permet pas de modifier le statut !
			if ($idvar>0) {	// Affiche la liste déroulante UNIQUEMENT pour les comptes existants (pas en création)
				$listeStatutsDisponibles = array($id_statut=>$infoStatut['statut']." (statut actuel)");	// En modification, on permet de modifier le statut, valeur par défut pour le conserver
				if ($AUTORISATION==2) {	// Pour administrateur :
					switch ($id_statut) {
						case 1:	// On autorise à valider ssi le login est indiqué
                                                        $listeStatutsDisponibles[2] = "Ce compte ne sera qu'une entrée d'annuaire (corrigez l'alias !)";
							if ($login!="") {	$listeStatutsDisponibles[9] = "Compte créé (il restera la saisie du mot de passe)";
                                                                                $listeStatutsDisponibles[3] = "Compte validé (l'utilisateur a saisi son mot de passe)";                             }
							else {			$listeStatutsDisponibles['DISABLED9'] = "Indiquez le login pour pouvoir signaler que ce compte est créé ou validé";	}
						break;
						case 2:	// On autorise à convertir en compte info ssi le login est indiqué
							if ($login!="") {	$listeStatutsDisponibles[9] = "Compte créé (il restera la saisie du mot de passe)";
                                                                                $listeStatutsDisponibles[3] = "Compte validé (l'utilisateur a saisi son mot de passe)";    	}
							else {			$listeStatutsDisponibles['DISABLED3'] = "Indiquez le login pour pouvoir convertir en compte informatique actif";	}
						case 3:	// Compte actif
							$listeStatutsDisponibles[5] = "Suspendre (temporaire)";
							$listeStatutsDisponibles[7] = "Supprimer (définitif)";
							$listeStatutsDisponibles[2] = "Convertir en entrée d'annuaire";
						break;
						case 4:	// Suspension demandée par un gestionnaire
							$listeStatutsDisponibles[5] = "Suspendre (temporaire)";
							$listeStatutsDisponibles[3] = "Annuler la demande de suspension";
							$listeStatutsDisponibles[2] = "Convertir ce compte en entrée d'annuaire";
						break;
                                                case 5:	case 7: // Compte suspendu ou supprimé
							$listeStatutsDisponibles[8] = "Demander la revalidation";
							$listeStatutsDisponibles[3] = "Revalider";
						break;
						case 8:	// revalidation demandée
							$listeStatutsDisponibles[3] = "Revalider";
						break;
						case 6: // Suppression demandée
							$listeStatutsDisponibles[7] = "Supprimer (définitif)";
							$listeStatutsDisponibles[3] = "Annuler la demande de suppression";
							$listeStatutsDisponibles[2] = "Convertir ce compte en entrée d'annuaire";
						break;
						case 9:	// On autorise à valider ssi le login est indiqué
							if ($login!="") {	$listeStatutsDisponibles[3] = "Compte validé (l'utilisateur a saisi son mot de passe)";	}
							else {			$listeStatutsDisponibles['DISABLED3'] = "Indiquez le login pour pouvoir valider ce compte";	}
						break;
					}
				} elseif ($AUTORISATION==3) {	// Pour gestionnaires :
					switch ($id_statut) {
                                                case 1: case 4:	case 6: case 8: case 9:	// Si une demande est en cours, un gestionnaire ne pourra en fait pas modifier le statut !
							$listeStatutsDisponibles = NULL;
						break;
						case 2:	// Entrée d'annuaire
							$listeStatutsDisponibles[1] = "Demander à créer un compte informatique";
						case 3:	// Compte actif
							$listeStatutsDisponibles[4] = "Demander la suspension (absence temporaire)";
							$listeStatutsDisponibles[6] = "Demander la suppression (départ définitif)";
							$listeStatutsDisponibles[2] = "Convertir en entrée d'annuaire simple";
						break;
						case 5: case 7:	// Compte suspendu ou supprimé
							$listeStatutsDisponibles[8] = "Demander la revalidation (retour effectif de la personne dans l'unité)";
						break;
					}
				}
			}
			if (is_array($listeStatutsDisponibles)) {	// Si la liste des statuts est bien un tableau, on affiche la liste déroulante
				echo( champListe('id_statut', 'Choisissez un statut', (int)$id_statut, 1, $listeStatutsDisponibles, FALSE, $MODIFIABLE) );
			}
                        else {	// Sinon on affiche seulement le statut actuel !
				echo( champID('id_statut'.$id_statut).$infoStatut['statut']." (non modifiable)");
			}
			echo(" </td></tr>");
		// Nom
			echo("<tr><th> Nom </th>");
			echo("<td> ".champTexte('nom', 'Indiquez le nom (obligatoire !)', $nom, 20, $MODIFIABLE)." </td>");
		// Prénom
			echo("<tr><th> Prénom </th>");
			echo("<td> ".champTexte('prenom', 'Indiquez le prénom (obligatoire !)', $prenom, 20, $MODIFIABLE)." </td></tr>");
		// Login
			echo("<tr class='ecran'><th> Login </th><td> ");
			echo( champTexte('login', 'Indiquez le login'.(($id_statut<>2)?' (obligatoire !)':''), $login, 15, (($MODIFIABLE)&&($AUTORISATION==2))) );	// Spécifique : modifiable que pour admin !
			echo(" </td>");
		// Labo
			echo("<tr><th> Laboratoire de rattachement </th><td>");
			$labosTous = sqlSelect('reseau.labos', 'reseau.labos.idx NOT IN (5,7)', 'reseau.labos.nom');
			$listeLabos = array(0=>NON_INDIQUE);
			foreach ($labosTous as $un) {	$listeLabos[$un['idx']] = $un['nom'];	}
			echo( champListe('id_labo', 'Indiquez le laboratoire (obligatoire)', $id_labo, 1, $listeLabos, TRUE, $MODIFIABLE) );
			echo("</td></tr>");
		// Equipe
			echo("<tr><th rowspan='2'> Equipe / Service </th><td>");
                        // On récupère d'abore les équipes déjà liées au compte :
                        $requeteEquipe = 'SELECT comptes_equipes.id_equipe as id_equipe, reseau.equipes.equipe as nom_equipe, reseau.labos.nom as nom_labo,';
                        $requeteEquipe.= ' comptes_equipes.quotite as quotite, responsabilites.responsabilite as responsabilite, responsabilites.idx as id_responsabilite';
                        $requeteEquipe.= ' FROM comptes_equipes LEFT OUTER JOIN reseau.equipes ON reseau.equipes.idx=comptes_equipes.id_equipe';
                        $requeteEquipe.= ' LEFT OUTER JOIN reseau.labos ON reseau.labos.idx=reseau.equipes.id_labo';
                        $requeteEquipe.= ' LEFT OUTER JOIN responsabilites ON responsabilites.idx=comptes_equipes.id_responsabilite';
                        $requeteEquipe.= ' WHERE comptes_equipes.id_compte='.$idvar;
                        $requeteEquipe.= ' ORDER BY reseau.labos.nom, reseau.equipes.equipe ;';
                        $equipesLabos = sqlRequete($requeteEquipe);
                        $listeEquipesLiees = array();
                        if ($MODIFICATION_EQUIPE) { // On permet de modifier la responsabilité et la quotité, mais on affichera que les boutons Détacher et Valider les modifs
                                if (is_array($equipesLabos)) {
                                    foreach ($equipesLabos as $une) {   
                                        $listeEquipesLiees[$une['id_equipe']] = $une;    
                                    }
                                }
                                echo(" Modification de l'équipe : ".$listeEquipesLiees[$id_equipe]['nom_equipe'].champID('id_equipe', $id_equipe) );
                                    $equipe_quotite = $listeEquipesLiees[$id_equipe]['quotite'];
                                    $equipe_responsabilite = $listeEquipesLiees[$id_equipe]['id_responsabilite'];
                                    // Affiche la liste déroulante des responsabilité,
                                    $listeResponsabilites    = array();
                                    foreach (sqlSelect('responsabilites', '', 'idx') as $uneResponsabilite) {  $listeResponsabilites[$uneResponsabilite['idx']] = $uneResponsabilite['responsabilite'];  }
                                    echo("<br />Responsabilite : ".champListe('equipe_responsabilite', 'Indiquez la responsabilité de la personne dans l&acute;équipe', $equipe_responsabilite, 1, $listeResponsabilites, FALSE, $MODIFIABLE) );
                                            // Affiche le champ de quotité (liste déroulante ? curseur ? texte ?)
                                    echo(" | Quotité : ".formNumber('equipe_quotite', 'Indiquez la quotité', $equipe_quotite, 0, 100, 5, $MODIFIABLE) );
                                    echo( "<br />".formBouton('action', ACTION_VALIDER_EQUIPE)."&nbsp;".formBouton('action', ACTION_DETACHER_EQUIPE) );
                                echo('</td></tr>');
                                echo('<tr><td> Ajouter l&acute;équipe : Vous pourrez ajouter une équipe après avoir validé ou détacher l&acute;équipe ci-dessus...');
                                echo(champID('add_equipe', 0) );
                        } else {    // On permet de choisir l'équipe dans la liste ou d'en ajouter une, mais pas de modifier responsabilité et quoité !
                                echo(" Equipes déjà rattachées : ");
                                if (is_array($equipesLabos)) {
                                    foreach ($equipesLabos as $une) {
                                        $listeEquipesLiees[$une['id_equipe']] = $une['nom_equipe'].' ('.$une['nom_labo'].') - '.$une['quotite'].'%'.($une['id_responsabilite']!=1?' ('.$une['responsabilite'].')':'');
                                    }
                                }
                                echo(champID('ex_id_equipe', $id_equipe));  // Permettra de détecter si l'id_equipe a été modifié au réaffichage du formulaire
                                if ($listeEquipesLiees!=array()) { // On affiche les équipes liées que s'il y en a !
                                    echo(champListe('id_equipe', 'Indiquez l&acute;équipe à modifier', $id_equipe, 1, $listeEquipesLiees, FALSE, $MODIFIABLE) );
                                    echo( "<br />".formBouton('action', ACTION_MODIFIER_EQUIPE)."&nbsp;".formBouton('action', ACTION_DETACHER_EQUIPE) );
                                } else {    // Pas d'équipe liée, on garde juste un id_equipe
                                    echo(" aucune".champID('id_equipe', $id_equipe));
                                }
                                echo('</td></tr>');
                                echo('<tr><td> Ajouter l&acute;équipe : ');
                                // On affiche ensuite les équipes PAS liées au compte, pour proposer de les ajouter
                                $requeteEquipe = 'SELECT reseau.equipes.idx as id_equipe, reseau.equipes.equipe as nom_equipe, reseau.labos.nom as nom_labo';
                                $requeteEquipe.= ' FROM reseau.equipes LEFT OUTER JOIN reseau.labos ON reseau.labos.idx=reseau.equipes.id_labo';
                                if ($listeEquipesLiees!=array()) {
                                    $requeteEquipe.= ' WHERE reseau.equipes.idx not in ('.implode(',', array_keys($listeEquipesLiees)).')';
                                }
                                $requeteEquipe.= ' ORDER BY reseau.labos.nom, reseau.equipes.equipe ;';
                                $equipesLabos = sqlRequete($requeteEquipe);
                                $listeEquipes = array('0'=>NON_INDIQUE);
                                $ex_labo = "";
                                foreach ($equipesLabos as $une) {
                                        if ($ex_labo!==$une['nom_labo']) {
                                            $listeEquipes['DISABLED'.$une['nom_labo']] = '---'.(is_null($une['nom_labo'])?"Equipes disparues":$une['nom_labo']);
                                            $ex_labo = $une['nom_labo'];
                                        }
                                        if (!array_key_exists($une['id_equipe'], $listeEquipesLiees)) {
                                            $listeEquipes[$une['id_equipe']] = $une['nom_equipe'];
                                        }
                                }
                                echo(champListe('add_equipe', 'Indiquez l&acute;équipe à ajouter', $add_equipe, 1, $listeEquipes, FALSE, $MODIFIABLE) );
                                            // Affiche le bouton pour valider l'AJOUT de l'équipe
                                echo(formBouton('action', ACTION_AJOUTER_EQUIPE));
                            
                        }
			echo(" </td></tr>");
		// Telephone
			echo("<tr><th> Poste téléphonique</th>");
			echo("<td> ".champTexte('poste', 'Indiquez le numéro de téléphone', $poste, 20, $MODIFIABLE)."  <i class='ecran'>format XX.XX</i></td></tr>");
		// Lieu
			echo("<tr><th> Localisation </th>");
			echo("<td> ".champTexte('lieu', 'Indiquez le lieu de travail', $lieu, 20, $MODIFIABLE)." </td></tr>");
		// Nouveau membre
			echo("<tr class='ecran'><th> Nouveau membre (Si LMGM !) </th>");
			echo("<td> ".creerCaseCochee('nvx_membres', 'checked', $nvx_membres, $MODIFIABLE, FALSE)." </td></tr>");
		// Fonction
			echo("<tr><th> Fonction ou Type de stage </th>");
			$requeteEmplois = 'SELECT emploi.id_job as id_job, emploi.id_categorie as id_categorie, emploi.job as job,';
			$requeteEmplois.= ' emploi.temporaire as temporaire, emploi_categorie.categorie as categorie';
			$requeteEmplois.= ' FROM emploi LEFT OUTER JOIN emploi_categorie ON emploi_categorie.idx=emploi.id_categorie';
			$requeteEmplois.= ' ORDER BY emploi.id_categorie, emploi.job';
			$tousEmplois = sqlRequete($requeteEmplois);
			$tempCategorie = '';
			$listeEmplois = array('0'=>'(choisissez dans la liste)');
			if (is_array($tousEmplois)) {
				foreach ($tousEmplois as $une) {
					if ($tempCategorie<>$une['categorie']) {
						$listeEmplois['DISABLED'.$une['categorie']] = "- ".$une['categorie']." -";
						$tempCategorie = $une['categorie'];
					}
					$listeEmplois[$une['id_job']] = $une['job'].($une['temporaire']==0?"":" (Temporaire)")."";
				}
			}
			echo("<td> ".champListe('id_job', 'Indiquez l&acute;emploi ou stage', $id_job, 1, $listeEmplois, FALSE, $MODIFIABLE)." </td></tr>");
		// Appartenance
			echo("<tr><th> Appartenance </th>");
			$tousAppartenances = sqlSelect('appartenance');
			$listeAppartenances = array('0'=>'(choisissez dans la liste)');
			if (is_array($tousAppartenances)) {
				foreach ($tousAppartenances as $une) {
					$listeAppartenances[$une['idx']] = $une['appartenance'];
				}
			}
			echo("<td> ".champListe('id_appartenance', 'Indiquez l&acute;appartenance', $id_appartenance, 1, $listeAppartenances, FALSE, $MODIFIABLE)." </td></tr>");
		// Date d'arrivée
			echo("<tr><th> Date d'arrivée<br /><em class='ecran'>Obligatoire</em> </th>");
			echo("<td> ". champDate('date_arrivee', 'Date d&acute;arrivée', $date_arrivee, $MODIFIABLE)." </td></tr>");
		// Date de départ
			echo("<tr><th> Date de départ<br /><em class='ecran'>Obligatoire pour les stagiaires et CDD</em> </th>");
			echo("<td> ".champDate('date_depart', 'Date de départ', ( $date_depart=="" ? "" : $date_depart ), $MODIFIABLE));
			echo(" </td></tr>");
		// Mail	
			echo("<tr><th> E-mail Professionnelle </th><td>");
			echo("<div class='impression'>Cette adresse email sera celle affichée sur l'annuaire.</div>");
			echo( champTexte('email', 'Indiquez le mail', $email, 40, $MODIFIABLE) );
			echo(" </td></tr>");
		// Mail	
			echo("<tr><th> E-mail Personnelle </th><td>");
			echo("<div class='impression'>Pour les personnes restant moins de 3 mois au laboratoire, les messages envoyés à l'adresse professionnelle seront transférés directement sur cette adresse.");
			echo("Le webmail de l'IBCG ne sera pas utilisable. Important : cette adresse personnelle ne sera pas affichée sur les sites internet professionnels, ni sur l'annuaire IBCG.</div>");
			echo( champTexte('email_perso', 'Indiquez le mail', $email_perso, 40, $MODIFIABLE) );
			echo(" </td></tr>");
		// Parution annuaire ?
			echo("<tr class='ecran'><th> Parution sur l'annuaire de l'IBCG </th><td>");
			if ($id_statut==2) {	// Obligatoire pour les entrées d'annuaire !
				echo( champID('annuaire', "checked")."OUI");
			} else {
				echo( creerCaseCochee('annuaire', 'checked', $annuaire, $MODIFIABLE, FALSE) );
			}
			echo(" </td></tr>");
		// Abonnements aux liste de diffusion
			echo("<tr class='ecran'><th> Abonnements aux listes de diffusion </th><td>");
			if ($id_statut==1) {	// Proposer une case à cocher si le compte est à créer
				echo( creerCaseCochee('inscriptionListeSeminaire', 'checked', $inscriptionListeSeminaire, $MODIFIABLE, FALSE) );
			} else {    // Proposer des boutons d'inscription et désinscription sinon
				echo( champID('inscriptionListeSeminaire', "") );
                                echo( formBouton('action', MAIL_SEMINAIRE_INSCRIPTION)."&nbsp;".formBouton('action', MAIL_SEMINAIRE_DESINSCRIPTION) );
			}
			echo(" </td></tr>");
		// Boutons
			echo("<tr><th colspan='2' class='ecran'> ");
                        if (!$MODIFICATION_EQUIPE) {    // On n'affiche les boutons de validation du formulaire et d'impression que si on n'est pas en train de modifier responsabilité et quotité d'une équipe...
                            if ($MODIFIABLE) {	// Formulaire modifiable, permet de valider
                                    echo( creerFormBouton('action', ACTION_VALIDER) );
                            } elseif ( ($idvar>0) && ($id_statut==1) ) {	// Demande de création en cours pour un compte existant : ajoute les infos pour imprimer !!
                                    echo( "<h4>Imprimez ce formulaire, remettez-le au nouvel arrivant qui doit l'apporter signé au service informatique</h4>" );
                            }
                            echo("<input class='ecran' type='button' value='Imprimer' onClick='window.print()'> </th></tr></table>");
                        } else {
                            echo("Vous devez valier la modification d'équipe !");
                        }
                        echo("<span class='impression'>");
			echo("<strong> Informations importantes </strong>");
			echo("<pVous allez devoir saisir le mot de passe de votre compte. Il doit respecter les règles suivantes :</p>");
			echo("<ul><li>Avoir au moins 12 caractères,</li>");
			echo("<li>Comporter au moins 3 alphabets sur les 4 (minuscules, majuscules, chiffres, caractères spéciaux),</li>");
			echo("<li>Ne pas comporter de lettres accentuées, ni vos nom, prénom, ou de mot pouvant être présent dans un dictionnaire.</li></ul>");
			echo("<p>Le choix d'un mot de passe trop facile à trouver mettrait en péril la sécurité de l'ensemble du réseau informatique.</p>");
			echo("<p><strong>PS IMPORTANT</strong> : Le titulaire du compte et son responsable s'engagent auprès du service informatique à en faciliter la");
			echo(" fermeture lors de son départ du laboratoire (libération de l'espace disque sur les serveurs, nettoyage de la messagerie&hellip; etc&hellip;)</p>");
			echo("<p>Je déclare avoir pris connaissance de la <u>charte informatique</u> du CNRS.</p>");
                        echo("<p class='encadrement'>");
                        echo("Signature précédée de la mention &quot;lu et approuvé&quot; :<br />Le ___ / ___ / _____ &nbsp; <br /></p>");
			echo("</span>");
		echo("</form>");
	echo("</div>");
}

require_once('pied.inc.php');
?>
