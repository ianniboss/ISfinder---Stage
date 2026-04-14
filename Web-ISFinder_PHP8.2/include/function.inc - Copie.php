<?php
// Vérification du Referer pour les variables passées en POST
 if(!empty($_SERVER['HTTP_REFERER'])
   && substr($_SERVER['HTTP_REFERER'], 8, strlen($_SERVER['SERVER_NAME'])) != $_SERVER['SERVER_NAME']){
     $_POST = array();
 }
  //_____________ Adresses mails _______________
function addressMail($prenomDest,$nomDest,$domaineDest){
	$domaine = (!empty($domaineDest)) ? $domaineDest : "univ-tlse3.fr";
	$nom = (!empty($prenomDest)) ? $prenomDest.".".$nomDest : $nomDest ;
	$adresse = $nom."@".$domaine ;
	return $adresse ;
}
//_______________________________connexion_________________________________

function connexion($host,$user,$bdd,$mdp)
{
$connecte=mysqli_connect($host,$user,$mdp,$bdd) or die("Server connection error");
mysqli_select_db($connecte,$bdd) or die("connection error to the database".$bdd);
mysqli_query($connecte,"SET NAMES 'utf8'");
return $connecte;
}
//___________________________________erreur_________________________________
function erreur_sql($res,$requete)
{
if ($res === false){
	echo "Sorry, request invalidates.<br> Please contact the administrator of the site<br>";
	echo "<a href='javascript:history.go(-1)'>Back</a><br>";

//		Indication du type d'erreur pour débogger
		$erreur_no = mysql_errno();
		$erreur_txt = mysql_error();
		if ($erreur_no == "1062") {
			$error_str = explode(" ", $erreur_txt);
			echo "<h3>Error, this IS name: $error_str[2] was already submitted.<br></h3>";
			echo "Please contact the <a href='mailto: ".addressMail('Patricia','Siguier','')."'> Database Manager</a>.";
			exit;
			} else {
        echo "SQL ERROR :".$requete."<br>\n";
        echo "ERROR :".mysql_errno().":".mysql_error()."<br>\n";

		}

    exit;
    }
}
//______________________erreur formulaire recherche____________________________________
function erreur_car($chaine,$erreur) {
	if ($erreur == 0){
		echo "<h3>Query failed, a minimum of 3 characters is required for \" $chaine \".</h3>";
	}else{
		echo "<h3>Query failed.</h3>";
	}
	
	echo "<a href='javascript:history.go(-1)'>Back</a><br>";
	}
//______________________erreur formulaire recherche (1 essential field est requis)____________________________________
function erreur_ess_field() {
	echo "<h3>Query failed, at least 1 essential field is required.</h3>";
	echo "<a href='javascript:history.go(-1)'>Back</a><br>";
	}


//______________________erreur formulaire soumission (champ vide) ____________________________________
function erreur_sub($chaine,$mess) {
	if ($mess == 0){
		echo "<h3>Submission failed, \" $chaine \"  field is not valid.</h3>";
	}else{
		echo "<h3>Submission failed, \" $chaine \"  field is required.</h3>";
	}
	echo "<a href='javascript:history.go(-1)'>Back</a><br>";
	}
//______________________erreur formulaire soumission (champ non valide) ____________________________________
function erreur_val($chaine, $cause) {
	echo "<h3>Submission failed, \" $chaine \"  field is not valid.<br>$cause</h3>";
	echo "<a href='javascript:history.go(-1)'>Back</a><br>";
}

function erreur_nom_is() {
	echo "<h3>Submission failed,this is name is not valid</h3>";
	echo "<a href='/is/is_name_attrib.html'>Click here to go to the IS Name Attribution form.</a><br>";
}
//________________________executer requete____________________________________
function execute_sql($req){
	$connect=connexion("localhost","isfinder","ISfinder","mCjMPEJ_16");
	$res=mysqli_query($connect,$req);
	if($res===false){
		erreur_sql($res,$req);
		exit;
	}
   return $res;
}
//________________________executer requete version pour PHP7 du 28042022____________________________________
function execute_sql_new($connect,$req){
//	$connect=connexion("localhost","isfinder","ISfinder","mCjMPEJ_16");
	$res=mysqli_query($connect,$req);
	if($res===false){
		erreur_sql($res,$req);
		exit;
	}
   return $res;
}
//_______________________________ Remplacement de mysql_result qui n'a pas d'equivalent en php7 _________________________________________
function mysqli_result($res,$row=0,$col=0){ 
    $numrows = mysqli_num_rows($res); 
    if ($numrows && $row <= ($numrows-1) && $row >=0){
        mysqli_data_seek($res,$row);
        $resrow = (is_numeric($col)) ? mysqli_fetch_row($res) : mysqli_fetch_assoc($res);
        if (isset($resrow[$col])){
            return $resrow[$col];
        }
    }
    return false;
}

//_________ecriture de la requette en fonction des parametres (end, begin...)____
function ecrit_requette($condition,$variable) {
		if (!$variable) {
			$partie = "";
			} else {
			  if ($condition == "contains") {
				  $partie = "like \"%$variable%\"";
				  } else if ($condition == "begin") {
				  $partie = "like '$variable%'";
				  } else if ($condition == "end") {
				  $partie = "like '%$variable'";
				  } else if ($condition == "egal") {
				  $partie = "= \"$variable\"";
				  } else if ($condition == "inf") {
				  $partie = "<= \"$variable\"";
				  } else if ($condition == "sup") {
				  $partie = ">= \"$variable\"";
			  } else {
				  die("Erreur fatale, requete non conforme!");
			  }
			  return $partie;
			}
}
//________Requête pour récupérer la valeur numérique de MGE Type _____________
function req_mgeType($mge) {
	if ($mge == "all"){
		$mge_numerique = 0 ;
	}else{
		$temp = connexion("localhost","isfinder","ISfinder","mCjMPEJ_16");	
	
		$req_mge = "SELECT `ID_Type_ET` FROM `type_element_transposable` WHERE `Type_ET` LIKE '$mge'";
		$result = execute_sql_new($temp,$req_mge);	
		$mge_numerique= mysqli_result($result,0);
		mysqli_close($temp) ;
	}
	return $mge_numerique;
}
//______formatage de l'affichage d'une séquence__________________________

function affiche_seq($phrase,$entete) {
		if (($phrase == "NONE") or ($phrase == "-") or !$phrase) {
			echo "<p>";
		} else {
			echo "<table border='0' width='600'><tr><th bgcolor='c2b09a'>";
			echo "<b>$entete: </b></th></tr><tr><td><tt>";
			for ($i=0;$i<strlen($phrase);$i+=60) {
				$temp = substr($phrase,$i,60);
				echo "$temp<br>";
			}
			echo "</tt><br></td></tr></table>";
		}
}
//________Pour afficher les accents sur page html sans perdre les balises html_______________
function encodaccent($chaine){
//encodage des accents ET des balises en code html
 $chaine= htmlentities($chaine);
//  remet les balises html (au final, seuls les accents sont modifiés) 
 $chaine= htmlspecialchars_decode($chaine);
 return $chaine;
 }
?>