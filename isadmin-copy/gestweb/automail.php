<?php
// Cette page doit être insérée dans la crontab, de manière journalière.
//
define('DB_server', "lezat.ibcg.biotoul.fr");
define('DB_user',"gestannuaire");
define('DB_password',"aE3vUOKqHJe6ayti");
define('DB_bdd', "Labo");
require_once('../function_variables.inc.php');
require_once('../function_sql.inc.php');
require_once('../function_dates.inc.php');
require_once('../function_controles.inc.php');
date_default_timezone_set("Europe/Paris");
define('MAIL_SERVEUR_DEFAUT', "ibcg.biotoul.fr");
define('MAIL_SERVICE_INFO', "Service Informatique IBCG <sinfo@ibcg.biotoul.fr>");
define('MAIL_SERVEUR_INFO', "Administrateur IBCG <hpadmin@ibcg.biotoul.fr>");
define('SITE_RACINE', "https://secure.ibcg.biotoul.fr/gestweb/");
$rapport = array();
$requete = 'SELECT Labo.comptes.id as id, Labo.comptes.id_statut as id_statut, Labo.comptes_statut.statut as statut, Labo.comptes.annuaire as annuaire, '
        . ' Labo.comptes.email as email, Labo.comptes.nom as nom, Labo.comptes.prenom as prenom, reseau.labos.nom as labo, reseau.equipes.equipe as equipe,'
        . ' Labo.comptes.poste as poste, Labo.emploi_categorie.categorie as emploi_categorie, Labo.emploi.job as job, Labo.appartenance.appartenance as appartenance,'
        . ' Labo.comptes.date_arrivee as date_arrivee, Labo.comptes.date_depart as date_depart, Labo.comptes.date_suppression as date_suppression,'
        . ' CASE WHEN Labo.autorisation.id_niveau IS NULL THEN 1 ELSE Labo.autorisation.id_niveau END as id_autorisation,'
        . ' CASE WHEN Labo.autorisation.certificat IS NULL THEN "n-a" ELSE Labo.autorisation.certificat END as certificat,'
        . ' CASE WHEN Labo.autorisation_niveau.description IS NULL THEN "Non identifiable" ELSE Labo.autorisation_niveau.description END as autorisation,'
        . ' DATEDIFF(Labo.comptes.date_depart,NOW()) as jours_avant_fin'
        . ' FROM Labo.comptes'
        . ' LEFT OUTER JOIN Labo.comptes_statut ON Labo.comptes_statut.idx=Labo.comptes.id_statut'
        . ' LEFT OUTER JOIN reseau.equipes ON reseau.equipes.idx=Labo.comptes.id_equipe'
        . ' LEFT OUTER JOIN reseau.labos ON reseau.labos.idx=reseau.equipes.id_labo'
        . ' LEFT OUTER JOIN Labo.emploi ON Labo.emploi.id_job=Labo.comptes.id_job'
        . ' LEFT OUTER JOIN Labo.emploi_categorie ON Labo.emploi_categorie.idx=Labo.emploi.id_categorie'
        . ' LEFT OUTER JOIN Labo.appartenance ON Labo.appartenance.idx=Labo.comptes.id_appartenance'
        . ' LEFT OUTER JOIN Labo.autorisation ON LOWER(Labo.autorisation.mail)=LOWER(Labo.comptes.email)'
        . ' LEFT OUTER JOIN Labo.autorisation_niveau ON Labo.autorisation_niveau.idx=Labo.autorisation.id_niveau';
