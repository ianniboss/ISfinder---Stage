<?php
session_start();
require_once("../includes/affiche.inc.php");
require_once("../includes/function.inc.php");
require_once("../includes/actions.inc.php");

$ident =  $_GET['IDET'] ;
$action = $_GET['action'] ;
$bdd = $_GET['bdd'] ;

// Transférer un element dans les bases wait, trash ou sub
if ($action == "sub" || $action == "wait" || $action == "trash" ){
	/* Connexion à la base de données */
	$cnx = connexion($bdd) ;	
	if (!$cnx){
		// traitement de l'erreur ;
		$_SESSION['error'] = "Problème de connexion à la base de données" ;
	}else{
	if ($bdd == "ISsubmit"){
		switch ($action) {
			case "sub":
				$sql_maj = "UPDATE `element_transposable` SET `base_ID_Base`= '1' WHERE `ID_ET` = '$ident'" ;
				$res=execute_sql($cnx,$sql_maj);
				break;			
			case "wait": 
				$sql_maj = "UPDATE `element_transposable` SET `base_ID_Base`= '2' WHERE `ID_ET` = '$ident'" ;
				$res=execute_sql($cnx,$sql_maj);
				break;
			case "trash":
				$sql_maj = "UPDATE `element_transposable` SET `base_ID_Base`= '3' WHERE `ID_ET` = '$ident'" ;
				$res=execute_sql($cnx,$sql_maj);
				break;
		}
	}elseif ($bdd == "isfinder"){
		$_SESSION['error'] = "En cours de dev" ;
	}else{
		$_SESSION['error'] = "Problème de base de données" ;
	}

	  // Fermeture de la connexion
	mysqli_close($cnx);
	}
	
}elseif ($action == "ok"){						// Transférer un element dans la base ISfinder
	$recup = recup_data($ident,'',$bdd) ;
	if ($recup){
		echo $bdd;
		echo "<br>" ;
		$ecrit = ecrit_data ($ident,$_SESSION['ET_name'],"isfinder") ;
		if ($ecrit){		
			$suppr = suppression($ident,'',$bdd) ;
		}else{
			$_SESSION['error'] .= "probleme d'écriture dans la base, Transfert interrompu<br>" ;
			header("Location: ../liste.php?list=1");
			exit();
		}
//		$_SESSION['error'] .= ($envoie = envoyerMail($_SESSION['ET_name'],$_SESSION['Mail'])) ? "" : "problème d'envoie du mail de validation<br>";
		$envoie = envoyerMail($_SESSION['ET_name'],$_SESSION['Mail']);
		if (!$envoie){
			$_SESSION['error'] .= "problème d'envoie du mail de validation<br>" ;
		}
		if (!$suppr){
			$_SESSION['error'] .= "Fiche transférée dans ISfinder mais probleme suppression dans ISsubmit" ;
			header("Location: ../liste.php?list=1");
			exit();
		}
	}else{
		$_SESSION['error'] .= "probleme de recupération des données dans ISsubmit, Transfert interrompu" ;
	}	
}elseif ($action == "suppr"){					// Suppression d'un element 
	$suprime = suppression($ident,'',$bdd);
	if (!$suprime){
		$_SESSION['error'] .= "probleme lors de la suppression" ;
	}
		
}else{
	$_SESSION['error'] = "Action non autorisée" ;
}
	
		// Retour à la liste
header("Location: ../liste.php?list=1");

?>

