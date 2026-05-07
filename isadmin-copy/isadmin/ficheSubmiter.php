<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('includes/entete.inc.php');
require_once('includes/aside.inc.php');
require_once("includes/function.inc.php");
require_once("includes/affiche.inc.php");

if (!empty($_SESSION['error'])){
	echo "<p class='erreur'>".$_SESSION['error']."</p><hr/>";
}
/* $_SESSION['Lastname'] =	($_GET['Lastname']) ? strip_tags($_GET['Lastname']) : $_SESSION['Lastname'];
$name = $_SESSION['Lastname'] ;
$condition = "`Lastname` like '".$name."'" ;
*/
		// ID_Submiter
$_SESSION['ID_Submiter'] =	(isset($_GET['ID_Submiter']) && $_GET['ID_Submiter']) ? strip_tags($_GET['ID_Submiter']) : (isset($_SESSION['ID_Submiter']) ? $_SESSION['ID_Submiter'] : "");
$ID_Submiter = $_SESSION['ID_Submiter'] ;
$condition = "`ID_Submiter` like '".$ID_Submiter."'" ;

$_SESSION['bdd'] = $bdd = "isfinder";
	
		/* Connexion à la base de données */
$cnx = connexion($bdd) ;	
if (!$cnx){
	echo "Problème de connexion à la base de données" ;
}else{
	$reqSubmiter = "SELECT * FROM `submiters` WHERE $condition" ;	
	/* Execution de la requette et si résultat, alors on continue */
	$result = execute_sql($cnx,$reqSubmiter);
	If (mysqli_num_rows($result) != 1) {
		header('Location: https://secure.ibcg.biotoul.fr/isadmin');
		exit();		
		}else{	
		$is = mysqli_fetch_array($result);		
		foreach($is as $index=>$valeur){
				$_SESSION[$index] = strip_tags($valeur) ;
		}					

	mysqli_close($cnx);
}  // Fin du if resultat (mysqli_num_rows($result) != 1)
}	// Fin du else il y a connexion

$background = base_color("ISsubmiters") ;		
$fond_base = 'class="base_ISsubmiters"';  // couleur de background des <TH> 
?>

<!--    <link type="text/css" rel="stylesheet" href="styles/ficheMGE.css" media="screen" />      -->
<link type="text/css" rel="stylesheet" href="styles/fiche.css" media="screen" />
<script type="text/javascript" src="scripts/function.js"></script>

<article style="background-color:<?php echo $background; ?>">
<!--		<div class="ecran">contenu de mon &eacutecran></div> -->
	<section>

<form method="post" action="scripts/modifSubmiter.php" name="ficheSubmiter">

 <fieldset id=submitter>
    <legend>Submitter information</legend>
    <ul><li>
        <label for=nom>First Name :</label>
  		<INPUT TYPE="text" NAME="Firstname" required VALUE="<?php  echo isset($_SESSION['Firstname']) ? $_SESSION['Firstname'] : ""; ?>" SIZE="25" MAXLENGTH=60>
  		</li><li>
        <label for=mname>Middle Name :</label>
		<INPUT TYPE="text" NAME="Middlename" VALUE="<?php  echo isset($_SESSION['Middlename']) ? $_SESSION['Middlename'] : ""; ?>" SIZE="20" MAXLENGTH=60>
        </li><li>
        <label for=lname>Last Name :</label>
		<INPUT TYPE="text" NAME="Lastname" VALUE="<?php  echo isset($_SESSION['Lastname']) ?  $_SESSION['Lastname'] : ""; ?>" SIZE="25" required MAXLENGTH=60>
     </li></ul>
     <ul><li>
        <label for=institut>Institution :</label>
		<INPUT TYPE="text" NAME="Institution" VALUE="<?php  echo isset($_SESSION['Institution']) ?  $_SESSION['Institution'] : ""; ?>" SIZE=80 required MAXLENGTH=100>
  		</li><li>
         <label for=depart>Department :</label>
		<INPUT TYPE="text" NAME="Department" VALUE="<?php  echo isset($_SESSION['Department']) ?  $_SESSION['Department'] : ""; ?>" SIZE=80 MAXLENGTH=100>
  		</li><li>
         <label for=address>Postal address :</label>
		<INPUT TYPE="text" NAME="Address" VALUE="<?php  echo isset($_SESSION['Address']) ?  $_SESSION['Address'] : ""; ?>" SIZE=80 MAXLENGTH=100>         
	</li></ul>
    <ul><li>
        <label for=postCode>Postal/ZIP code :</label>
  		<INPUT TYPE="text" NAME="Code" VALUE="<?php  echo isset($_SESSION['Code']) ?  $_SESSION['Code'] : ""; ?>" SIZE="25" MAXLENGTH=60>
  		</li><li>
        <label for=country>Country :</label>
		<INPUT TYPE="text" NAME="Country" VALUE="<?php  echo isset($_SESSION['Country']) ?  $_SESSION['Country'] : ""; ?>" SIZE="27" required MAXLENGTH=60>
        </li></ul>
    <ul><li>
        <label for=courriel>e-mail address :</label>
  		<INPUT TYPE="email" NAME="Mail" VALUE="<?php  echo isset($_SESSION['Mail']) ?  $_SESSION['Mail'] : ""; ?>" SIZE="40" required MAXLENGTH=80>
  		</li><li>
        <label for=tel>Telephone :</label>
		<INPUT TYPE="text" NAME="Phone" VALUE="<?php  echo isset($_SESSION['Phone']) ?  $_SESSION['Phone'] : ""; ?>" SIZE="20" MAXLENGTH=60>
    </li></ul>
   </fieldset>        

    	<div class="piedSection">
			<ul>
			<li><input type="submit" name="Onsubmit" value="Submit"></li>
			<li><INPUT TYPE="reset" name="reset" VALUE="Reset Defaults" onclick = "loadPage(window.location.pathname,0);"></li>
			</ul>				
		</div>
    </form>
 
    </section> 
</article>

</div><!-- Fin du div page de entete.php-->
</body>
</html>