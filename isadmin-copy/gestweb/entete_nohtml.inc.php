<?php
define('DB_server', "astun.ibcg.biotoul.fr");
define('DB_user',"gestannuaire");
define('DB_password',"aE3vUOKqHJe6ayti");
define('DB_bdd', "Labo");
require_once('../function_variables.inc.php');
require_once('../function_sql.inc.php');
require_once('../function_dates.inc.php');
require_once('../function_controles.inc.php');
require_once('../function_formulaires.inc.php');
require_once('../fpdf/fpdf.php');
define('DATE_VIERGE', "0000-00-00");
define('NON_INDIQUE', "(non indiqué)");
date_default_timezone_set("Europe/Paris");
define('PREFIXE_SESSION', "intranet_IBCG_");
define('MODE_LISTE_ANNUAIRE', "ListeAnnuaireIBCG");
define('MODE_LISTE_DEMANDES', "DemandesActuelles");
define('MODE_LISTE_FIN_ACTIVITE', "ComptesFinActivite");
define('MODE_LISTE_SUPPRIME', "ComptesSupprimes");
define('MODE_LISTE_AUTORISATION', "Autorisations");
define('MODE_COMPTE', "CompteInformatique");
define('MODE_ANNUAIRE', "EntreeAnnuaire");
define('MAIL_SERVEUR_DEFAUT', "ibcg.biotoul.fr");
define('MAIL_SERVICE_INFO', "Service Informatique IBCG <sinfo@ibcg.biotoul.fr>");
define('MAIL_SERVEUR_INFO', "Administrateur IBCG <hpadmin@ibcg.biotoul.fr>");
define('SITE_RACINE', "https://secure.ibcg.biotoul.fr/gestweb/");
#
# Vérification du certificat !!!
#
$qui = getenv('SSL_CLIENT_S_DN_CN'); 
$cnrs = getenv('SSL_CLIENT_S_DN_OU');
$AUTORISATION = ( in_array($cnrs, array("FR3743", "UMR5099", "UMR5100", "UMR5547")) ? 1 : 0 );	// Doit faire partie d'un labo !
$AUTORISATION_CERTIFICAT = $qui;
$AUTORISATION_DESCRIPTION = ($AUTORISATION==1?"Visiteur":"Aucune");
$AUTORISATION_ID_EQUIPE = 0;
if ($AUTORISATION==1) {	// Valide, vérifie s'il y a moyen d'avoir des autorisations supplémentaire !
	$requete = 'select autorisation.id_niveau as id_niveau, autorisation.mail as mail, autorisation_niveau.description as description';
	$requete.= ' from autorisation left outer join autorisation_niveau on autorisation.id_niveau=autorisation_niveau.idx';
	$requete.= ' where autorisation.certificat="'.$qui.'" limit 1 ;';
	$infoAutorisation = sqlrequete($requete);
	if (is_array($infoAutorisation[0])) {	// Trouvé dans les autorisations accordées, bénéficie d'une élévation de privilège !
		$AUTORISATION = $infoAutorisation[0]['id_niveau'];
		$AUTORISATION_DESCRIPTION = $infoAutorisation[0]['description'];
		$infoLabo = sqlTrouve('reseau.labos', 'code_unite="'.$cnrs.'"');
		$AUTORISATION_ID_LABO = (is_array($infoLabo)?$infoLabo['idx']:8);
		$AUTORISATION_MAIL = $infoAutorisation[0]['mail'];
	}
} else {  // Invalide : on jette tout !
	header('Status: 403 Access denied', false, 403);      
	header('Location: http://intranet.ibcg.biotoul.fr');      
	exit();        
}
?>