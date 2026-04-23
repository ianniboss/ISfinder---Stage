<!DOCTYPE html>
<html>
<head>
<title>Comments and Suggession</title>
<meta charset="utf-8" /> 
<meta name="author" content="Jo" />
<meta name="keywords" content="Feedback" />
<link type="text/css" rel="stylesheet" href="../styles/styles_feedback.css" media="screen" />
<link type="text/css" rel="stylesheet" href="../styles/styles.css" media="screen" />
<link type="text/css" rel="stylesheet" href="../styles/menu.css" media="screen" />
<link rel="icon" href="../favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
</head>
<?php
require("ptitcaptcha.php");
?>
<body>
<div id="page">
<header>
</header>

<?php 
$nav_en_cours='about';
include('../include/menu.inc.php');
?>
<article>
<section>
<?php
Include_once ("../include/function.inc.php");

$form_soumis = htmlentities($_POST['Onsubmit']);
if($form_soumis == "Submit"){

/* traitement si Captcha OK, pour limiter le spam */
if (PtitCaptchaHelper::checkCaptcha()) {

/* Pour les register globals off - On récupère les champs soumis tout en supprimant les balises HTML*/
	foreach($_POST as $champ=>$valeur){		// on remplit $_SESSION ET une variable portant le nom du champ 
		$$champ = $_SESSION[$champ] = stripslashes(htmlentities($valeur)) ;		// pour ne pas écrire tt le tps $_SESSION[]
		}

/* Vérification des champs */
	if(filter_var($courriel, FILTER_VALIDATE_EMAIL)===FALSE){
		die(erreur_sub("email address",0));
		}
	if (!$courriel) {
		die(erreur_sub("Email adress",1));
	}
	if (!$Lname) {
		die(erreur_sub("Last name",1));
	}
	if (!$Fname) {
		die(erreur_sub("first name",1));
	}
	if (!$institution) {
		die(erreur_sub("institution",1));
	}
	if (!$country) {
		die(erreur_sub("country",1));
	}

/* Envoi du mail aux personnes concernées */
	$cc = addressMail('','cbi.webadmin-isfinder','');
	$cc .= ','.addressMail('',"mc2126","georgetown.edu").','.addressMail("Patricia","Siguier","").','.addressMail("Jacques","Mahillon","uclouvain.be");
	$headers = "From: ".addressMail('','cbi.webadmin-isfinder','')."\r\n";
	$headers .= "CC: ".$cc."\r\n";
	$headers .= "Content-Type: text/html; charset=\"utf-8\"\r\n" ;	
	$headers .= "X-Mailer: PHP/ISFinder\r\n";
	$texte = "IS Feedback form<br><br>";
	$texte .= "Name: ".$title." ".$Fname." ".$Lname."<br>";
	$texte .= "Institution: ".$institution."<br>";
	$texte .= "Department: ".$department."<br>";
	$texte .= "Address: ".$address."<br>";
	$texte .= "         ".$postCode."<br>";
	$texte .= "Country: ".$country."<br>";
	$texte .= "Email: ".$courriel."<br>";
	$texte .= "Comments: ".nl2br($comments)."<br>";

	$res_mail=mail($courriel,"[ISfinder] Feedback IS",$texte,$headers);
	if ($res_mail){
	// Affichage quand tout c'est bien passé
	echo "Your Comments and Suggestion on the IS Database has been sent,<br>" ;
	echo "Thank you for your interest in our IS Database.<br><br><HR>";
	echo "<a href='https://www-lmgm.biotoul.fr/' target='_top'><b>LMGM</b></a>&nbsp;&nbsp; | &nbsp;&nbsp;<a href='https://www-is.biotoul.fr/' target='_top'><b>IS HomePage</b></a>";
	} else {
		echo "<br>ERROR : contact the administrator<br>";
	}
} else {
	$ptitcaptcha_entry ? die(erreur_sub("picture",1)) : die(erreur_sub("picture",0));
}// Fin du Captcha
}// Fin demande Submit
?>

</section>
</article>
<?php include('../include/footer.inc.php'); ?>
</div> <!-- Fin du div page -->
</body>
</html>