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
?>
<article>

<!--		<div class="ecran">contenu de mon &eacutecran</div> -->
	<section> 

<?php 
Include_once ("../include/function.inc.php");
Include_once ("../include/affiche.inc.php");
var_dump($_POST);
die(); // Arrête l'exécution du script pour voir uniquement le résultat de var_dump
// $demande_tri = isset($_SESSION["demande_tri"]) ? $_SESSION["demande_tri"] : '0';
// $form_soumis = (!empty($_POST['Onsubmit'])) ? htmlspecialchars($_POST['Onsubmit']) : Null ;

$form_soumis = filter_input(INPUT_POST, 'Onsubmit', FILTER_SANITIZE_SPECIAL_CHARS) ?: null;

// 2. Initialisation de $_SESSION['demande_tri'] si non défini
if (!isset($_SESSION['demande_tri'])) {
    $_SESSION['demande_tri'] = 0; // Valeur par défaut
}

		// Vérification que l'appel à search-db.php vient bien du formulaire search.php
		// mais il faut pouvoir réafficher le résultat si demande de tri différent
		// inconvénient : la var. de session reste active tant que navigateur pas fermé 
		// mais cel limite tout de même l'utilisation d'un script autotomatique
// if($form_soumis == "Submit" || preg_match("#https://www-is.biotoul.fr/#",$_SERVER['HTTP_REFERER'])){
if($form_soumis === "Submit" || $_SESSION['demande_tri'] == 1){
	$_SESSION['demande_tri'] = 1;

	if(!isset($_SESSION['requete'])){
	# Pour les register globals off
	# On récupère les champs soumis tout en supprimant les balises HTML
	/*	$name = strip_tags($_POST['name']);
		$accession = strip_tags($_POST['accession']);
		$family = strip_tags($_POST['family']);
		$grp = strip_tags($_POST['grp']);
		$ir_r = strip_tags($_POST['ir_r']);
		$ir_l = strip_tags($_POST['ir_l']);
		$host = strip_tags($_POST['host']);
		$ir = strip_tags($_POST['ir']);
		$dr = strip_tags($_POST['dr']);
		$orfSize = strip_tags($_POST['orfSize']);
		$orfFunction = strip_tags($_POST['orfFunction']);
		$length = strip_tags($_POST['length']);
		$frameshift = strip_tags($_POST['frameshift']);
		$mge = strip_tags($_POST['MGEtype']);
		$tout = strip_tags($_POST['tout']);
		$output = strip_tags($_POST['output']); */

	 // On récupère les champs soumis en supprimant les balises HTML avec une valeur par défaut si non défini
       // $name 			= isset($_POST['name']) ? trim(strip_tags($_POST['name'])) : null;
$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
$name = !empty($name) ? trim($name) : null;

        $accession     = isset($_POST['accession']) ? strip_tags($_POST['accession']) : null;
        $family        = isset($_POST['family']) ? strip_tags($_POST['family']) : null;
        $grp           = isset($_POST['grp']) ? strip_tags($_POST['grp']) : null;
        $ir_r          = isset($_POST['ir_r']) ? strip_tags($_POST['ir_r']) : null;
        $ir_l          = isset($_POST['ir_l']) ? strip_tags($_POST['ir_l']) : null;
        $host          = isset($_POST['host']) ? strip_tags($_POST['host']) : null;
        $ir            = isset($_POST['ir']) ? strip_tags($_POST['ir']) : null;
        $dr            = isset($_POST['dr']) ? strip_tags($_POST['dr']) : null;
        $orfSize       = isset($_POST['orfSize']) ? strip_tags($_POST['orfSize']) : null;
        $orfFunction   = isset($_POST['orfFunction']) ? strip_tags($_POST['orfFunction']) : null;
        $length        = isset($_POST['length']) ? strip_tags($_POST['length']) : null;
        $frameshift    = isset($_POST['frameshift']) ? strip_tags($_POST['frameshift']) : null;
        $mge           = isset($_POST['MGEtype']) ? strip_tags($_POST['MGEtype']) : null;
        $tout          = isset($_POST['tout']) ? strip_tags($_POST['tout']) : null;
        $output        = isset($_POST['output']) ? strip_tags($_POST['output']) : null;
		
	/* Tout d'abord test des champs entrés, on quitte si un champ est inf�rieur � 3 caract�res
	ou un champ numerique non numérique */
	/* if (!$name && !$accession && !$family && !$grp && !$host && !$tout){			// 1 de ces champs est obligatoire
			die(erreur_ess_field());		
		}
		if ((strlen($name) < 3) and ($name)) {
			die(erreur_car("name field",0));
			}
		 if ((strlen($accession) < 3) and ($accession)) {
		 die(erreur_car("Accession Number field",0));
			}
		 if ((strlen($family) < 3) and ($family)) {
		 die(erreur_car("family field",0));
			}
		 if ((strlen($grp) < 3) and ($grp)) {
		 die(erreur_car("group field",0));
			}
		 if ((strlen($host) < 3) and ($host)) {
		 die(erreur_car("host field",0));
			}		
		 if ((strlen($ir_r) < 3) and ($ir_r)) {
		 die(erreur_car("Right End field",0));
			}
		 if ((strlen($ir_l) < 3) and ($ir_l)) {
		 die(erreur_car("Left End field",0));
			}
		 if ((strlen($dr) < 1) and ($dr)) {
		 die(erreur_car("DR field",1));
			}
		 if ((strlen($ir) < 1) and ($ir)) {
		 die(erreur_car("IR field",1));
			}
		 if ((strlen($orfSize) < 3) and ($orfSize)) {
		 die(erreur_car("ORF field",1));
			}
		 if ((strlen($orfFunction) < 3) and ($orfFunction)) {
		 die(erreur_car("ORF Function field",1));
			}
		 if ((strlen($length) < 3) and ($length)) {
		 die(erreur_car("Length field",1));
			}
		 if(isset($mge) && (!in_array($mge,array('IS','MITE','MIC','tIS','transposon','all')))){
			die(erreur_car("MGE type",1));
		 }
		 if(isset($frameshift) && (!in_array($frameshift,array(0,1,2)))){
			die(erreur_car("Frameshift field",1));
		 }
	
		 if ((strlen($tout) < 3 || $tout=='is ') and ($tout)) {
		 die(erreur_car("search in all fields",0));
			}  */

// Vérification qu'au moins un champ obligatoire est rempli
	if (
		empty($name) &&
		empty($accession) &&
		empty($family) &&
		empty($grp) &&
		empty($host) &&
		empty($tout)
	) {
		die(erreur_ess_field());
	}

	// Vérification de la longueur minimale pour chaque champ
		if ($name && strlen($name) < 3) {
    		die(erreur_car("name field", 0));
		}
		if ($accession && strlen($accession) < 3) {
    		die(erreur_car("Accession Number field", 0));
		}
		if ($family && strlen($family) < 3) {
    		die(erreur_car("family field", 0));
		}
		if ($grp && strlen($grp) < 3) {
    		die(erreur_car("group field", 0));
		}
		if ($host && strlen($host) < 3) {
			die(erreur_car("host field", 0));
		}
		if ($ir_r && strlen($ir_r) < 3) {
			die(erreur_car("Right End field", 0));
		}
		if ($ir_l && strlen($ir_l) < 3) {
			die(erreur_car("Left End field", 0));
		}
		if ($dr && strlen($dr) < 1) {
			die(erreur_car("DR field", 1));
		}
		if ($ir && strlen($ir) < 1) {
			die(erreur_car("IR field", 1));
		}
		if ($orfSize && strlen($orfSize) < 3) {
			die(erreur_car("ORF field", 1));
		}
		if ($orfFunction && strlen($orfFunction) < 3) {
			die(erreur_car("ORF Function field", 1));
		}
		if ($length && strlen($length) < 3) {
			die(erreur_car("Length field", 1));
		}

		// Vérification des valeurs autorisées pour $mge
		$validMGE = ['IS', 'MITE', 'MIC', 'tIS', 'transposon', 'all'];
		if ($mge && !in_array($mge, $validMGE)) {
			die(erreur_car("MGE type", 1));
		}

		// Vérification des valeurs autorisées pour $frameshift
		$validFrameshift = [0, 1, 2];
		if ($frameshift !== null && !in_array($frameshift, $validFrameshift)) {
			die(erreur_car("Frameshift field", 1));
		}

		// Vérification de la longueur minimale pour $tout
		if ($tout && (strlen($tout) < 3 || $tout === 'is ')) {
			die(erreur_car("search in all fields", 0));
		}


		/* Choix des champs à afficher */
		switch ($output) {
			case 0: 
				$champs="`ET_name`,`ID_ET`,`ET_Length`,`ET_Accession_number`,`ID_iso`,`Synonyme`, `Group_Name`,`Family_Name`,`Direct_Repeat_Length`,`IR_Length`, `ORF_Begin`, `ORF_End`, `ORF_Length_AA` ";
				break;
			case 1: 
				$champs="`ET_name`,`ID_ET`, `Group_Name`,`Family_Name` ";
				break;
			case 2: 
				$champs="`ET_name`,`ID_ET`, `Group_Name`,`Family_Name` ";
				break;
			case 3: 
				$champs="`ET_name`,`ID_ET`, `Group_Name`,`Family_Name`,`Left_End`,`Rigth_End` ";
				break;
			case 4: 
				$champs="`ET_name`,`ID_ET`, `Group_Name`,`Family_Name`,`LE`,`RE` ";
				break;
			default:
				$champs="`ET_name`,`ID_ET`,`ET_Length`,`ET_Accession_number`,`ID_iso`,`Synonyme`, `Group_Name`,`Family_Name`,`Direct_Repeat_Length`,`IR_Length`, `ORF_Begin`, `ORF_End`, `ORF_Length_AA` ";
	
		}
				
		/* On traite le cas ou on veux faire une recherche sur tous les champs 
		ATTENTION references necessite les `` (mot reserve?)*/
		
		if ($tout) {
			$reqfinal = "SELECT $champs FROM `element_transposable` ET
				JOIN `family` FAM
				ON `Family_ID_Family` = `ID_Family`
				LEFT JOIN `groups` GRP
				ON `Groups_ID_Groups` = `ID_Groups`
				LEFT JOIN `is_ends` ISE
				ON `ID_ET` = ISE.`Element_transposable_ID_ET`
				LEFT JOIN `et_insertion_site` ETIS
				ON `ID_ET` = ETIS.`Element_transposable_ID_ET`
				LEFT JOIN `orf`
				ON `ID_ET` = orf.`Element_transposable_ID_ET`
				LEFT JOIN `synonyme` SYN
				ON `ID_ET` = SYN.`Element_transposable_ID_ET`
				LEFT JOIN `element_transposable_has_host` ETHH
				ON `ID_ET` = ETHH.`Element_transposable_ID_ET`
				WHERE (ET_name like '%$tout%') or (Synonyme like '%$tout%') 
				or (Family_Name like '%$tout%') or (Group_Name like '%$tout%')
				or (ET_Reference like '%$tout%') or (ET_Accession_number like '%$tout%') or (ET_comments like '%$tout%')
				or (Left_End like '%$tout%') or (LE like '%$tout%') or (Rigth_End like '%$tout%') or (RE like '%$tout%')
				or (ISE.Ends_comments like '%$tout%') or (ORF_Comment like '%$tout%')
				or (ETHH.`Host_ID_host` IN (SELECT `ID_host` FROM `host` WHERE `Host` like '%$tout%'))
				GROUP BY `ET_name`";
	
			}else{
				
		/* on r�cup�re les diff�rents morceaux de requettes et on construit la premiere partie*/
	# Toujours les registers globals off
	 
				$namecond=strip_tags($_POST['namecond']);
				$accessioncond=strip_tags($_POST['accessioncond']);
				$familycond=strip_tags($_POST['familycond']);
				$grpcond=strip_tags($_POST['grpcond']);
				$ir_lcond=strip_tags($_POST['ir_lcond']);
				$ir_rcond=strip_tags($_POST['ir_rcond']);
				$hostcond=strip_tags($_POST['hostcond']);
				$ircond=strip_tags($_POST['ircond']);
				$drcond=strip_tags($_POST['drcond']);
				$orfsizecond=strip_tags($_POST['orfsizecond']);
				$orffunctioncond=strip_tags($_POST['orffunctioncond']);
				$lengthcond=strip_tags($_POST['lengthcond']);
	
				if (($partname=ecrit_requette($namecond,$name))==""){$partname="LIKE '%%'";};
				$partaccession=ecrit_requette($accessioncond,$accession);
				$partfamily=ecrit_requette($familycond,$family);
				$partgrp=ecrit_requette($grpcond,$grp);
				$partir_l=ecrit_requette($ir_lcond,$ir_l);
				$partir_r=ecrit_requette($ir_rcond,$ir_r);
				$parthost=ecrit_requette($hostcond,$host);
				$partir=ecrit_requette($ircond,$ir);
				$partdr=ecrit_requette($drcond,$dr);
				$partorfsize=ecrit_requette($orfsizecond,$orfSize);
				$partorffunction=ecrit_requette($orffunctioncond,$orfFunction);
				$partlength=ecrit_requette($lengthcond,$length);

				$mge_num=req_mgeType($mge);
				
		/* On construit la requete finale avec des and */
				$req="(`ET_name` $partname OR `Synonyme` $partname)";
				if ($partaccession) {$req = "$req and `ET_Accession_number` $partaccession";};
				if ($partfamily) {$req = "$req and `Family_Name` $partfamily";};
				if ($partgrp) {$req="$req and `Group_Name` $partgrp";};
				if ($partir_l) {$req = "$req and (`Left_End` $partir_l OR `LE` $partir_l)";};
				if ($partir_r) {$req = "$req and (`Rigth_End` $partir_r OR `RE` $partir_r)";};
				if ($partir) {$req = "$req and `IR_Length` $partir";};
				if ($partdr) {$req = "$req and `Direct_Repeat_Length` $partdr";};
				if ($partorfsize) {$req="$req and `ORF_Length_AA` $partorfsize";};
				if ($partlength) {$req = "$req and `ET_Length` $partlength";};
				if ($mge && $mge_num != 0) {$req = "$req and `type_element_transposable_ID_Type_ET`=$mge_num";};
				if ($frameshift==1) {$req = "$req and `recode` = \"frameshift\"";};
				if ($frameshift==0) {$req = "$req and (`recode` != \"frameshift\" OR `recode` IS NULL)";};
				if ($parthost) {$req = "$req and ETHH.`Host_ID_host` IN (SELECT `ID_host` FROM `host` WHERE `Host` $parthost)";};

		/* La requette sql finale */
				$reqfinal = "SELECT $champs 
				  FROM `element_transposable` ET
				  JOIN `family` FAM
				  ON `Family_ID_Family` = `ID_Family`
				  LEFT JOIN `groups` GRP
				  ON `Groups_ID_Groups` = `ID_Groups`
				  LEFT JOIN `is_ends` ISE
				  ON `ID_ET` = ISE.`Element_transposable_ID_ET`
				  LEFT JOIN `et_insertion_site` ETIS
				  ON `ID_ET` = ETIS.`Element_transposable_ID_ET`
				  LEFT JOIN `orf`
				  ON `ID_ET` = orf.`Element_transposable_ID_ET`
				  LEFT JOIN `synonyme` SYN
				  ON `ID_ET` = SYN.`Element_transposable_ID_ET`
				  LEFT JOIN `element_transposable_has_host` ETHH
				  ON `ID_ET` = ETHH.`Element_transposable_ID_ET`
				  WHERE $req GROUP BY `ET_name`" ;
				 }
		$_SESSION['requete']=$reqfinal;
		$_SESSION['affichage']=$output;
	}
	/* Le résultat trier sur la colonne sélectionnée ou name par défaut */
			/* Choix de la colonne de tri */
		$tri_autorises = array('ET_name','Family_Name','Group_Name','Host','ET_Length','Direct_Repeat_Length');
	
		$ordre = (!empty($_GET['tri'])) ? strip_tags($_GET['tri']) : "";
		$tri = ( !empty($ordre) &&  in_array($ordre,$tri_autorises,true)) ? $ordre : "`ET_name`";

	$marequete=$_SESSION['requete'];
	$reqtrier = $marequete." ORDER BY ".$tri ;
	/* Execution de la requette et si r�sultat, alors on continue */
	$cnx=connexion("ISfinder");
	$result = execute_sql_new($cnx,$reqtrier);
	$nombre = mysqli_num_rows($result);
	
	If ($nombre > 0) {
		$nom=sort_link('Name','ET_name');
		$family=sort_link('Family','Family_Name');
		$group=sort_link('Group','Group_Name');
//		$origin=sort_link('Origin','Host');
		$length=sort_link('Length','ET_Length');
		$DR=sort_link('DR','Direct_Repeat_Length');

		print "<h3>Result of your query: ".$nombre."</h3>";
		print "<table>";
// echo $reqfinal;
		$output = $_SESSION['affichage'];
		if ($output==0){
			print "<table class=\"result\"><tr><th>N°</th><th>$nom</th><th>$family</th><th>$group</th>";
			print "<th>Synomyns</th><th>Iso</th><th>Origin</th><th>$length</th>";
			print "<th>IR</th><th>$DR</th><th>ORF</th><th>Accession <br>Number</th></tr>\n";
			affiche_result($result,$output);
		}elseif ($output==1){
			print "<table class=\"result\"><tr><th>N°</th><th>$nom</th><th>$family</th><th>$group</th>";
			print "<th>Origin</th><th>Host</th>";
			print "</tr>\n";
			affiche_result($result,$output);
		}elseif ($output==2){
			print "<table class=\"result\"><tr><th>N°</th><th>$nom</th><th>$family</th><th>$group</th>";
			print "<th>Insertion Site</th>";
			print "</tr>\n";
			affiche_result($result,$output);
		}elseif ($output==3){
			print "<table class=\"result\"><tr><th>N°</th><th>$nom</th><th>$family</th><th>$group</th>";
			print "<th>Left end</th><th>Right end</th>";
			print "</tr>\n";
			affiche_result($result,$output);
		}elseif ($output==4){
			print "<table class=\"result\"><tr><th>N°</th><th>$nom</th><th>$family</th><th>$group</th>";
			print "<th>Left end</th><th>Right end</th>";
			print "</tr>\n";
			affiche_result($result,$output);
		}
		
		print "</table>";
	} else {									/* Pas de r�sultat */
		print "No result in the database </h3>";
	}	
	mysqli_close($cnx);
}else{																	//Fin form soumis
	header("Location: /search.php");
	exit();
}
 
?>
	</section>
</article>
<?php include('../include/footer.inc.php'); ?>

</div> <!-- Fin du div page -->
</body>
</html>
