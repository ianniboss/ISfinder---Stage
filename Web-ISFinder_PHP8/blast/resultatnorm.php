<html>
<head>
<?php

$title = htmlspecialchars($_GET['title']);
echo "<title>Blast ".$title."</title>";

?>
<meta charset="utf-8" /> 
<meta name="author" content="Jo" />
<meta name="keywords" content="IS, Insertion Sequence" />
<link type="text/css" rel="stylesheet" href="../styles/styles.css" media="screen" />
<link type="text/css" rel="stylesheet" href="../styles/menu.css" media="screen" />
<link type="text/css" rel="stylesheet" href="../styles/ficheMGE.css" media="screen" />
<link rel="icon" href="favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
</head>
<body>
<div id="page">
<header>
</header>

<?php 
$nav_en_cours='tools';
include('../include/menu.inc.php');
include_once ("../include/function.inc.php");
include_once ("../include/affiche.inc.php");

echo "<article>" ;

# On ne veut pas de cochoncet�s dans la param�tre id!!!
$id_fichier=trim(stripslashes(htmlspecialchars($_GET['id'])));
$fichier = "/var/www/uploads/blast/tmp/".$id_fichier.".res";
if ((strlen($id_fichier) == 9) and (!preg_match('[^A-Za-z0-9]',$id_fichier)) and (file_exists($fichier))) {
	$command="ps -ef|grep ".$id_fichier."[.]";
	$resultat=exec($command);

	if (!$resultat) {
			echo "</head><body><h3>BLAST search result</h3>";
			$result = file_get_contents($fichier);
			$affich_result = str_replace("\n","<br>", $result); 
			echo "<PRE>".$affich_result."</PRE>" ;
			echo "</article></body></html>";
		} else {
			echo "<meta http-equiv='refresh' content='10'>";
			echo "</head><body><h3>Result</h3><br>";
			echo "Process is still running<br>";
			echo "This page will be automatically reloaded in 10 seconds<br>";
			echo "Please be patient...<br>";
			echo "</article></body></html>";
		}
} else {
	echo "</head><body><h3>Bad request !!!</h3>";
	echo "<h4>All activity is logged !!!</h4>";
	echo "<p>If you think it's server error, please contact the <a href=\"mailto: ".addressMail('','cbi.webadmin-isfinder','')."\">webmaster</a>.</p></body></html>";
}
?>

