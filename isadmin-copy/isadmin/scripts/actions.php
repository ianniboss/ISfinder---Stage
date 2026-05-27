<?php
session_start();
require_once("../includes/affiche.inc.php");
require_once("../includes/function.inc.php");
require_once("../includes/actions.inc.php");

// Validate and sanitize inputs
$ident = isset($_GET['IDET']) ? intval($_GET['IDET']) : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';
$bdd = isset($_GET['bdd']) ? $_GET['bdd'] : '';

// Safety check: ident must be a positive integer
if ($ident <= 0) {
    $_SESSION['error'] = "Identifiant invalide.";
    header("Location: ../liste.php?list=1");
    exit();
}

// ─── Transfert ISsubmit interne : sub / wait / trash ───────────────────────
// Déplace une fiche entre les sous-bases de ISsubmit (base_ID_Base 1/2/3)
if ($action == "sub" || $action == "wait" || $action == "trash") {
    /* Connexion à la base de données */
    $cnx = connexion($bdd);
    if (!$cnx) {
        $_SESSION['error'] = "Problème de connexion à la base de données";
    } else {
        if ($bdd == "ISsubmit") {
            switch ($action) {
                case "sub":
                    $sql_maj = "UPDATE `element_transposable` SET `base_ID_Base` = '1' WHERE `ID_ET` = '$ident'";
                    execute_sql($cnx, $sql_maj);
                    break;
                case "wait":
                    $sql_maj = "UPDATE `element_transposable` SET `base_ID_Base` = '2' WHERE `ID_ET` = '$ident'";
                    execute_sql($cnx, $sql_maj);
                    break;
                case "trash":
                    $sql_maj = "UPDATE `element_transposable` SET `base_ID_Base` = '3' WHERE `ID_ET` = '$ident'";
                    execute_sql($cnx, $sql_maj);
                    break;
            }
        } else {
            $_SESSION['error'] = "Action non autorisée pour cette base";
        }
        mysqli_close($cnx);
    }

// ─── Validation ISsubmit → ISfinder : ok ───────────────────────────────────
// Récupère les données ISsubmit, les écrit dans ISfinder, supprime de ISsubmit
} elseif ($action == "ok") {
    $recup = recup_data($ident, '', $bdd);
    if ($recup) {
        $ecrit = ecrit_data($ident, $_SESSION['ET_name'], "isfinder");
        if ($ecrit) {
            $suppr = suppression($ident, '', $bdd);
            if (!$suppr) {
                $_SESSION['error'] .= "Fiche transférée dans ISfinder mais probleme suppression dans ISsubmit";
                header("Location: ../liste.php?list=1");
                exit();
            }
        } else {
            $_SESSION['error'] .= "probleme d'écriture dans la base, Transfert interrompu<br>";
            header("Location: ../liste.php?list=1");
            exit();
        }
        // Envoi du mail de confirmation au submitter
        $envoie = envoyerMail($_SESSION['ET_name'], $_SESSION['Mail']);
        if (!$envoie) {
            $_SESSION['error'] .= "problème d'envoie du mail de validation<br>";
        }
    } else {
        $_SESSION['error'] .= "probleme de recupération des données dans ISsubmit, Transfert interrompu";
    }

// ─── Renvoi ISfinder → ISsubmit : sendback ─────────────────────────────────
// Uniquement pour la base ISfinder : copie la fiche dans ISsubmit puis la supprime de ISfinder.
// IMPORTANT : ne supprime PAS le submiter de ISfinder (il reste dans la base publique).
} elseif ($action == "sendback" && $bdd == "isfinder") {
    $recup = recup_data($ident, '', $bdd);
    if ($recup) {
        // Écrire dans ISsubmit (base_ID_Base = 1 = ISSub)
        $ecrit = ecrit_data_issub($ident, $_SESSION['ET_name'], 'ISsubmit');
        if ($ecrit) {
            // Suppression safe dans ISfinder : ne touche pas aux submiters
            $suppr = suppression_isfinder($ident, 'isfinder');
            if (!$suppr) {
                $_SESSION['error'] .= "Fiche copiée dans ISsubmit mais problème lors de la suppression dans ISfinder<br>";
            }
        } else {
            $_SESSION['error'] .= "Problème d'écriture dans ISsubmit, transfert interrompu<br>";
        }
    } else {
        $_SESSION['error'] .= "Problème de récupération des données dans ISfinder, transfert interrompu<br>";
    }

// ─── Suppression directe : suppr ───────────────────────────────────────────
// Pour ISsubmit : supprime fiche + submiter + hosts liés.
// Pour ISfinder : suppression safe (voir suppression_isfinder dans actions.inc.php).
} elseif ($action == "suppr") {
    if ($bdd == "isfinder") {
        // Suppression safe : ne supprime PAS le submiter de ISfinder
        $suprime = suppression_isfinder($ident, $bdd);
    } else {
        // Suppression complète pour ISsubmit (submiter + hosts + IS)
        $suprime = suppression($ident, '', $bdd);
    }
    if (!$suprime) {
        $_SESSION['error'] .= "probleme lors de la suppression";
    }

} else {
    $_SESSION['error'] = "Action non autorisée";
}

// Retour à la liste
header("Location: ../liste.php?list=1");
?>
