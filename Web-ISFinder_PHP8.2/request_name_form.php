<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Gestion du bouton reset
$raz = (isset($_GET['raz'])) ? intval($_GET['raz']) : 0;
if ($raz == 1) {
    session_unset();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>ISfinder request a name</title>
    <meta charset="utf-8" /> 
    <meta name="author" content="Jo" />
    <meta name="keywords" content="IS, Insertion Sequence" />
    <link type="text/css" rel="stylesheet" href="styles/styles.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="styles/submission.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="styles/menu.css" media="screen" />
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <script type="text/javascript" src="scripts/function_submission.js"></script>
</head>

<body>
<div id="page">
    <header></header>

    <?php 
    $nav_en_cours = 'submission';
    include('include/menu.inc.php');
    ?>

    <article>
        <section>
            <form action="scripts/request_name.php" method="POST" name="request_name">
                <h2>MGE Name Attribution</h2>
                <hr/>
                <p class="requis">* Indicates required field</p>
                
                <fieldset id="submitter">
                    <legend>Registrant information</legend>
                    <ul>
                        <li>
                            <label for="nom"><span class="etoile">*</span>First Name :</label>
                            <input type="text" name="Fname" id="nom" required value="<?php echo (!empty($_SESSION['Fname'])) ? htmlspecialchars($_SESSION['Fname']) : ""; ?>" size="25" maxlength="60">
                        </li>
                        <li>
                            <label for="mname">Middle Name :</label>
                            <input type="text" name="Mname" id="mname" value="<?php echo (!empty($_SESSION['Mname'])) ? htmlspecialchars($_SESSION['Mname']) : ""; ?>" size="20" maxlength="60">
                        </li>
                        <li>
                            <label for="lname"><span class="etoile">*</span>Last Name :</label>
                            <input type="text" name="Lname" id="lname" required value="<?php echo (!empty($_SESSION['Lname'])) ? htmlspecialchars($_SESSION['Lname']) : ""; ?>" size="25" maxlength="60">
                        </li>
                    </ul>
                    
                    <ul>
                        <li>
                            <label for="institut"><span class="etoile">*</span>Institution :</label>
                            <input type="text" name="institution" id="institut" value="<?php echo (!empty($_SESSION['institution'])) ? htmlspecialchars($_SESSION['institution']) : ""; ?>" size="80" required maxlength="100">
                        </li>
                        <li>
                            <label for="depart">Department :</label>
                            <input type="text" name="department" id="depart" value="<?php echo (!empty($_SESSION['department'])) ? htmlspecialchars($_SESSION['department']) : ""; ?>" size="80" maxlength="100">
                        </li>
                        <li>
                            <label for="address">Postal address :</label>
                            <input type="text" name="address" id="address" value="<?php echo (!empty($_SESSION['address'])) ? htmlspecialchars($_SESSION['address']) : ""; ?>" size="80" maxlength="100">         
                        </li>
                    </ul>
                    
                    <ul>
                        <li>
                            <label for="postCode">Postal/ZIP code :</label>
                            <input type="text" name="postCode" id="postCode" value="<?php echo (!empty($_SESSION['postCode'])) ? htmlspecialchars($_SESSION['postCode']) : ""; ?>" size="25" maxlength="60">
                        </li>
                        <li>
                            <label for="country"><span class="etoile">*</span>Country :</label>
                            <input type="text" name="country" id="country" value="<?php echo (!empty($_SESSION['country'])) ? htmlspecialchars($_SESSION['country']) : ""; ?>" size="27" required maxlength="60">
                        </li>
                    </ul>
                    
                    <ul>
                        <li>
                            <label for="courriel"><span class="etoile">*</span>e-mail address :</label>
                            <input type="email" name="courriel" id="courriel" value="<?php echo (!empty($_SESSION['courriel'])) ? htmlspecialchars($_SESSION['courriel']) : ""; ?>" size="40" required maxlength="80">
                        </li>
                        <li>
                            <label for="tel">Telephone :</label>
                            <input type="text" name="tel" id="tel" value="<?php echo (!empty($_SESSION['tel'])) ? htmlspecialchars($_SESSION['tel']) : ""; ?>" size="20" maxlength="60">
                        </li>
                    </ul>
                </fieldset>
                
                <fieldset id="bacterial">
                <legend>Bacterial information :</legend>
                <ul>
                    <li>
                        <label for="bact_host" class="label_court"><span class="etoile">*</span>Bacterial host :</label>
                        <input type="text" name="bact_host" id="bact_host" value="<?php echo (!empty($_SESSION['bact_host'])) ? htmlspecialchars($_SESSION['bact_host']) : ""; ?>" size="80" required maxlength="100">
                    </li>
                    <li>
                        <label for="MGEtype" class="label_court">MGE type :</label>
                        <select name="typeMGE" id="MGEtype">
                            <option value="1" <?php if ((!empty($_SESSION['typeMGE'])) && $_SESSION['typeMGE'] == "1") echo 'selected="selected"'; ?>>IS</option>
                            <option value="2" <?php if ((!empty($_SESSION['typeMGE'])) && $_SESSION['typeMGE'] == "2") echo 'selected="selected"'; ?>>MITE</option>
                            <option value="4" <?php if ((!empty($_SESSION['typeMGE'])) && $_SESSION['typeMGE'] == "4") echo 'selected="selected"'; ?>>MIC</option>
                            <option value="5" <?php if ((!empty($_SESSION['typeMGE'])) && $_SESSION['typeMGE'] == "5") echo 'selected="selected"'; ?>>tIS</option>
                        </select>         
                    </li>
                    <li>
                        <label for="nb_name" class="label_large">Number of names requested for this host :</label>
                        <input type="number" name="nb_name" id="nb_name" value="1" max="20" maxlength="2" size="3" min="1" step="1">
                    </li>
                </ul>
                <section>
                    <br>
                    If you need a name for a transposon, please ask it at <a href="https://transposon.lstmed.ac.uk/" target="_blank">Tn number registry</a>
                </section>
            </fieldset>

            <fieldset id=comments_section>
                <legend>Comments</legend>
                <textarea cols="100" rows="5" name="bact_comments" id="bact_comments"><?php echo (!empty($_SESSION['bact_comments'])) ? htmlspecialchars($_SESSION['bact_comments']) : ""; ?></textarea>
            </fieldset>

            <fieldset id=captcha>
                <legend>Anti-Spam Verification</legend>
                <?php
                require_once('scripts/ptitcaptcha.php');
                ?>
                <ul>
                    <li>
                        <label>To validate your submission, please type the above text in the field below :</label>
                        <?php 
                        echo PtitCaptchaHelper::generateImgTags('');
                        echo PtitCaptchaHelper::generateHiddenTags();
                        echo PtitCaptchaHelper::generateInputTags();
                        
                        if (!empty($_SESSION['error'])) {
                            echo "<script type='text/javascript'>
                                    const captchaInput = document.getElementsByName('ptitcaptcha_entry')[0];
                                    captchaInput.setCustomValidity('" . addslashes(strip_tags($_SESSION['error'])) . "');
                                    captchaInput.reportValidity();
                                    // Reset validity on input so the user can type
                                    captchaInput.oninput = () => captchaInput.setCustomValidity('');
                                  </script>";
                            unset($_SESSION['error']);
                        }
                        ?>
                    </li>
                </ul>
            </fieldset>

                <div class="piedSection">
                    <ul>
                        <li><input type="submit" name="Onsubmit" value="Submit"></li>
                        <li><input type="reset" name="reset" value="Reset Defaults" onclick="loadPage(window.location.pathname, 1);"></li>
                    </ul>				
                </div>
            </form>
        </section>
    </article>

    <?php include('include/footer.inc.php'); ?>

</div> <!-- Fin du div page -->
</body>
</html>