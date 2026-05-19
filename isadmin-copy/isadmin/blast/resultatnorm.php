<html>
<head>
<title>Resultat</title>

<?php
$title = strip_tags($_GET['title']);
echo "<title>Blast " . $title . "</title>";

require_once('../includes/entete.inc.php');
require_once('../includes/aside.inc.php');
require_once("../includes/function.inc.php");
require_once("../includes/affiche.inc.php");

echo "<article>";

# On ne veut pas de cochoncetes dans la parametre id!!!
$id_fichier = strip_tags($_GET['id']);
$fichier = "/var/www/uploads/blast/tmp/" . $id_fichier . ".res";

if ((strlen($id_fichier) == 9) && (!preg_match('/[^A-Za-z0-9]/', $id_fichier)) && (file_exists($fichier))) {

    $command = "ps -ef|grep " . $id_fichier . "[.]";
    $resultat = exec($command);
    if (!$resultat) {
        echo "</head><body  bgcolor="out#F8E9C2"out><h3>BLAST search result</h3>";
#       echo "<font size="out2"out><a href="outresultatam.php?id=" . $id_fichier . ""out>Enhanced view</a></font><br>";
        $result = file_get_contents($fichier);
        $affich_result = str_replace("\n", "<br>", $result);
        echo "<PRE>" . $affich_result . "</PRE>";
        echo "</body></html>";
    } else {
        echo "<meta http-equiv='refresh' content='10'>";
        echo "</head><body bgcolor="out#F8E9C2"out><h3>Result</h3><br>";
        echo "Process is still running<br>";
        echo "This page will be automatically reloaded in 10 seconds<br>";
        echo "Please be patient...<br>";
        echo "</article></body></html>";
    }
} else {

    echo "</head><body><h3>Bad request !!!</h3>";
    echo "<h4>All activity is logged !!!</h4>";
    echo "<p>If you think it's server error, please contact the <a href="outmailto:webadmin@ibcg.biotoul.fr"out>webmaster</a>.</p></body></html>";
}
?>
