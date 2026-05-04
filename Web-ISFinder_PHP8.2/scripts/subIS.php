<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
<title>Result of your query</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> 
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
	<section> 

<?php 
Include_once ("../include/function.inc.php");
Include_once ("../include/function_sub.inc.php");


if (empty($_POST)){ $_SESSION["error"] = "Problem with the data. \n Please contact the webadmin";}

$form_soumis = (!empty($_POST['Onsubmit'])) ? htmlspecialchars($_POST['Onsubmit']) : Null ;
if($form_soumis == "Submit"){
/* initialisation de la variable des erreurs éventuelles si tt les variables du formulaire ne correspondent aux critères */
	$_SESSION["error"] = "";

/* Pour les register globals off - On récupère les champs soumis tout en supprimant les balises HTML*/
	foreach($_POST as $champ=>$valeur){		// on remplit $_SESSION ET une variable portant le nom du champ 
		$$champ = $_SESSION[$champ] =  trim(stripslashes(htmlentities($valeur))) ;		// pour ne pas écrire tt le tps $_SESSION[]
		}

/* On teste les champs entrés et s'il y a des erreurs on remplit $_SESSION["error"]  */
	$_SESSION["error"] .= (empty($Fname)||(preg_match("/[^a-zA-Z- \éèêçà'ñ]/",$Fname))) ? "First name correct is required.</br>" : "";
	$_SESSION["error"] .= (empty($Lname)|| preg_match("/[^a-zA-Z- \éèêçà'ñ]/",$Lname)) ? "Last name correct is required.</br>" : "";
	$_SESSION["error"] .= (empty($institution)||strlen($institution)<2) ? "Field institution is required.</br>" : "";
	$_SESSION["error"] .= (empty($country)||strlen($country)<2) ? "Field country is required.</br>" : "";
	$_SESSION["error"] .= (empty($courriel)) ? "e-mail address is required.</br>" : "";
	$_SESSION["error"] .= (empty($isname)||strlen($isname)<5 ||preg_match("/[^a-zA-Z0-9_]/",$isname)) ? "IS name is required with min 5 char.</br>" : "";
	$_SESSION["error"] .= (empty($origin)) ? "Field origin is required.</br>" : "";

	if(filter_var($courriel, FILTER_VALIDATE_EMAIL)===FALSE){
		$_SESSION["error"] .= "l'adresse e-mail saisie n'est pas valide.</br>";
		}
		
/* Pour les séquences DNA et Prot élimination des blancs et retour charriots */		
	$car_elim = array("\n", "\r", " ");
	$dna_seq = str_replace($car_elim,"",$dna_seq);			
	$_SESSION["error"] .= (empty($dna_seq)) ? "DNA sequence is required.</br>" : "";
	$_SESSION["error"] .= (estdna($dna_seq)!= true) ? "Only A, T, C, G and N characters are allowed.</br>" : "";
	
	if(isset($nb_orf) && $nb_orf < 16){
		for($i=1 ; $i<= $nb_orf ; $i++){
			$var_dynamique = 'orf'.$i.'_seq' ;
			$$var_dynamique = str_replace($car_elim,"",$$var_dynamique);			
			$_SESSION["error"] .= (isset($$var_dynamique) && estprot($$var_dynamique)!= true) ? "Only amino acid and * are allowed.</br>" : "";
		}
	}

/* Si tt est ok ($error est vide) on se connecte à la base */		
	if($_SESSION["error"]===""){
/*		foreach ($_SESSION as $elt_session => $var_session){			// Pour afficher les variables sur la page web 
			echo $elt_session." = ".$var_session."<br>" ;	
		}*/
		
// Connexion à la  base ISsubmit
	    $cnx=connexion('localhost', 'ibinsyahrulazlan', 'ISsubmit', 'yNCNLvH9vwX^f~$i');			// pour ISfinder-test
		

// Insertion des infos du Submiters et récupération du ID_Submiter
		$sql_sub="INSERT INTO submiters(Firstname, Middlename, Lastname, Institution, Department, Address, Code, Country, Mail, Phone)" ;
		$sql_sub.=" VALUES ('".mysqli_real_escape_string($cnx,$Fname)."','".mysqli_real_escape_string($cnx,$Mname)."','".mysqli_real_escape_string($cnx,$Lname)."','".mysqli_real_escape_string($cnx,$institution)."','".mysqli_real_escape_string($cnx,$department)."','".mysqli_real_escape_string($cnx,$address)."','".mysqli_real_escape_string($cnx,$postCode)."', '".mysqli_real_escape_string($cnx,$country)."', '".$courriel."', '".mysqli_real_escape_string($cnx,$tel)."')";
		$res=execute_sql_new($cnx,$sql_sub);
		
		$ID_Submiter = mysqli_insert_id($cnx);
		
// Insertion des infos concernant l'IS et récupération du ID_ET
		// Table element_transposable
		$sql_sub="INSERT INTO element_transposable(Groups_ID_Groups, Family_ID_Family, type_element_transposable_ID_Type_ET, ET_Accession_number, ET_name, ET_Length, ET_DNA_Sequence, Transposition, ET_comments, ET_Reference)" ;
		$sql_sub.=" VALUES ('".mysqli_real_escape_string($cnx,$group)."','".mysqli_real_escape_string($cnx,$family)."', '".mysqli_real_escape_string($cnx,$MGEtype)."','".mysqli_real_escape_string($cnx,$numAcc)."','".mysqli_real_escape_string($cnx,$isname)."','".intval($islength)."','".$dna_seq."','".mysqli_real_escape_string($cnx,$transposition)."', '".mysqli_real_escape_string($cnx,$comments)."', '".mysqli_real_escape_string($cnx,$references)."')";
		$res=execute_sql_new($cnx,$sql_sub);
		$ID_ET = mysqli_insert_id($cnx);

		// Table is_ends (calcul des extrémités)
		$Left_End = is_ends($dna_seq,"left");
		$Rigth_End = is_ends($dna_seq,"right");
		$LE = is_ends($dna_seq,"LE");
		$RE = is_ends($dna_seq,"RE");
		
		$sql_sub="INSERT INTO is_ends(Element_transposable_ID_ET, Left_End, Rigth_End, IR_Length, LE, RE, Ends_comments)" ;
		$sql_sub.=" VALUES ('".$ID_ET."','".mysqli_real_escape_string($cnx,$Left_End)."','".mysqli_real_escape_string($cnx,$Rigth_End)."','".mysqli_real_escape_string($cnx,$irlength)."','".mysqli_real_escape_string($cnx,$LE)."','".mysqli_real_escape_string($cnx,$RE)."','".mysqli_real_escape_string($cnx,$Ends_comments)."')";
		$res=execute_sql_new($cnx,$sql_sub);
		
		// Table et_insertion_site
/*		if ($directRE || $DRLE || $leftFL || $rightFL){
			$sql_sub="INSERT INTO et_insertion_site(Element_transposable_ID_ET, Direct_Repeat, Direct_Repeat_Length, DR_Left_Flank, DR_Rigth_Flank)" ;
			$sql_sub.=" VALUES ('".$ID_ET."','".$directRE."','".$DRLE."','".$leftFL."','".$rightFL."')";
			$res=execute_sql($sql_sub);
		}
*/
		// Table et_insertion_site
		$nb_site = (isset ($_SESSION['nb_site'])) ? $_SESSION['nb_site'] : 0;
		$ins_site = "Nb Insertion site: ".$nb_site."\n" ; 		// On remplit une variable pour renseigner le mail de confirmation envoyé au submiter
		for ($j=0 ; $j < $nb_site ; $j++){
			$id_site = (!empty($_SESSION[$j.'ID_ET_Insertion_Site'])) ? ($_SESSION[$j.'ID_ET_Insertion_Site']) : 0;
			$Direct_Repeat = $_SESSION[$j.'Direct_Repeat'];
			$Direct_Repeat_Length = $_SESSION[$j.'Direct_Repeat_Length'];
			$DR_Left_Flank = $_SESSION[$j.'DR_Left_Flank'];
			$DR_Rigth_Flank = $_SESSION[$j.'DR_Rigth_Flank'];
/*			$LE_CS = $_SESSION[$j.'LE_CS'];
			$RE_CS = $_SESSION[$j.'RE_CS'];
			$LE_CS_Left_Flank = $_SESSION[$j.'LE_CS_Left_Flank'];
			$RE_CS_Rigth_Flank = $_SESSION[$j.'RE_CS_Rigth_Flank'];
			$sql_sub="INSERT INTO et_insertion_site(Element_transposable_ID_ET, Direct_Repeat, Direct_Repeat_Length, DR_Left_Flank, DR_Rigth_Flank, LE_CS, RE_CS, LE_CS_Left_Flank, RE_CS_Rigth_Flank)" ;
			$sql_sub.=" VALUES ('".$ID_ET."','".$Direct_Repeat."','".$Direct_Repeat_Length."','".$DR_Left_Flank."','".$DR_Rigth_Flank."','".$LE_CS."','".$RE_CS."','".$LE_CS_Left_Flank."','".$RE_CS_Rigth_Flank."')";
			$res=execute_sql($sql_sub);
		}
*/
			if (!empty($Direct_Repeat) || !empty($Direct_Repeat_Length) || !empty($DR_Left_Flank) || !empty($DR_Rigth_Flank)){
				$sql_sub="INSERT INTO et_insertion_site(Element_transposable_ID_ET, Direct_Repeat, Direct_Repeat_Length, DR_Left_Flank, DR_Rigth_Flank)" ;
				$sql_sub.=" VALUES ('".$ID_ET."','".mysqli_real_escape_string($cnx,$Direct_Repeat)."','".$Direct_Repeat_Length."','".mysqli_real_escape_string($cnx,$DR_Left_Flank)."','".mysqli_real_escape_string($cnx,$DR_Rigth_Flank)."')";
				$res=execute_sql_new($cnx,$sql_sub);
						// On complète la variable pour renseigner le mail de confirmation envoyé au submiter
				$ins_site .= "Direct Repeat: ".$Direct_Repeat.", DR length: ".$Direct_Repeat_Length.", Left flank: ".$DR_Left_Flank.", Right flank: ".$DR_Rigth_Flank."\n" ;
			}
		}

		// Table parent_link
		$parents = explode(" ",$related_elt);
		foreach ($parents as $parent) {
			if (preg_match('/^[a-zA-Z]/',$parent)){		// ne pas traiter les lignes vides ou commençant par un nombre
				$sql_sub="INSERT INTO parent_link(Element_transposable_ID_ET, Element_transposable_parent_ID_ET)" ;
				$sql_sub.=" VALUES ('".intval($ID_ET)."','".mysqli_real_escape_string($cnx,$parent)."')";
				$res=execute_sql_new($cnx,$sql_sub);
			}
		}

		// Table host et element_transposable_has_host
		$sql_sub="INSERT INTO host(Host) VALUES ('".mysqli_real_escape_string($cnx,$origin)."')";
		$res=execute_sql_new($cnx,$sql_sub);
		$ID_Host = mysqli_insert_id($cnx);
		$sql_sub="INSERT INTO element_transposable_has_host(Element_transposable_ID_ET, Host_ID_host, Origin) VALUES ('".$ID_ET."','".$ID_Host."','1')";
		$res=execute_sql_new($cnx,$sql_sub);
		
		$liste_hosts = explode("\n",$hosts);
		foreach ($liste_hosts as $host) {
			if (preg_match('/^[a-zA-Z]/',$host)){		// ne pas traiter les lignes vides ou commençant par un nombre
				$sql_sub="INSERT INTO host(Host) VALUES ('".mysqli_real_escape_string($cnx,$host)."')";
				$res=execute_sql_new($cnx,$sql_sub);
				$ID_Host = mysqli_insert_id($cnx);
				$sql_sub="INSERT INTO element_transposable_has_host(Element_transposable_ID_ET, Host_ID_host, Origin) VALUES ('".$ID_ET."','".$ID_Host."','0')";
				$res=execute_sql_new($cnx,$sql_sub);
			}
		}

		// Table orf
		$orf = "Nb Orf: ".$nb_orf."\n" ; 		// On remplit une variable pour renseigner le mail de confirmation envoyé au submiter
		for($i=1 ; ($nb_orf!=0 && $i<= $nb_orf) ; $i++){
		$sql_sub="INSERT INTO orf(Element_transposable_ID_ET, Tnp_chemestry_ID_Tnp_chemestry, AG_description_ID_AG_description, PG_function_ID_PG_function, ORF_Begin, ORF_End, ORF_Sequence, ORF_rank, ORF_Strand, ORF_Comment, ORF_Length_DNA, ORF_Length_AA, ORF_Frameshift, ORF_function, PG_annotation)" ;
						// Utilisation des variables dynamiques pour générer
						// le nom des variables en fonctin de $i
		$var_dyn_chem = 'orf'.$i.'_chem' ;
		$var_dyn_chemAG = 'orf'.$i.'_chemAG' ;
		$var_dyn_chemPG = 'orf'.$i.'_chemPG' ;
		$var_dyn_begin = 'orf'.$i.'_begin' ;
		$var_dyn_end = 'orf'.$i.'_end' ;
		$var_dyn_seq = 'orf'.$i.'_seq' ;
		$var_dyn_strand = 'orf'.$i.'_strand' ;
		$var_dyn_comment = 'orf'.$i.'_Comment' ;
		$var_dyn_lengthbp = 'orf'.$i.'_lengthbp' ;
		$var_dyn_lengthaa = 'orf'.$i.'_lengthaa' ;
		$var_dyn_frameshift = 'orf'.$i.'_frameshift' ;
		$var_dyn_function = 'orf'.$i.'_function' ;
		 	$orf_function = ($$var_dyn_function < 1 ) ? 'NULL' : $$var_dyn_function;
		$var_dyn_annotation = 'orf'.$i.'_annotation' ;
								// Ces 3 champs doivent être valide ou bien null car table orf avec contrainte
					$chem = ($$var_dyn_chem < 1 || $$var_dyn_chem > 6) ? 'NULL' : $$var_dyn_chem;
					$chemAG = ($$var_dyn_chemAG < 1 || $$var_dyn_chemAG > 7) ? 'NULL' : $$var_dyn_chemAG;
					$chemPG = ($$var_dyn_chemPG < 1 || $$var_dyn_chemPG > 2) ? 'NULL' : $$var_dyn_chemPG;
					
					// Pour nettoyer les variables non utilisées si changement de choix sur le nbr d'ORF
		switch ($orf_function){
			case 'Tnp': $chemAG = 'NULL' ;
						$chemPG = 'NULL' ;
						break;
			case 'AG':	$chem = 'NULL' ;
						$chemPG = 'NULL' ;
						break;
			case 'PG':	$chem = 'NULL' ;
						$chemAG = 'NULL' ;
						break;
		}
					// règle pb de $orf_function dans l'écriture de la requête Sql: Qd Null, il ne faut pas les '' mais si valeur il faut 'valeur' avec les apostrophes
		$orf_function_ecriture = ($orf_function == 'NULL') ? $orf_function : "'".$orf_function."'" ;
		$sql_sub.=" VALUES ('".$ID_ET."',$chem, $chemAG,$chemPG,'".intval($$var_dyn_begin)."','".intval($$var_dyn_end)."','".$$var_dyn_seq."','".$i."', '".$$var_dyn_strand."', '".mysqli_real_escape_string($cnx,$$var_dyn_comment)."', '".intval($$var_dyn_lengthbp)."', '".intval($$var_dyn_lengthaa)."', '".$$var_dyn_frameshift."', $orf_function_ecriture, '".mysqli_real_escape_string($cnx,$$var_dyn_annotation)."')";

		if ($$var_dyn_chem !='' || $$var_dyn_chemAG!='' || $$var_dyn_chemPG!='' || $$var_dyn_begin!='' || $$var_dyn_end!='' || $$var_dyn_seq!='' || $$var_dyn_strand!='' || $$var_dyn_comment!='' || $$var_dyn_lengthbp!='' || $$var_dyn_lengthaa!='' || $$var_dyn_frameshift!='' || $$var_dyn_function!=''){
			$res=execute_sql_new($cnx,$sql_sub);
									// On complète la variable pour renseigner le mail de confirmation envoyé au submiter
			$orf .= "ORF Sequence: ".$$var_dyn_seq.", ORF_rank: ".$i.", ORF_Length_AA: ".$$var_dyn_lengthaa.", ORF function: ".$orf_function."\n" ;

		}	// Fin du if
		}	// Fin du FOR

		// Table submission
		$date_sub = date("Y-m-d");
		$sql_sub="INSERT INTO submission(Submiters_ID_Submiter, Element_transposable_ID_ET, Submission_date) VALUES ('".intval($ID_Submiter)."','".$ID_ET."','".mysqli_real_escape_string($cnx,$date_sub)."')";
		$res=execute_sql_new($cnx,$sql_sub);
		
  // Fermeture de la connexion
    	mysqli_close($cnx);
		

	// Envoi du mail récapitulatif de soumission, au submitter, Patricia et Edith
	$signature = "\r\n\r\nRegards\r\nPatricia Siguier\r\n\r\n------------------\r\nPatricia Siguier\r\n";
	$signature.= "Curator of ISFinder : https://www-is.biotoul.fr/\r\n------------------\r\n";
	$cc = addressMail("Patricia","Siguier","");
	$headers = "From: ".addressMail('','cbi.webadmin-isfinder','')."\r\n";
	$headers .= "CC: ".$cc."\r\n";
	$headers .= "Content-Type: text/plain; charset=\"utf-8\"\r\n" ;
	$headers .= "X-Mailer: PHP/ISFinder\r\n";
	
	$titre = "         Summary of your submission on ISFinder\n\n";
	
/*	foreach ($_SESSION as $elt_session => $var_session){
		if ($var_session && $elt_session != "Onsubmit" && $elt_session != "requete" && $elt_session != "comments" && $elt_session != "references" && preg_match("/^orf/", $elt_session)!=1) {
			$texte .= $elt_session." = ".$var_session."\n" ;	
		}		
	}*/
	$texte = "Name: ".$Fname." ".$Mname." ".$Lname."\n";
	$texte .= "Institution: ".$institution."\n";
	$texte .= "Department: ".$department."\n";
	$texte .= "Address: ".$address."\n";
	$texte .= "         ".$postCode."\n";
	$texte .= "Country: ".$country."\n";
	$texte .= "Email: ".$courriel."\n";
	$texte .= "Telephone: ".$tel."\n\n";
	$texte .= "Accession Number: ".$numAcc."\n";
	$texte .= "Is Name: ".$isname."\n";	
	if ($family) $texte .= "Family : ".$family ."\n";	
	if ($group) $texte .= "Group : ".$group ."\n";	
	
	$detail = "Origin = ".$origin."\n" ;
	if ($hosts) $detail .= "Host = ".$hosts."\n\n" ; 
	$detail .= "Sequence = ".$dna_seq."\n" ;
	if ($transposition) $detail .= "Transposition = ".$transposition."\n" ;
	if ($related_elt) $detail .= "Related element(s) = ".$related_elt."\n" ;
	if ($islength ) $detail .= "IS Length = ".$islength."\n" ;
	if ($irlength ) $detail .= "IR Length = ".$irlength."\n" ;
	if ($Ends_comments) $detail .= "Ends comments = ".$Ends_comments."\n" ;
	$detail .= "\n".$ins_site."\n".$orf."\n" ;
	if ($comments) $detail .= "Comments = ".$comments."\n" ;	
	if ($references) $detail .= "References = ".$references."\n" ; 
	
	$resume = $titre.$texte.$detail.$signature ;
	mail($courriel,"[ISfinder] Submission on ISfinder Database",$resume,$headers);
	
	// Envoi du mail à ISfinder team
	$to = addressMail('','cbi.webadmin-isfinder','');
	$cc = addressMail('',"mc2126","georgetown.edu").','.addressMail("Patricia","Siguier","").','.addressMail("Jacques","Mahillon","uclouvain.be");
	$headers = "From: ".addressMail('','cbi.webadmin-isfinder','')."\r\n";
	$headers .= "CC: ".$cc."\r\n";
	$headers .= "X-Mailer: PHP/ISFinder\r\n";
	$headers .= "Content-Type: text/plain; charset=\"utf-8\"\r\n" ;	
	$titre = "Nouvelle soumission IS\n";
	$resume = $titre.$texte ;
	$mail = mail($to,"[ISfinder] Nouvelle soumission d'IS",$resume,$headers);	

	echo "Your submission has been registered,<br>" ;
	echo "Thank you for your interest in our IS Database.";
	echo "<br><br>You will receive an email as soon as ". $isname." has been added to the public database.<br><br><HR>";
	echo "<a href='https://www-lmgm.biotoul.fr/' target='_top'><b>LMGM</b>&nbsp;&nbsp; | &nbsp;&nbsp;<a href='https://www-is.biotoul.fr/' target='_top'><b>IS HomePage</b></a>";

/* Si formulaire soumis mais il y a des erreurs	on retourne au formulaire sans l'effacer  */
	}else{
		header("Location: /submission.php?raz=0");
	}		

/* Si formulaire pas soumis mais avec nbr d'orf défini on remplit les variables de session et on retourne au formulaire sans l'effacer  */
}else{
	$dynModif = isset($_POST['DynModif']) ? intval($_POST['DynModif']) : 0;
	switch ($dynModif) {
//	switch (intval($_POST['DynModif'])){
			case 0: 
				header("Location: /submission.php?raz=1");
				break;

			case 1:				// Changement du nbr de site d'insertion et retour à la ficheIS
			$_SESSION['nb_site']+=1 ;
			foreach($_POST as $index=>$valeur){
				$_SESSION[$index] = trim(stripslashes(htmlentities($valeur))) ;
				}
			header("Location: /submission.php?raz=0"."#InsertionSite");
			break;
		case 2:				// Changement du nbr d'ORF et retour à la ficheIS
			$nbOrf = htmlentities($_POST['nb_orf']);
				echo "rien";

			if (isset($nbOrf) && (intval($_POST['nb_orf'])< 16) && (intval($_POST['nb_orf'])>= 0)){
				foreach($_POST as $index=>$valeur){
				$_SESSION[$index] = trim(stripslashes(htmlentities($valeur))) ; 	// trim(stripslashes(htmlentities($valeur))) 
				}
			header("Location: /submission.php?raz=0"."#Orf");
			}
			break;
		default:				// Dans les autres cas on affiche le formulaire en effaçant toutes variables de session */
			header("Location: /submission.php?raz=1");
			break;
	}
}
 
?>
	</section>
</article>
<?php include('../include/footer.inc.php'); ?>

</div> <!-- Fin du div page -->
</body>
</html>
