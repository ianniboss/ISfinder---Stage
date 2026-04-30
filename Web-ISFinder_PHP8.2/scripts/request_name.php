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
            include_once("../include/function.inc.php");
            include_once("../include/function_sub.inc.php");
            require_once("ptitcaptcha.php");

            $form_soumis = htmlspecialchars($_POST['Onsubmit'] ?? '', ENT_QUOTES, 'UTF-8');
            if ($form_soumis === "Submit") {
                
                // 1. Verify Captcha first
                if (!PtitCaptchaHelper::checkCaptcha()) {
                    if (session_status() === PHP_SESSION_NONE) session_start();
                    $_SESSION["error"] = "The anti-spam code entered was incorrect. Please try again.";
                    
                    // Save form data to session to avoid re-typing
                    foreach ($_POST as $key => $value) {
                        $_SESSION[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                    }
                    
                    header("Location: ../request_name_form.php");
                    exit();
                }

                // Connexion à la base de données
                $cnx = connexion('localhost', 'ibinsyahrulazlan', 'ISsubmit', 'yNCNLvH9vwX^f~$i');
 
                if (!$cnx) {
                    die("<p class='erreur'>Server connection error. Please try again later.</p>");
                }

                // Initialisation des erreurs 
                $_SESSION["error"] = "";

                // Nettoyage et assignation des champs POST
                foreach ($_POST as $champ => $valeur) {
                    $valeur = trim(htmlspecialchars($valeur, ENT_QUOTES, 'UTF-8'));
                    $$champ = $_SESSION[$champ] = $valeur;
                }

                // Validation des champs
                $_SESSION["error"] .= empty($Fname) ? "First name is required.<br>" : "";
                $_SESSION["error"] .= empty($Lname) ? "Last name is required.<br>" : "";
                $_SESSION["error"] .= empty($institution) ? "Institution is required.<br>" : "";
                $_SESSION["error"] .= empty($courriel) || !filter_var($courriel, FILTER_VALIDATE_EMAIL) ? "Valid email is required.<br>" : "";

                if (empty($_SESSION["error"])) {
                    // Ensuring all optional variables are defined to avoid PHP 8 crashes
                    $Mname = $Mname ?? "";
                    $department = $department ?? "";
                    $address = $address ?? "";
                    $postCode = $postCode ?? "";
                    $tel = $tel ?? "";
                    $bact_comments = $bact_comments ?? "";

                    // 1. First Insert (Submitters)
                    $sql_sub = "INSERT INTO submiters(Firstname, Middlename, Lastname, Institution, Department, Address, Code, Country, Mail, Phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $cnx->prepare($sql_sub);

                    if (!$stmt) {
                        die("<p class='erreur'>Prepare failed: " . htmlspecialchars($cnx->error) . "</p>");
                    }

                    $stmt->bind_param("ssssssssss", $Fname, $Mname, $Lname, $institution, $department, $address, $postCode, $country, $courriel, $tel);
                    $stmt->execute();
                    $ID_Submiter = $cnx->insert_id;
                    $stmt->close();

                    // 2. Second Insert (Request Names)
                    $date_req_name = date("Y-m-d");
                    $sql_name = "INSERT INTO request_names(bact_origin, submiters_ID_Submiter, comments_author, nbr_names, MGE_type, date_demande) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt2 = $cnx->prepare($sql_name);

                    if (!$stmt2) {
                        die("<p class='erreur'>Second Prepare failed: " . htmlspecialchars($cnx->error) . "</p>");
                    }

                    $stmt2->bind_param("sisiss", $bact_host, $ID_Submiter, $bact_comments, $nb_name, $typeMGE, $date_req_name);
                    $stmt2->execute();
                    $stmt2->close();

                    // 3. Determine MGE type
                    $typeMGE_int = intval($typeMGE);
                    switch ($typeMGE_int) {
                        case 2: $type = "MITE"; break;
                        case 4: $type = "MIC"; break;
                        case 5: $type = "tIS"; break;
                        default: $type = "IS"; break;
                    }

                    // 4. Emails
                    // User confirmation
                    $headers = "From: " . addressMail('', 'cbi.webadmin-isfinder', '') . "\r\n";
                    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
                    $headers .= "X-Mailer: PHP/ISFinder\r\n";
                    
                    $texte = "IS Name Attribution Form\n\n";
                    $texte .= $Fname . " " . $Lname . ", ";
                    $texte .= "you will receive an email with the attributed " . $type . " name as soon as possible:\n";
                    $texte .= "For your request, Host : " . $bact_host . "\n";
                    $texte .= "Comments: " . $bact_comments . "\n\n";
                    $texte .= "Thank you for your interest in our IS Database.\n";
                    mail($courriel, "[ISfinder] IS name attribution request", $texte, $headers);

                    // Team notification
                    $cc = addressMail('', "mc2126", "georgetown.edu") . ',' . addressMail("Patricia", "Siguier", "") . ',' . addressMail("Jacques", "Mahillon", "uclouvain.be");
                    $headers_team = "From: " . addressMail('', 'cbi.webadmin-isfinder', '') . "\r\n";
                    $headers_team .= "CC: " . $cc . "\r\n";
                    $headers_team .= "Content-Type: text/plain; charset=utf-8\r\n";
                    $headers_team .= "X-Mailer: PHP/ISFinder\r\n";
                    
                    $texte_team = "IS Name Attribution Form :\n";
                    $texte_team .= "Name: " . $Fname . " " . $Mname . " " . $Lname . "\n";
                    $texte_team .= "Institution: " . $institution . "\n";
                    $texte_team .= "Email: " . $courriel . "\n";
                    $texte_team .= "Host: " . $bact_host . "\n";
                    $texte_team .= "Comments: " . $bact_comments . "\n";
                    
                    mail(addressMail('', 'cbi.webadmin-isfinder', ''), "[ISfinder] New name request", $texte_team, $headers_team);

                    // Final Success Message
                    echo "<h2>Success!</h2>";
                    echo "<p>Your request for a new $type name has been submitted successfully.</p>";
                    echo "<p>A confirmation email has been sent to <strong>" . htmlspecialchars($courriel) . "</strong>.</p>";
                    echo "<hr/><p><a href='../request_name_form.php'>Back to form</a> | <a href='../index.php'>Back to Home</a></p>";
                } else {
                    // Redirect back if there are validation errors
                    header("Location: ../request_name_form.php");
                    exit();
                }
            }
            ?>
        </section>
    </article>
    <?php include('../include/footer.inc.php'); ?>
</div>
</body>
</html>
