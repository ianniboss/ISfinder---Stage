<?php
	date_default_timezone_set('Europe/Paris');
	/*Si le formulaire est soumis On r�cup�re les donn�es du formulaire*/
	$form_soumis = (!empty(htmlentities($_POST['blast']))) ? htmlentities($_POST['blast']) : Null;

	//	$form_soumis = htmlentities($_POST['blast']);
if( $form_soumis == "ok"){	
	//	$title = (!empty($_POST[title])) ? htmlspecialchars($_POST[title]) : "" ;

	/* Pour les register globals off - On r�cup�re les champs soumis tout en supprimant les balises HTML*/
	foreach($_POST as $champ=>$valeur){		// on remplit une variable portant le nom du champ 
		$$champ = trim((htmlentities($valeur))) ;
	}
		# La sequence
	$uploaddir="/var/www/uploads/blast";

	# Cas ou la sequence est copi�e/collee
	if (!empty($seq)) {
		$tmpfname = tempnam($uploaddir."/tmp","tmp");
		if ($handle= fopen($tmpfname, "w")){
			fwrite($handle,$seq);
			fclose($handle);
			$seqfichier=$tmpfname.".seq";
			rename($tmpfname,$seqfichier);
		}else{
			die("<br><h3>Sorry the server encountered a problem. Please contact the web administrator.</h3><br><a href='https://www-is.biotoul.fr/blast.php'>Back</a>");			
		}
	}	
	# Cas ou la sequence est upload�e
	else if ($_FILES['seqfile']['type']!= "application" && $_FILES['seqfile']['size']< 10000000 && is_uploaded_file($_FILES['seqfile']['tmp_name'])) {
			$fichier=$_FILES['seqfile']['tmp_name'];
			$seqfichier = $uploaddir.$fichier.".seq";
			if (move_uploaded_file($fichier,$seqfichier)== FALSE){
				die("<br><h3>Sorry the server encountered a problem. Please contact the web administrator.</h3><br><a href='https://www-is.biotoul.fr/blast.php'>Back</a>");
			}

	# Sinon, il faut absolument une s�quence!
	} else {
		die("<br><h3>You have to give a sequence!</h3><br><a href='https://www-is.biotoul.fr/blast.php'>Back</a>");
	}

	# Gapcosts contient le couple openGap / extensionGap
	if (!empty($gapcosts)) {
		$tab_gap = explode(" ", $gapcosts);
	
		if (!is_numeric($tab_gap[0])) {
			die("Numeric value only for open gap!");
		}
			$gapopen = "-gapopen ".$tab_gap[0];
	
		if (!is_numeric($tab_gap[1])) {
				die("Numeric value only for extension gap!");
		}
			$gapextend = "-gapextend ".$tab_gap[1];
	}
	# reward / penalty fix� � 1/-3
	$reward = " -reward 1" ;
	$penalty = " -penalty -3" ;


	# Expectation value

	if (!is_numeric($expect)) {
	        die("Numeric value only for expectation value!");
	}else if ($expect <= 0){
	        die("expect value must be greater than zero!");
	}

        $expect = "-evalue ".$expect;

	# Word size

        if (!is_numeric($wordsize)) {
	        die("Numeric value only for word size!");
	}
        $mot = "-word_size ".$wordsize;

	# Filtre
	$filtre = (isset($filtre)) ? "yes" : "no";
	
	# Type d'alignement
	switch ($_POST['alignment']) {
		case 0:
			$align = "-outfmt 0";
			break;
		case 1:
			$align = "-outfmt 1";
			break;
		case 2:
			$align = "-outfmt 2";
			break;
		case 3:
			$align = "-outfmt 3";
			break;
		case 4:
			$align = "-outfmt 4";
			break;
		case 5:
			$align = "-outfmt 5";
			break;
		case 6:
			$align = "-outfmt 6";
			break;
		case 7:
			$align = "-outfmt 7";
			break;
		case 10:
			$align = "-outfmt 10";
			break;
		default:
			die("Unknowned alignement");
	}

	/*
	#  number of on-line descriptions

			if (!is_numeric($_POST['old'])) {
				die("Numeric value only for number of on-line descriptions!");
		}
			$old= "-v ".$_POST['old'];

	#number of alignement to show			

		if (!is_numeric($_POST['nas'])) {
				die("Numeric value only for number of alignement to show!");
		}
			$nas = "-b ".$_POST['nas'];
	*/
	# on commence le calcul en fonction du type de sortie
		$prog_use = $prog;		// Pour envoyer le prog utilis� � resultat.php
        switch ($prog) {
        case "blastn":
			$prog="blastn -db /var/www/cgi-bin/blast/DB/ISfindernt";
			$option="-dust ".$filtre.$reward.$penalty;
            break;
        case "megabl":
			$prog="blastn -db /var/www/cgi-bin/blast/DB/ISfindernt -task megablast";
			$option="-dust ".$filtre.$reward.$penalty;
            break;
        case "blastp":
            $prog="blastp -db /var/www/cgi-bin/blast/DB/ISfinderaa";
			$option="-seg ".$filtre;
			break;
        case "blastx":
			$prog="blastx -db /var/www/cgi-bin/blast/DB/ISfinderaa";
			$option="-seg ".$filtre;
                break;
        case "tblastn":
            $prog="tblastn -db /var/www/cgi-bin/blast/DB/ISfindernt";				
			$option="-seg ".$filtre;
                break;
        case "tblastx":
            $prog="tblastx -db /var/www/cgi-bin/blast/DB/ISfindernt";
			$option="-seg ".$filtre;
                break;
        default:
                die("Unknowned program!!!".$prog);
        }
	
	# On construit le nom du fichier resultat en fonction du nom du fichier sequence 
	$tempo = explode(".",$seqfichier);
	$fichresult=$tempo[0];
	$fichfinal=$tempo[0].".res";
	$fichlog="/var/www/uploads/blast/logs/blast.log";

		# aggr�gation des arguments du blastall

		$command = $prog." -html"." ".$expect;
		if ($prog!='tblastx'){	$command .= " ".$gapopen." ".$gapextend;}
		/*		$command .= " ".$old." ".$nas." ".$thrsld." ".$qgc." ".$bqd." ".$bhtk." ".$elss;  */
		$command .= " ".$mot." ".$align;
		$command .= " ".$option." -query ".$seqfichier ;

		############################### Attention, si trop de process, on quitte ################
		$max_process=3;
		exec("ps -ef|grep blast",$lignes_ret);
		if (count($lignes_ret) > $max_process) {
	        echo "<html><head><title>Result</title><body>\n";
			echo "<br><br><h3>Too many blast processes on this server!!</h3>\n";
			echo "<br>Please try later...</h3><br><br>";
			echo "<a href='javascript:history.go(-1)'>Back...</a>";
			echo "</body></html>";
	# Sinon, on peut y aller

		} else {  
			if (!strpos($command,";")) {
			$command = escapeshellcmd($command);
	# Ecriture dans un fichier de log

			$hand=fopen($fichlog,"a");
			fwrite($hand,date("[D d M Y G:i:s] "));
			fwrite($hand,$_SERVER['REMOTE_ADDR']);
			fwrite($hand," : blastall");
			fwrite($hand,$command);
			fwrite($hand," \n");
			fclose($hand);

	# La commande finale, tout est redirig� dans un fichier pour avoir aussi les messages d'erreurs.

			$finalcom="/var/www/cgi-bin/blast/bin/".$command." >& $fichfinal &";
			$result = exec($finalcom);
			}
			$nomfich=substr($fichresult,27);

	# Le process est lanc�, on affiche une page temporaire, qui au bout de 2 secondes charge resutat.php
			echo "<html><head><meta http-equiv='refresh' content='2;";
			if ($_POST['alignment']==0){
				echo "URL=resultat.php?id=$nomfich&title=$title&prog=$prog_use'></head>\n";
				echo "<body><h2>Please wait, your request is in progress...</h2>\n";
				echo "<p>If automatic refresh doesn't work, please click ";
				echo "<a href=\"resultat.php?id=$nomfich&title=$title&prog=$prog_use\">here...</a></p></body></html>\n";
			}else{
				echo "URL=resultatnorm.php?id=$nomfich&title=$title&prog=$prog_use'></head>\n";
			}

		}
	}	else	{
	echo "<br><h3>Please use the form : <a href='https://www-is.biotoul.fr/blast.php'> blast.php</a></h3><br>";
}
?>
