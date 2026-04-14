<!DOCTYPE html>
<html>
<head>
<title>ISfinder</title>
<meta charset="utf-8" /> 
<meta name="author" content="Jo" />
<meta name="keywords" content="IS, Insertion Sequence" />
<link type="text/css" rel="stylesheet" href="styles/styles.css" media="screen" />
<link type="text/css" rel="stylesheet" href="styles/nomenclature.css" media="screen" />
<link type="text/css" rel="stylesheet" href="styles/menu.css" media="screen" />
<link rel="icon" href="favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<script src="scripts/nomenclature.js" type="text/javascript"></script>
</head>
<body>
<div id="page">
<header>
</header>

<?php 
$nav_en_cours='infos';
include('include/menu.inc.php');
include_once ("include/function.inc.php");
include_once ("include/function_nomenclature.inc.php");

// 1ere Lettre choisit (a par défaut) et type de bestiole choisit (IS par défaut)
$lettre = (isset($_GET['lettre']) && ctype_alpha($_GET['lettre']) && strlen($_GET['lettre'])== 1) ? htmlspecialchars($_GET['lettre']) : 'a';
$tab_typeGere = array("1","2","4");		// type 1 = IS, type 2 = MITE, type 4 = MIC
$type = (isset($_GET['type']) && in_array($_GET['type'],$tab_typeGere)) ? htmlspecialchars($_GET['type']) : '1';
$champrecherche = (isset($_GET['champrecherche'])) ? htmlspecialchars($_GET['champrecherche']) : "";
$champ = (isset($_GET['champ'])) ? htmlspecialchars($_GET['champ']) : "";
$cocher = ($champrecherche!="") ? 0 : 1 ;		// Permet de savoir s'il faut cocher le type de bestiole (non coché 
												// si affichage vient du formulaire NomenclatureRecherche
?>

<article>
<!--		<div class="ecran">contenu de mon &eacutecran</div> -->
	<section>
		<h2>Nomenclature for different bacterial species</h2>
		<hr/>
        <div class="new_div">
<!--    Choix de la bestiole : on recharge la page avec lettre déjà sélectionnée si changement de bestiole   -->        
        <ul class="bouton_ligne">
            <li class="bouton_ligne">
              <input value="1" name=type type=radio <?php echo check('type',1,$cocher); ?> onClick="reLoadPage3Param(window.location.pathname,'<?php echo $lettre; ?>',this.value,0)" />
              <label for=is>IS names</label>
            </li><li>
              <input value="2" name=type type=radio <?php echo check('type',2,$cocher); ?> onClick="reLoadPage3Param(window.location.pathname,'<?php echo $lettre; ?>' ,this.value,0)" />
              <label for=mite>MITE names</label>
            </li><li>
              <input value="4" name=type type=radio <?php echo check('type',4,$cocher); ?> onClick="reLoadPage3Param(window.location.pathname,'<?php echo $lettre; ?>',this.value,0)" />
              <label for=mic>MIC names</label>
            </li>
        </ul>            
		</div>
        <div>            
		<fieldset class="recherche">           
			<form id="NomenclatureRecherche" action="scripts/recherche.php" method="post">
            <input type="hidden" name="nom_script" value="nomenclature.php" />             
<li>					<label for="champrecherche">Search</label>
		<INPUT TYPE="text" NAME="champrecherche" id="champrecherche" VALUE="" SIZE="12">        
		<input type="submit" value="Submit" name="submitrecherche" /></li>
<li>	    <input type="radio" name="champ" value="origin" checked="checked" />Origin<br /></li>
<li>        <input type="radio" name="champ" value="code" />Code<br />  </li>
    		</form>      
		</fieldset>
        </div>
 <fieldset>
<?php
// Affichage des lesttres A-Z avec lien pour chaque lettre, rechargeant la page avec lettre et type de bestiole sélectionné
// la lettre sélectionnée est en gras sauf si l'affichage vient du formulaire NomenclatureRecherche
	$liens_lettre = "";
	for ($i=97;$i<123;$i++) {
	   $liens_lettre .= (chr($i)==$lettre && $champrecherche =="") ? "<a class='lettre_select' " : "<a ";
	   $liens_lettre .= "href=\"nomenclature.php?lettre=".chr($i)."&type=".$type."\">";
	   $liens_lettre .= strtoupper(chr($i));
	   $liens_lettre .= "</a>";
	   if ($i != 122) {
	       $liens_lettre .= "&nbsp;-&nbsp;";
	   }
    }
    echo $liens_lettre;
	
// Préparation de la requete
	$condition = ($champ == "origin") ? "`bact_origin` LIKE '%$champrecherche%' OR `new_taxo` LIKE '%$champrecherche%'" : "`nomType` LIKE '%$champrecherche%'";
	$where = ($champrecherche == "") ? "`type_element_transposable_ID_Type_ET` =$type AND `bact_origin` like '$lettre%'" : $condition ;
	$req = "SELECT `bact_origin`,`nomType`,`comment`, `new_taxo` FROM `nom_type` WHERE $where ORDER BY `nom_type`.`bact_origin`" ;
	
// Connexion à la base et execution de la requette  
	$cnx = connexion("ISfinder");		
	$result = execute_sql_new($cnx,$req);
	$nombre = mysqli_num_rows($result);
	mysqli_close($cnx);
	
// Affichage résultat
	If ($nombre > 0) {

		print "<table><tr><th>Origin</th><th>Code</th><th>Comment</th><th>Supplementary Taxo</th></tr>\n";
		for ($i=0;$i<$nombre;$i++){
			$nomenclature = mysqli_fetch_array($result);
			$bact_origin=$nomenclature['bact_origin'];
			$nomType=$nomenclature['nomType'];
			$comment=$nomenclature['comment'];
			$new_taxo=$nomenclature['new_taxo'];
			print "<tr><td>$bact_origin</td><td>$nomType</td><td>$comment</td><td>$new_taxo</td></tr>";
		}
		print "</table>";
	}else{ print "<h2><br/>No result</h2>";}
?>


        </fieldset>
	</section>
</article>

<?php include('include/footer.inc.php'); ?>
</div> <!-- Fin du div page -->
</body>
</html>