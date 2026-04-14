<?php
session_start();
header('Pragma: no-cache');
header("Cache-Control: no-cache, must-revalidate");	
if(isset($_POST['submitrecherche'])){

/* Pour les register globals off - On récupère les champs soumis tout en supprimant les balises HTML*/
	foreach($_POST as $champ=>$valeur){		// on remplit une variable portant le nom du champ 
		$$champ = htmlspecialchars(strip_tags($valeur)) ;
		}
	$script = $_POST['nom_script'];
//	$script = $champ.'nom_script';

	// On ré-affiche le script appelant avec les critéres défini 
	header("Location: ../$script?champrecherche=$champrecherche&champ=$champ");	
	exit();

/* Si formulaire pas soumis  */	
}else{
	header("Location: https://www-is.biotoul.fr/");
	exit();
}
?>
