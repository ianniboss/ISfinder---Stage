<?php
session_start();//déclare l'ouverture d'une session si aucune n'a été déclarée auparavant.
// ____________________________________________________Gestion du bouton reset_____________________________
if (intval($_GET['raz']) == 1) {					
 	session_unset();
    }
$nb_orf = (isset($_SESSION['nb_orf'])&&($_SESSION['nb_orf']<16)&& ($_SESSION['nb_orf']>=0)) ? intval($_SESSION['nb_orf']) : 1 ;
$nb_site = (isset($_SESSION['nb_site'])&&($_SESSION['nb_site']<50)) ? intval($_SESSION['nb_site']) : 1 ;
$_SESSION['nb_site'] = $nb_site;
?>
<!DOCTYPE html>
<html>
<head>
<title>ISfinder submission</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> 
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
<!--		<div class="ecran">contenu de mon &eacutecran</div> -->
<?php
	if ($_SESSION['error']){
		echo "<p class='erreur'>".$_SESSION['error']."</p><hr/>";
	}
?>
	<section>
    <form action="scripts/subIS.php" method="POST" name="submission">
    				<!-- Champ caché pour savoir quelle modification est demanndée si appel à modifIS.php sans soumission (juste sur OnChange :
                     Ajout site d'insertion, upload fichier ou nbr d'ORF -->  
		<input type='hidden' id='DynModif' name='DynModif' value=''>
		<h2>MGE Submission</h2>
		<hr/>
        <p class="requis">* Indicates required field</p>
 <fieldset id=submitter>
    <legend>Submitter information</legend>
    <ul><li>
        <label for=nom><span class="etoile">*</span>First Name :</label>
  		<INPUT TYPE="text" NAME="Fname" required VALUE="<?php  echo isset($_SESSION['Fname']) ?  $_SESSION['Fname'] : ""; ?>" SIZE="25" MAXLENGTH=60>
  		</li><li>
        <label for=mname>Middle Name :</label>
		<INPUT TYPE="text" NAME="Mname" VALUE="<?php  echo isset($_SESSION['Mname']) ?  $_SESSION['Mname'] : ""; ?>" SIZE="20" MAXLENGTH=60>
        </li><li>
        <label for=lname><span class="etoile">*</span>Last Name :</label>
		<INPUT TYPE="text" NAME="Lname" VALUE="<?php  echo isset($_SESSION['Lname']) ?  $_SESSION['Lname'] : ""; ?>" SIZE="25" required MAXLENGTH=60>
     </li></ul>
     <ul><li>
        <label for=institut><span class="etoile">*</span>Institution :</label>
		<INPUT TYPE="text" NAME="institution" VALUE="<?php  echo isset($_SESSION['institution']) ?  $_SESSION['institution'] : ""; ?>" SIZE=80 required MAXLENGTH=100>
  		</li><li>
         <label for=depart>Department :</label>
		<INPUT TYPE="text" NAME="department" VALUE="<?php  echo isset($_SESSION['department']) ?  $_SESSION['department'] : ""; ?>" SIZE=80 MAXLENGTH=100>
  		</li><li>
         <label for=address>Postal address :</label>
		<INPUT TYPE="text" NAME="address" VALUE="<?php  echo isset($_SESSION['address']) ?  $_SESSION['address'] : ""; ?>" SIZE=80 MAXLENGTH=100>         
	</li></ul>
    <ul><li>
        <label for=postCode>Postal/ZIP code :</label>
  		<INPUT TYPE="text" NAME="postCode" VALUE="<?php  echo isset($_SESSION['postCode']) ?  $_SESSION['postCode'] : ""; ?>" SIZE="25" MAXLENGTH=60>
  		</li><li>
        <label for=country><span class="etoile">*</span>Country :</label>
		<INPUT TYPE="text" NAME="country" VALUE="<?php  echo isset($_SESSION['country']) ?  $_SESSION['country'] : ""; ?>" SIZE="27" required MAXLENGTH=60>
        </li></ul>
    <ul><li>
        <label for=courriel><span class="etoile">*</span>e-mail address :</label>
  		<INPUT TYPE="email" NAME="courriel" VALUE="<?php  echo isset($_SESSION['courriel']) ?  $_SESSION['courriel'] : ""; ?>" SIZE="40" required MAXLENGTH=80>
  		</li><li>
        <label for=tel>Telephone :</label>
		<INPUT TYPE="text" NAME="tel" VALUE="<?php  echo isset($_SESSION['tel']) ?  $_SESSION['tel'] : ""; ?>" SIZE="20" MAXLENGTH=60>
    </li></ul>
   </fieldset>        
  <fieldset id=infoIS>
    <legend>General Information about MGE</legend>
  		<ul><li>
        <label for=isname><span class="etoile">*</span>IS name :</label>
		<INPUT TYPE="text" NAME="isname" VALUE="<?php  echo isset($_SESSION['isname']) ?  $_SESSION['isname'] : ""; ?>" SIZE=15 required MAXLENGTH=20>
  		</li><li>
        <label for=family>Family :</label>
		<INPUT TYPE="text" NAME="family" VALUE="<?php  echo isset($_SESSION['family']) ?  $_SESSION['family'] : ""; ?>" SIZE=15 MAXLENGTH=20>
  		</li><li>
        <label for=group>Group :</label>
		<INPUT TYPE="text" NAME="group" VALUE="<?php  echo isset($_SESSION['group']) ?  $_SESSION['group'] : ""; ?>" SIZE=15 MAXLENGTH=20>
  		</li></ul>
    	<ul><li>
        <label for=MGEtype>MGE type :</label>
		<SELECT NAME="MGEtype"> 
		<OPTION value="1" selected <?php if (isset($_SESSION['MGEtype']) && $_SESSION['MGEtype'] == "1") echo 'selected="selected"'; ?>>IS </option>
        <OPTION value="2" <?php if (isset($_SESSION['MGEtype']) && $_SESSION['MGEtype'] == "2") echo 'selected="selected"'; ?>>MITE </option>
		<OPTION value="4" <?php if (isset($_SESSION['MGEtype']) && $_SESSION['MGEtype'] == "4") echo 'selected="selected"'; ?>>MIC </option>
		<OPTION value="5" <?php if (isset($_SESSION['MGEtype']) && $_SESSION['MGEtype'] == "5") echo 'selected="selected"'; ?>>tIS </option>
		<OPTION value="3" <?php if (isset($_SESSION['MGEtype']) && $_SESSION['MGEtype'] == "3") echo 'selected="selected"'; ?>>Transposon</option>
        </SELECT>         
        </li><li>
        <label for=related_elt>Related element(s) :</label>
		<INPUT TYPE="text" NAME="related_elt" VALUE="<?php  echo isset($_SESSION['related_elt']) ?  $_SESSION['related_elt'] : ""; ?>" SIZE=50 MAXLENGTH=100>
        <div><span class="infosuppl">(Only for non autonomous MGE)</span></div>
  		</li></ul>
<table>
<tr><th>Accession number</th><th>Transposition</th><th><span class="etoile">*</span>Origin (bacterial host)</th><th>Hosts</th></tr>     
<tr>
	<td><INPUT TYPE="text" NAME="numAcc" VALUE="<?php  echo isset($_SESSION['numAcc']) ?  $_SESSION['numAcc'] : ""; ?>" SIZE=17 MAXLENGTH=25></td>
    <td><SELECT NAME="transposition"> 
        <OPTION value="ND"<?php if (isset($_SESSION['transposition']) && $_SESSION['transposition'] == "ND") echo 'selected="selected"'; ?>>ND </option>
		<OPTION value="Y"<?php if (isset($_SESSION['transposition']) && $_SESSION['transposition'] == "Y") echo 'selected="selected"'; ?>>Yes </option>
		<OPTION value="N"<?php if (isset($_SESSION['transposition']) && $_SESSION['transposition'] == "N") echo 'selected="selected"'; ?>>No </option>
		</SELECT> </td>
     <td><INPUT TYPE="text" NAME="origin" VALUE="<?php echo isset($_SESSION['origin']) ?  $_SESSION['origin'] : ""; ?>" SIZE=45 required MAXLENGTH=100></td>
    <td><textarea cols=45 rows=2 name="hosts"><?php echo isset($_SESSION['hosts']) ?  $_SESSION['hosts'] : ""; ?></textarea></td>
</tr></table>    
	<section>
    <div class="enteteSection">
	<span class='entete_propriete'>DNA section</span>
	</div>

        <label for=islength>IS Length :</label>
		<INPUT TYPE="text" NAME="islength" VALUE="<?php  echo isset($_SESSION['islength']) ?  $_SESSION['islength'] : ""; ?>" SIZE=15 MAXLENGTH=20>
	<div class="entete_propriete">Ends</div>
    <ul><li>
        <label for=irlength>IR Length :</label>
		<INPUT TYPE="text" NAME="irlength" VALUE="<?php  echo isset($_SESSION['irlength']) ?  $_SESSION['irlength'] : ""; ?>" SIZE=15 MAXLENGTH=20>
	</li><li>
        <label for=ends_comments>Ends comments :</label>
		<textarea cols=65 name="Ends_comments"><?php  echo isset($_SESSION['Ends_comments']) ?  $_SESSION['Ends_comments'] : ""; ?></textarea>
     </li></ul>

<a name="InsertionSite" id="InsertionSite"></a>
	<div class="entete_propriete">Insertion site</div> 
        <table>
        <tr><th>Left flank</th><th>Direct repeat</th><th>Right flank</th><th>DR Length</th></tr>     
        <?php           // Boucle pour afficher le nombre de sites voulus
        for($j=0 ; $j < $nb_site ; $j++){
        ?>
        <tr>
            <td><INPUT TYPE="text" class="seq" NAME="<?php echo $j;?>DR_Left_Flank" VALUE="<?php  echo isset($_SESSION[$j.'DR_Left_Flank']) ?  $_SESSION[$j.'DR_Left_Flank'] : ""; ?>" SIZE=40 MAXLENGTH=50></td>
            <td><INPUT TYPE="text" class="seq" NAME="<?php echo $j;?>Direct_Repeat" VALUE="<?php  echo isset($_SESSION[$j.'Direct_Repeat']) ?  $_SESSION[$j.'Direct_Repeat'] : ""; ?>" SIZE=30 MAXLENGTH=120></td>
            <td><INPUT TYPE="text" class="seq" NAME="<?php echo $j;?>DR_Rigth_Flank" VALUE="<?php  echo isset($_SESSION[$j.'DR_Rigth_Flank']) ?  $_SESSION[$j.'DR_Rigth_Flank'] : ""; ?>" SIZE=40 MAXLENGTH=50></td>
            <td><INPUT TYPE="text" NAME="<?php echo $j;?>Direct_Repeat_Length" VALUE="<?php  echo isset($_SESSION[$j.'Direct_Repeat_Length']) ?  $_SESSION[$j.'Direct_Repeat_Length'] : ""; ?>" SIZE=6 MAXLENGTH=10></td>
        </tr>
        <?php
            }				// Fin de la boucle for qui affiche les sites d'insertions : general case
        ?>
        <tr>
          <td colspan="4"><img src='images/plus.jpg' alt='Insertion site' onclick="document.getElementById('DynModif').value='1'; document.forms['submission'].submit();"/></td>
        </tr>
        </table>   

	<div class="entete_propriete"><span class="etoile">*</span>DNA sequence</div> 
	<div class="seq"><textarea cols=100 required rows=3 name="dna_seq"><?php  echo isset($_SESSION['dna_seq']) ?  $_SESSION['dna_seq'] : ""; ?></textarea> </div>
   	<div class="piedSection"></div>    
    </section>    
	<section>
    <div class="enteteSection">
	<span class='entete_propriete'>Protein section</span>
	</div>
    
<a name="Orf" id="Orf"></a>
						<!-- Affichage d'une liste de nombre (1 à 15) avec sélection du choix actif
							 et rechargement de la page si choix différent -->    
    <label for=orfnumber>ORF number :</label>
<!--		<select name = "nb_orf"  onchange = "loadPage(window.location.pathname,this.value,0);" />  

_________________Si changement du nombre d'orf on soumet le formulaire pour récupérer les variables de session _______________
mais sans utiliser le bouton Onsubmit ( Attention à ne pas nommer le bouton de soumission "submit" sinon ce script ne fonctionne plus -->
		<select name = "nb_orf"  onchange = "document.getElementById('DynModif').value='2' ; document.forms['submission'].submit();" />
		<script language="javascript">liste_nombre(0,16,<?php echo $nb_orf;?>);</script>
		</select>        

<!--		<select name = "nb_orf"  onchange = "document.forms['submission'].submit();" /> -->
     
<?php           // Boucle pour afficher le nombre d'orf sélectionné
if ($nb_orf !=0){
	for($i=1 ; $i <= $nb_orf ; $i++){
?>
	<div class="entete_propriete">ORF <?php print $i ?> :</div> 
<table>
<tr><th colspan="2" scope="col">Length</th><th>Begin</th><th>End</th><th>Strand</th><th>Fusion ORF</th></tr>     
<tr>
	<td><INPUT TYPE="text" NAME="orf<?php echo $i;?>_lengthbp" align="right" VALUE="<?php echo isset($_SESSION['orf'.$i.'_lengthbp'])? $_SESSION['orf'.$i.'_lengthbp'] : ""; ?>" SIZE=20 MAXLENGTH=50> bp &nbsp;</td>
	<td><INPUT TYPE="text" NAME="orf<?php echo $i;?>_lengthaa" alt="aa" VALUE="<?php echo isset($_SESSION['orf'.$i.'_lengthaa'])? $_SESSION['orf'.$i.'_lengthaa'] : ""; ?>" SIZE=20 MAXLENGTH=50> aa &nbsp;</td>
	<td><INPUT TYPE="text" NAME="orf<?php echo $i;?>_begin" VALUE="<?php echo isset($_SESSION['orf'.$i.'_begin'])? $_SESSION['orf'.$i.'_begin'] : ""; ?>" SIZE=20 MAXLENGTH=120></td>
    <td><INPUT TYPE="text" NAME="orf<?php echo $i;?>_end" VALUE="<?php echo isset($_SESSION['orf'.$i.'_end'])? $_SESSION['orf'.$i.'_end'] : ""; ?>" SIZE=20 MAXLENGTH=50></td>
    <td><SELECT NAME="orf<?php echo $i;?>_strand"> 
		<OPTION value="1"<?php if (isset($_SESSION['orf'.$i.'_strand']) && $_SESSION['orf'.$i.'_strand'] == "1") echo 'selected="selected"'; ?>>&nbsp; + </option>
		<OPTION value="0"<?php if (isset($_SESSION['orf'.$i.'_strand']) && $_SESSION['orf'.$i.'_strand'] == "0") echo 'selected="selected"'; ?>>&nbsp; - </option>
		</SELECT> </td>
    <td><SELECT NAME="orf<?php echo $i;?>_frameshift"> 
		<OPTION value="0"<?php if (isset($_SESSION['orf'.$i.'_frameshift']) && $_SESSION['orf'.$i.'_frameshift'] == "0") echo 'selected="selected"'; ?>> No</option>
		<OPTION value="1"<?php if (isset($_SESSION['orf'.$i.'_frameshift']) && $_SESSION['orf'.$i.'_frameshift'] == "1") echo 'selected="selected"'; ?>>Yes </option>
		</SELECT> </td>
</tr></table>     

	<div class="entete_propriete">ORF function :</div>
    <span id="function">
<!-- L'affichage de la div suivante dépend (fonction JS Affiche_div) de la fonction ORF sélectionnée ici -->

		<select name = "orf<?php echo $i;?>_function" class = "orf_function"onchange = "Affiche_div('functionORF_<?php echo $i;?>',this.value+'_'+<?php echo $i;?>)" />
                       
        	<option value = "">«Choice»</option>
            <option value = "Tnp" <?php if (isset($_SESSION['orf'.$i.'_function']) && $_SESSION['orf'.$i.'_function'] == "Tnp") echo 'selected = "selected"'; ?>>Transposase</option>
            <option value = "AG" <?php if (isset($_SESSION['orf'.$i.'_function']) && $_SESSION['orf'.$i.'_function'] == "AG") echo 'selected = "selected"'; ?>>Accessory gene</option>
            <option value = "PG" <?php if (isset($_SESSION['orf'.$i.'_function']) && $_SESSION['orf'.$i.'_function'] == "PG") echo 'selected = "selected"'; ?>>Passenger gene</option>
                           
    	</select>
    </span>

<!-- Groupe de 3 div : la fonction Affiche_div permet d'en passer une à display: inline
	On teste aussi les variables de session si le form a déjà été soumis pour afficher les select ayant déjà une valeur -->    
    <div class = "orf_function" id = "functionORF_<?php echo $i;?>">
        <div id="Tnp_<?php echo $i.'"'; echo (isset($_SESSION['orf'.$i.'_function']) && $_SESSION['orf'.$i.'_function'] == "Tnp") ? 'style= "display:inline"' : 'style= "display:none"' ?>>
            <label for=Tnp>&nbsp; Chemistry :</label>
            <select name = "orf<?php echo $i;?>_chem">
                       <option value = "">«Choice»</option>
                       <option value = "1" <?php if (isset($_SESSION['orf'.$i.'_chem']) && $_SESSION['orf'.$i.'_chem'] == "1") echo 'selected = "selected"'; ?>>DDE</option>
                       <option value = "2" <?php if (isset($_SESSION['orf'.$i.'_chem']) && $_SESSION['orf'.$i.'_chem'] == "2") echo 'selected = "selected"'; ?>>DEDD</option>
                       <option value = "3" <?php if (isset($_SESSION['orf'.$i.'_chem']) && $_SESSION['orf'.$i.'_chem'] == "3") echo 'selected = "selected"'; ?>>Y1</option>
                       <option value = "4" <?php if (isset($_SESSION['orf'.$i.'_chem']) && $_SESSION['orf'.$i.'_chem'] == "4") echo 'selected = "selected"'; ?>>Y2</option>
                       <option value = "5" <?php if (isset($_SESSION['orf'.$i.'_chem']) && $_SESSION['orf'.$i.'_chem'] == "5") echo 'selected = "selected"'; ?>>Serine</option>
                       <option value = "6" <?php if (isset($_SESSION['orf'.$i.'_chem']) && $_SESSION['orf'.$i.'_chem'] == "6") echo 'selected = "selected"'; ?>>Unknow</option>
             </select>
         </div>
         <div id="AG_<?php echo $i.'"'; echo (isset($_SESSION['orf'.$i.'_function']) && $_SESSION['orf'.$i.'_function'] == "AG") ? 'style= "display:inline"' : 'style= "display:none"' ?>>
            <label for=AG>&nbsp; AG :</label>
            <select name = "orf<?php echo $i;?>_chemAG">
                       <option value = "">«Choice»</option>
                       <option value = "1" <?php if (isset($_SESSION['orf'.$i.'_chemAG']) && $_SESSION['orf'.$i.'_chemAG'] == "1") echo 'selected = "selected"'; ?>>IS21 helper</option>
                       <option value = "2" <?php if (isset($_SESSION['orf'.$i.'_chemAG']) && $_SESSION['orf'.$i.'_chemAG'] == "2") echo 'selected = "selected"'; ?>>TnpB</option>
                       <option value = "3" <?php if (isset($_SESSION['orf'.$i.'_chemAG']) && $_SESSION['orf'.$i.'_chemAG'] == "3") echo 'selected = "selected"'; ?>>IS66 TnpA</option>
                       <option value = "4" <?php if (isset($_SESSION['orf'.$i.'_chemAG']) && $_SESSION['orf'.$i.'_chemAG'] == "4") echo 'selected = "selected"'; ?>>IS66 TnpB</option>
                       <option value = "5" <?php if (isset($_SESSION['orf'.$i.'_chemAG']) && $_SESSION['orf'.$i.'_chemAG'] == "5") echo 'selected = "selected"'; ?>>IS91 integrase_resolvase</option>
                       <option value = "6" <?php if (isset($_SESSION['orf'.$i.'_chemAG']) && $_SESSION['orf'.$i.'_chemAG'] == "6") echo 'selected = "selected"'; ?>>Tn3 resolvase</option>
                       <option value = "7" <?php if (isset($_SESSION['orf'.$i.'_chemAG']) && $_SESSION['orf'.$i.'_chemAG'] == "7") echo 'selected = "selected"'; ?>>Other</option>
               </select>
           </div>
           <div id="PG_<?php echo $i.'"'; echo (isset($_SESSION['orf'.$i.'_function']) && $_SESSION['orf'.$i.'_function'] == "PG") ? 'style= "display:inline"' : 'style= "display:none"' ?>>
             <label for=PG>&nbsp; Description :</label>
             <select name = "orf<?php echo $i;?>_chemPG">
                       <option value = "">«Choice»</option>
                       <option value = "1" <?php if (isset($_SESSION['orf'.$i.'_chemPG']) && $_SESSION['orf'.$i.'_chemPG'] == "1") echo 'selected = "selected"'; ?>>Antibiotic resistance</option>
                       <option value = "2"<?php if (isset($_SESSION['orf'.$i.'_chemPG']) && $_SESSION['orf'.$i.'_chemPG'] == "2") echo 'selected = "selected"'; ?>>Transcriptional Regulator factor</option>
             </select>
             <p>&nbsp;</p>
             <label for=Annotation>Annotation :</label>
             <INPUT type="text" name="orf<?php echo $i;?>_annotation" VALUE="<?php echo isset($_SESSION['orf'.$i.'_annotation']) ? $_SESSION['orf'.$i.'_annotation'] : ""; ?> " SIZE=100 MAXLENGTH=150>
           </div>
     </div>  
	
    <div class="entete_propriete">ORF sequence</div> 
	<div class="seq"><textarea cols=100 rows=3 name="orf<?php echo $i;?>_seq"><?php echo isset($_SESSION['orf'.$i.'_seq']) ? $_SESSION['orf'.$i.'_seq'] : ""; ?></textarea> </div>

    <div class="entete_propriete">ORF comments</div> 
	<div><textarea cols=100 name="orf<?php echo $i;?>_Comment"><?php  echo isset($_SESSION['orf'.$i.'_Comment']) ? $_SESSION['orf'.$i.'_Comment'] : ""; ?></textarea> </div> 
<?php
	}				// Fin de la boucle for qui affiche les orf
}				// Fin du if ($nb_orf !=0)
?>


</section>   
<section>
    <div class="enteteSection">
	<span class='entete_propriete'>Comments</span>
	</div>
   	<textarea cols=100 name="comments"><?php  echo isset($_SESSION['comments']) ? $_SESSION['comments'] : ""; ?></textarea>
</section>
<section>
    <div class="enteteSection">
	<span class='entete_propriete'>References</span>
	</div>
   	<textarea cols=100 name="references"><?php  echo isset($_SESSION['references']) ? $_SESSION['references'] : ""; ?></textarea>
</section>
 
  </fieldset> 
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