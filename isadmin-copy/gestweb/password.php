<?php
require_once('entete_nohtml.inc.php');
require_once('menu.inc.php');
require_once('../function_check_password.inc.php');
require_once('../function_ssh.inc.php');
require_once('../function_ldap.inc.php');
define('ACTION_VALIDER', "Valider");
define('ACTION_RETOUR_FORMULAIRE', "Retour au formulaire");
require_once('../function_mail.inc.php');
#
# On commence par jeter l'utilisateur s'il n'a pas le droit d'afficher cette page !
#
$messErr = array();
$messWarn = array();
// Si en modification d'un compte, modifie valeurs par défaut !
$idvar = (int)getVar('id', 0);	// ($idvar==0) -> Ajout, sinon modification !
if ($idvar>0) {	// On vérifie que l'id_compte indiqué correspond à un vrai compte (pas une entrée d'annuaire) et qu'il a un login...
	$infoCompte = sqlTrouve('comptes', 'id='.$idvar);
	if (is_array($infoCompte)) {	// Modifie les valeurs par défaut si le compte existe
                if ($infoCompte['id_statut']==2) {
                    $idvar = 0;
                    $messErr[] = "L'id de compte indiqué est une simple entrée d'annuaire !";
                }
                if ($infoCompte['login']=="") {
                    $idvar = 0;
                    $messErr[] = "L'id de compte indiqué n'a pas de login !";
                }
	} else {	// Supprime l'id si le compte n'existe pas
		$idvar = 0;
                $messErr[] = "L'id de compte indiqué n'existe pas dans la base !";
	}
}
$AFFICHER_FORMULAIRE = ( ($AUTORISATION==2) and ($idvar>0) );	// Si VRAI, affichera le formulaire
$login =                    $infoCompte["login"];	
if ($idvar>0) {     // Cette fois, on sait que $idvar correspond a un compte dont on peut changer le mot de passe !!!
    // Vérifie si le compte existe bien dans l'annuaire LDAP :
    $infoLDAP = ldapTrouveUtilisateur($login);
    if (is_null($infoLDAP)) {
            $messErr[] = "Le login n'existe pas dans l'annuaire, vérifiez !";
            $AFFICHER_FORMULAIRE = FALSE;
    }
    elseif ($infoLDAP===FALSE) {
            $messErr[] = "L'annuaire LDAP n'est pas disponible.";
            $AFFICHER_FORMULAIRE = FALSE;
            break;
    }
    // On a les infos dans $infoCompte :
                // $login = $infoCompte['login'];
                // $email = $infoCompte['email'];
    // On récupère aussi les infos des équipes liées !
    $equipes_liees = array();
    $infoEquipes = sqlSelect('comptes_equipes', 'id_compte='.$idvar);
    if (is_array($infoEquipes)) {
        foreach ($infoEquipes as $uneEquipe) {
            $equipes_liees[$uneEquipe['id_equipe']] = $uneEquipe;
        }
    }
} else {
    $AFFICHER_FORMULAIRE = FALSE;
}
if ($AFFICHER_FORMULAIRE) {	// Récupère les valeurs du formulaire !
	$id_statut =                (int)$infoCompte['id_statut'];   // Le statut est important car s'il est égal à 9, il sera modifié en 3 avec envoi d'un mail 
	$nom =                      html_entity_decode($infoCompte["nom"]);
	$prenom =                   html_entity_decode($infoCompte["prenom"]);
	$email =                    str2lower(trim($infoCompte['email']));
	$email_perso =              str2lower(trim($infoCompte['email_perso']));
	$mail_demandeur =           str2lower($infoCompte['mail_demandeur']);
        $password =                 trim(getVar('password', VIDE));
        $password2 =                trim(getVar('password2', VIDE));
// Effectue les actions simples !
	$action = getVar('action', "");
        if ($action==ACTION_RETOUR_FORMULAIRE) {    // Si on demande à modifier le mot de passe du compte...
            header('Location: '.$REDIRIGER_VERS_PAGE);      
            exit();
        }
// Vérifie et corrige les valeurs du formulaire
	if ($action==ACTION_VALIDER) {	// Tout d'abord, on fait uniquement les contrôles et corrections
                $solidPassword = checkMotPasse($password, $password2, $messErr, array($nom,$prenom,$login));
		if (!$solidPassword) {    $action = "";	}	// On annule l'enregistrement s'il y a des erreurs...
	}
// Enregistrement des données, les contrôles sont faits !
	if ($action==ACTION_VALIDER) {
            // Changer le mot de passe : $password2 a été corrigé par la fonction checkMotPasse !
                //$commande = '/bin/echo -e "'.$password2.'\n'.$password2.'" | /usr/sbin/smbldap-passwd '.$login;
                $commande = 'samba-tool user setpassword '.$login.' --newpassword='.$password2;
                $retourSSH = sshCommande($commande);    var_dump($retourSSH);
                if (strpos($retourSSH['out'], "Unable to find user")) {   // L'utilisateur n'existe pas !!!
                        $messErr[] = "Le compte $login n'existe pas dans l'annuaire AD, contactez le service informatique...";
                }
                elseif (strpos($retourSSH['out'], "Changed password OK")) {   // Le mot de passe a été changé !
                        $messWarn[] = "Le mot de passe a été changé dans l'annuaire AD";
                        if (ldapAuthentifieUtilisateur($login,$password)) {    // VÃ©rifie si le mot de passe a pu Ãªtre changÃ© :
                                if (in_array($id_statut, array(1,9))) {    // Le compte était en attente de validation, on change le statut pour 3 et on envoie les mails !!!!! !
                                        sqlUpdate('comptes', 'id='.$idvar, array('id_statut'=>3));
                                        $messWarn[] = "Le compte a été validé automatiquement.";
                                        // Récupération des données dans de compte dans des variables, pour simpliier le code (qui ressemble à s'y méprendre à ce qui est dans compte.php)
                                        $id_labo = $infoCompte['id_labo'];
                                        $infoLabo = sqlTrouve('reseau.labos', 'idx='.$id_labo);
                                        $infoEmploi = sqlTrouve('emploi', 'id_job='.$infoCompte['id_job']);
                                        $infoEmploiCategorie = sqlTrouve('emploi_categorie', 'idx='.$infoEmploi['id_categorie']);	//var_dump($id_appartenance);
                                        $infoAppartenance = sqlTrouve('appartenance', 'idx='.$infoCompte['id_appartenance']);
                                        $poste = $infoCompte['poste'];
                                        $date_arrivee = $infoCompte['date_arrivee'];
                                        $date_depart = $infoCompte['date_depart'];
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
                                        if (in_array($id_labo, array(1,2,8))) { // Envoi uniquement si labo LBME, LMGM ou CBI !
                                            if ( envoyerMail(MAIL_CONTENU_AP, "Assistants de Prevention <ibcg.ap@ibcg.biotoul.fr>", MAIL_SERVEUR_INFO,
                                            array('prenom'=>$prenom, 'nom'=>$nom, 'date_arrivee'=>$date_arrivee, 'date_depart'=>$date_depart, 'equipe'=>$infoEquipe['equipe'], 'labo'=>$infoLabo['nom'],
                                                'poste'=>$poste, 'job'=> $infoEmploi['job'], 'categorie'=>$infoEmploiCategorie['categorie'], 'appartenance'=>$infoAppartenance['appartenance']) ) ) {
                                                // [, , , poste, job, categorie, appartenance, , ]
                                                    $messWarn[] = "Un message a été envoyé aux assistants de prévention pour les prévenir.";
                                            } else {
                                                    $messErr[] = "Le message n'a pas pu être envoyé aux assistants de prévention : veuillez les prévenir par un autre moyen !";
                                            }
                                        }
                                        if (in_array($id_labo, array(1,2,8))) { // Envoi uniquement si labo LBME, LMGM, CBI, CBD, LBCMCP ou CRCA !
                                            if ( envoyerMail(MAIL_CONTENU_TECHNIK, "Service Technique <technik@ibcg.biotoul.fr>", MAIL_SERVEUR_INFO,
                                            array('prenom'=>$prenom, 'nom'=>$nom, 'date_arrivee'=>$date_arrivee, 'date_depart'=>$date_depart, 'equipe'=>$infoEquipe['equipe'], 'labo'=>$infoLabo['nom'],
                                                'poste'=>$poste, 'job'=> $infoEmploi['job'], 'categorie'=>$infoEmploiCategorie['categorie'], 'appartenance'=>$infoAppartenance['appartenance']) ) ) {
                                                    $messWarn[] = "Un message a été envoyé au service technique pour la distribution éventuelle d'un badge.";
                                            } else {
                                                    $messErr[] = "Le message n'a pas pu être envoyé au service technique : veuillez les prévenir par un autre moyen !";
                                            }
                                        }
                                        if ( envoyerMail(MAIL_CONTENU_RH, "Marie Pelletier <Marie.Pelletier@univ-tlse3.fr>", MAIL_SERVEUR_INFO,
                                            array('prenom'=>$prenom, 'nom'=>$nom, 'date_arrivee'=>$date_arrivee, 'date_depart'=>$date_depart, 'equipe'=>$infoEquipe['equipe'], 'labo'=>$infoLabo['nom'],
                                                'poste'=>$poste, 'job'=> $infoEmploi['job'], 'categorie'=>$infoEmploiCategorie['categorie'], 'appartenance'=>$infoAppartenance['appartenance']) ) ) {
                                                $messWarn[] = "Un message a été envoyé au gestionnaire RH.";
                                        } else {
                                                $messErr[] = "Le message n'a pas pu être envoyé au gestionnaire RH : veuillez la prévenir par un autre moyen !";
                                        }
                                }
                                // Mail info changement mot de passe
                                if (envoyerMail(MAIL_CONTENU_CHANGE_PASSWORD_SINFO, MAIL_SERVEUR_INFO, MAIL_SERVEUR_INFO, array('nom'=>$nom, 'prenom'=>$prenom, 'login'=>$login))) {
                                        $messWarn[] = "Un message d'information a été envoyé au responsable d'équipe et au gestionnaire.";
                                } else {
                                        $messWarn[] = "Le message d'information n'a pas pu être envoyé au responsable d'équipe et au gestionnaire !";
                                }
                        } else {	// Le nouveau mot de passe n'a pas pu Ãªtre changÃ©, explique pourquoi et quoi faire.
                                $messageErreur[] = "Le nouveau mot de passe a été refusé par le serveur<br /><strong>Essayez-en un autre</strong> !";
                        }
                }
                else {	// Erreur SSH
                        $messErr[] = $retourSSH['erreur']." (".$retourSSH['out'].")";
                }
	}
}
// ZERO HTML Avant ce point !!!!
$TITRE_PAGE = "Changement de mot de passe";
require_once('entete_html.inc.php');
echo('<div class="content">');
// Afficher les avertissements et erreurs :
echo("<div id='notifications' class='ecran'>");
if (count($messErr)>0) {
	echo("<div class='ouvert notification rouge'>");
	echo("<h2>Erreurs bloquantes :</h2><ul>");
	foreach ($messErr as $une) {	echo("<li>".$une."</li>");	}
	echo("<li><strong>Le mot de passe n'a pas été modifié</strong></li>");
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
	echo("<div class='boite'>");
	echo("<div class='ecran'><h1> ".$TITRE_PAGE." </h1></div>");
	echo("<form method='post' action='' enctype='multipart/form-data'>");
		echo(champID());
		echo("<table>");
		// Statut
			$infoStatut = sqlTrouve('comptes_statut', 'idx='.$id_statut);
			echo("<tr class='ecran'><th> Statut </th><td> ".$infoStatut['statut']." </td></tr>");
		// Nom
			echo("<tr><th> Nom </th><td> ".$nom." </td>");
		// Prénom
			echo("<tr><th> Prénom </th><td> ".$prenom." </td></tr>");
		// Login
			echo("<tr class='ecran'><th> Login </th><td> ".$login." </td>");
		// Mail	
			echo("<tr><th> E-mail Professionnelle </th><td>".$email." </td></tr>");
		// Mail	Perso
			echo("<tr><th> E-mail Personnelle </th><td>".$email_perso." </td></tr>");
                // Password
			echo("<tr><th> Mot de passe </th><td> ". formPasse('password', "Indiquez le mot de passe", 20, TRUE) ." </td>");
                // Password confirmé
			echo("<tr><th> Confirmation du mot de passe </th><td> ". formPasse('password2', "Confirmez le mot de passe", 20, TRUE) ." </td>");
		// Boutons
			echo("<tr><th colspan='2' class='ecran'> ");
                        echo( creerFormBouton('action', ACTION_VALIDER) );
		echo("</form>");//	var_dump($_REQUEST);
	echo("</div>");
}

require_once('pied.inc.php');
?>
