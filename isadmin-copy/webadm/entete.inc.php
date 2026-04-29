<?php
$pageMenu = array(	"http://intranet.ibcg.biotoul.fr" =>	"Accueil intranet",
					"https://resibcg.ibcg.biotoul.fr" =>	"ResIBCG",
					$Root."index.php" =>					"WebAdmin - METI");
$optionMenu = array(	$Root."menus.php" =>				"Menus",	// ID 260
						$Root."labos.php" =>				"Laboratoires",	// ID 261
						$Root."contacts.php" =>				"Contacts", // ID 2
						$Root."publications.php" =>			"Publications",	// ID 263
						$Root."auteurs.php" =>				"Auteurs",	// ID 264
						$Root."traductions.php" =>			"Mots",	// ID 265
						$Root."paragraphes.php" =>			"Paragraphes"/*,	// ID 266
						$Root."pages.php" =>				"Pages",	// ID 
						$Root."galerie.php" =>				"Photo/Vidéo",	// ID 
						$Root."liens.php" =>				"Liens"*/);	// ID 
$optionMenu = array();
$requete = 'SELECT menu_opt.page as lien, traduction.mot_fr as texte FROM menu_opt LEFT OUTER JOIN traduction ON menu_opt.id_traduction=traduction.id_traduction WHERE menu_opt.id_menu=5 ORDER BY menu_opt.ordre';
$menu = sqlRequete($requete);
foreach($menu as $item) {	$optionMenu[$item['lien']] = $item['texte']; }
if (!isset($pageMenuSelection)) {	$pageMenuSelection = $Root."meti/index.php"; }
if (!isset($optionMenuSelection)) {	$optionMenuSelection = $Root."meti/index.php"; }
?>

<!DOCTYPE html>
<html>
<head>
<title> INTRANET IBCG - <?php echo( ($pageTitre!="" ? $pageTitre : "Accueil" ) ); ?></title>
<meta charset="utf-8" />
<link type="text/css" rel="stylesheet" href="<?php echo($Root); ?>styles/webadmin_styles.css" media="screen">
<link type="text/css" rel="stylesheet" href="<?php echo($Root); ?>styles/text.css" media="screen">
<link type="text/css" rel="stylesheet" href="<?php echo($Root); ?>styles/text2.css" media="screen">
<script src="<?php echo($Root); ?>ckeditor/ckeditor.js"></script>
<link rel="stylesheet" href="<?php echo($Root); ?>ckeditor/sample.css">

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

	<h1> <?php echo( ($pageTitre!="" ? ($pagePreTitre!="" ? $pagePreTitre." - " : "" ).$pageTitre : "Administration des Sites WEB - IBCG" ) ); ?> </h1>
    
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
