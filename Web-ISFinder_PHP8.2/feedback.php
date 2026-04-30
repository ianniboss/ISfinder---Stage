<!DOCTYPE html>
<html>
<head>
<title>Comments and Suggession</title>
<meta charset="utf-8" /> 
<meta name="author" content="Jo" />
<meta name="keywords" content="Feedback" />
<link type="text/css" rel="stylesheet" href="styles/styles.css" media="screen" />
<link type="text/css" rel="stylesheet" href="styles/submission.css" media="screen" />
<link type="text/css" rel="stylesheet" href="styles/styles_feedback.css" media="screen" />
<link type="text/css" rel="stylesheet" href="styles/menu.css" media="screen" />
<link rel="icon" href="favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
</head>
<body>
<div id="page">
<header>
</header>

<?php 
if (session_status() === PHP_SESSION_NONE) session_start();
$nav_en_cours='about';
include('include/menu.inc.php');
?>
<article>
<section>
    <form action="scripts/feedback_mail.php" method="POST" name="feedback">
        <h2>Feedback</h2>
        <hr/>
        <p class="requis">* Indicates required field</p>
        
        <fieldset id=submitter>
            <legend>Registrant information</legend>
            <ul>
                <li>
                    <label for="title">Title :</label>
                    <select name="title" id="title"> 
                        <option value="prof" <?php if ((!empty($_SESSION['title'])) && $_SESSION['title'] == "prof") echo 'selected'; ?>>Prof.</option> 
                        <option value="ing" <?php if ((!empty($_SESSION['title'])) && $_SESSION['title'] == "ing") echo 'selected'; ?>>Ing.</option> 
                        <option value="dr" <?php if ((!empty($_SESSION['title'])) && $_SESSION['title'] == "dr") echo 'selected'; ?>>Dr.</option> 
                        <option value="mrs" <?php if ((!empty($_SESSION['title'])) && $_SESSION['title'] == "mrs") echo 'selected'; ?>>Mrs.</option> 
                        <option value="mr" <?php if ((!empty($_SESSION['title'])) && $_SESSION['title'] == "mr") echo 'selected'; ?>>Mr.</option>
                    </select>
                </li>
                <li>
                    <label for="nom"><span class="etoile">*</span>First Name :</label>
                    <input type="text" name="Fname" id="nom" required value="<?php echo (!empty($_SESSION['Fname'])) ? htmlspecialchars($_SESSION['Fname']) : ""; ?>" size="25" maxlength="60">
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
                    <input type="text" name="country" id="country" value="<?php echo (!empty($_SESSION['country'])) ? htmlspecialchars($_SESSION['country']) : ""; ?>" size="25" required maxlength="60">
                </li>
            </ul>
            
            <ul>
                <li>
                    <label for="courriel"><span class="etoile">*</span>e-mail address :</label>
                    <input type="email" name="courriel" id="courriel" value="<?php echo (!empty($_SESSION['courriel'])) ? htmlspecialchars($_SESSION['courriel']) : ""; ?>" size="80" required maxlength="80">
                </li>
            </ul>
        </fieldset>

        <fieldset id=comments_section>
            <legend>Comments</legend>
            <textarea cols="110" rows="10" name="comments" id="comments"><?php echo (!empty($_SESSION['comments'])) ? htmlspecialchars($_SESSION['comments']) : ""; ?></textarea>
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
                <li><input type="reset" name="reset" value="Clear"></li>
            </ul>				
        </div>
    </form>
</section>
</article>
<?php include('include/footer.inc.php'); ?>
</div> <!-- Fin du div page -->
</body>
</html>