<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Result of your query</title>
    <meta charset="utf-8" />
    <link type="text/css" rel="stylesheet" href="../styles/styles.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="../styles/menu.css" media="screen" />
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
</head>

<body>
<div id="page">
    <header></header>

    <?php
    $nav_en_cours = 'tools';
    include('../include/menu.inc.php');
    ?>

    <article>
        <section>
            <?php
            // Configuration pour le débogage
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);

            // Inclusion des fichiers de fonctions
            include_once("../include/function.inc.php");
            include_once("../include/function_sub.inc.php");

            // Vérification de la soumission du formulaire
            $form_soumis = htmlspecialchars($_POST['Onsubmit'] ?? '', ENT_QUOTES, 'UTF-8');
            if ($form_soumis === "Submit") {
                // Initialisation des erreurs
                $_SESSION["error"] = "";

                // Nettoyage et assignation des champs POST
                foreach ($_POST as $champ => $valeur) {
                    $valeur = trim(htmlspecialchars($valeur, ENT_QUOTES, 'UTF-8'));
                    $$champ = $_SESSION[$champ] = $valeur;
                }

                // Validation des champs
                $_SESSION["error"] .= empty($Fname) || preg_match("/[^a-zA-Z- \éèêç']/u", $Fname) ? "First name correct is required.<br>" : "";
                $_SESSION["error"] .= empty($Lname) || preg_match("/[^a-zA-Z- \éèêç']/u", $Lname) ? "Last name correct is required.<br>" : "";
                $_SESSION["error"] .= empty($institution) || strlen($institution) < 2 ? "Field institution is required.<br>" : "";
                $_SESSION["error"] .= empty($country) || strlen($country) < 2 ? "Field country is required.<br>" : "";
                $_SESSION["error"] .= empty($courriel) ? "E-mail address is required.<br>" : "";
                $_SESSION["error"] .= empty($bact_host) || preg_match("/[^a-zA-Z0-9- _:.']/u", $bact_host) ? "Bacterial host is required.<br>" : "";

                // Validation de l'email
                if (filter_var($courriel, FILTER_VALIDATE_EMAIL) === false) {
                    $_SESSION["error"] .= "E-mail address is not valid.<br>";
                }

                // Si aucune erreur, traitement de la base de données
                if (empty($_SESSION["error"])) {
                    // Connexion à la base de données
                    $cnx = connexion("ISsubmit");

                    // Insertion des informations du soumissionnaire
                    $sql_sub = "INSERT INTO submiters(Firstname, Middlename, Lastname, Institution, Department, Address, Code, Country, Mail, Phone) ";
                    $sql_sub .= "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $cnx->prepare($sql_sub);
                    $stmt->bind_param(
                        "ssssssisss",
                        $Fname,
                        $Mname,
                        $Lname,
                        $institution,
                        $department,
                        $address,
                        $postCode,
                        $country,
                        $courriel,
                        $tel
                    );
                    $stmt->execute();
                    $ID_Submiter = $stmt->insert_id;
                    $stmt->close();

                    // Insertion des informations concernant l'hôte bactérien
                    $date_req_name = date("Y-m-d");
                    $sql_sub = "INSERT INTO request_names(bact_origin, submiters_ID_Submiter, comments_author, nbr_names, MGE_type, date_demande) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $cnx->prepare($sql_sub);
                    $stmt->bind_param(
                        "sisiss",
                        $bact_host,
                        $ID_Submiter,
                        $bact_comments,
                        $nb_name,
                        $typeMGE,
                        $date_req_name
                    );
                    $stmt->execute();
                    $stmt->close();

                    // Détermination du type de GE
                    $type = match (intval($typeMGE)) {
                        2 => "MITE",
                        4 => "MIC",
                        5 => "tIS",
                        default => "IS",
                    };

                    // Envoi du mail de confirmation au soumissionnaire
                    $headers = [
                        "From: " . addressMail('', 'cbi.webadmin-isfinder', ''),
                        "Content-Type: text/plain; charset=utf-8",
                        "X-Mailer: PHP/ISFinder"
                    ];
                    $texte = "IS Name Attribution Form\n\n";
                    $texte .= $Fname . " " . $Lname . ", ";
                    $texte .= "you will receive an email with the attributed " . $type . " name as soon as possible:\n";
                    $texte .= "For your request, Host : " . $bact_host . "\n";
                    $texte .= "Comments: " . $bact_comments . "\n\n";
                    $texte .= "Thank you for your interest in our IS Database.\n";
                    mail($courriel, "[ISfinder] IS name attribution request", $texte, implode("\r\n", $headers));

                    // Envoi du mail à l'équipe ISfinder
                    $to = addressMail('', 'cbi.webadmin-isfinder', '');
                    $cc = addressMail('', "mc2126", "georgetown.edu") . ',' . addressMail("Patricia", "Siguier", "") . ',' . addressMail("Jacques", "Mahillon", "uclouvain.be");
                    $headers = [
                        "From: " . addressMail('', 'cbi.webadmin-isfinder', ''),
                        "CC: " . $cc,
                        "X-Mailer: PHP/ISFinder",
                        "Content-Type: text/plain; charset=utf-8"
                    ];
                    $texte = "IS Name Attribution Form :\n";
                    $texte .= "Name: " . $Fname . " " . $Mname . " " . $Lname . "\n";
                    $texte .= "Institution: " . $institution . "\n";
                    $texte .= "Department: " . $department . "\n";
                    $texte .= "Address: " . $address . "\n";
                    $texte .= "         " . $postCode . "\n";
                    $texte .= "Country: " . $country . "\n";
                    $texte .= "Email: " . $courriel . "\n";
                    $texte .= "Telephone: " . $tel . "\n\n";
                    $texte .= "Request: " . $nb_name . " " . $type . "\n";
                    $texte .= "Host: " . $bact_host . "\n";
                    $texte .= "Comments: " . $bact_comments . "\n";
                    mail($to, "[ISfinder] IS name attribution request", $texte, implode("\r\n", $headers));

                    // Affichage de confirmation
                    echo "Your application form has been registered,<br>";
                    echo "Thank you for your interest in our IS Database.<br><br><HR>";
                    echo "<a href='https://lmgm.cbi-toulouse.fr/en/home/' target='_top'><b>LMGM</b></a>&nbsp;&nbsp; | &nbsp;&nbsp;<a href='https://www-is.biotoul.fr/' target='_top'><b>IS HomePage</b></a>";

                    // Fermeture de la connexion et destruction de la session
                    mysqli_close($cnx);
                    session_destroy();
                } else {
                    // Redirection en cas d'erreur
                    header("Location: /request_name_form.php");
                    exit();
                }
            } else {
                // Redirection si le formulaire n'a pas été soumis
                header("Location: /request_name_form.php");
                exit();
            }
            ?>
        </section>
    </article>

    <?php include('../include/footer.inc.php'); ?>
</div> <!-- Fin du div page -->
</body>
</html>
