<!DOCTYPE html>
<html>
<head>
<title>General features</title>
<meta charset="utf-8" /> 
<meta name="author" content="Jo" />
<meta name="keywords" content="IS, Insertion Sequence" />
<link type="text/css" rel="stylesheet" href="styles/styles.css" media="screen" />
<link type="text/css" rel="stylesheet" href="styles/menu.css" media="screen" />
<link type="text/css" rel="stylesheet" href="styles/informations.css" media="screen" />
<link rel="icon" href="favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">

</head>
<body>
<div id="page">
<header>
</header>

<?php 
$nav_en_cours='infos';
include('include/menu.inc.php');
include_once ("include/function.inc.php");
?>

<article>
<!--		<div class="ecran">contenu de mon &eacutecran</div> -->
	<section>
		<h2>General features and properties  of insertion sequence elements</h2>
		<hr/>

		<fieldset>
			<?php include('IS_Infos/1_0.html'); ?>
		</fieldset>
	</section>
</article>

<?php include('include/footer.inc.php'); ?>
</div> <!-- Fin du div page -->
</body>
</html>