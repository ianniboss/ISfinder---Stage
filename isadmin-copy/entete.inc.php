<?php
if (!defined('DB_server')) {	define('DB_server', "astun.ibcg.biotoul.fr");	}
if (!defined('DB_user')) {	define('DB_user',"gestannuaire");	}
if (!defined('DB_password')) {	define('DB_password',"aE3vUOKqHJe6ayti");	}
if (!defined('DB_bdd')) {	define('DB_bdd', "Labo");	}
require_once($Root.'function_variables.inc.php');
require_once($Root.'function_sql.inc.php');
require_once($Root.'function_controles.inc.php');
require_once($Root.'function_formulaires.inc.php');
require_once($Root.'fpdf/fpdf.php');
define('DATE_VIERGE', "0000-00-00");
define('NON_INDIQUE', "(non indiqué)");
date_default_timezone_set("Europe/Paris");
define('PREFIXE_SESSION', "intranet_IBCG_");
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Intranet IBCG</title>
<link rel="stylesheet" href="<?php echo($Root); ?>/styles/twoColFixLt.css" type="text/css">
<link rel="stylesheet" href="<?php echo($Root); ?>/styles/text.css" type="text/css">
</head>

<body>
<div class="container">
	<div name="bandeau" id="bandeau">
    	<img src="<?php echo($Root); ?>images/Bandeau_complet.png" usemap="#liens" />
        <map name="liens">
          <area shape="rect" coords="-4,-3,92,64" href="<?php echo($Root); ?>index.php" target="_self">
          <area shape="rect" coords="484,16,544,34" href="http://www.cnrs.fr/fr/organisme/presentation.htm" target="_blank">
          <area shape="rect" coords="552,16,634,34" href="www.cnrs.fr" target="_blank">
          <area shape="rect" coords="638,17,751,34" href="http://www.cnrs.fr/fr/une/sites-cnrs.htm" target="_blank">
          <area shape="rect" coords="3,67,151,131" href="www.cnrs.fr" target="_blank">
          <area shape="rect" coords="4,133,151,200" href="www-frbt.biotoul.fr" target="_blank">
        </map>
    	
    </div>