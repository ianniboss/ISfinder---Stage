<?php
session_start();
header('Pragma: no-cache');
header("Cache-Control: no-cache, must-revalidate");    
if (isset($_POST['submitrecherche'])) {

    /* Fields sanitization and retrieval */
    foreach ($_POST as $champ => $valeur) {
        $$champ = htmlspecialchars(strip_tags($valeur));
    }
    $script = $_POST['nom_script'];

    // Redirect to the calling script with criteria
    header("Location: ../$script?champrecherche=$champrecherche&champ=$champ");    
    exit();

} else {
    header("Location: https://www-is.biotoul.fr/");
    exit();
}
?>