$condition = ' WHERE Labo.comptes.date_depart>=NOW() ORDER BY jours_avant_fin';
$liste = sqlRequete($requete.$condition);
$rapport = array();
if (is_array($liste)) {
    foreach ($liste as $un) {
        $delai = NULL;
        switch ($un['jours_avant_fin']) {
            case "0": $delai = "";                              $delaiUS = "";           break;
            case "1": $delai = "demain";                        $delaiUS = "tomorrow";   break;
            case "7": $delai = "dans une semaine (7 jours)";    $delaiUS = "in a week (7 days)";   break;
            case "30": $delai = "dans un mois (30 jours)";      $delaiUS = "in a month (30 days)";   break;
        }
        if (!is_null($delai)) {
            $prenom = html_entity_decode($un['prenom']);
            $nom = html_entity_decode($un['nom']);
            $labo = html_entity_decode($un['labo']);
            $equipe = html_entity_decode($un['equipe']);
            $message = "Bonjour ".$prenom." ".$nom.",\r\n\r\n";
            $message.= "Vous recevez ce message automatique car votre compte ".($delai==""?"est arrivé":"va arriver")." à expiration ".$delai.".\r\n";
            $message.= "Si votre compte doit rester actif, vous devez nous l'indiquer en répondant à ce mail ET en passant nous voir au service informatique.\r\n";
            $message.= "Votre gestionnaire reste votre contact privilégié pour gérer votre dossier :";
            $message.= " il/elle peut également directement demander la prolongation de votre compte si votre contrat est prolongé.\r\n\r\n";
            $message.= "Sans nouvelle de votre part, votre compte sera fermé ".($delai==""?"très prochainement":"le ".dateConv($un['date_depart'], DATE_FORMAT_HUMAIN)).".\r\n\r\n";
            $message.= "Il vous appartient de récupérer vos données avant sa fermeture.\r\n\r\n======\r\n";
            $message.= "Hello ".$prenom." ".$nom.",\r\n\r\n";
            $message.= "You receive this automatic message because your account ".($delai==""?"is expired":"will expire ".$delaiUS).".\r\n";
            $message.= "If your account is to remain active, you must let us know by replying to this mail AND by visiting the IT department.\r\n";
            $message.= "Your manager remains your privileged contact to manage your file :";
            $message.= " it can also directly request the extension of your account if your contract is extended.\r\n\r\n";
            $message.= "With no news from you, your account will be closed ".($delai==""?"soon":"on ".dateConv($un['date_depart'], DATE_FORMAT_HUMAIN_US)).".\r\n\r\n";
            $message.= "It is up to you to recover your data before it closes.\r\n";
            $mailExpediteur = MAIL_SERVEUR_INFO;
            $mailDestinataire = $prenom." ".$nom." <".$un['email'].">";
            $mailEntete = 'MIME-Version: 1.0'."\r\n";
            $mailEntete.= 'Content-type: text/html; charset=utf-8'."\r\n";
            $mailEntete.= "From: ".$mailExpediteur."\r\n";
            $mailEntete.= "Cc: ".$mailExpediteur."\r\n";
            $mailEntete.= "Reply-to: ".$mailExpediteur."\r\n";
            $mailSubject =	"[GestWEB] Expiration de votre compte";
            if ( mail($mailDestinataire, $mailSubject, str_replace("\r\n", "<br />", $message), $mailEntete) ) {
                    $rapport[] = "L'annonce d'expiration du compte de ".$prenom." ".$nom." (".$labo." - ".$equipe.") a été envoyée au titulaire du compte.";
            } else {
                    $rapport[] = "L'annonce d'expiration du compte de ".$prenom." ".$nom." (".$labo." - ".$equipe.") n'a pas pu être envoyée au titulaire du compte.";
            }
        }
    }
    $message = "Bonjour aux administrateurs,\r\n\r\n";
    if ($rapport==array()) {  // Si le tableau $rapport est vide, c'est qu'aucun compte n'est concerné !
        $message.= "Aucun compte ne fait l'objet d'une annonce de fermeture aujourd'hui : aucun mail n'a été envoyé.";
    } else {
        $message.= "Liste des comptes qui vont arriver à expiration :<ul>";
        foreach ($rapport as $rap) {
            $message.= "<li>".$rap."</li>";
        }  
        $message.= "</ul>";
    }
    $mailExpediteur = MAIL_SERVEUR_INFO;
    $mailDestinataire = MAIL_SERVEUR_INFO;
    $mailEntete = 'MIME-Version: 1.0'."\r\n";
    $mailEntete.= 'Content-type: text/html; charset=utf-8'."\r\n";
    $mailEntete.= "From: ".$mailExpediteur."\r\n";
    $mailEntete.= "Cc: ".$mailExpediteur."\r\n";
    foreach ($listeResponsables as $un) {
        $mailEntete.= "Cc: ".$un['email']."\r\n";
    }
    $mailEntete.= "Reply-to: ".$mailExpediteur."\r\n";
    $mailSubject =	"[GestWEB] Expiration des comptes";
    mail($mailDestinataire, $mailSubject, str_replace("\r\n", "<br />", $message), $mailEntete);
}
?>