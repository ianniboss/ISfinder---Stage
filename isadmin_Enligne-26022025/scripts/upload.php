<?php
session_start();

$uploaddir ="/var/www/html/intranet/secure/isadmin/drawings/" ;
$_SESSION['recoding_image_error'] = "" ;

// Si le fichier à télécharger est un .jpg et pas d'erreur de téléchargement 
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

		// On retourne au formulaire de modification d'une fiche	
$ident = $_SESSION['ID_ET'] ;
$base = $_SESSION['bdd'] ;

header("Location: ../ficheIS.php?ident=$ident&bdd=$base&val_session=1"."#Recoding");
	
?>
