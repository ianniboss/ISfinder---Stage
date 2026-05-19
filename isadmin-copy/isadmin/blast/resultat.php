<?php
$title = strip_tags($_GET['title']);
echo "<title>Blast " . $title . "</title>";

require_once('../includes/entete.inc.php');
require_once('../includes/aside.inc.php');
require_once("../includes/function.inc.php");
require_once("../includes/affiche.inc.php");

echo "<article>";

$id_fichier = strip_tags($_GET['id']);
$fichier = "/var/www/uploads/blast/tmp/" . $id_fichier . ".res";
$database = (strip_tags($_GET['database']) == "isfinder") ? "isfinder" : "ISsubmit";

// On ne veut pas de cochoncets dans la paramtre id!!!
if ((strlen($id_fichier) == 9) && (!preg_match('/[^A-Za-z0-9]/', $id_fichier)) && (file_exists($fichier))) {
	//  $cnx = connect("isfinder",$database);
    $cnx = connexion($database);

    if ($cnx) {
		// On charge une partie des infos de la base dans des tableaux
        if ($database == "isfinder") {
            $req = "SELECT `ET_name`, `Family_Name`, `Group_Name`, `ID_ET` FROM `element_transposable` ET
                  JOIN `family` FAM ON `Family_ID_Family` = `ID_Family`
                  LEFT JOIN `groups` GRP ON `Groups_ID_Groups` = `ID_Groups`";
        } else {
            $req = "SELECT `ET_name`, `Family_ID_Family`, `Groups_ID_Groups`, `ID_ET` FROM `element_transposable`";
        }
        $result = execute_sql($cnx, $req);

        $i = 0;
        for ($i = 0; $i < mysqli_num_rows($result); $i++) {
            $tmptab = mysqli_fetch_row($result);
            $tableis[$i] = $tmptab[0] . " ";
            $tablefam[$i] = $tmptab[1];
            $tablegrp[$i] = $tmptab[2];
            $tableori[$i] = ncbi_origin_link(is_origin($cnx, $tmptab[3]));
            $urlis[$i] = "<a href=\"https://secure.ibcg.biotoul.fr/isadmin/ficheIS.php?ident=" . $tmptab[3] . "&bdd=$database\" target=\"_blank\">" . $tmptab[0] . "</a>";
        }

    	// On va regarder si le process blast lanc dernirement est termin
        $command = "ps -ef|grep " . $id_fichier . "[.]";
        $resultat = exec($command);
        if (!$resultat) {                       // OUI

            echo "<h3>BLAST search result : " . $title . "</h3>";
    		// echo "<font size=\"2\"><a href=\"resultatnorm.php?id=".$id_fichier."\">Normal view</a></font><br>";
            $lignes = file($fichier);
            $chaine = "Sequences producing significant alignments:";
            $j = 0;
            $stop = false;
            reset($lignes);
            $nbr_lignes = count($lignes);

    		// On parse le fichier rsultat de blast pour rajouter les liens vers la base
            while ($lignes[$j] && $j < $nbr_lignes - 1) {

				// D'abord, on recherche la ligne qui dfini le dbut des rsultats Sequences producing significant alignments:
                if (is_numeric(strpos($lignes[$j], $chaine))) {

					// On y est, on écrit les titres des colonnes du tableau
                    $stop = true;
                    $lignesext[$j - 5] = "";
                    $lignesext[$j - 6] = "";
                    $lignesext[$j - 1] = "</pre>";
                    $lignesext[$j] = "<table><tr>";
                    $lignesext[$j] .= "<th>Sequences producing<br> significant alignments</th><th>IS Family</th><th>Group</th>";
                    $lignesext[$j] .= "<th>Origin</th><th>Score<br>(bits)</th><th width='50px'>E. value</th>";
                    $lignesext[$j] .= "</tr>";
                    $j = $j + 2;   // Sur le site il y avait +1 et sur mon poste +2  et sur ISfinder +2   ???
                }
				// On est stoppé, donc ce sont les lignes du tableau 
                if ($stop === true) {
                    $tempi = explode("      ", $lignes[$j]);
                    $nameis = trim($tempi[0]) . " ";
                    if (strpos($nameis, "_aa")) {
                        $tempa = explode("_aa", $nameis);
                        $nameis = $tempa[0] . " ";
                    }
                    $cle = array_search($nameis, $tableis);
//                  $ch1 = "<script src=";                    Ancien version de blast
                    $ch1 = "><a name=";
                    if (preg_match("/^$ch1.*$/", $lignes[$j])) {         // on arrive à la premiere ligne après le tableau
                        $stop = false;
                        $lignesext[$j - 3] .= "</table>";
                        $lignesext[$j - 2] = "\n<pre class='blast'>\r";
                        $lignesext[$j - 1] = "";
                        $lignes[$j] = preg_replace('/<script(.*)script>/', ' ', $lignes[$j]);  // on enleve le script blastresult.js
                    } else {                                        // on remplit le tableau
                        $tabi = explode("   ", $lignes[$j]);
                        $url = $urlis[$cle];
                        $tabo = explode("   ", trim(substr($lignes[$j], -46, 46)));
                        $score = trim($tabo[0]);
                        $value = trim($tabo[1]);
                        $lignesext[$j] = "<tr><td>" . $url . "</td><td>";
                        $lignesext[$j] .= $tablefam[$cle] . "</td><td>" . $tablegrp[$cle] . "</td><td>" . $tableori[$cle];

                        $lignesext[$j] .= "</td><td>" . $score . "</td><td>" . $value . "</td>";
                        $lignesext[$j] .= "</tr>\n";
                        $j++;
                    }
                } else {                                                // on est sorti de la partie tableau
                    if (preg_match("/^><a/", trim($lignes[$j]))) {      // on ajoute le nom de famille sur la 1er ligne
                        $tempi = explode(" ", trim($lignes[$j]));
                        $nameis = trim($tempi[2]) . " ";
                        if (strpos($nameis, "_aa")) {
                            $tempa = explode("_aa", $nameis);
                            $nameis = $tempa[0] . " ";
                        }
                        $cle = array_search($nameis, $tableis);
                        $lignesext[$j] = trim($lignes[$j]) . " <span class='entete_propriete'> Family: " . $tablefam[$cle] . "</span>\n";
                    } else {
                        $lignesext[$j] = $lignes[$j];
                    }
                    $j++;
                }
            }           // Fin du while

    		// On affiche toutes les lignes
            $nbr_lignes = count($lignesext);

            for ($j = 0; $j < ($nbr_lignes - 1); $j++) {
                echo $lignesext[$j];
            }

            echo "</article></body></html>";
                        // Fin du if il y  a un resultat
    	// Le process tourne encore, on rafraichira dans 10 secondes
        } else {
            echo "<meta http-equiv='refresh' content='2'>";
            echo "</head><body bgcolor=\"#F8E9C2\"><h3>Result</h3><br>";
            echo "Process is still running<br>";
            echo "This page will be automatically reloaded in 10 seconds<br>";
            echo "Please be patient...<br>";
            echo "</body></html>";
        }
    } // Fin du if $cnx
// Il semblerait que le paramtre id pass dans l'url n'est pas bon, on gueule!
} else {

    echo "</head><body  bgcolor=\"#F8E9C2\"><h3>Bad request !!!   $id_fichier</h3>";
    echo "<h4>All activity is logged !!!</h4>";
    echo "<p>If you think it's server error, please contact the <a href=\"mailto:webadmin@ibcg.biotoul.fr\">webmaster</a>.</p>";
}

include('../includes/pied.inc.php');
?>
</div> <!-- Fin du div page -->
</body>
</html>