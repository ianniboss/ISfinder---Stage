<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('includes/entete.inc.php');
require_once('includes/aside.inc.php');
require_once("includes/function.inc.php");
require_once("includes/affiche.inc.php");

$nb_orf = (isset($_SESSION['nb_orf'])&&($_SESSION['nb_orf']<16)&& ($_SESSION['nb_orf']>=0)) ? intval($_SESSION['nb_orf']) : 0 ;	

if (!empty($_SESSION['error'])){
	echo "<p class='erreur'>".$_SESSION['error']."</p><hr/>";
}
// $_SESSION['ET_name'] =	($_GET['name']) ? strip_tags($_GET['name']) : $_SESSION['ET_name'];
$_SESSION['bdd'] =	(isset($_GET['bdd']) && $_GET['bdd']) ? strip_tags($_GET['bdd']) : (isset($_SESSION['bdd']) ? $_SESSION['bdd'] : "");
$_SESSION['ID_ET'] = (isset($_GET['ident']) && ctype_digit($_GET['ident'])) ? $_GET['ident'] : (isset($_SESSION['ID_ET']) ? $_SESSION['ID_ET'] : "");

// $name = $_SESSION['ET_name'];
$bdd = $_SESSION['bdd'];
$ident = $_SESSION['ID_ET'];

if (intval($_GET['val_session'] ?? 0) != 1) {			// val_session = 1 On garde les valeurs de $_SESSION sinon on lit les données dans la base 
	
	// La recherche de la fiche MGE tient compte du parametre passé, soit le nom soit ID_ET de l'IS
//	$condition = ($_GET('ident') == '') ? "`ET_name` like '".$name."'" : "`ID_ET` like '".$ident."'";
	$condition = "`ID_ET` = '".$ident."'" ;

		/* Connexion à la base de données */
	$cnx = connexion($bdd) ;	
	if (!$cnx){
		// traitement de l'erreur ;
		echo "Problème de connexion à la base de données" ;
	}else{
		if ($bdd == "isfinder"){
			$reqIS = "SELECT * FROM `element_transposable` ET
					  LEFT JOIN `family` FAM
					  ON `Family_ID_Family` = `ID_Family`
					  LEFT JOIN `groups` GRP
					  ON `Groups_ID_Groups` = `ID_Groups`
					  LEFT JOIN `type_element_transposable` TET
					  ON `type_element_transposable_ID_Type_ET` = `ID_Type_ET`
					  LEFT JOIN `is_ends` ISE
					  ON `ID_ET` = ISE.`Element_transposable_ID_ET`
					  LEFT JOIN `et_insertion_site` ETIS
					  ON `ID_ET` = ETIS.`Element_transposable_ID_ET`
					  LEFT JOIN `orf`
					  ON `ID_ET` = orf.`Element_transposable_ID_ET`
					  LEFT JOIN `synonyme` SYN
					  ON `ID_ET` = SYN.`Element_transposable_ID_ET`
					  LEFT JOIN `element_transposable_has_host` ETHH
					  ON `ID_ET` = ETHH.`Element_transposable_ID_ET`
					  LEFT JOIN `submission` SUB
					  ON `ID_ET` = SUB.`Element_transposable_ID_ET`
					  WHERE $condition LIMIT 1" ;
		}else{
		$reqIS = "SELECT * FROM `element_transposable` ET
					  LEFT JOIN `family` FAM
					  ON `Family_ID_Family` = `ID_Family`
					  LEFT JOIN `groups` GRP
					  ON `Groups_ID_Groups` = `ID_Groups`
					  LEFT JOIN `base`
					  ON `base_ID_Base` = `ID_Base`
					  LEFT JOIN `parent_link` PL
					  ON `ID_ET` = PL.`Element_transposable_ID_ET`
					  LEFT JOIN `type_element_transposable` TET
					  ON `type_element_transposable_ID_Type_ET` = `ID_Type_ET`
					  LEFT JOIN `is_ends` ISE
					  ON `ID_ET` = ISE.`Element_transposable_ID_ET`
					  LEFT JOIN `et_insertion_site` ETIS
					  ON `ID_ET` = ETIS.`Element_transposable_ID_ET`
					  LEFT JOIN `orf`
					  ON `ID_ET` = orf.`Element_transposable_ID_ET`
					  LEFT JOIN `synonyme` SYN
					  ON `ID_ET` = SYN.`Element_transposable_ID_ET`
					  LEFT JOIN `element_transposable_has_host` ETHH
					  ON `ID_ET` = ETHH.`Element_transposable_ID_ET`
					  LEFT JOIN `submission` SUB
					  ON `ID_ET` = SUB.`Element_transposable_ID_ET`
					  WHERE $condition LIMIT 1" ;	
		}
				  
		/* Execution de la requette et si résultat, alors on continue */
		$result = execute_sql($cnx,$reqIS);
		If (mysqli_num_rows($result) != 1) {
			header('Location: https://secure.ibcg.biotoul.fr/isadmin');
			exit();		
			}else{	
			$is = mysqli_fetch_array($result);
			
			foreach($is as $index=>$valeur){
					$_SESSION[$index] = strip_tags($valeur) ;
			}					

		is_submiter($cnx,$_SESSION['ID_ET']);		// récupere le submiter à partir de IDET résulta écrit dans $_SESSION
				
		$origin = is_origin($cnx,$_SESSION['ID_ET']);
		$origintab = explode(" ", $origin);
		$_SESSION['Origin']	= $origintab[0]." ".$origintab[1] ;
					
		$hosts = is_hosts($cnx,$_SESSION['ID_ET']);
		$i = 0;
		$_SESSION['Hosts'] = $origin;
		while ($host = mysqli_fetch_array($hosts)){
			if ($host['Host'] != $origin){
				$_SESSION['Hosts'] .= "\n";
				$i++;
				$_SESSION['Hosts'] = $_SESSION['Hosts'].$host['Host'];
				$_SESSION['ID_host'][$i] = $host['ID_host'];
			}
		}

	    $_SESSION['ID_iso'] = (isset($_SESSION['ID_iso'])) ?  $_SESSION['ID_iso'] : ""; 

		$site = unserialize(is_champX($cnx,'*','et_insertion_site','Element_transposable_ID_ET',$_SESSION['ID_ET'],''));
		$_SESSION['nb_site'] = ($site == '') ? 0 : count($site) ;
		for ($i = 0 ; $i <= $_SESSION['nb_site'] && $site ; $i++){			// Création des variables de session  
			foreach($site[$i] as $champ=>$valeur){							// avec l'indentation du iéme site d'insertion + le nom du champ Mysql
			$_SESSION[$i.$champ] = $site[$i][$champ];
			}
		}
		
		$ORF = unserialize(is_champX($cnx,'*','orf','Element_transposable_ID_ET',$_SESSION['ID_ET'],'ORF_rank'));
		$_SESSION['nb_orf'] = ($ORF == '') ? 0 : count($ORF) ;
		$nb_orf = $_SESSION['nb_orf'];
		for ($i = 1 ; $i <= $_SESSION['nb_orf'] && $ORF ; $i++){			// Création des variables de session  
			foreach($ORF[$i-1] as $champ=>$valeur){							// avec l'indentation du iéme ORF + le nom du champ Mysql
			$_SESSION[$champ.$i] = $ORF[$i-1][$champ];
			}
		}
		
		if ($bdd == "isfinder"){			// Dans isfinder il peut y avoir plusieurs enregistrement de parents et synonyme pour 1 IS
											// Dans ISfinder l'iso est un integer et non un varchar 
											// la structure de la BDD ISfinder est différente d'ISsubmit aussi pour groupe et famille
			$parent = unserialize(is_champX($cnx,'Element_transposable_parent_ID_ET','parent_link','Element_transposable_ID_ET',$_SESSION['ID_ET'],''));
			$_SESSION['parents'] = "";
			for ($i = 0 ; $i < count($parent) && $parent ; $i++){
				$_SESSION['parents'] = $_SESSION['parents'].$parent[$i]['Element_transposable_parent_ID_ET']." ";
			}

			$result_syn= (!empty($is['Synonyme'])) ? is_syn($cnx,$_SESSION['ID_ET']) : null;	
			if ($result_syn) {
				$syn = mysqli_fetch_array($result_syn) ;
				$_SESSION['Synonyme'] = $syn['Synonyme'];		
				while ($syn = mysqli_fetch_array($result_syn)){
					$_SESSION['Synonyme'] = $_SESSION['Synonyme'].", " .$syn['Synonyme'];
				}
			}

			$iso = (isset($_SESSION['ID_iso'])) ? is_champ($cnx,'ET_Name', 'element_transposable', 'ID_ET', trim($_SESSION['ID_iso'])) : "NULL";
	 	}

		mysqli_close($cnx);
	}  // Fin du if resultat (mysqli_num_rows($result) != 1)
}	// Fin du else il y a connexion
}	// fin du val_session != 1

