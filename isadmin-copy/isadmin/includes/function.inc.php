<?php
//______________________________connexions________________________________________
function connexion($bdd)
{
//		$host = "astun.ibcg.biotoul.fr";
//		$host = "192.168.12.42";		
		$host = "localhost" ;
        $user = 'ibinsyahrulazlan';
        $password = 'yNCNLvH9vwX^f~$i';
		
		$connecte=mysqli_connect($host,$user,$password,$bdd) or die(mysqli_connect_error());

		mysqli_query($connecte,"SET NAMES 'utf8'");
return $connecte;

		
/*        $connexion = @mysql_connect($host,$user,$password);
        if ($connexion) {
			if (@mysqli_select_db($bdd,$connexion)){
//				mysqli_query($connexion,"SET NAMES 'utf8'");
				mysql_set_charset('utf8',$connexion);
				return $connexion;
			}
		}*/
}
function connect($user,$bdd)		//il faudrait remplacer toutes les utilisations de connexion() par connect() plus souple
{
//        $host = $host.".ibcg.biotoul.fr";
		$host = "localhost";

//		$password = ($user == "isadmin") ? "P&G_adm2is" : "isfinder";
		$password = ($user == 'ibinsyahrulazlan') ? 'yNCNLvH9vwX^f~$i' : 'isfinder';	
        $connexion = mysqli_connect($host,$user,$password,$bdd) or die("Server connection error");
		
		mysqli_query($connexion,"SET NAMES 'utf8'");
return $connexion;
}
//___________________________________erreur_________________________________
function erreur_sql($res, $requete, $cnx)
{
    if (!$res) {
        $erreur_no = mysqli_errno($cnx);
        $erreur_txt = mysqli_error($cnx);
        echo "erreur sql :" . $requete . "<br>\n";
        echo "erreur :" . $erreur_no . ":" . $erreur_txt . "<br>\n";
        mysqli_close($cnx);
        exit;
    }
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
//________________________executer requete____________________________________

function execute_sql($connect, $req)
{
    $res = mysqli_query($connect, $req);
    if ($res === false) {
        erreur_sql($res, $req, $connect);
        exit;
    }
    return $res;
}
//_______________________________La variable est une s�quence nucl�otidique _______________
function estdna($chaine){
	return (preg_match("/[^acgtnACGTN]/", $chaine)) ? false : true;
}

//_______________________________La variable est une s�quence proteique _______________
function estprot($chaine){
	return (preg_match("/[^ACDEFGHIKLMNOPQRSTUVWY*acdefghiklmnopqrstuvwy]/", $chaine)) ? false : true;
}
//_______________________________La famille existe  _______________
function familleValide($famille){
	$tab_famille = array();
/*        global $cnx;
	if (!$cnx){
//		connect("astun","isadmin","ISfinder");
		connexion("ISfinder");
		
	}
*/
	$cnx = connexion("ISfinder");
	if (is_int($famille)){
		$req_famille = "SELECT `ID_Family` FROM `family`";
		$res_famille=execute_sql($cnx,$req_famille) ;
		while ($ligne = mysqli_fetch_assoc($res_famille)) {
			$tab_famille[] = $ligne['ID_Family'];
		}
		return (in_array($famille,$tab_famille)) ? true : false;
	}else{
		$req_famille = "SELECT `Family_Name` FROM `family`";
		$res_famille=execute_sql($cnx,$req_famille) ;
		while ($ligne = mysqli_fetch_assoc($res_famille)) {
			$tab_famille[] = $ligne['Family_Name'];
		}
		return (in_array($famille,$tab_famille)) ? true : false;
	}
	
}
//_______________________________L'�l�ment transposable ID_ET existe dans une table _______________
function existe_ID_ET($cnx,$element, $table){
	$req = "SELECT * FROM `$table` WHERE  `Element_transposable_ID_ET`= $element LIMIT 1" ;
	$res=execute_sql($cnx,$req) ;
	return (mysqli_num_rows($res) > 0) ? true : false ;
}
//______________ Calcul des extr�mit�s ____________________
function is_ends($seq,$extr) {
	switch ($extr) {
		case "left":
			$extremite = substr($seq, 0, 50) ;
			break ;
		case "right":
			$extremite = substr($seq, -50, 50) ;
					// Transformation de Right End en son r�verse-compl�mentaire
			$extremite = strrev($extremite);
			$extremite = strtr($extremite,"atcgATCG","tagcTAGC");
			break ;
		case "LE":
			$extremite = substr($seq, 0, 100) ;
			break ;
		case "RE":
			$extremite = substr($seq, -100, 100) ;
			break ;
	}
	return $extremite ;
}
//________Pour afficher les accents sur page html sans perdre les balises html_______________
function encodaccent($chaine){
//encodage des accents ET des balises en code html
 $chaine= htmlentities($chaine);
//  remet les balises html (au final, seuls les accents sont modifi�s) 
 $chaine= htmlspecialchars_decode($chaine);
 return $chaine;
 }
 //____________________________________________________________________________________
function upload(){
	$uploaddir ="/var/www/html/intranet/secure/isadmin/drawings/" ;
	header('Content-Type: image/jpg;'   );
	$_SESSION['recoding_image_error'] = "" ;
	// Si le fichier � t�l�charger est un .jpg et pas d'erreur de t�l�chargement 
	if (preg_match("/.jpg$/i", $_FILES['recoding_image']['name']) && is_uploaded_file($_FILES['recoding_image']['tmp_name'])) {
		$fichier=$_FILES['recoding_image']['tmp_name'];
		$name_fich = $_FILES['recoding_image']['name'];
		$image = $uploaddir.$name_fich ;
		move_uploaded_file($fichier,$image);
		$_SESSION['recoding_image'] = $name_fich ;
		}else{				// Sinon remplissage d'une variable de session en fonction de l'erreur
			$_SESSION['recoding_image_error'] = ($_FILES['recoding_image']['error'] == 2) ? "uploaded file exceeds the MAX_FILE_SIZE specified in the html form" : "There was a problem with your upload." ;
			switch($_FILES['recoding_image']['error']){
				case 1: //uploaded file exceeds the upload_max_filesize directive in php.ini
				  $_SESSION['recoding_image_error'] =  "The file you are trying to upload is too big.";
				  break;
				case 2: //uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form
				  $_SESSION['recoding_image_error'] =  "The file you are trying to upload is too big (Limite : 200Ko).";
				  break;
				case 3: //uploaded file was only partially uploaded
				  $_SESSION['recoding_image_error'] =  "The file you are trying upload was only partially uploaded.";
				  break;
				case 4: //no file was uploaded
				  $_SESSION['recoding_image_error'] =  "You must select an image for upload.";
				  break;
				default: //a default error, just in case!  
				  $_SESSION['recoding_image_error'] =  "There was a problem with your upload (accept only .jpg).";
				  break;
			}
		}

}
  //_____________ Adresses mails _______________
function addressMail($prenomDest,$nomDest,$domaineDest){
	$domaine = (!empty($domaineDest)) ? $domaineDest : "ibcg.biotoul.fr";
	$nom = (!empty($nomDest)) ? $prenomDest.".".$nomDest : $prenomDest ;
	$adresse = $nom."@".$domaine ;
	return $adresse ;
}
//_______________________________Bact_origin existe d�j� dans la table nom_type (base ISfinder) _______________
function bact_origin_exist($cnx,$bact_origin_demande,$MGE_type,$retour){
	$req = "SELECT `$retour` FROM `nom_type` 
			WHERE  `type_element_transposable_ID_Type_ET` = $MGE_type
					AND `bact_origin` LIKE '$bact_origin_demande' 
			LIMIT 1" ;
	$res=execute_sql($cnx,$req) ;
	$reponse = mysqli_fetch_row($res);
	return (mysqli_num_rows($res) > 0) ? $reponse[0] : false ;
}
//_______________________________Submiter existe d�j� dans la table submiters _______________
function submiter_exist($cnx,$Lastname_demande,$Mail){
	$req = "SELECT `ID_Submiter` FROM `submiters` 
			WHERE  `Lastname` LIKE '$Lastname_demande'
					AND `Mail` LIKE '$Mail' 
			LIMIT 1" ;
	$res=execute_sql($cnx,$req) ;
	$reponse = mysqli_fetch_row($res);
	return (mysqli_num_rows($res) > 0) ? $reponse[0] : false ;
}
//______________________________Envoie du mail apr�s attribution de nom _______________
function envoyerMail_attribNom($nomIS,$courriel,$nomSub){
	$retour = "1";
	$signature = "\r\n\r\nRegards\r\nPatricia Siguier\r\n\r\n------------------\r\nPatricia Siguier\r\n";
	$signature.= "Curator of ISFinder : https://www-is.biotoul.fr/\r\n------------------\r\n";
	$cc = addressMail("Patricia","Siguier","univ-tlse3.fr");
	$headers = "From: ".addressMail("webadmin","","")."\r\n";
	$headers .= "CC: ".$cc."\r\n";
	$headers .= "Content-Type: text/plain; charset=\"utf-8\"\r\n" ;
	$headers .= "X-Mailer: PHP/ISFinder\r\n";
	$subject = "[ISfinder]  IS name attribution";
	$texte = "Dear Dr ".$nomSub."\n\n";
	$texte .= "For the IS element you found we would like to suggest ".$nomIS.".\n";
	$texte .= "It would be very helpfull if you could submit these directly to the web site : http://www-is.biotoul.fr/ ";
	$texte .= "(if the instructions are not sufficiently clear please do not hesitate to contact us). \n\n";
	$texte .= "Sincerely \n";	
	$texte .= $signature ;
	mail($courriel,$subject,$texte,$headers);	
	return($retour);
}
?>
	
