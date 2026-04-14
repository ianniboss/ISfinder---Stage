<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
<title>Result of your query</title>
<meta charset="utf-8" /> 
<link type="text/css" rel="stylesheet" href="../styles/styles.css" media="screen" />
<link type="text/css" rel="stylesheet" href="../styles/menu.css" media="screen" />
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
require("ptitcaptcha.php");
?>
<article>
	<section> 

<?php 
Include_once ("../include/function.inc.php");
Include_once ("../include/function_sub.inc.php");

$form_soumis = htmlentities($_POST['Onsubmit']);
if($form_soumis == "Submit"){

/* traitement si Captcha OK, pour limiter le spam */
if (PtitCaptchaHelper::checkCaptcha()) {	

/* initialisation de la variable des erreurs éventuelles si tt les variables du formulaire ne correspondent aux critères */
	$_SESSION["error"] = "";
	
/* Pour les register globals off - On récupère les champs soumis tout en supprimant les balises HTML*/
	foreach($_POST as $champ=>$valeur){		// on remplit $_SESSION ET une variable portant le nom du champ 
		$$champ = $_SESSION[$champ] = trim(htmlentities($valeur)) ;		// pour ne pas écrire tt le tps $_SESSION[]
		}

/* On teste les champs entrés et s'il y a des erreurs on remplit $_SESSION["error"]  */
	$_SESSION["error"] .= (empty($Fname)||(preg_match("/[^a-zA-Z- \éèêç']/",$Fname))) ? "First name correct is required.</br>" : "";
	$_SESSION["error"] .= (empty($Lname)|| preg_match("/[^a-zA-Z- \éèêç']/",$Lname)) ? "Last name correct is required.</br>" : "";
	$_SESSION["error"] .= (empty($institution)||strlen($institution)<2) ? "Field institution is required.</br>" : "";
	$_SESSION["error"] .= (empty($country)||strlen($country)<2) ? "Field country is required.</br>" : "";
	$_SESSION["error"] .= (empty($courriel)) ? "e-mail address is required.</br>" : "";
	$_SESSION["error"] .= (empty($bact_host) ||preg_match("/[^a-zA-Z0-9_-:. ']/",$bact_host)) ? "Bacterial host is required.</br>" : "";

	if(filter_var($courriel, FILTER_VALIDATE_EMAIL)===FALSE){
		$_SESSION["error"] .= "e-mail address is not valid.</br>";
		}

/* Si tt est ok ($error est vide) on se connecte à la base */		
	if($_SESSION["error"]===""){
		
// Connexion à la  base ISsubmit
	    $cnx=connexion("astun.ibcg.biotoul.fr","issubmit","ISsubmit","My;14U#GvACsP9*,");

// Insertion des infos du Submiters et récupération du ID_Submiter
		$sql_sub="INSERT INTO submiters(Firstname, Middlename, Lastname, Institution, Department, Address, Code, Country, Mail, Phone)" ;
		$sql_sub.=" VALUES ('".mysqli_real_escape_string($cnx,$Fname)."','".mysqli_real_escape_string($cnx,$Mname)."','".mysqli_real_escape_string($cnx,$Lname)."','".mysqli_real_escape_string($cnx,$institution)."','".mysqli_real_escape_string($cnx,$department)."','".mysqli_real_escape_string($cnx,$address)."','".intval($postCode)."', '".mysqli_real_escape_string($cnx,$country)."', '".$courriel."', '".mysqli_real_escape_string($cnx,$tel)."')";
		$res=execute_sql_new($cnx,$sql_sub);
		
		$ID_Submiter = mysqli_insert_id($cnx);
		
// Insertion des infos concernant bacterial host
		$date_req_name = date("Y-m-d");
		$sql_sub="INSERT INTO request_names(bact_origin, submiters_ID_Submiter, comments_author, nbr_names, MGE_type, date_demande) VALUES ('".mysqli_real_escape_string($cnx,$bact_host)."','".intval($ID_Submiter)."','".mysqli_real_escape_string($cnx,$bact_comments)."','".intval($nb_name)."','".mysqli_real_escape_string($cnx,$typeMGE)."','".mysqli_real_escape_string($cnx,$date_req_name)."')";
		$res=execute_sql_new($cnx,$sql_sub);

  // Fermeture de la connexion
    	mysqli_close($cnx);

	//Determination du type de GE	
	switch (intval($typeMGE)){
		case 2:
			$type = "MITE" ;
			break;
		case 4:
			$type = "MIC" ;
			break;
		case 5:
			$type = "tIS" ;
			break;
		default:
			$type = "IS" ;
	}
	
	// Envoi du mail de confirmation au submitter
	$headers = "From: ".addressMail('','cbi.webadmin-isfinder','')."\r\n";
	$headers .= "Content-Type: text/plain; charset=\"utf-8\"\r\n" ;	
	$headers .= "X-Mailer: PHP/ISFinder\r\n";	
	$texte = "IS Name Attribution Form\n\n";
	$texte .= $Fname." ".$Lname.", ";
	$texte .= "you will receive an email with the attributed ".$type." name as soon as possible:\n";
	$texte .= "For your request, Host : ".$bact_host."\n";
	$texte .= "Comments: ".$bact_comments."\n\n";
	$texte .= "Thank you for your interest in our IS Database.\n";
	mail($courriel,"[ISfinder] IS name attribution request",$texte,$headers);
	
	// Envoi du mail à ISfinder team
	$to = addressMail('','cbi.webadmin-isfinder','');		
	$cc = addressMail('',"mc2126","georgetown.edu").','.addressMail("Patricia","Siguier","").','.addressMail("Jacques","Mahillon","uclouvain.be");
	$headers = "From: ".addressMail('','cbi.webadmin-isfinder','')."\r\n";
	$headers .= "CC: ".$cc."\r\n";
	$headers .= "X-Mailer: PHP/ISFinder\r\n";
	$headers .= "Content-Type: text/plain; charset=\"utf-8\"\r\n" ;	
	$texte = "IS Name Attribution Form :\n";
	$texte .= "Name: ".$Fname." ".$Mname." ".$Lname."\n";
	$texte .= "Institution: ".$institution."\n";
	$texte .= "Department: ".$department."\n";
	$texte .= "Address: ".$address."\n";
	$texte .= "         ".$postCode."\n";
	$texte .= "Country: ".$country."\n";
	$texte .= "Email: ".$courriel."\n";
	$texte .= "Telephone: ".$tel."\n\n";
	$texte .= "Request: ".$nb_name." ".$type."\n";
	$texte .= "Host: ".$bact_host."\n";
	$texte .= "Comments: ".$bact_comments."\n";

	mail($to,"[ISfinder] IS name attribution request",$texte,$headers);	

	// Affichage quand tout c'est bien passé
	echo "Your application form has been registered,<br>" ;
	echo "Thank you for your interest in our IS Database.<br><br><HR>";
	echo "<a href='https://www-lmgm.biotoul.fr/' target='_top'><b>LMGM</b></a>&nbsp;&nbsp; | &nbsp;&nbsp;<a href='https://www-is.biotoul.fr/' target='_top'><b>IS HomePage</b></a>";

	session_destroy();
	}else{	   //Fin tt est ok
		header("Location: /request_name_form.php");
		exit();
	}

} else {
	$ptitcaptcha_entry ? die(erreur_sub("picture",1)) : die(erreur_sub("picture",0));
}// Fin du Captcha


}else{																	//Fin form soumis
	header("Location: /request_name_form.php");
	exit();
}

?>
	</section>
</article>
<?php include('../include/footer.inc.php'); ?>

</div> <!-- Fin du div page -->
</body>
</html>
