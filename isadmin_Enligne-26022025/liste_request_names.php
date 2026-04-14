<?php
require_once('includes/entete.inc.php');
require_once('includes/aside.inc.php');
require_once("includes/function.inc.php");
require_once("includes/affiche.inc.php");
echo '<link type="text/css" rel="stylesheet" href="styles/styles.css" media="screen" />';
echo '<script type="text/javascript" src="scripts/function.js"></script>';

echo "<article> ";
if ($_GET['error']){
	echo "<p class='erreur'>".$_GET['error']."</p><hr/>";
}
$error = "";
$bdd = ($_GET['bdd'] == "ISfinder") ? "ISfinder" : "ISsubmit";			
$champrecherche = $_GET['champ'];

// Ecriture de la requête
if ($bdd == "ISsubmit"){		// Requête pour lister les demandes de nom
	$champs="`ID_Request_names`,`bact_origin`, `nbr_names`, `MGE_type`, `date_demande`, `Lastname`, `Firstname`, `Type_ET` ";
	$requete = "SELECT $champs FROM `request_names` ";
	$requete .= " LEFT JOIN `submiters` SUB
				ON `submiters_ID_Submiter` = SUB.`ID_Submiter`
				 LEFT JOIN `type_element_transposable` TET
				ON `MGE_type` = TET.`ID_Type_ET` 
				WHERE `validation` != 1 "
				;
	$champDefaut = "`Lastname`,`date_demande`" ;
	
}else{		// Requête pour lister les noms attribués (avec critère de recherche)
$condition = "`bact_origin` LIKE '%$champrecherche%' OR `ET_name` LIKE '%$champrecherche%'";
/*$requete = "SELECT `bact_origin`, `ET_name`,`Qui`,`date_attribution`, CONCAT(`Firstname`,' ',`Lastname`),CONCAT(`Institution`,IF(`Department`<>'',', ','  '),`Department`,IF(`Address`<>'',', ',''),`Address`,IF(`Code`<>'',', ',' '),`Code`,IF(`Country`<>'',', ',''),`Country`),`comments`
	FROM  `nom_type`,`current_names`, `name_attribution` , `submiters`
	WHERE $condition
	AND `submiters_ID_submiter`= `ID_Submiter`
	AND `current_names_ID_Current_names`= `ID_Current_names`
	AND `nom_type_ID_nom_type`= `ID_nom_type`";
*/
$condition = "`bact_origin` LIKE '%$champrecherche%' OR `ET_name` LIKE '%$champrecherche%'";

$requete = "SELECT `bact_origin`, `ET_name`, `Lastname`, `Firstname`, `date_attribution`
	FROM  `nom_type`
	LEFT JOIN `current_names` CN 	ON `ID_nom_type` = CN.`nom_type_ID_nom_type` 
	LEFT JOIN `name_attribution` NA 	ON `ID_Current_names` = NA.`current_names_ID_Current_names` 	
	LEFT JOIN `submiters` SUB 	ON `submiters_ID_submiter` = SUB.`ID_Submiter` 
	WHERE $condition";
	$champDefaut = "`bact_origin`,`ET_name`" ;
}

		// Connexion à la  base et exécution de la requete
if ($cnx=connexion($bdd)){
	mysqli_query($cnx,"SET NAMES UTF8"); 
// Le résultat trier sur la colonne sélectionnée ou name par défaut
/* Choix de la colonne de tri
		$tri_autorises = array('Lastname','date_demande');
		if (!empty($_GET['tri'])){ $ordre= strip_tags($_GET['tri']);}
		$tri = ( !empty($ordre) &&  in_array($ordre,$tri_autorises,true)) ? $ordre : $champDefaut;
*/
	$reqtrier = $requete." ORDER BY ".$champDefaut ;
			
		/* Execution de la requette et si résultat, alors on continue */
	$result = execute_sql($cnx,$reqtrier);
	$nombre = mysqli_num_rows($result);

	if ($nombre > 0) {
		if ($bdd == "ISsubmit"){
			echo "<h2>Attribution de nom : </h2>";							
			print "<h3>Nombre de demande: ".$nombre."</h3>";
			
			print "<table><tr class='request'><th>N°</th><th colspan=\"2\">Actions</th><th>Nom</th>><th>Prenom</th><th>bact_origin</th><th>nbr</th><th>MGE_type</th><th>date</th></tr>";				
			affiche_attrib_nom($result);
			print "</table>";
		}else{
			echo "<h2>Nom déjà attribué : </h2>";							
			print "<h3>Nombre de réponse: ".$nombre."</h3>";
			
			print "<table><tr class='request'><th>N°</th><th>ET_name</th><th>bact_origin</th><th>Nom</th>><th>Prenom</th><th>date</th></tr>";				
			affiche_nom_attribue($result);
			print "</table>";
		}
		
	}else{									/* Pas de r�sultat */
		print "<h2> $base_select </h2> Nothing was found<BR/>";
	}
}else{
	$error = "Problème de connexion";
	}
echo "</article>" ;
?>
