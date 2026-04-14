<?php
session_start();
header('Pragma: no-cache');
header('Cache-Control: no-cache');
header("Cache-Control: no-cache, must-revalidate");	
if(isset($_POST['submitrecherche'])){

/* Pour les register globals off - On récupère les champs soumis tout en supprimant les balises HTML*/
	foreach($_POST as $champ=>$valeur){		// on remplit une variable portant le nom du champ 
		$$champ = strip_tags($valeur) ;
		}

	// On affiche liste.php avec les critéres défini 
	$bases = serialize($_SESSION['base']);
	header("Location: ../liste.php?champrecherche=$champrecherche&champ=$champ");	

/* Si formulaire submiters  soumis  */
}elseif (isset($_POST['lancerecherche'])) {
	
	foreach($_POST as $champ=>$valeur){
	$$champ = strip_tags($valeur) ;
	}

	header("Location: ../liste.php?champ=$champSubmiterRecherche&base_submiter=1");	

/* Si	*/
}elseif (isset($_POST['nameAttibuted'])) {
	
	foreach($_POST as $champ=>$valeur){
	$$champ = strip_tags($valeur) ;
	}

	header("Location: ../liste_request_names.php?champ=$champNameAttrRecherche&bdd=ISfinder");	

/* Si formulaire pas soumis  */	
}else{
	header("Location: ../liste.php");
}
?>
