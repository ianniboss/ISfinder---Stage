<?php
$Root = "";
if (!defined('DB_server')) {	define('DB_server', "lezat.ibcg.biotoul.fr");	}
if (!defined('DB_user')) {		define('DB_user',"gestannuaire");	}
if (!defined('DB_password')) {	define('DB_password',"aE3vUOKqHJe6ayti");	}
if (!defined('DB_bdd')) {		define('DB_bdd', "Labo");	}
require_once($Root.'function_variables.inc.php');
require_once($Root.'function_sql.inc.php');
require_once($Root.'function_controles.inc.php');
require_once($Root.'function_formulaires.inc.php');
define('DATE_VIERGE', "0000-00-00");
define('NON_INDIQUE', "(non indiqué)");
date_default_timezone_set("Europe/Paris");
define('PREFIXE_SESSION', "intranet_IBCG_");
	$modeSelection = '( ( comptes.id_statut IN (2,3)) ) AND ( comptes.annuaire=1 )';	// Comptes infos publiés et entrées d'annuaire
	$modeSelection.= ' AND ( comptes.date_arrivee<=NOW() )';					// ET date_arrivee passée
	$modeSelection.= ' AND ( comptes.date_depart>=NOW() OR comptes.date_depart IS NULL )';	// ET date_depart absente ou future
	$modeSelection.= ' AND ( comptes.date_suppression>=NOW() OR comptes.date_suppression IS NULL )';	// ET date_suppression absente ou future
	if ($selectionLabo!=$tab[0]) {
		$modeSelection.= ' AND reseau.labos.nom="'.$selectionLabo.'"';
	}	
?>