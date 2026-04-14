<!DOCTYPE html>
<html>
<head>
<?php
$title = htmlspecialchars($_GET['title']);
// $title = (isset($_GET['title'])) ? htmlspecialchars($_GET[title]) : "";
echo "<title>Blast ".$title."</title>";
?>
<meta charset="utf-8" /> 
<meta name="author" content="Jo" />
<meta name="keywords" content="IS, Insertion Sequence" />
<link type="text/css" rel="stylesheet" href="../styles/styles.css" media="screen" />
<link type="text/css" rel="stylesheet" href="../styles/menu.css" media="screen" />
<link type="text/css" rel="stylesheet" href="../styles/ficheMGE.css" media="screen" />
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
include_once ("../include/function.inc.php");
include_once ("../include/affiche.inc.php");
$lignesext[0] = "";

echo "<article>" ;
$prog=trim(stripslashes(htmlspecialchars($_GET['prog'])));
$id_fichier=trim(stripslashes(htmlspecialchars($_GET['id'])));
$fichier = "/var/www/uploads/blast/tmp/".$id_fichier.".res";
	
		//  On ne veut pas de cochoncet�s dans la param�tre id!!!
if ((strlen($id_fichier) == 9) and (!preg_match('[^A-Za-z0-9]',$id_fichier)) and (file_exists($fichier))) {
	$cnx = connexion("ISfinder");
			
// On charge une partie des infos de la base dans des tableaux

	$req = "SELECT `ET_name`, `Family_Name`, `Group_Name`, `ID_ET` FROM `element_transposable` ET
				  JOIN `family` FAM ON `Family_ID_Family` = `ID_Family`
				  LEFT JOIN `groups` GRP ON `Groups_ID_Groups` = `ID_Groups`" ;		

	$result = execute_sql_new($cnx,$req);
	$i = 0;
	for ($i=0; $i<mysqli_num_rows($result); $i++) {
		$tmptab = mysqli_fetch_row($result);
		$tableis[$i]=$tmptab[0]." ";
		$tablefam[$i]=$tmptab[1];
		$tablegrp[$i]=$tmptab[2];
		$tableori[$i]=ncbi_origin_link(is_origin($tmptab[3]));
		$urlis[$i] = "<a href=\"../scripts/ficheIS.php?name=".$tmptab[0]."\" target=\"_blank\">".$tmptab[0]."</a>";
		}
	
// On va regarder si le process blast lanc� derni�rement est terminé		  
		$command="ps -ef|grep ".$id_fichier."[.]";
		$resultat=exec($command);
		if (!$resultat) {						// OUI

			echo "<h3>BLAST search result : ".$title."</h3>";			
// echo "<font size=\"2\"><a href=\"resultatnorm.php?id=".$id_fichier."\">Normal view</a></font><br>";
			$lignes=file($fichier);
			$chaine="Sequences producing significant alignments:";
			$j=0;
			$l=0;
			$stop=false;
			reset($lignes);

// On parse le fichier r�sultat de blast pour rajouter les liens vers la base
			while (!empty($lignes[$j])) {

// D'abord, on recherche la ligne qui d�fini le d�but des r�sultats Sequences producing significant alignments:
				if (is_numeric(strpos($lignes[$j],$chaine))){ 

// On y est, on écrit les titres des colonnes du tableau
					$stop = true;
					$lignesext[$j-5]="";
					$lignesext[$j-6]="";
					$lignesext[$j-1]="</pre>";
					$lignesext[$j]="<table><tr>";
					$lignesext[$j].="<th>Sequences producing<br> significant alignments</th><th>IS Family</th><th>Group</th><th>Origin</th>";
					if ($prog == "blastp" || $prog=="blastx"){
						$lignesext[$j].="<th>ORF function</th>";
					}
					$lignesext[$j].="<th>Score<br>(bits)</th><th width='50px'>E. value</th>";
					$lignesext[$j].="</tr>";
					$j=$j+2;
					
				}
// On est stoppé, donc ce sont les lignes du tableau 				
				if ($stop === true){
					$tempi = explode("      ",$lignes[$j]);
					$nameis=trim($tempi[0])." ";
					if (strpos($nameis,"_aa")) {
						$tempa=explode("_aa",$nameis);
						$nameis=$tempa[0]." ";
						// Requete pour récupérer la fonction de l'orf
						$orf_rank=$tempa[1];
						$req_function = "SELECT `ORF_function` FROM `orf` LEFT JOIN `element_transposable` ON `Element_transposable_ID_ET` = `ID_ET`
										where `ET_name`='$nameis' AND `ORF_rank`=$orf_rank" ;
						$result = execute_sql_new($cnx,$req_function);
					//	$orf_fonction= mysql_result($result,0);
						$orf_fonction = (mysqli_num_rows($result)==0) ? '' : mysqli_result($result,0);
						$tabfonction[$l] = $orf_fonction ;
						$l++;
					}
					$cle=array_search($nameis,$tableis);
//					$ch1 = "<script src=";			Ancienne version de blast
					$ch1 = "><a name=";                  
                    if (preg_match("/^$ch1.*$/", $lignes[$j])){			// on arrive à la premiere ligne après le tableau
						$stop=false;
						$l = 0;
						$lignesext[$j-3].="</table>";
						$lignesext[$j-2]="\n<pre>\r";						
						$lignesext[$j-1]="";
						$lignes[$j]= preg_replace('/<script(.*)script>/',' ',$lignes[$j]);  // on enleve le script blastresult.js
						} else {										// on remplit le tableau
							$tabi=explode("   ",$lignes[$j]);
							$url=$urlis[$cle];
							$tabo=explode("   ",trim(substr($lignes[$j],-46)));						
							$tabo[0] = (!empty($tabo[0])) ? $tabo[0] : "";
							$score=trim($tabo[0]);
							$tabo[1] = (!empty($tabo[1])) ? $tabo[1] : "";
							$value=trim($tabo[1]);
							
							$lignesext[$j]="<tr><td>".$url."</td>";
							$lignesext[$j] .= "<td>".$tablefam[$cle]."</td><td>".$tablegrp[$cle]."</td><td>".$tableori[$cle]."</td>";
							if ($prog == "blastp" || $prog=="blastx"){
								$lignesext[$j] .= "<td>".$orf_fonction."</td>";
							}
							$lignesext[$j].="<td>".$score."</td><td>".$value."</td>";
							$lignesext[$j].="</tr>\n";
							$j++;
							}
				} else {												// on est sorti de la partie tableau
					$fonction = "";										// Innitialisation de $fonction
					if (preg_match("/^><a/",trim($lignes[$j]))){		// on ajoute le nom de famille sur la 1er ligne
						$tempi = explode(" ",trim($lignes[$j]));
						$nameis=trim($tempi[2])." ";
						if (strpos($nameis,"_aa")) {
							$tempa=explode("_aa",$nameis);
							$nameis=$tempa[0]." ";
							$fonction = $tabfonction[$l];
							$l++;
						}
						$cle=array_search($nameis,$tableis);
						if ($fonction){
							$lignesext[$j]= trim($lignes[$j])." <span class='entete_propriete'> Family: ".$tablefam[$cle]." - ".$fonction."</span>\n";
						}else{							
							$lignesext[$j]= trim($lignes[$j])." <span class='entete_propriete'> Family: ".$tablefam[$cle]."</span>\n";
						}
					}else{
						$lignesext[$j]=$lignes[$j];
					}
					$j++;
				}
			}			// Fin du while
// On affiche toutes les lignes
			for ($j=1;$j<(count($lignesext)-1);$j++){
					if (!empty($lignesext[$j])){
						  echo $lignesext[$j];
						}
					}									
					
			echo "</article></body></html>";
			mysqli_close($cnx);
						// Fin du if il y  a un resultat
// Le process tourne encore, on rafraichira dans 10 secondes			
		} else {
			echo "<meta http-equiv='refresh' content='2'>";
			echo "</head><body><h3>Result</h3><br>";
			echo "Process is still running<br>";
			echo "This page will be automatically reloaded in 10 seconds<br>";
			echo "Please be patient...<br>";
			echo "</body></html>";
		}
// Il semblerait que le param�tre id pass� dans l'url n'est pas bon, on gueule!		
	} else {

		echo "</head><body><h3>Bad request !!!   $id_fichier</h3>";
		echo "<h4>All activity is logged !!!</h4>";
		echo "<p>If you think it's server error, please contact the <a href=\"mailto: ".addressMail('','cbi.webadmin-isfinder','')."\">webmaster</a>.</p>";
	}

include('../include/footer.inc.php'); 
?>
</div> <!-- Fin du div page -->
</body>
</html>