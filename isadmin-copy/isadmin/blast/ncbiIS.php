<?php
date_default_timezone_set('Europe/Paris');

// On recupere les donnees du formulaire
# Le titre du Job
$title = (isset($_POST['title'])) ? strip_tags($_POST['title']) : "";

# La sequence
$uploaddir = "/var/www/uploads/blast";

# Cas ou la sequence est copiee/collee
if (isset($_POST['seq']) && !empty($_POST['seq'])) {
    $sequence = strip_tags($_POST['seq']);
    $tmpfname = tempnam($uploaddir . "/tmp", "tmp");
    $handle = fopen($tmpfname, "w");
    fwrite($handle, $sequence);
    fclose($handle);
    $seqfichier = $tmpfname . ".seq";
    rename($tmpfname, $seqfichier);
}
# Cas ou la sequence est uploadee
else if (is_uploaded_file($_FILES['seqfile']['tmp_name'])) {
    $fichier = $_FILES['seqfile']['tmp_name'];
    $seqfichier = $uploaddir . $fichier . ".seq";
    move_uploaded_file($fichier, $seqfichier);

# Sinon, il faut absolument une sequence!
} else {
    die("You have to give a sequence!");
}

# Gapcosts contient le couple openGap / extensionGap
if (isset($_POST['gapcosts'])) {
    $tab_gap = explode(" ", $_POST['gapcosts']);

    if (!is_numeric($tab_gap[0])) {
        die("Numeric value only for open gap!");
    }
    $gapopen = "-gapopen " . $tab_gap[0];

    if (!is_numeric($tab_gap[1])) {
        die("Numeric value only for extension gap!");
    }
    $gapextend = "-gapextend " . $tab_gap[1];
}

# reward / penalty fixe a 1/-3
$reward = " -reward 1";
$penalty = " -penalty -3";

# Expectation value
if (!is_numeric($_POST['expect'])) {
    die("Numeric value only for expectation value!");
} else if ($_POST['expect'] <= 0) {
    die("expect value must be greater than zero!");
}

$expect = "-evalue " . $_POST['expect'];

# Word size
if (!is_numeric($_POST['wordsize'])) {
    die("Numeric value only for word size!");
}
$mot = "-word_size " . $_POST['wordsize'];

# Filtre
$filtre = (isset($_POST['filtre'])) ? "yes" : "no";

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
$old = "-v " . $_POST['old'];

#number of alignement to show           
if (!is_numeric($_POST['nas'])) {
    die("Numeric value only for number of alignement to show!");
}
$nas = "-b " . $_POST['nas'];
*/

# on commence le calcul en fonction du type de sortie
$database = $_POST['database'];

switch ($_POST['prog']) {
    case "blastn":
        $db = $database . "nt";
        $prog = "blastn -db /var/www/html/secure/isadmin/blast/DB/$db";
        $option = "-dust " . $filtre . $reward . $penalty;
        break;
    case "megabl":
        $db = $database . "nt";
        $prog = "blastn -db /var/www/html/secure/isadmin/blast/DB/$db -task megablast";
        $option = "-dust " . $filtre . $reward . $penalty;
        break;
    case "blastp":
        $db = $database . "aa";
        $prog = "blastp -db /var/www/html/secure/isadmin/blast/DB/$db";
        $option = "-seg " . $filtre;
        break;
    case "blastx":
        $db = $database . "aa";
        $prog = "blastx -db /var/www/html/secure/isadmin/blast/DB/$db";
        $option = "-seg " . $filtre;
        break;
    case "tblastn":
        $db = $database . "nt";
        $prog = "tblastn -db /var/www/html/secure/isadmin/blast/DB/$db";
        $option = "-seg " . $filtre;
        break;
    case "tblastx":
        $db = $database . "nt";
        $prog = "tblastx -db /var/www/html/secure/isadmin/blast/DB/$db";
        $option = "-seg " . $filtre;
        break;
    default:
        die("Unknowned program!!!" . $prog);
}

# On construit le nom du fichier resultat en fonction du nom du fichier sequence 
$tempo = explode(".", $seqfichier);
$fichresult = $tempo[0];
$fichfinal = $tempo[0] . ".res";
$fichlog = "/var/www/uploads/blast/logs/blast.log";

# aggregation des arguments du blastall
$command = $prog . " -html " . $expect;
if ($prog != 'tblastx') {
    $command .= " " . $gapopen . " " . $gapextend;
}
/*      $command .= " ".$old." ".$nas." ".$thrsld." ".$qgc." ".$bqd." ".$bhtk." ".$elss;  */
$command .= " " . $mot . " " . $align;
$command .= " " . $option . " -query " . $seqfichier;

############################### Attention, si trop de process, on quitte ################
$max_process = 3;
exec("ps -ef|grep blast", $lignes_ret);

if (count($lignes_ret) > $max_process) {
    echo "<html><head><title>Result</title><body bgcolor=\"#F8E9C2\">\n";
    echo "<br><br><h3>Too many blast processes on this server!!</h3>\n";
    echo "<br>Please try later...</h3><br><br>";
    echo "<a href='javascript:history.go(-1)'>Back...</a>";
    echo "</body></html>";
# Sinon, on peut y aller
} else {
    if (!strpos($command, ";")) {
        $command = escapeshellcmd($command);
	# Ecriture dans un fichier de log
        $hand = fopen($fichlog, "a");
        // PHP 8.5 Fix : Ajout d'une verification de la ressource avant ecriture pour eviter une erreur fatale TypeError
        if ($hand) {
            fwrite($hand, date("[D d M Y G:i:s] "));
            fwrite($hand, $_SERVER['REMOTE_ADDR']);
			#fwrite($hand, " : blastall");
            fwrite($hand, $command);
            fwrite($hand, " \n");
            fclose($hand);
        }

		# La commande finale, tout est redirige dans un fichier pour avoir aussi les messages d'erreurs.
        // PHP 8.5 Fix : Remplacement de la syntaxe specifique bash '>&' par la redirection standard POSIX '> $file 2>&1 &' pour eviter les erreurs de syntaxe shell
        $finalcom = "/var/www/html/secure/isadmin/blast/bin/" . $command . " > $fichfinal 2>&1 &";
        $result = exec($finalcom);
    }
    $nomfich = substr($fichresult, 27);
	# Le process est lance, on affiche une page temporaire, qui au bout de 2 secondes charge resutat.php
    echo "<html><head><meta http-equiv='refresh' content='2;";
    if ($_POST['alignment'] == 0) {
        // PHP 8.5 Fix : Suppression de l'URL du serveur de production en dur (secure.ibcg.biotoul.fr) pour permettre l'execution en local
        echo "URL=resultat.php?id=$nomfich&database=$database&title=$title'></head>\n";
        echo "<body><h2>Please wait, your request is in progress...$nomfich</h2>\n";
        echo "<p>If automatic refresh doesn't work, please click ";
        echo "<a href=\"resultat.php?id=$nomfich&database=$database&title=$title\">here...</a></p></body></html>\n";
    } else {
        // PHP 8.5 Fix : Suppression de l'URL du serveur de production en dur
        echo "URL=resultatnorm.php?id=$nomfich&title=$title'></head>\n";
    }
}
?>
