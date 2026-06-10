<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = $_SESSION['error'] ?? "";
$_SESSION['error'] = ""; // Clear error after retrieving

$champrecherche = $_GET['champrecherche'] ?? "";
$champ = $_GET['champ'] ?? "";

require_once('includes/entete.inc.php');
require_once('includes/aside.inc.php');
require_once("includes/function.inc.php");
require_once("includes/affiche.inc.php");

echo '<script type="text/javascript" src="scripts/function.js"></script>';
echo '<link type="text/css" rel="stylesheet" href="styles/styles.css" media="screen" />';
echo "<article> ";

if ($error) {
    echo "<p class='erreur'>" . htmlspecialchars($error) . "</p><hr/>";
}

$bdd = "ISsubmit";
$cnx = connexion($bdd);

// Base query to find incomplete sequences
$requete = "SELECT ET.ID_ET, ET.ET_name, ET.ET_Length, FAM.Family_Name 
            FROM `element_transposable` ET 
            LEFT JOIN `submission` SUB ON ET.`ID_ET` = SUB.`Element_transposable_ID_ET` 
            LEFT JOIN `family` FAM ON ET.`Family_ID_Family` = FAM.`ID_Family` 
            WHERE SUB.`Element_transposable_ID_ET` IS NULL";

// Sort handling
$tri_autorises = array('ID_ET', 'ET_name', 'ET_Length', 'Family_Name');
$ordre = "";
if (!empty($_GET['tri'])) {
    $ordre = strip_tags($_GET['tri']);
}
$tri = (!empty($ordre) && in_array($ordre, $tri_autorises, true)) ? $ordre : 'ID_ET';
$reqtrier = $requete . " ORDER BY " . $tri;

// For descending order on ID as default, we can add a simple condition
if ($tri === 'ID_ET' && empty($_GET['tri'])) {
    $reqtrier .= " DESC";
}

$result = execute_sql($cnx, $reqtrier);
$nombre = mysqli_num_rows($result);

echo "<h2> Séquences Incomplètes </h2>";
echo "<p>Cette liste affiche les éléments transposables qui n'ont aucune soumission liée (le processus d'enregistrement s'est arrêté ou a échoué avant la fin).</p>";

if ($nombre > 0) {
    print "<h3>Résultat de votre requête : " . $nombre . " enregistrements</h3>";
    
    // Create sortable headers
    $id_header = sort_link('ID_ET', 'ID_ET', $champrecherche, $champ);
    $nom_header = sort_link('Name', 'ET_name', $champrecherche, $champ);
    $len_header = sort_link('Length', 'ET_Length', $champrecherche, $champ);
    $family_header = sort_link('Family', 'Family_Name', $champrecherche, $champ);

    print "<table>";
    print "<tr class='base_ISTrash'>"; // Standard distinct row class
    print "<th>$id_header</th>";
    print "<th>$nom_header</th>";
    print "<th>$len_header</th>";
    print "<th>$family_header</th>";
    print "</tr>";

    while ($ligne = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($ligne['ID_ET']) . "</td>";
        echo "<td>" . htmlspecialchars($ligne['ET_name']) . "</td>";
        echo "<td>" . htmlspecialchars($ligne['ET_Length']) . "</td>";
        echo "<td>" . htmlspecialchars($ligne['Family_Name']) . "</td>";
        echo "</tr>";
    }
    print "</table>";
} else {
    print "<h3>Résultat de votre requête : 0 enregistrement</h3>";
    print "<p>Aucune inscription incomplète trouvée.</p>";
}

echo "</article>";
?>
