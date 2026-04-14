<!DOCTYPE html>
<html>
<head>
<title>ISfinder</title>
<meta charset="utf-8" /> 
<meta name="author" content="Jo" />
<meta name="keywords" content="IS, Insertion Sequence" />
<link type="text/css" rel="stylesheet" href="styles/styles.css" media="screen" />
<link type="text/css" rel="stylesheet" href="styles/menu.css" media="screen" />
<link rel="icon" href="favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
</head>
<body>
<div id="page">
<header>
</header>

<?php
$nav_en_cours = 'home';
include('include/menu.inc.php'); ?>

<article>
<p>
  <!--		<div class="ecran">contenu de mon &eacutecran</div> --></p>
<p><img src="images/IS-garde.png" width="1024" height="531" border="0" usemap="#Map_logo" class="image">
  
  <map name="Map_logo">
    <area shape="rect" coords="15,445,150,510" href="https://lmgm.cbi-toulouse.fr/en/home/" target="_blank">
    <area shape="rect" coords="920,430,1000,520" href="http://www.cnrs.fr/index.php" target="_blank">
  </map>
  
  <?php
Include_once ("include/function.inc.php");

   // Connexion à la base isfinder et Récupération de la date de la dernière soumission validée
$cnx=connexion("ISfinder");

$sql_request="SELECT `Validation_Date`  FROM `submission` ORDER BY `submission`.`Validation_Date` DESC";
if ($result=execute_sql_new($cnx,$sql_request)){
	$date_sub=mysqli_result($result,0);
}else{
	$date_sub="";
}

mysqli_close($cnx);
?>
</p>
<p class="lastmaj">Last Database Update :&nbsp;<?php if (!empty($date_sub)){ echo $date_sub;}?></p>
</article>

<?php include('include/footer.inc.php'); ?>

</div> <!-- Fin du div page -->
</body>
</html>