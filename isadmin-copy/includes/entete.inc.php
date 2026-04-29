<?php
$pageMenu = array(	"http://intranet.ibcg.biotoul.fr" =>	"Accueil intranet",
					"http://intranet2.ibcg.biotoul.fr" =>	"Nouvel Intranet",
					"index.php" =>							"ResIBCG");
$optionMenu = array(	"index.php" =>			"Recherche",
						"liste2.php" =>			"Adresses IP",
						"gestion_dhcp2.php" =>	"Gestion DHCP",
						"liste_vlan2.php" =>	"VLAN",
						"liste_prise2.php" =>	"Prises réseau",
						"liste_lieu2.php" =>	"Lieux");
// Spécificité pour cas ou il FAUT aller dans la gestion du DHCP
$requete = 'SELECT * FROM lan LEFT OUTER JOIN vlans ON vlans.idx=lan.vlan';
$requete.= ' WHERE vlans.fichier_dhcp_conf<>"" AND ( lan.dhcp>1 OR ( lan.dhcp=1 and ( lan.nom<>lan.dhcp_nom or lan.ether<>lan.dhcp_ether or lan.utilisateur<>lan.dhcp_user ) ) )';
if (is_array(sqlRequete($requete))) {
	$optionMenu["gestion_dhcp2.php"] = "<span class='attention'>".$optionMenu["gestion_dhcp2.php"]."</span>";
}
if (!isset($pageMenuSelection)) {	$pageMenuSelection = "index.php"; }
if (!isset($optionMenuSelection)) {	$optionMenuSelection = "index.php"; }
?>

<!DOCTYPE html>
<html>
<head>
<title> INTRANET IBCG - <?php echo( ($pageTitre!="" ? $pageTitre : "Accueil" ) ); ?></title>
<meta charset="utf-8" />
<link type="text/css" rel="stylesheet" href="styles/resibcg_styles.css" media="screen">
<link type="text/css" rel="stylesheet" href="text.css" media="screen">
<link type="text/css" rel="stylesheet" href="text2.css" media="screen">

<!--[if lt IE 9]>
<script src="html5.js"></script>
<![endif]-->
<script>
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
//-->
</script>
</head>
<body>
<div id="page">
<header>

	<h1> <?php echo( ($pageTitre!="" ? ($pagePreTitre!="" ? $pagePreTitre." - " : "" ).$pageTitre : "Bienvenue sur le site du GDS IBCG" ) ); ?> </h1>
    
</header>
<nav>
	<h2 class="accessibility">menu</h2>
	<ul>
    	<?php
		foreach ($pageMenu as $unLien=>$unMenu) {
			echo("<li".( $pageMenuSelection==$unLien ? " class='selection'" : "" ).">");
			echo("<a href='".$unLien."' target='_".( (substr($unLien,0,4)=="http") ? "blank" : "self" )."' > ".$unMenu." </a></li>");
		}
		?>
	</ul>
	<ul class="sous_menu">
    	<?php
		foreach ($optionMenu as $unLien=>$unMenu) {
			echo("<li".( $optionMenuSelection==$unLien ? " class='selection'" : "" ).">");
			echo("<a href='".$unLien."' target='_".( (substr($unLien,0,4)=="http") ? "blank" : "self" )."'> ".$unMenu." </a></li>");
		}
		?>
	</ul>
</nav>
