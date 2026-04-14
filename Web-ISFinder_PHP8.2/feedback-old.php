<?php
require("scripts/ptitcaptcha.php");
?>
<!DOCTYPE html>
<html>
<head>
<title>Comments and Suggession</title>
<meta charset="utf-8" /> 
<meta name="author" content="Jo" />
<meta name="keywords" content="Feedback" />
<link type="text/css" rel="stylesheet" href="styles/styles.css" media="screen" />
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
$nav_en_cours='about';
include('include/menu.inc.php');
?>
<article>
<section>
<form action="scripts/feedback_mail.php" method="POST" name="feedback">
		<h2>Feedback</h2>
		<hr/>
        <p class="requis">* Indicates required field</p>
 <fieldset>
    <legend>Registrant information</legend>
    <ul><li>
        <label for=title>title :</label>
      <SELECT name=title> 
      <OPTION value="prof">Prof.</OPTION> 
      <OPTION value="ing">Ing.</OPTION> 
      <OPTION value="dr">Dr.</OPTION> 
      <OPTION value="mrs">Mrs.</OPTION> 
      <OPTION value="mr">Mr.</OPTION></SELECT>
        </li><li>
        <label for=nom><span class="etoile">*</span>First Name :</label>
  		<INPUT TYPE="text" NAME="Fname" required VALUE="" SIZE="25" MAXLENGTH=60>
  		</li><li>
        <label for=lname><span class="etoile">*</span>Last Name :</label>
		<INPUT TYPE="text" NAME="Lname" required VALUE="" SIZE="25" MAXLENGTH=60>
     </li></ul>
     <ul><li>
        <label for=institut><span class="etoile">*</span>Institution :</label>
		<INPUT TYPE="text" NAME="institution" VALUE="" SIZE=80 required MAXLENGTH=100>
  		</li><li>
         <label for=depart>Department :</label>
		<INPUT TYPE="text" NAME="department" VALUE="" SIZE=80 MAXLENGTH=100>
  		</li><li>
         <label for=address>Postal address :</label>
		<INPUT TYPE="text" NAME="address" VALUE="" SIZE=80 MAXLENGTH=100>         
	</li></ul>
    <ul><li>
        <label for=postCode>Postal/ZIP code :</label>
  		<INPUT TYPE="text" NAME="postCode" VALUE="" SIZE="25" MAXLENGTH=60>
  		</li><li>
        <label for=country><span class="etoile">*</span>Country :</label>
		<INPUT TYPE="text" NAME="country" VALUE="" SIZE="25" required MAXLENGTH=60>
        </li></ul>
    <ul><li>
        <label for=courriel><span class="etoile">*</span>e-mail address :</label>
  		<INPUT TYPE="email" NAME="courriel" VALUE="" SIZE="80" required MAXLENGTH=80>
  		</li></ul>
   </fieldset>
   <fieldset>
    <legend>Comments</legend>
    <textarea cols="110" rows="10" name="comments"></textarea>
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
			<li><INPUT TYPE="reset" name="reset" VALUE="Clear"></li>
			</ul>				
		</div>
    </form>
</section>
</article>
<?php include('include/footer.inc.php'); ?>
</div> <!-- Fin du div page -->
</body>
</html>