$base_name = ($bdd == "isfinder") ? "IS" : (isset($_SESSION['Base_Name']) ? $_SESSION['Base_Name'] : "");
$background = base_color($base_name) ;	
$fond_base = 'class="base_'.$base_name.'"';		 // couleur de background des <TH> en fonction de la base

?>
<!--    <link type="text/css" rel="stylesheet" href="styles/ficheMGE.css" media="screen" />      -->
<link type="text/css" rel="stylesheet" href="styles/fiche.css" media="screen" />
<link type="text/css" rel="stylesheet" href="styles/styles.css" media="screen" />
<script type="text/javascript" src="scripts/function.js"></script>

<article style="background-color:<?php echo $background; ?>">
<!--		<div class="ecran">contenu de mon &eacutecran></div> -->
	<section>

<form action="scripts/modifIS.php" enctype="multipart/form-data" method="POST" name="ficheIS">
				<!-- Champ caché pour savoir quelle modification est demanndée si appel à modifIS.php sans soumission (juste sur OnChange : Ajout site d'insertion, upload fichier ou nbr d'ORF -->  
<input type='hidden' id='DynModif' name='DynModif' value=''>

 <fieldset id=submitter>
    <legend>Submitter information</legend>
    <ul><li>
        <label for=nom>First Name :</label>
  		<INPUT TYPE="text" NAME="Firstname" VALUE="<?php  echo isset($_SESSION['Firstname']) ? $_SESSION['Firstname'] : ""; ?>" SIZE="25" MAXLENGTH=60>
  		</li><li>
        <label for=mname>Middle Name :</label>
		<INPUT TYPE="text" NAME="Middlename" VALUE="<?php  echo isset($_SESSION['Middlename']) ? $_SESSION['Middlename'] : ""; ?>" SIZE="20" MAXLENGTH=60>
        </li><li>
        <label for=lname>Last Name :</label>
		<INPUT TYPE="text" NAME="Lastname" VALUE="<?php  echo isset($_SESSION['Lastname']) ?  $_SESSION['Lastname'] : ""; ?>" SIZE="25" required MAXLENGTH=60>
     </li></ul>
     <ul><li>
        <label for=institut>Institution :</label>
		<INPUT TYPE="text" NAME="Institution" VALUE="<?php  echo isset($_SESSION['Institution']) ?  $_SESSION['Institution'] : ""; ?>" SIZE=80 MAXLENGTH=100>
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
		<INPUT TYPE="text" NAME="Country" VALUE="<?php  echo isset($_SESSION['Country']) ?  $_SESSION['Country'] : ""; ?>" SIZE="27" MAXLENGTH=60>
        </li></ul>
    <ul><li>
        <label for=courriel>e-mail address :</label>
  		<INPUT TYPE="email" NAME="Mail" VALUE="<?php  echo isset($_SESSION['Mail']) ?  $_SESSION['Mail'] : ""; ?>" SIZE="40" MAXLENGTH=80>
  		</li><li>
        <label for=tel>Telephone :</label>
		<INPUT TYPE="text" NAME="Phone" VALUE="<?php  echo isset($_SESSION['Phone']) ?  $_SESSION['Phone'] : ""; ?>" SIZE="20" MAXLENGTH=60>
    </li></ul>
   </fieldset>        
  <fieldset id=infoIS>
    <legend>General Information about MGE</legend>
  		<ul><li>
        <label for=isname>IS name :</label>
		<INPUT TYPE="text" NAME="ET_name" VALUE="<?php  echo isset($_SESSION['ET_name']) ?  $_SESSION['ET_name'] : ""; ?>" SIZE=15 required MAXLENGTH=20>
  		</li><li>
        <label for=family>Family :</label>
		<INPUT TYPE="text" NAME="Family_ID_Family" VALUE="<?php  if ($bdd == "isfinder"){ echo isset($_SESSION['Family_Name']) ?  $_SESSION['Family_Name'] : ""; }else{ echo isset($_SESSION['Family_ID_Family']) ?  $_SESSION['Family_ID_Family'] : "";} ?>" SIZE=15 MAXLENGTH=20>
  		</li><li>
        <label for=group>Group :</label>
		<INPUT TYPE="text" NAME="Groups_ID_Groups" VALUE="<?php if ($bdd == "isfinder"){  echo isset($_SESSION['Group_Name']) ?  $_SESSION['Group_Name'] : ""; }else{ echo isset($_SESSION['Groups_ID_Groups']) ?  $_SESSION['Groups_ID_Groups'] : "";} ?>" SIZE=15 MAXLENGTH=20>
  		</li></ul>
    	<ul><li>
        <label for=MGEtype>MGE type :</label>
		<SELECT NAME="type_element_transposable_ID_Type_ET"> 
		<OPTION value="1" selected <?php if (isset($_SESSION['type_element_transposable_ID_Type_ET']) && $_SESSION['type_element_transposable_ID_Type_ET'] == "1") echo 'selected="selected"'; ?>>IS </option>
        <OPTION value="2" <?php if (isset($_SESSION['type_element_transposable_ID_Type_ET']) && $_SESSION['type_element_transposable_ID_Type_ET'] == "2") echo 'selected="selected"'; ?>>MITE </option>
		<OPTION value="4" <?php if (isset($_SESSION['type_element_transposable_ID_Type_ET']) && $_SESSION['type_element_transposable_ID_Type_ET'] == "4") echo 'selected="selected"'; ?>>MIC </option>
		<OPTION value="5" <?php if (isset($_SESSION['type_element_transposable_ID_Type_ET']) && $_SESSION['type_element_transposable_ID_Type_ET'] == "5") echo 'selected="selected"'; ?>>tIS </option>
		<OPTION value="3" <?php if (isset($_SESSION['type_element_transposable_ID_Type_ET']) && $_SESSION['type_element_transposable_ID_Type_ET'] == "3") echo 'selected="selected"'; ?>>Transposon</option>
        </SELECT>         
        </li><li>
        <label for=related_elt>Related element(s) separated by a comma :</label>      
		<INPUT TYPE="text" NAME="Element_transposable_parent_ID_ET" VALUE="<?php echo isset($_SESSION['Element_transposable_parent_ID_ET']) ? $_SESSION['Element_transposable_parent_ID_ET'] : "" ;?>" SIZE=50 MAXLENGTH=100>      
<!-- Base IS : for ($i = 0 ; $i < $nbr_parent ; $i++){ echo isset($_SESSION['Element_transposable_parent_ID_ET'][$i][0]) ? $_SESSION['Element_transposable_parent_ID_ET'][$i][0]."  " : "";} -->

  		</li></ul>
  		<ul><li>
        <label for=isoform>Isoform :</label>
		<INPUT TYPE="text" NAME="ID_iso" VALUE="<?php  echo ($bdd == "isfinder") ? $iso :  $_SESSION['ID_iso']; ?>" SIZE=15 MAXLENGTH=20>
  		</li><li>
        <label for=synonym>Synonym(s) separated by a comma :</label>
		<INPUT TYPE="text" NAME="Synonyme" VALUE="<?php  echo isset($_SESSION['Synonyme']) ?  $_SESSION['Synonyme'] : ""; ?>" SIZE=50 MAXLENGTH=100>
  		</li></ul>
             
<table>
<tr><th <?php echo $fond_base; ?>>Accession number</th><th <?php echo $fond_base; ?>>Transposition</th><th <?php echo $fond_base; ?>>Origin</th><th <?php echo $fond_base; ?>>Hosts (separated by a return - First=Origin)</th></tr>     
<tr>
	<td><INPUT TYPE="text" NAME="ET_Accession_number" VALUE="<?php  echo isset($_SESSION['ET_Accession_number']) ?  $_SESSION['ET_Accession_number'] : ""; ?>" SIZE=17 MAXLENGTH=25></td>
    <td><SELECT NAME="Transposition"> 
        <OPTION value="ND"<?php if (isset($_SESSION['Transposition']) && $_SESSION['Transposition'] == "ND") echo 'selected="selected"'; ?>>ND </option>
		<OPTION value="Y"<?php if (isset($_SESSION['Transposition']) && $_SESSION['Transposition'] == "Y") echo 'selected="selected"'; ?>>Yes </option>
		<OPTION value="N"<?php if (isset($_SESSION['Transposition']) && $_SESSION['Transposition'] == "N") echo 'selected="selected"'; ?>>No </option>
		</SELECT> </td>
    <td><INPUT TYPE="text" NAME="Origin" VALUE="<?php  echo isset($_SESSION['Origin']) ?  $_SESSION['Origin'] : ""; ?>" disabled="disabled" SIZE=45 MAXLENGTH=100></td>
    <td><textarea cols=45 rows=2 name="Hosts"><?php echo isset($_SESSION['Hosts']) ? $_SESSION['Hosts'] : ""; ?></textarea></td>
</tr></table>    
	<section>
    <div class="enteteSection">
	<span class='entete_propriete'>DNA section</span>
	</div>
        <label for=islength>IS Length :</label>
		<INPUT TYPE="text" NAME="ET_Length" VALUE="<?php  echo isset($_SESSION['ET_Length']) ?  $_SESSION['ET_Length'] : ""; ?>" SIZE=15 MAXLENGTH=20>
	<div class="entete_propriete">Ends</div>
	<div class="entete_propriete_decal">General case</div>
    <ul><li>
        <label for=irlength>IR Length :</label>
		<INPUT TYPE="text" NAME="IR_Length" VALUE="<?php  echo isset($_SESSION['IR_Length']) ?  $_SESSION['IR_Length'] : ""; ?>" SIZE=15 MAXLENGTH=20>
	</li><li>
        <label for=calcul_ends>Calcul ends :</label>    
    	<INPUT TYPE="radio" name="calcul_ends" value="Oui" />Oui&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    	<INPUT TYPE="radio" name="calcul_ends" value="Non" checked="checked" />Non&nbsp;
    </li></ul>
    <ul><li>
        <label for=irl class="label_court">IRL :</label>
		<INPUT TYPE="text" class="seq" NAME="Left_End" VALUE="<?php  echo isset($_SESSION['Left_End']) ?  $_SESSION['Left_End'] : ""; ?>" SIZE=60 MAXLENGTH=100>
	</li><li>
        <label for=irr class="label_court">IRR :</label>
		<INPUT TYPE="text" class="seq" NAME="Rigth_End" VALUE="<?php  echo isset($_SESSION['Rigth_End']) ?  $_SESSION['Rigth_End'] : ""; ?>" SIZE=60 MAXLENGTH=100>
	</li></ul><hr />

	<div class="entete_propriete_decal">Single strand case</div>    
    <ul><li>
        <label for=le class="label_large">Left end / oriIS :</label>
		<INPUT TYPE="text" class="seq" NAME="LE" VALUE="<?php  echo isset($_SESSION['LE']) ?  $_SESSION['LE'] : ""; ?>" SIZE=102 MAXLENGTH=150>
	</li><li>
        <label for=LEstII>Struct. II :</label>
			<SELECT NAME="LE_Structure_II"> 
		<OPTION value="1"<?php if (isset($_SESSION['LE_Structure_II']) && $_SESSION['LE_Structure_II'] == "1") echo 'selected="selected"'; ?>>Yes </option>
		<OPTION value="0"<?php if (isset($_SESSION['LE_Structure_II']) && $_SESSION['LE_Structure_II'] == "0") echo 'selected="selected"'; ?>>No </option>
		</SELECT>
    </li><li>
        <label for=re class="label_large">Right end /terIS :</label>
		<INPUT TYPE="text" class="seq" NAME="RE" VALUE="<?php  echo isset($_SESSION['RE']) ?  $_SESSION['RE'] : ""; ?>" SIZE=102 MAXLENGTH=150>
	</li><li>
        <label for=LEstII>Struct. II :</label>
			<SELECT NAME="RE_Structure_II"> 
		<OPTION value="1"<?php if (isset($_SESSION['RE_Structure_II']) && $_SESSION['RE_Structure_II'] == "1") echo 'selected="selected"'; ?>>Yes </option>
		<OPTION value="0"<?php if (isset($_SESSION['RE_Structure_II']) && $_SESSION['RE_Structure_II'] == "0") echo 'selected="selected"'; ?>>No </option>
		</SELECT>
    </li></ul> 
    <ul><li>   
        <label for=ends_comments>Ends comments :</label>
		<textarea cols=100 name="Ends_comments"><?php  echo isset($_SESSION['Ends_comments']) ?  $_SESSION['Ends_comments'] : ""; ?></textarea>
    </li></ul>
<a name="InsertionSite" id="InsertionSite"></a>
	<div class="entete_propriete">Insertion site</div> 
	<div class="entete_propriete_decal">General case</div>    
    <table>
<tr><th <?php echo $fond_base; ?>>Left flank</th><th <?php echo $fond_base; ?>>Direct repeat</th><th <?php echo $fond_base; ?>>Right flank</th><th <?php echo $fond_base; ?>>DR Length</th></tr>     
<?php           // Boucle pour afficher le nombre de sites voulus
for($j=0 ; $j < $_SESSION['nb_site'] ; $j++){
?>
<tr>
	<td><INPUT TYPE="text" class="seq" NAME="<?php echo $j;?>DR_Left_Flank" VALUE="<?php  echo isset($_SESSION[$j.'DR_Left_Flank']) ?  $_SESSION[$j.'DR_Left_Flank'] : ""; ?>" SIZE=40 MAXLENGTH=50></td>
	<td><INPUT TYPE="text" class="seq" NAME="<?php echo $j;?>Direct_Repeat" VALUE="<?php  echo isset($_SESSION[$j.'Direct_Repeat']) ?  $_SESSION[$j.'Direct_Repeat'] : ""; ?>" SIZE=40 MAXLENGTH=120></td>
    <td><INPUT TYPE="text" class="seq" NAME="<?php echo $j;?>DR_Rigth_Flank" VALUE="<?php  echo isset($_SESSION[$j.'DR_Rigth_Flank']) ?  $_SESSION[$j.'DR_Rigth_Flank'] : ""; ?>" SIZE=40 MAXLENGTH=50></td>
	<td><INPUT TYPE="text" NAME="<?php echo $j;?>Direct_Repeat_Length" VALUE="<?php  echo isset($_SESSION[$j.'Direct_Repeat_Length']) ?  $_SESSION[$j.'Direct_Repeat_Length'] : ""; ?>" SIZE=5 MAXLENGTH=10></td>
</tr>
<?php
	}				// Fin de la boucle for qui affiche les sites d'insertions : general case
?>
</table>
	<div class="entete_propriete_decal">Single strand case</div>    
<table>
<tr><th <?php echo $fond_base; ?>>Left flank</th><th <?php echo $fond_base; ?>>LE cleavage site</th><th <?php echo $fond_base; ?>>Right flank</th><th <?php echo $fond_base; ?>>RE cleavage site</th></tr>     
<?php           // Boucle pour afficher le nombre de sites voulus
for($j=0 ; $j < $_SESSION['nb_site'] ; $j++){
?>
<tr>
	<td><INPUT TYPE="text" class="seq" NAME="<?php echo $j;?>LE_CS_Left_Flank" VALUE="<?php  echo isset($_SESSION[$j.'LE_CS_Left_Flank']) ?  $_SESSION[$j.'LE_CS_Left_Flank'] : ""; ?>" SIZE=40 MAXLENGTH=50 /></td>
	<td><INPUT TYPE="text" class="seq" NAME="<?php echo $j;?>LE_CS" VALUE="<?php  echo isset($_SESSION[$j.'LE_CS']) ?  $_SESSION[$j.'LE_CS'] : ""; ?>" SIZE=23 MAXLENGTH=20 /></td>
    <td><INPUT TYPE="text" class="seq" NAME="<?php echo $j;?>RE_CS_Rigth_Flank" VALUE="<?php  echo isset($_SESSION[$j.'RE_CS_Rigth_Flank']) ?  $_SESSION[$j.'RE_CS_Rigth_Flank'] : ""; ?>" SIZE=40 MAXLENGTH=50 /></td>
	<td><INPUT TYPE="text" class="seq" NAME="<?php echo $j;?>RE_CS" VALUE="<?php  echo isset($_SESSION[$j.'RE_CS']) ?  $_SESSION[$j.'RE_CS'] : ""; ?>" SIZE=23 MAXLENGTH=20 /></td>
</tr>
<?php
	}				// Fin de la boucle for qui affiche les sites d'insertions : single strand case
?>
<tr>
  <td colspan="4"><img src='images/plus.jpg' alt='Insertion site' onclick="document.getElementById('DynModif').value='1' ; document.forms['ficheIS'].submit();"/></td>
</tr>
</table>
	<div class="entete_propriete">DNA sequence</div> 
	<div class="seq"><textarea cols=100 rows=3 name="ET_DNA_Sequence"><?php  echo isset($_SESSION['ET_DNA_Sequence']) ?  $_SESSION['ET_DNA_Sequence'] : ""; ?></textarea> </div>
   	<div class="piedSection"></div>    
    </section>    
    
<a name="Recoding"></a>    
<div class="entete_propriete">Recoding section</div> 
<label for="Recodingby" class="entete_propriete_decal">Recoding by :</label>
		<SELECT NAME="recode"> 
		<OPTION value="NULL" selected <?php if (isset($_SESSION['recode']) && ($_SESSION['recode'] == "NULL" || $_SESSION['recode'] == "" )) echo 'selected="selected"'; ?>> </option>
        <OPTION value="frameshift" <?php if (isset($_SESSION['recode']) && $_SESSION['recode'] == "frameshift") echo 'selected="selected"'; ?>>frameshift </option>
        <OPTION value="selenocysteine" <?php if (isset($_SESSION['recode']) && $_SESSION['recode'] == "selenocysteine") echo 'selected="selected"'; ?>>selenocysteine </option>
		<OPTION value="pyrrolysine" <?php if (isset($_SESSION['recode']) && $_SESSION['recode'] == "pyrrolysine") echo 'selected="selected"'; ?>>pyrrolysine </option>
        </SELECT>         
<label for="frame" class="entete_propriete_decal">Frame :</label>
		<SELECT NAME="frame"> 
		<OPTION value="NULL" selected <?php if (isset($_SESSION['frame']) && ($_SESSION['frame'] == "NULL" || $_SESSION['frame'] == "" )) echo 'selected="selected"'; ?>> </option>
        <OPTION value="-1" <?php if (isset($_SESSION['frame']) && $_SESSION['frame'] == "-1") echo 'selected="selected"'; ?>>-1 </option>
        <OPTION value="+1" <?php if (isset($_SESSION['frame']) && $_SESSION['frame'] == "+1") echo 'selected="selected"'; ?>>+1 </option>
        </SELECT> 
<label for="type" class="entete_propriete_decal">Type :</label>
		<SELECT NAME="type"> 
		<OPTION value="NULL" selected <?php if (isset($_SESSION['type']) && ($_SESSION['type'] == "NULL" || $_SESSION['type'] == "" )) echo 'selected="selected"'; ?>> </option>
        <OPTION value="translational" <?php if (isset($_SESSION['type']) && $_SESSION['type'] == "translational") echo 'selected="selected"'; ?>>translational</option>
        <OPTION value="transcriptional" <?php if (isset($_SESSION['type']) && $_SESSION['type'] == "transcriptional") echo 'selected="selected"'; ?>>transcriptional</option>
        <OPTION value="unknow" <?php if (isset($_SESSION['type']) && $_SESSION['type'] == "unknow") echo 'selected="selected"'; ?>>unknow</option>
        </SELECT> 
<label for="exp_demontred" class="entete_propriete_decal">Experimentally demonstrated :</label>
		<SELECT NAME="exp_demontred"> 
		<OPTION value="NULL" selected <?php if (isset($_SESSION['exp_demontred']) && ($_SESSION['exp_demontred'] == "NULL" || $_SESSION['exp_demontred'] == "" )) echo 'selected="selected"'; ?>> </option>
        <OPTION value="Yes" <?php if (isset($_SESSION['exp_demontred']) && $_SESSION['exp_demontred'] == "Yes") echo 'selected="selected"'; ?>>Yes</option>
        <OPTION value="No" <?php if (isset($_SESSION['exp_demontred']) && $_SESSION['exp_demontred'] == "No") echo 'selected="selected"'; ?>>No</option>
        </SELECT> 

<div class="entete_propriete_decal">Stimulators : </div> 
  	<label for="Shine" class='entete_propriete_decal'>Shine-Dalgarno sequence : </label>
		<SELECT NAME="SD"> 
		<OPTION value="NULL" selected <?php if (isset($_SESSION['SD']) && ($_SESSION['SD'] == "NULL" || $_SESSION['SD'] == "" )) echo 'selected="selected"'; ?>> </option>
        <OPTION value="Yes" <?php if (isset($_SESSION['SD']) && $_SESSION['SD'] == "Yes") echo 'selected="selected"'; ?>>Yes</option>
        <OPTION value="No" <?php if (isset($_SESSION['SD']) && $_SESSION['SD'] == "No") echo 'selected="selected"'; ?>>No</option>
        </SELECT>     
  	<label for="structure" class='entete_propriete_decal'>Secondary structure : </label>  
		<SELECT NAME="structure"> 
		<OPTION value="NULL" selected <?php if (isset($_SESSION['structure']) && ($_SESSION['structure'] == "NULL" || $_SESSION['structure'] == "" )) echo 'selected="selected"'; ?>> </option>
        <OPTION value="No-structure" <?php if (isset($_SESSION['structure']) && $_SESSION['structure'] == "No-structure") echo 'selected="selected"'; ?>>No-structure</option>
        <OPTION value="stem-loop" <?php if (isset($_SESSION['structure']) && $_SESSION['structure'] == "stem-loop") echo 'selected="selected"'; ?>>stem-loop</option>
        <OPTION value="pseudoknot" <?php if (isset($_SESSION['structure']) && $_SESSION['structure'] == "pseudoknot") echo 'selected="selected"'; ?>>pseudoknot</option>
        </SELECT> 
    
<div class="entete_propriete_decal">Recoding motif : </div> 
	<div class="seq"><textarea cols=100 rows=3 name="recoding_seq"><?php  echo isset($_SESSION['recoding_seq']) ?  $_SESSION['recoding_seq'] : ""; ?></textarea> </div>
	<div class="seq"><textarea cols=100 rows=3 name="recoding_annot"><?php  echo isset($_SESSION['recoding_annot']) ?  $_SESSION['recoding_annot'] : ""; ?></textarea> </div>
    
<input type="hidden" name="MAX_FILE_SIZE" value="200000" />
<label for="Recoding_image" class='entete_propriete_decal'>Recoding image (file .jpg) :</label> 
	<?php echo $_SESSION['recoding_image'] ?>;
	<INPUT TYPE="file" NAME="recoding_image" SIZE=20 MAXLENGTH=30 />    
	<input type="button" name="rec_image" value="Upload" onclick="document.getElementById('DynModif').value='3' ; document.forms['ficheIS'].submit();"/>
    <?php if ($_SESSION['recoding_image_error']) { echo "<p class='erreur'>".$_SESSION['recoding_image_error'] ;} ?>    

<?php 
if ($_SESSION['recoding_image']!=NULL){
	$recoding_image = $_SESSION['recoding_image'] ;
	$taille = getimagesize('drawings/'.$recoding_image);
	$largeur = ($taille[0] < 800) ? $taille[0] : 800 ;
	print "<div id='image'><img src='drawings/$recoding_image' width=$largeur></div>";
}
?>    
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
		<select name = "nb_orf"  onchange = "document.getElementById('DynModif').value='2' ; document.forms['ficheIS'].submit();" />
		<script language="javascript">liste_nombre(0,16,<?php echo $nb_orf;?>);</script>
		</select>        
<?php           // Boucle pour afficher le nombre d'orf sélectionné

if ($nb_orf !=0){
	for($i=1 ; $i <= $nb_orf ; $i++){
?>
	<div class="entete_propriete">ORF <?php print $i ?> :</div> 
<table>
<tr><th <?php echo $fond_base; ?> colspan="2" scope="col">Length</th><th <?php echo $fond_base; ?>>Begin</th><th <?php echo $fond_base; ?>>End</th><th <?php echo $fond_base; ?>>Strand</th><th <?php echo $fond_base; ?>>Fusion ORF</th></tr>     
<tr>
	<td><INPUT TYPE="text" NAME="ORF_Length_DNA<?php echo $i;?>" align="right" VALUE="<?php echo isset($_SESSION['ORF_Length_DNA'.$i])? $_SESSION['ORF_Length_DNA'.$i] : ""; ?>" SIZE=20 MAXLENGTH=50> bp &nbsp;</td>
	<td><INPUT TYPE="text" NAME="ORF_Length_AA<?php echo $i;?>" alt="aa" VALUE="<?php echo isset($_SESSION['ORF_Length_AA'.$i])? $_SESSION['ORF_Length_AA'.$i] : ""; ?>" SIZE=20 MAXLENGTH=50> aa &nbsp;</td>
	<td><INPUT TYPE="text" NAME="ORF_Begin<?php echo $i;?>" VALUE="<?php echo isset($_SESSION['ORF_Begin'.$i])? $_SESSION['ORF_Begin'.$i] : ""; ?>" SIZE=20 MAXLENGTH=120></td>
    <td><INPUT TYPE="text" NAME="ORF_End<?php echo $i;?>" VALUE="<?php echo isset($_SESSION['ORF_End'.$i])? $_SESSION['ORF_End'.$i] : ""; ?>" SIZE=20 MAXLENGTH=50></td>
	<td><INPUT TYPE="text" NAME="ORF_Strand<?php echo $i;?>" VALUE="<?php echo isset($_SESSION['ORF_Strand'.$i])? $_SESSION['ORF_Strand'.$i] : ""; ?>" SIZE=10 MAXLENGTH=10></td>
	<td><INPUT TYPE="text" NAME="ORF_Frameshift<?php echo $i;?>" VALUE="<?php echo isset($_SESSION['ORF_Frameshift'.$i])? $_SESSION['ORF_Frameshift'.$i] : ""; ?>" SIZE=10 MAXLENGTH=10></td>
</tr></table>     

	<div class="entete_propriete">ORF function :
    <span id="function">
<!-- L'affichage de la div suivante dépend (fonction JS Affiche_div) de la fonction ORF sélectionnée ici -->

		<select name = "ORF_function<?php echo $i;?>" class = "ORF_function"onchange = "Affiche_div('functionORF_<?php echo $i;?>',this.value+'_'+<?php echo $i;?>)" />
                       
        	<option value = "">«Choice»</option>
            <option value = "Tnp" <?php if (isset($_SESSION['ORF_function'.$i]) && $_SESSION['ORF_function'.$i] == "Tnp") echo 'selected = "selected"'; ?>>Transposase</option>
            <option value = "AG" <?php if (isset($_SESSION['ORF_function'.$i]) && $_SESSION['ORF_function'.$i] == "AG") echo 'selected = "selected"'; ?>>Accessory gene</option>
            <option value = "PG" <?php if (isset($_SESSION['ORF_function'.$i]) && $_SESSION['ORF_function'.$i] == "PG") echo 'selected = "selected"'; ?>>Passenger gene</option>
                           
    	</select>
    </span>

<!-- Groupe de 3 div : la fonction Affiche_div permet d'en passer une à display: inline
	On teste aussi les variables de session si le form a déjà été soumis pour afficher les select ayant déjà une valeur -->    
    <div class = "ORF_function" id = "functionORF_<?php echo $i;?>">
        <div id="Tnp_<?php echo $i.'"'; echo (isset($_SESSION['ORF_function'.$i]) && $_SESSION['ORF_function'.$i] == "Tnp") ? 'style= "display:inline"' : 'style= "display:none"' ?>>
            <label for=Tnp>&nbsp; Chemistry :</label>
            <select name = "Tnp_chemestry_ID_Tnp_chemestry<?php echo $i;?>">
                       <option value = "">«Choice»</option>
                       <option value = "1" <?php if (isset($_SESSION['Tnp_chemestry_ID_Tnp_chemestry'.$i]) && $_SESSION['Tnp_chemestry_ID_Tnp_chemestry'.$i] == "1") echo 'selected = "selected"'; ?>>DDE</option>
                       <option value = "2" <?php if (isset($_SESSION['Tnp_chemestry_ID_Tnp_chemestry'.$i]) && $_SESSION['Tnp_chemestry_ID_Tnp_chemestry'.$i] == "2") echo 'selected = "selected"'; ?>>DEDD</option>
                       <option value = "3" <?php if (isset($_SESSION['Tnp_chemestry_ID_Tnp_chemestry'.$i]) && $_SESSION['Tnp_chemestry_ID_Tnp_chemestry'.$i] == "3") echo 'selected = "selected"'; ?>>Y1</option>
                       <option value = "4" <?php if (isset($_SESSION['Tnp_chemestry_ID_Tnp_chemestry'.$i]) && $_SESSION['Tnp_chemestry_ID_Tnp_chemestry'.$i] == "4") echo 'selected = "selected"'; ?>>Y2</option>
                       <option value = "5" <?php if (isset($_SESSION['Tnp_chemestry_ID_Tnp_chemestry'.$i]) && $_SESSION['Tnp_chemestry_ID_Tnp_chemestry'.$i] == "5") echo 'selected = "selected"'; ?>>Serine</option>
                       <option value = "6" <?php if (isset($_SESSION['Tnp_chemestry_ID_Tnp_chemestry'.$i]) && $_SESSION['Tnp_chemestry_ID_Tnp_chemestry'.$i] == "6") echo 'selected = "selected"'; ?>>Unknow</option>
             </select>
            <label for=Tnp>&nbsp; Description :</label>
            <select name = "Tnp_description_ID_Tnp_description<?php echo $i;?>">
                       <option value = "">«Choice»</option>
                       <option value = "1" <?php if (isset($_SESSION['Tnp_description_ID_Tnp_description'.$i]) && $_SESSION['Tnp_description_ID_Tnp_description'.$i] == "1") echo 'selected = "selected"'; ?>>First part of the transposase</option>
                       <option value = "2" <?php if (isset($_SESSION['Tnp_description_ID_Tnp_description'.$i]) && $_SESSION['Tnp_description_ID_Tnp_description'.$i] == "2") echo 'selected = "selected"'; ?>>Second part of the transposase</option>
             </select>
         </div>
         <div id="AG_<?php echo $i.'"'; echo (isset($_SESSION['ORF_function'.$i]) && $_SESSION['ORF_function'.$i] == "AG") ? 'style= "display:inline"' : 'style= "display:none"' ?>>
            <label for=AG>&nbsp; AG :</label>
            <select name = "AG_description_ID_AG_description<?php echo $i;?>">
                       <option value = "">«Choice»</option>
                       <option value = "1" <?php if (isset($_SESSION['AG_description_ID_AG_description'.$i]) && $_SESSION['AG_description_ID_AG_description'.$i] == "1") echo 'selected = "selected"'; ?>>IS21 helper</option>
                       <option value = "2" <?php if (isset($_SESSION['AG_description_ID_AG_description'.$i]) && $_SESSION['AG_description_ID_AG_description'.$i] == "2") echo 'selected = "selected"'; ?>>TnpB</option>
                       <option value = "3" <?php if (isset($_SESSION['AG_description_ID_AG_description'.$i]) && $_SESSION['AG_description_ID_AG_description'.$i] == "3") echo 'selected = "selected"'; ?>>IS66 TnpA</option>
                       <option value = "4" <?php if (isset($_SESSION['AG_description_ID_AG_description'.$i]) && $_SESSION['AG_description_ID_AG_description'.$i] == "4") echo 'selected = "selected"'; ?>>IS66 TnpB</option>
                       <option value = "5" <?php if (isset($_SESSION['AG_description_ID_AG_description'.$i]) && $_SESSION['AG_description_ID_AG_description'.$i] == "5") echo 'selected = "selected"'; ?>>IS91 integrase_resolvase</option>
                       <option value = "6" <?php if (isset($_SESSION['AG_description_ID_AG_description'.$i]) && $_SESSION['AG_description_ID_AG_description'.$i] == "6") echo 'selected = "selected"'; ?>>Tn3 resolvase</option>
                       <option value = "7" <?php if (isset($_SESSION['AG_description_ID_AG_description'.$i]) && $_SESSION['AG_description_ID_AG_description'.$i] == "7") echo 'selected = "selected"'; ?>>Other</option>
               </select>
           </div>
           <div id="PG_<?php echo $i.'"'; echo (isset($_SESSION['ORF_function'.$i]) && $_SESSION['ORF_function'.$i] == "PG") ? 'style= "display:inline"' : 'style= "display:none"' ?>>
             <label for=PG>&nbsp; Description :</label>
             <select name = "PG_function_ID_PG_function<?php echo $i;?>">
                       <option value = "">«Choice»</option>
                       <option value = "1" <?php if (isset($_SESSION['PG_function_ID_PG_function'.$i]) && $_SESSION['PG_function_ID_PG_function'.$i] == "1") echo 'selected = "selected"'; ?>>Antibiotic resistance</option>
                       <option value = "2" <?php if (isset($_SESSION['PG_function_ID_PG_function'.$i]) && $_SESSION['PG_function_ID_PG_function'.$i] == "2") echo 'selected = "selected"'; ?>>Transcriptional Regulator factor</option>
             </select>
             <p>&nbsp;</p>
             <label for=Annotation>Annotation :</label>
             <INPUT type="text" name="PG_annotation<?php echo $i;?>" VALUE="<?php echo isset($_SESSION['PG_annotation'.$i]) ? $_SESSION['PG_annotation'.$i] : ""; ?> " SIZE=100 MAXLENGTH=150>
           </div>
     </div>  
</div>	
    <div class="entete_propriete">ORF sequence</div> 
	<div class="seq"><textarea cols=100 rows=3 name="ORF_Sequence<?php echo $i;?>"><?php echo isset($_SESSION['ORF_Sequence'.$i]) ? $_SESSION['ORF_Sequence'.$i] : ""; ?></textarea> </div>
    
    <div class="entete_propriete">Blast result</div> 
	<div><textarea cols=100 name="ORF_Blast_Result<?php echo $i;?>"><?php  echo isset($_SESSION['ORF_Blast_Result'.$i]) ? $_SESSION['ORF_Blast_Result'.$i] : ""; ?></textarea> </div> 
    
    <div class="entete_propriete">ORF comments</div> 
	<div><textarea cols=100 name="ORF_Comment<?php echo $i;?>"><?php  echo isset($_SESSION['ORF_Comment'.$i]) ? $_SESSION['ORF_Comment'.$i] : ""; ?></textarea> </div> 
<?php
	}				// Fin de la boucle for qui affiche les ORF
}				// Fin du if ($nb_orf !=0)
?>


</section>   
<section>
    <div class="enteteSection">
	<span class='entete_propriete'>Comments</span>
	</div>
   	<textarea cols=100 name="ET_Comments"><?php  echo isset($_SESSION['ET_Comments']) ? $_SESSION['ET_Comments'] : ""; ?></textarea>
</section>
<section>
    <div class="enteteSection">
	<span class='entete_propriete'>References</span>
	</div>
   	<textarea cols=100 name="ET_Reference"><?php  echo isset($_SESSION['ET_Reference']) ? $_SESSION['ET_Reference'] : ""; ?></textarea>
</section>
<section>
    <div class="enteteSection">
	<span class='entete_propriete'>Private comments</span>
	</div>
   	<textarea cols=100 name="ET_Private_comments"><?php  echo isset($_SESSION['ET_Private_comments']) ? $_SESSION['ET_Private_comments'] : ""; ?></textarea>
</section>
 
  </fieldset> 
    	<div class="piedSection">
			<ul>
			<li><input type="submit" name="Onsubmit" value="Submit" onclick = "return Confirmer(this)"></li>
			<li><INPUT TYPE="reset" name="reset" VALUE="Reset Defaults" onclick = "loadPage(window.location.pathname,0);"></li>
			</ul>				
		</div>
    </form>

                <!-- Formulaire de téléchargement de fichier supprimé et remplacé par boutton de téléchargement 
                    dans le formulaire précédent 
                <form enctype="multipart/form-data" action="scripts/upload.php" method="POST" name="upload_image">
                  <fieldset id=infoIS>
                
                <input type="hidden" name="MAX_FILE_SIZE" value="200000" />
                <label for="Recoding_image" class='entete_propriete_decal'>Recoding image (file .jpg) :</label> 
                    <?php echo $_SESSION['recoding_image'] ?>;
                    <INPUT TYPE="file" NAME="recoding_image" SIZE=20 MAXLENGTH=30 />    
                    <input type="submit" name="rec_image" value="Upload" />
                    <?php if ($_SESSION['recoding_image_error']) { echo "<p class='erreur'>".$_SESSION['recoding_image_error'] ;} ?>
                    </fieldset>
                </form> 
                                        -->
 
    </section> 
</article>

</div><!-- Fin du div page -->
</body>
</html>