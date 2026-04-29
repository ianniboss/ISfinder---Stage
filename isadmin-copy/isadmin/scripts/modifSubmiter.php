<?php
session_start();
require_once('../includes/entete.inc.php');
require_once('../includes/aside.inc.php');
require_once("../includes/function.inc.php");
?>
<link type="text/css" rel="stylesheet" href="../styles/styles.css" media="screen" />
<article>
	<section> 

<?php 
if(isset($_POST['Onsubmit'])){
/* initialisation de la variable des erreurs éventuelles si tt les variables du formulaire ne correspondent aux critères */
	$_SESSION["error"] = "";

/* Pour les register globals off - On récupère les champs soumis tout en supprimant les balises HTML*/
	foreach($_POST as $champ=>$valeur){		// on remplit $_SESSION ET une variable portant le nom du champ 
		$$champ = $_SESSION[$champ] = strip_tags($valeur) ;		// pour ne pas écrire tt le tps $_SESSION[]
		}

/* On teste les champs entrés et s'il y a des erreurs on remplit $_SESSION["error"]  */
	$_SESSION["error"] .= (preg_match("/[^a-zA-Z- \éèêçà']/",$Firstname)) ? "First name correct is required.</br>" : "";
	$_SESSION["error"] .= (empty($Lastname)|| preg_match("/[^a-zA-Z- \éèêçà']/",$Lastname)) ? "Lastname correct is required.</br>" : "";
	if(filter_var($Mail, FILTER_VALIDATE_EMAIL)===FALSE){
		$_SESSION["error"] .= "l'adresse e-mail saisie n'est pas valide.</br>";
		}
		


/* Si tt est ok ($error est vide) on se connecte à la base */		
	if($_SESSION["error"]===""){
		foreach ($_SESSION as $elt_session => $var_session){	// on remplit une variable portant le nom du champ 		
			$$elt_session = strip_tags($var_session) ;			// pour ne pas écrire tt le tps $_SESSION[]
		}
		
	/* Connexion à la base de données */
$cnx = connexion($_SESSION['bdd']) ;	
if (!$cnx){
	// traitement de l'erreur ;
	$_SESSION["error"] .= "Problème de connexion à la base de données" ;
}else{
// Mise à jour des infos du Submiters	
	$sql_sub="UPDATE `submiters` SET `Firstname`= '".mysqli_real_escape_string($cnx,$Firstname)."', `Middlename`= '".mysqli_real_escape_string($cnx,$Middlename)."', `Lastname`= '".mysqli_real_escape_string($cnx,$Lastname)."', `Institution`= '".mysqli_real_escape_string($cnx,$Institution)."', `Department`= '".mysqli_real_escape_string($cnx,$Department)."', `Address`= '".mysqli_real_escape_string($cnx,$Address)."', `Code`= '".mysqli_real_escape_string($cnx,$Code)."', `Country`= '".mysqli_real_escape_string($cnx,$Country)."', `Mail`='".$Mail."', `Phone`= '".mysqli_real_escape_string($cnx,$Phone)."' " ;
	$sql_sub.=" WHERE `ID_Submiter` = '$ID_Submiter'";
		
	$res=execute_sql($cnx,$sql_sub);
		
  // Fermeture de la connexion
    	mysqli_close($cnx);
header("Location: ../index.php");
}
		
/* Si formulaire soumis mais il y a des erreurs	on retourne au formulaire sans l'effacer  */
	}else{
		header("Location: ../ficheSubmiter.php");
	}		
}

?>
	</section>
</article>

</div> <!-- Fin du div page -->
</body>
</html>
