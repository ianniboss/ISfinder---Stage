<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Comments and Suggestions</title>
    <meta charset="utf-8" /> 
    <meta name="author" content="Jo" />
    <meta name="keywords" content="Feedback" />
    <link type="text/css" rel="stylesheet" href="../styles/styles_feedback.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="../styles/styles.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="../styles/menu.css" media="screen" />
    <link rel="icon" href="../favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
</head>

<body>
<div id="page">
    <header></header>

    <?php 
    $nav_en_cours = 'about';
    include('../include/menu.inc.php');
    ?>

    <article>
        <section>
            <?php
            include_once("../include/function.inc.php");
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
                    
                    header("Location: ../feedback.php");
                    exit();
                }

                // Nettoyage et assignation des champs POST
                foreach ($_POST as $champ => $valeur) {
                    $valeur = trim(htmlspecialchars($valeur, ENT_QUOTES, 'UTF-8'));
                    $$champ = $_SESSION[$champ] = $valeur;
                }

                // Ensuring all optional variables are defined to avoid PHP 8 crashes
                $title = $title ?? "";
                $Fname = $Fname ?? "";
                $Lname = $Lname ?? "";
                $institution = $institution ?? "";
                $department = $department ?? "";
                $address = $address ?? "";
                $postCode = $postCode ?? "";
                $country = $country ?? "";
                $courriel = $courriel ?? "";
                $comments = $comments ?? "";

                // Verification des champs
                if (empty($courriel) || !filter_var($courriel, FILTER_VALIDATE_EMAIL)) {
                    die(erreur_sub("email address", 0));
                }
                if (empty($Lname)) { die(erreur_sub("Last name", 1)); }
                if (empty($Fname)) { die(erreur_sub("first name", 1)); }
                if (empty($institution)) { die(erreur_sub("institution", 1)); }
                if (empty($country)) { die(erreur_sub("country", 1)); }

                // Envoi du mail aux personnes concernées
                $to = addressMail('', 'cbi.webadmin-isfinder', '');
                $cc = addressMail('', "mc2126", "georgetown.edu") . ',' . addressMail("Patricia", "Siguier", "") . ',' . addressMail("Jacques", "Mahillon", "uclouvain.be");
                
                $headers = "From: " . addressMail('', 'cbi.webadmin-isfinder', '') . "\r\n";
                $headers .= "CC: " . $cc . "\r\n";
                $headers .= "Content-Type: text/html; charset=utf-8\r\n";	
                $headers .= "X-Mailer: PHP/ISFinder\r\n";
                
                $texte = "<html><body>";
                $texte .= "<h3>IS Feedback form</h3>";
                $texte .= "<strong>Name:</strong> " . $title . " " . $Fname . " " . $Lname . "<br>";
                $texte .= "<strong>Institution:</strong> " . $institution . "<br>";
                $texte .= "<strong>Department:</strong> " . $department . "<br>";
                $texte .= "<strong>Address:</strong> " . $address . " " . $postCode . "<br>";
                $texte .= "<strong>Country:</strong> " . $country . "<br>";
                $texte .= "<strong>Email:</strong> " . $courriel . "<br><br>";
                $texte .= "<strong>Comments:</strong><br>" . nl2br($comments) . "<br>";
                $texte .= "</body></html>";

                @mail($to, "[ISfinder] Feedback form", $texte, $headers);

                echo "<h2>Success!</h2>";
                echo "<p>Thank you for your feedback. It has been sent to the ISfinder team.</p>";
                echo "<hr/><p><a href='../feedback.php'>Back to form</a> | <a href='../index.php'>Back to Home</a></p>";
            }
            ?>
        </section>
    </article>
    <?php include('../include/footer.inc.php'); ?>
</div>
</body>
</html>