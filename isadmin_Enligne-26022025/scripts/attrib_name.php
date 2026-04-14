<?php
session_start();
require_once('../includes/entete.inc.php');
require_once('../includes/aside.inc.php');
require_once("../includes/function.inc.php");
date_default_timezone_set('Europe/Paris');
?>
<link type="text/css" rel="stylesheet" href="../styles/styles.css" media="screen" />
<article>
	<section> 

<?php 
if(isset($_POST['OnsubAttribName'])){
/* initialisation de la variable des erreurs éventuelles si tt les variables du formulaire ne correspondent aux critères */
	$_SESSION["error"] = "";

/* Pour les register globals off - On récupère les champs soumis tout en supprimant les balises HTML*/
	foreach($_POST as $champ=>$valeur){		// on remplit une variable portant le nom du champ 
		$$champ = strip_tags($valeur);
		}

/* On teste les champs entrés et s'il y a des erreurs on remplit $_SESSION["error"]  */
	$_SESSION["error"] .= (preg_match("/[^a-zA-Z- \éèêçà']/",$Firstname)) ? "First name correct is required.</br>" : "";
	$_SESSION["error"] .= (empty($Lastname)|| preg_match("/[^a-zA-Z- \éèêçà']/",$Lastname)) ? "Lastname correct is required.</br>" : "";
	if(filter_var($Mail, FILTER_VALIDATE_EMAIL)===FALSE){
		$_SESSION["error"] .= "l'adresse e-mail saisie n'est pas valide.</br>";
		}
/* Test des champs obligatoires	*/
	$_SESSION["error"] .= (empty($bact_origin)|| empty($nomType) || empty($ET_name1)) ? "Attention, il faut remplir tous les champs obligatoires.".$nomType." et ".$bact_origin.$ET_name1."</br>" : "";

/* Si tt est ok ($error est vide) on se connecte à la base */		
	if($_SESSION["error"]===""){
				
	/* Connexion à la base de données */
		$cnx = connexion("ISfinder") ;
		if (!$cnx){
			$_SESSION["error"] .= "Problème de connexion à la base de données ISfinder" ;
		}else{
	// Determine si Submiter existe dans ISfinder et  mise à jour du submiter ou insertion du submiter
			$ID_Submiter = submiter_exist($cnx,mysqli_real_escape_string($cnx,$Lastname),$Mail);
			if ($ID_Submiter){
				$sql_sub="UPDATE `submiters` SET `Firstname`= '".mysqli_real_escape_string($cnx,$Firstname)."', `Middlename`= '".mysqli_real_escape_string($cnx,$Middlename)."', `Lastname`= '".mysqli_real_escape_string($cnx,$Lastname)."', `Institution`= '".mysqli_real_escape_string($cnx,$Institution)."', `Department`= '".mysqli_real_escape_string($cnx,$Department)."', `Address`= '".mysqli_real_escape_string($cnx,$Address)."', `Code`= '".mysqli_real_escape_string($cnx,$Code)."', `Country`= '".mysqli_real_escape_string($cnx,$Country)."', `Mail`='".$Mail."', `Phone`= '".mysqli_real_escape_string($cnx,$Phone)."' " ;
				$sql_sub.=" WHERE `ID_Submiter` = '$ID_Submiter'";					
				$res=execute_sql($cnx,$sql_sub);
			}else{
				$sql_sub="INSERT INTO submiters(Firstname, Middlename, Lastname, Institution, Department, Address, Code, Country, Mail, Phone)" ;
				$sql_sub.=" VALUES ('".mysqli_real_escape_string($cnx,$Firstname)."','".mysqli_real_escape_string($cnx,$Middlename)."','".mysqli_real_escape_string($cnx,$Lastname)."','".mysqli_real_escape_string($cnx,$Institution)."','".mysqli_real_escape_string($cnx,$Department)."','".mysqli_real_escape_string($cnx,$Address)."','".mysqli_real_escape_string($cnx,$Code)."', '".mysqli_real_escape_string($cnx,$Country)."', '".$Mail."', '".mysqli_real_escape_string($cnx,$Phone)."')";
				$result = execute_sql($cnx,$sql_sub);
				if ($result){
					$ID_Submiter = mysqli_insert_id($cnx);
				}
			}
			
	// Déterminer si bact_origin existe déjà et le cré dans table nom_type s'il n'existe pas
			$ID_nom_type = bact_origin_exist($cnx,mysqli_real_escape_string($cnx,$bact_origin),$MGE_type,"ID_nom_type");
			if (!$ID_nom_type){
				$sql_sub="INSERT INTO nom_type(type_element_transposable_ID_Type_ET, bact_origin, new_taxo, nomType, comment)" ;
				$sql_sub.=" VALUES ('".$MGE_type."','".mysqli_real_escape_string($cnx,$bact_origin)."','".mysqli_real_escape_string($cnx,$new_taxo)."','".mysqli_real_escape_string($cnx,$nomType)."','".mysqli_real_escape_string($cnx,$comment)."')";
				$result = execute_sql($cnx,$sql_sub);
				$ID_nom_type = mysqli_insert_id($cnx);
			}

	// création de N lignes dans current_names et name_attribution
			$date_attribution = date("Y-m-d");
			$ok = 0;
			$ISnom ="";
			for ($i = 1 ; $i <= $nbr_names ; $i++){
				// table current_names
				$ET_name = "ET_name".$i;
				$organism = "organism".$i;
				$comments = "comments".$i;
				$sql_req="INSERT INTO current_names(nom_type_ID_nom_type, ET_name, organism, comments)" ;
				$sql_req.=" VALUES ('".$ID_nom_type."','".mysqli_real_escape_string($cnx,$$ET_name)."','".mysqli_real_escape_string($cnx,$$organism)."','".mysqli_real_escape_string($cnx,$$comments)."')";
				$result = execute_sql($cnx,$sql_req);
				$ID_Current_names = mysqli_insert_id($cnx);
				// table name_attribution
				$sql_req="INSERT INTO name_attribution(current_names_ID_Current_names, submiters_ID_Submiter, date_demande, date_attribution, Qui)" ;
				$sql_req.=" VALUES ('".$ID_Current_names."','".$ID_Submiter."','".mysqli_real_escape_string($cnx,$date_demande)."','".mysqli_real_escape_string($cnx,$date_attribution)."','".mysqli_real_escape_string($cnx,$Qui)."')";
				$result = execute_sql($cnx,$sql_req);
				$ok = 1;
				$ISnom .= ($i == 1)?  $$ET_name : ", ".$$ET_name;
			}
			mysqli_close($cnx);

			if ($ok){
				$cnx_sub = connexion("ISsubmit") ;
				$req_valid = "UPDATE `request_names` SET `validation`= 1 WHERE `ID_Request_names` = $ID_Request_names ";
				$result = execute_sql($cnx_sub,$req_valid);
				mysqli_close($cnx_sub);
				
		// envoyer mail
				$nomSub = $Lastname.",";
				$envoie = envoyerMail_attribNom($ISnom,$Mail,$nomSub);
				if (!$envoie){
					$_SESSION['error'] .= "problème d'envoie du mail de validation<br>" ;
				}
			}
			
			header("Location: ../index.php");
		}
		
/* Si formulaire soumis mais il y a des erreurs	on retourne au formulaire  */
	}else{
//		echo "<a href=javascript:history.back()'>texte du lien</a>" ;
		header("Location: ../ficheAttrib.php?ident=$ID_Request_names");
	}		
}

?>
	</section>
</article>
</div> <!-- Fin du div page -->
</body>
</html>