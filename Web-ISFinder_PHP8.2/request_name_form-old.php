<?php
session_start();
// ____________________________________________________Gestion du bouton reset_____________________________
$raz = (empty($_GET['raz'])) ? 1 : intval($_GET['raz']);
if ($raz == 1) {					
 	session_unset();
    }

require("scripts/ptitcaptcha.php");
?>
<!DOCTYPE html>
<html>
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
<header>
</header>

<?php 
$nav_en_cours='submission';
include('include/menu.inc.php');
?>

<article>
<?php
	if (!empty($_SESSION['error'])){
		echo "<p class='erreur'>".htmlspecialchars($_SESSION['error'])."</p><hr/>";
	}
?>

	<section>
    <form action="scripts/request_name.php" method="POST" name="request_name">
		<h2>MGE Name Attribution</h2>
		<hr/>
        <p class="requis">* Indicates required field</p>
 <fieldset id=submitter>
    <legend>Registrant information</legend>
    <ul><li>
        <label for=nom><span class="etoile">*</span>First Name :</label>
  		<INPUT TYPE="text" NAME="Fname" required VALUE="<?php  echo (!empty($_SESSION['Fname'])) ?  $_SESSION['Fname'] : ""; ?>" SIZE="25" MAXLENGTH=60>
  		</li><li>
        <label for=mname>Middle Name :</label>
		<INPUT TYPE="text" NAME="Mname" VALUE="<?php  echo (!empty($_SESSION['Mname'])) ?  $_SESSION['Mname'] : ""; ?>" SIZE="20" MAXLENGTH=60>
        </li><li>
        <label for=lname><span class="etoile">*</span>Last Name :</label>
		<INPUT TYPE="text" NAME="Lname" required VALUE="<?php  echo (!empty($_SESSION['Fname'])) ?  $_SESSION['Fname'] : ""; ?>" SIZE="25" MAXLENGTH=60>
     </li></ul>
     <ul><li>
        <label for=institut><span class="etoile">*</span>Institution :</label>
		<INPUT TYPE="text" NAME="institution" VALUE="<?php  echo (!empty($_SESSION['institution'])) ?  $_SESSION['institution'] : ""; ?>" SIZE=80 required MAXLENGTH=100>
  		</li><li>
         <label for=depart>Department :</label>
		<INPUT TYPE="text" NAME="department" VALUE="<?php  echo (!empty($_SESSION['department'])) ?  $_SESSION['department'] : ""; ?>" SIZE=80 MAXLENGTH=100>
  		</li><li>
         <label for=address>Postal address :</label>
		<INPUT TYPE="text" NAME="address" VALUE="<?php  echo (!empty($_SESSION['address'])) ?  $_SESSION['address'] : ""; ?>" SIZE=80 MAXLENGTH=100>         
	</li></ul>
    <ul><li>
        <label for=postCode>Postal/ZIP code :</label>
  		<INPUT TYPE="text" NAME="postCode" VALUE="<?php  echo (!empty($_SESSION['postCode'])) ?  $_SESSION['postCode'] : ""; ?>" SIZE="25" MAXLENGTH=60>
  		</li><li>
        <label for=country><span class="etoile">*</span>Country :</label>
		<INPUT TYPE="text" NAME="country" VALUE="<?php  echo (!empty($_SESSION['country'])) ?  $_SESSION['country'] : ""; ?>" SIZE="27" required MAXLENGTH=60>
        </li></ul>
    <ul><li>
        <label for=courriel><span class="etoile">*</span>e-mail address :</label>
  		<INPUT TYPE="email" NAME="courriel" VALUE="<?php  echo (!empty($_SESSION['courriel'])) ?  $_SESSION['courriel'] : ""; ?>" SIZE="40" required MAXLENGTH=80>
  		</li><li>
        <label for=tel>Telephone :</label>
		<INPUT TYPE="text" NAME="tel" VALUE="<?php  echo (!empty($_SESSION['tel'])) ?  $_SESSION['tel'] : ""; ?>" SIZE="20" MAXLENGTH=60>
    </li></ul>
   </fieldset>        
  <fieldset id=bacterial>
    <legend>Bacterial information :</legend>
    <ul><li>
        <label for=bact_host class="label_court"><span class="etoile">*</span>Bacterial host :</label>
		<INPUT TYPE="text" NAME="bact_host" VALUE="<?php  echo (!empty($_SESSION['bact_host'])) ?  $_SESSION['bact_host'] : ""; ?>" SIZE="80" required MAXLENGTH=100>
	</li><li>
        <label for=MGEtype class="label_court">MGE type :</label>
		<SELECT NAME="typeMGE">
		<OPTION value="1" selected<?php if ((!empty($_SESSION['typeMGE'])) && $_SESSION['typeMGE'] == "1") echo 'selected="selected"'; ?>>IS </option>
        <OPTION value="2" <?php if ((!empty($_SESSION['typeMGE'])) && $_SESSION['typeMGE'] == "2") echo 'selected="selected"'; ?>>MITE </option>
		<OPTION value="4" <?php if ((!empty($_SESSION['typeMGE'])) && $_SESSION['typeMGE'] == "4") echo 'selected="selected"'; ?>>MIC </option>
		<OPTION value="5" <?php if ((!empty($_SESSION['typeMGE'])) && $_SESSION['typeMGE'] == "5") echo 'selected="selected"'; ?>>tIS </option>
        </SELECT>         
 	</li><li>
        <label for=nb_name class="label_large">Number of names requested for this host :</label>
		<INPUT TYPE="number" NAME="nb_name" VALUE="1" max="20" maxlength="2" size="3" min= "1" step="1">
	</li></ul>
<section>
    <div class="enteteSection">
	<span class='entete_propriete'>Comments</span>
	</div>
   	<textarea cols=100 name="bact_comments"><?php  echo (!empty($_SESSION['bact_comments'])) ?  $_SESSION['bact_comments'] : ""; ?></textarea>
</section>
 <section>
 <BR>
	If you need a name for a transposon, please ask it at <a href="https://transposon.lstmed.ac.uk/" target="_blank">Tn number registry</a>
 </section>
</fieldset> 
<p>
  To validate your submission, please type the above text in the field below : 
	<?php echo PtitCaptchaHelper::generateImgTags("scripts/")?>
	<?php echo PtitCaptchaHelper::generateHiddenTags()?>
	<?php echo PtitCaptchaHelper::generateInputTags()?>
	</p>
    	<div class="piedSection">
			<ul>
			<li><input type="submit" name="Onsubmit" value="Submit"></li>
			<li><INPUT TYPE="reset" name="reset" VALUE="Reset Defaults" onclick = "loadPage(window.location.pathname,1);"></li>
			</ul>				
		</div>
    </form>
   </section>
</article>

<?php include('include/footer.inc.php'); ?>

</div> <!-- Fin du div page -->
</body>
</html>