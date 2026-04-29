<?php
define('MAIL_CONTENU_SUPPRESSION', "Suppression");
define('MAIL_CONTENU_DEMANDE_SUPPRESSION', "Demande Suppression");
define('MAIL_SEMINAIRE_INSCRIPTION', "Inscription Seminaire");
define('MAIL_SEMINAIRE_DESINSCRIPTION', "Desinscription Seminaire");
define('MAIL_CONTENU_VALIDATION', "Validation");
define('MAIL_CONTENU_REVALIDATION', "Demande Revalidation");
define('MAIL_CONTENU_ANNULE_DEMANDE', "Annulation Demande");
define('MAIL_CONTENU_AP', "Info AP");
define('MAIL_CONTENU_TECHNIK', "Info Technik");
define('MAIL_CONTENU_RH', "Info RH");
define('MAIL_CONTENU_CHANGE_PASSWORD_SINFO', "Mot de Passe Change par Sinfo");
define('MAIL_CONTENU_CHANGE_PASSWORD_USER', "Mot de Passe Change par User");
define('MAIL_CONTENU_DEMANDE_NOUVEAU', "Demande Nouveau Compte");

/*
 * Cette fonction retourne TRUE si le mail a été envoyé, FALSE dans le cas contraire
 */


function envoyerMail($contenu=NULL, $mailDestinataire=NULL, $mailExpediteur=NULL, $option=NULL) {
    global $AUTORISATION_MAIL;
    $mailSubject = "";
    $message = "";
    switch ($contenu) {
        case MAIL_CONTENU_SUPPRESSION : // Options = [prenom, nom, equipe, labo]
                $message = "Le compte de ".$option['prenom']." ".$option['nom']." (équipe ".$option['equipe'].", du laboratoire ".$option['labo'].")";
                $message.= "vient d'être supprimé pour ne rester que dans l'annuaire.\r\n\r\n\r\n";
                $message.= "Merci de supprimer le compte correspondant, il restera uniquement dans l'annuaire IBCG.\r\n\r\n";
                $message.= "Veillez à maintenir une redirection vers son adresse mail personnelle.\r\n\r\n";
                $mailSubject =	"[GestWEB] Suppression d'un compte (reste dans l'annuaire)";
            break;
        case MAIL_CONTENU_DEMANDE_SUPPRESSION : // Options = [prenom, nom, equipe, labo, demande, date_depart, lien_compte]
                $message = "Une demande de <strong>".$option['demande']."</strong> de compte vient d'être faite par ".getenv('SSL_CLIENT_S_DN_CN')." !\r\n";
                $message.= "Il s'agit de ".$option['prenom']." ".$option['nom']." (équipe ".$option['equipe'].", du laboratoire ".$option['labo']."),\r\n";
                $message.= "La suppression doit prendre effet à partir de ".dateConv($option['date_depart'], DATE_FORMAT_HUMAIN).".\r\n\r\n\r\n";
                $message.= "Validez la demande par ce lien : <a href='".$option['lien_compte']."'>".$option['lien_compte']."</a>\r\n\r\n";
                $mailSubject =	"[GestWEB] Demande de ".$option['demande']." de compte";
            break;
        case MAIL_CONTENU_DEMANDE_REVALIDATION :  // Options = [prenom, nom, equipe, labo, lien_compte]
                $message = "Une demande de <strong>revalidation</strong> de compte vient d'être faite par ".getenv('SSL_CLIENT_S_DN_CN')." !\r\n";
                $message.= "Il s'agit de ".$option['prenom']." ".$option['nom']." (équipe ".$option['equipe'].", du laboratoire ".$option['labo']."),\r\n\r\n\r\n";
                $message.= "Validez la demande par ce lien : <a href='".$option['lien_compte']."'>".$option['lien_compte']."</a>\r\n\r\n";
                $mailSubject =	"[GestWEB] Demande de revalidation de compte";
            break;
        case MAIL_CONTENU_REVALIDATION :    // Options : [prenom, nom, date_depart]
                $message = "Bonjour ".$option['prenom']." ".$option['nom'].",\r\n\r\n";
                $message.= "Vous recevez ce message automatique car votre compte vient d'être <strong>re</strong>validé par le service informatique.\r\n\r\n";
                $message.= "Votre compte restera actif ";
                $message.= ( $option['date_depart']==""?"sans limite de durée":"jusqu'au ".dateConv($option['date_depart'], DATE_FORMAT_HUMAIN)).".\r\n";
                $message.= "Il vous appartient de nous signaler ".( $option['date_depart']==""?"votre date de départ":"toute prolongation de contrat").".\r\n\r\n";
                $mailSubject =	"[GestWEB] Bienvenu a l'IBCG (encore !)";
            break;
        case MAIL_CONTENU_DEMANDE_NOUVEAU :  // array('valideur'=>getenv('SSL_CLIENT_S_DN_CN'), 'equipe'=>$infoEquipe['equipe'], 'poste'=>$poste, 'date_arrivee'=>$date_arrivee, 
                                            //      'date_depart'=>$date_depart, 'appartenance'=>$infoAppartenance['appartenance'], 'labo'=>$infoLabo['nom'], 'lien'=>$lienCompte,
                                            //      'job'=>$infoEmploi['job'], 'job_categorie'=>$infoEmploiCategorie['categorie'], 'nom'=>$nom, 'prenom'=>$prenom,)
                $message = "Une demande de compte vient d'être validée par ".$option['valideur']." !\r\n";
                $message.= "Il s'agit de ".$option['prenom']." ".$option['nom'].",\r\n";
                $message.= "qui fait partie de l'équipe ".$option['equipe'].", du laboratoire ".$option['labo'].",\r\n";
                $message.= "qui sera joignable au poste ".$option['poste']." et être ".$option['job']." (".$option['job_categorie'];
                $message.= "), dépendant de ".$option['appartenance'].".\r\n";
                $message.= "Son compte devrait être actif à partir du ".dateConv($option['date_arrivee'], DATE_FORMAT_HUMAIN).", ";
                $message.= ( $option['date_depart']==""?"sans limite de durée":"jusqu'au ".dateConv($option['date_depart'], DATE_FORMAT_HUMAIN)).".\r\n\r\n\r\n";
                $message.= "Merci de créer le compte correspondant, il ";
                $message.= ($option['annuaire']=="checked"?"sera ajouté à l'annuaire IBCG après validation.":"ne sera pas ajouté à l'annuaire IBCG").".\r\n\r\n";
                $message.= "Validez la demande par ce lien : <a href='".$option['lien']."'>".$option['lien']."</a>\r\n\r\n";
                $mailExpediteur = "<".$AUTORISATION_MAIL.">";
                $mailDestinataire = "Service Informatique IBCG <sinfo@ibcg.biotoul.fr>";
                $mailEntete = 'MIME-Version: 1.0'."\r\n";
                $mailEntete.= 'Content-type: text/html; charset=utf-8'."\r\n";
                $mailEntete.= "From: ".$mailExpediteur."\r\n";
                $mailEntete.= "Cc: ".$mailExpediteur."\r\n";
                $mailEntete.= "Reply-to: ".$mailExpediteur."\r\n";
                $mailSubject =	"[GestWEB] Demande de nouveau compte";
            break;
        case MAIL_CONTENU_VALIDATION : // Options = [prenom, nom, date_arrivee, date_depart, inscription=>BOOL]
                $message = "Bonjour ".$option['prenom']." ".$option['nom'].",\r\n\r\n";
                $message.= "Vous recevez ce message automatique car votre compte vient d'être validé par le service informatique.\r\n";
                $message.= "Quelques informations utiles :.\r\n";
                $message.= "- Pour tout problème lié à l'informatique, vous pouvez envoyer un message à <a href='mailto:sinfo@ibcg.biotoul.fr'>sinfo@ibcg.biotoul.fr</a> .\r\n";
                $message.= "- L'intranet de l'IBCG (<a href='https://admin.core-cloud.net/ou/FR3743/IBCG/SitePages/Accueil.aspx'>https://admin.core-cloud.net/ou/FR3743/IBCG/SitePages/Accueil.aspx</a>) regroupe un ensemble d'informations et de liens qui peuvent répondre à certaines de vos questions.\r\n";
                $message.= "- Merci de ne pas surcharger les disques réseaux (Notamment les fichiers personnels y sont prohibés, vous ne devez les stocker que sur les disques locaux des ordinateurs de bureau, sous réserve de ne pas perturber le fonctionnement des ordinateurs).
.\r\n";
                $message.= "- L'utilisation de votre compte informatique relève de votre responsabilité : ne donnez jamais votre mot de passe à quiconque.\r\n\r\n";
                $message.= "Votre gestionnaire reste votre contact privilégié pour toute modification sur l'annuaire.\r\n\r\n";
                $message.= "Votre compte sera actif du ".dateConv($option['date_arrivee'], DATE_FORMAT_HUMAIN)." ";
                $message.= ( $option['date_depart']==""?", sans limite de durée":"au ".dateConv($option['date_depart'], DATE_FORMAT_HUMAIN)).".\r\n";
                $message.= "Il vous appartient de nous signaler ".( $option['date_depart']==""?"votre date de départ":"toute prolongation de contrat").".\r\n\r\n";
                if ($option['inscription']) {	// Vérification si l'inscription est faite
                        $message.= "Vous avez été abonné à une liste pour la diffusion d'informations sur les séminaires organisés par le CBI.";
                        $message.= " Vous pouvez vous désabonner en cliquant sur ce lien :\r\n";
                        $message.= " <a href='mailto:sympa@univ-tlse3.fr?subject=UNSUBSCRIBE%20cbi.int-seminars%20".$option['prenom']."%20".$option['nom']."'>";
                        $message.= " mailto:sympa@univ-tlse3.fr?subject=UNSUBSCRIBE%20cbi.int-seminars%20".$option['prenom']."%20".$option['nom']."</a>\r\n";
                } else {	// Pas d'inscription à la liste, indique le lien pour s'abonner
                        $message.= "Il existe une liste pour la diffusion d'informations sur les séminaires organisés par le CBI.";
                        $message.= " Vous pouvez vous y abonner en cliquant sur ce lien :\r\n";
                        $message.= " <a href='mailto:sympa@univ-tlse3.fr?subject=SUBSCRIBE%20cbi.int-seminars%20".$option['prenom']."%20".$option['nom']."'>";
                        $message.= " mailto:sympa@univ-tlse3.fr?subject=SUBSCRIBE%20cbi.int-seminars%20".$option['prenom']."%20".$option['nom']."</a>\r\n";
                }
                $message.= "<hr />Hello ".$option['prenom']." ".$option['nom'].",\r\n\r\n";
                $message.= "You receive this automatic mail because your account has been validated by IT Service.\r\n";
                $message.= "Few useful informations :.\r\n";
                $message.= "- In case of problems with computers or network, send an email to <a href='mailto:sinfo@ibcg.biotoul.fr'>sinfo@ibcg.biotoul.fr</a> .\r\n";
                $message.= "- You will find many informations and links on intranet of IBCG (<a href='https://admin.core-cloud.net/ou/FR3743/IBCG/SitePages/Accueil.aspx'>https://admin.core-cloud.net/ou/FR3743/IBCG/SitePages/Accueil.aspx</a>) which can give you answers to your questions.\r\n";
                $message.= "- Be careful using network storage (Private files are prohibited, you must store them only on local disk of computers, and only if there is sufficient space on their disks).\r\n";
                $message.= "- You are responsible of usage of your account : don't give your password to anyone.\r\n\r\n";
                $message.= "Administration Service is your contact for any modification in the directory.\r\n\r\n";
                $message.= "Your account will be active since ".dateConv($option['date_arrivee'], DATE_FORMAT_HUMAIN)." ";
                $message.= ( $option['date_depart']==""?", with no limit of time":"until ".dateConv($option['date_depart'], DATE_FORMAT_HUMAIN)).".\r\n";
                $message.= "You must inform us for ".( $option['date_depart']==""?"the end date":"any modification")." of your contract.\r\n\r\n";
                if ($option['inscription']) {	// Vérification si l'inscription est faite
                        $message.= "You will receive informations about seminars organised by CBI.";
                        $message.= " You can unsubscribe to this list using this link :\r\n";
                        $message.= " <a href='mailto:sympa@univ-tlse3.fr?subject=UNSUBSCRIBE%20cbi.int-seminars%20".$option['prenom']."%20".$option['nom']."'>";
                        $message.= " mailto:sympa@univ-tlse3.fr?subject=UNSUBSCRIBE%20cbi.int-seminars%20".$option['prenom']."%20".$option['nom']."</a>\r\n";
                } else {	// Pas d'inscription à la liste, indique le lien pour s'abonner
                        $message.= "There is a list to receive informations about seminars organised by CBI.";
                        $message.= " You can subscribe to this list using this link :\r\n";
                        $message.= " <a href='mailto:sympa@univ-tlse3.fr?subject=SUBSCRIBE%20cbi.int-seminars%20".$option['prenom']."%20".$option['nom']."'>";
                        $message.= " mailto:sympa@univ-tlse3.fr?subject=SUBSCRIBE%20cbi.int-seminars%20".$option['prenom']."%20".$option['nom']."</a>\r\n";
                }
                $mailSubject =	"[GestWEB] Bienvenue a l'IBCG - Welcome to IBCG";
            break;
        case MAIL_CONTENU_ANNULE_DEMANDE :    // Options = [prenom, nom, equipe, labo, demande, lien_compte]
                $message = "Une demande de ".$option['demande']." de compte vient d'être <strong>annulée</strong> par ".getenv('SSL_CLIENT_S_DN_CN')." !\r\n"
                    . "Il s'agit de ".$option['prenom']." ".$option['nom'].",\r\n"
                    . "qui fait partie de l'équipe ".$option['equipe'].", du laboratoire ".$option['labo'].",\r\n"
                    . "Consultez le compte par ce lien : <a href='".$option['lien_compte']."'>".$option['lien_compte']."</a>\r\n\r\n";
                $mailSubject =	"[GestWEB] Demande de ".$option['demande']." de compte";
            break;
        case MAIL_SEMINAIRE_INSCRIPTION :  // Options = [prenom, nom]
                $mailSubject =	"SUBSCRIBE cbi.int-seminars ".$option['prenom']." ".$option['nom'];
            break;
        case MAIL_SEMINAIRE_DESINSCRIPTION :  // Options = [prenom, nom]
                $mailSubject =	"UNSUBSCRIBE cbi.int-seminars ".$option['prenom']." ".$option['nom'];
            break;
        case MAIL_CONTENU_AP : 
        case MAIL_CONTENU_TECHNIK : 
        case MAIL_CONTENU_RH :  // Options = [prenom, nom, equipe, labo, poste, job, categorie, appartenance, date_arrivee, date_depart]
                $message = "Une demande de compte vient d'être validée par ".getenv('SSL_CLIENT_S_DN_CN')." !\r\n"
                    . "Il s'agit de ".$option['prenom']." ".$option['nom'].",\r\n"
                    . "qui fait partie de l'équipe ".$option['equipe']." (laboratoire ".$option['labo']."),\r\n"
                    . "qui sera joignable à l'adresse ".$option['mail'].", poste ".$option['poste']." et être ".$option['job']." (".$option['categorie']
                    . "), dépendant de ".$option['appartenance'].".\r\n"
                    . "Son compte sera actif à partir du ".dateConv($option['date_arrivee'], DATE_FORMAT_HUMAIN)
                    . ( $option['date_depart']==""?",sans limite de durée":" et jusqu'au ".dateConv($option['date_depart'], DATE_FORMAT_HUMAIN)).".\r\n\r\n\r\n";
                if ($contenu===MAIL_CONTENU_TECHNIK) { $message."Vous pouvez lui fournir un badge pour la durée indiquée.\r\n\r\n"; }
                $mailSubject =	"[GestWEB] Nouvel arrivant";
            break;
        case MAIL_CONTENU_CHANGE_PASSWORD_SINFO:    // Options = [prenom, nom, login]
                $message = "Le mot de passe de ".$option['prenom']." ".$option['nom']." (login : ".$option['login'].") vient d'être modifié avec l'aide du service informatique !\r\n";
                $mailSubject =	"[GestWEB] Notification de changement de mot de passe";
            break;
        case MAIL_CONTENU_CHANGE_PASSWORD_USER:
                $message = $option['prenom']." ".$option['nom']." (login : ".$option['login'].") vient de changer son mot de passe !\r\n";
                $mailSubject =	"[GestWEB] Notification de changement de mot de passe";
            break;
        default : $retour = FALSE;
    }
    if ($mailSubject!=="") {    // Envoi du message s'il y a un sujet !...
        $mailEntete = 'MIME-Version: 1.0'."\r\n";
        $mailEntete.= 'Content-type: text/html; charset=utf-8'."\r\n";
        $mailEntete.= "From: ".$mailExpediteur."\r\n";
        $mailEntete.= "Cc: ".$mailExpediteur."\r\n";
        $mailEntete.= "Reply-to: ".$mailExpediteur."\r\n";
        $retour = ( mail($mailDestinataire, $mailSubject, str_replace("\r\n", "<br />", $message), $mailEntete) );
    }
    return $retour;
}



/*
                            
*/
?>