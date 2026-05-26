<!DOCTYPE html>
<html>
<head>
	<title>ISfinder</title>
	<meta charset="utf-8" /> 
	<meta name="author" content="Jo" />
	<meta name="keywords" content="IS, Insertion Sequence" />
	<link type="text/css" rel="stylesheet" href="../styles/styles.css" media="screen" />
	<link type="text/css" rel="stylesheet" href="../styles/menu.css" media="screen" />
	<link type="text/css" rel="stylesheet" href="../styles/ficheMGE.css" media="screen" />
	<link rel="icon" href="../favicon.ico" type="image/x-icon">
	<link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
</head>
<body>
<div id="page">
<header>
</header>

<?php
header('Content-Type: text/html; charset=UTF-8'); 
$nav_en_cours='tools';
include('../include/menu.inc.php'); 
include_once ("../include/function.inc.php");
include_once ("../include/affiche.inc.php");	

	/* Ajout pour register globals off*/
$name = (!empty($_GET['name'])) ? htmlspecialchars($_GET['name']) : "" ;
$ident = (!empty($_GET['ident']) && ctype_digit($_GET['ident'])) ? $_GET['ident'] : "0" ;

if ($name || $ident){
// La recherche de la fiche MGE tient compte du parametre passé, soit le nom soit ID_ET de l'IS
$condition = ($ident == '0') ? "`ET_name` like '".$name."'" : "`ID_ET` = '".$ident."'";

	/* Connexion à la base de données */
$cnx = connexion("ISfinder");	
$reqIS = "SELECT `ID_ET`,`ET_name`,`ET_Length`,`ET_Accession_number`,`ID_iso`,`Synonyme`, `Group_Name`,`Family_Name`, `ET_DNA_Sequence`, `Transposition`, `recode`,`frame`, `type`, `exp_demontred`, `SD`, `structure`, `recoding_seq`, `recoding_annot`, `recoding_image`, `Type_ET`, `ET_Comments`, `ET_Reference`, `IR_Length`, `Left_End`, `Rigth_End`
				  FROM `element_transposable` ET
				  JOIN `family` FAM
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
				  WHERE $condition LIMIT 1" ;	
				  
	/* Execution de la requette et si résultat, alors on continue */
	if ($result = execute_sql_new($cnx,$reqIS)){
		$nombre = mysqli_num_rows($result);
		if ($nombre == 1) {
			$is = mysqli_fetch_array($result);
			$ID_ET=$is['ID_ET'];
			$name=$is['ET_name'];
			$family=$is['Family_Name'];
			$groupe=$is['Group_Name'];
			$egm_type=$is['Type_ET'];
			$iso= ($is['ID_iso']!=NULL) ? is_iso($is['ID_iso']) : '';
			$result_syn= ($is['Synonyme']!= NULL) ? is_syn($ID_ET) : '';
			$result_parent = is_parent($ID_ET);
			$acc_num = $is['ET_Accession_number'];
			$transposit = $is['Transposition'];
			$originvar= is_origin($ID_ET);
			$length= $is['ET_Length'];
			$IR= $is['IR_Length'];		
			$IRL= $is['Left_End'];		
			$IRR= $is['Rigth_End'];		
			$comment= encodaccent($is['ET_Comments']);
			$reference= encodaccent($is['ET_Reference']);
			$dna_seq=$is['ET_DNA_Sequence'];
			$recode=$is['recode'];
			$frame=$is['frame'];
			$type=$is['type'];
			$exp_demontred=$is['exp_demontred'];
			$shine=$is['SD'];
			$structure=$is['structure'];
			$recoding_seq=$is['recoding_seq'];
			$recoding_annot=$is['recoding_annot'];
			$recoding_image=$is['recoding_image'];	
			
			$ISEcomments= is_champ('Ends_comments','is_ends','Element_transposable_ID_ET',$ID_ET);
			$LE= is_champ('LE','is_ends','Element_transposable_ID_ET',$ID_ET);
			$RE= is_champ('RE','is_ends','Element_transposable_ID_ET',$ID_ET);
			$LE_structII_bdd= is_champ('LE_Structure_II','is_ends','Element_transposable_ID_ET',$ID_ET);
			$RE_structII_bdd= is_champ('RE_Structure_II','is_ends','Element_transposable_ID_ET',$ID_ET);
			$LE_structII = ($LE_structII_bdd == "0") ? "No" : "Yes" ;			// $LE_structII_bdd
			$RE_structII = ($RE_structII_bdd == "0") ? "No" : "Yes";			// $RE_structII_bdd
	
			$insertion_site= is_champX('*','et_insertion_site','Element_transposable_ID_ET',$ID_ET);
			$insertion = unserialize ($insertion_site);
			// PHP 8.5: count() on non-array throws TypeError; guard with is_array()
			$nbr_siteInsert = is_array($insertion) ? count($insertion) : 0 ;
	
	/*		$Left_flank= is_champ('DR_Left_Flank','et_insertion_site','Element_transposable_ID_ET',$ID_ET);
			$Direct_repeat= is_champ('Direct_Repeat','et_insertion_site','Element_transposable_ID_ET',$ID_ET);
			$Right_flank= is_champ('DR_Rigth_Flank','et_insertion_site','Element_transposable_ID_ET',$ID_ET);
			$DR_length= is_champ('Direct_Repeat_Length','et_insertion_site','Element_transposable_ID_ET',$ID_ET);
	*/		
			$LE_CS_Left_flank= is_champ('LE_CS_Left_Flank','et_insertion_site','Element_transposable_ID_ET',$ID_ET);
			$LE_CS= is_champ('LE_CS','et_insertion_site','Element_transposable_ID_ET',$ID_ET);
			$RE_CS_Right_flank= is_champ('RE_CS_Rigth_Flank','et_insertion_site','Element_transposable_ID_ET',$ID_ET);
			$RE_CS= is_champ('RE_CS','et_insertion_site','Element_transposable_ID_ET',$ID_ET);
    ?>

<article>
<!--		<div class="ecran">contenu de mon &eacutecran</div> -->
	<section>
<?php
		print "<div id=seq_ident>";
		print "<p>".$name."</p>";
		print "<ul><li><span class='entete_propriete'>Family </span>".$family."</li><li><span class='entete_propriete'>Group </span>".$groupe."</li></ul>";
		print "</div>";
		print "<span class='entete_propriete'> MGE type </span>".$egm_type;
		print "<span class='entete_propriete_decal'>Related element(s) : </span>";
		for ($nbr_parent = 0; $result_parent !='' && $parent= mysqli_fetch_array($result_parent); $nbr_parent++ ){
			($nbr_parent==0) ? print "$parent[ET_name]":print ", $parent[ET_name]";
		}
		print "<br>";		
		print "<span class='entete_propriete'>Isoform </span>".$iso."<span class='entete_propriete_decal'>Synonym(s) </span>";
		
		for ($nbr_syn = 0; $result_syn !='' && $synonym= mysqli_fetch_array($result_syn); $nbr_syn++ ){
			($nbr_syn==0) ? print "$synonym[Synonyme]":print ", $synonym[Synonyme]";
		}
?>
	<div class="piedSection"></div>
    </section>
    <section>    

<table>
<tr><th>Accession number</th><th>Transposition</th><th>Origin</th><th>Host</th></tr>     
<tr>
	<td align="center"><?php print $acc_num ?></td>
    <td align="center"><?php print $transposit ?></td>
    <td><?php print ncbi_origin_link($originvar) ?></td>
    <td><div class="ascenseurAuto"><?php $result_hosts = is_hosts($ID_ET);	
			  for($nbr_hosts = 0; $hosts = mysqli_fetch_array($result_hosts); $nbr_hosts++){
				  ($nbr_hosts==0) ? print "$hosts[Host]":print "<br>$hosts[Host]";
				  }?>
              </div></td>
</tr></table>
	<div class="piedSection"></div>
	</section>
   	<section>
    <div class="enteteSection">
	<span class='entete_propriete'>DNA section</span>
	<div id="droite"><span class='entete_propriete'>IS Length : </span><?php print $length ?> bp</div>
	</div>
   	<div class="piedSection"></div>
	</section>
    <section>
    <div><p class='entete_propriete' align='center'>Ends</p>
<?php
if ($family == 'IS110' && $groupe != 'IS1111'){
	print "<br><span class='entete_propriete_tri'>Left&nbsp;end&nbsp;: </span><span class='seq'>".$LE."</span>";
	print "<br><span class='entete_propriete_tri'>Right&nbsp;end&nbsp;: </span><span class='seq'>".$RE."</span><br>";
	
}elseif ($family== 'IS91' || $family== 'ISCR'){
	print "<br><span class='entete_propriete_tri'>oriIS : </span><span class='seq'>".$LE."</span>";
	print "<span class='entete_propriete'> II struct. : </span>".$LE_structII;
	print "<br><span class='entete_propriete_tri'>terIS : </span><span class='seq'>".$RE."</span>";
	print "<span class='entete_propriete'> II struct. : </span>".$RE_structII;
}elseif ($family== 'IS200/IS605'){
	print "<br><span class='entete_propriete_tri'>Left&nbsp;end&nbsp;: </span><span class='seq'>".$LE."</span>";
	print "<span class='entete_propriete'> II struct. : </span>".$LE_structII;
	print "<br><span class='entete_propriete_tri'>Right&nbsp;end&nbsp;: </span><span class='seq'>".$RE."</span>";
	print "<span class='entete_propriete'> II struct. : </span>".$RE_structII;
}else{
	print "<br><span class='entete_propriete'>IR Length : </span>".$IR."<br>";
	print "<br><span class='entete_propriete_bis'>IRL : </span><span class='seq'>".$IRL."</span>";
	print "<br><span class='entete_propriete_bis'>IRR : </span><span class='seq'>".$IRR."</span><br>";
}
if ($ISEcomments)  print "<br><span class='entete_propriete'>Comments : </span>".$ISEcomments;	
?>
	</div>
	<div><p class='entete_propriete' align='center'>Insertion site</p><br>
<?php
print "<table>";
if ($family== 'IS91' || $family== 'ISCR' || $family== 'IS200/IS605'){
	print "<tr><th>Left flank</th><th>LE cleavage site</th><th>Right flank</th><th>RE cleavage site</th></tr>";
	for ($i = 0 ; $i < $nbr_siteInsert ; $i++){
		print "<tr class='seq'>	<td align='right'>".$insertion[$i]['LE_CS_Left_Flank']."</td>";
		print "<td class='seq' align='center'>".$insertion[$i]['LE_CS']."</td>";
    	print "<td class='seq' align='left'>".$insertion[$i]['RE_CS_Rigth_Flank']."</td>";
    	print "<td class='seq' align='center'>".$insertion[$i]['RE_CS']."</td>";
		print "</tr>";
	} // Fin du for
}else{
	print "<tr><th>Left flank</th><th>Direct repeat</th><th>Right flank</th><th>DR Length</th></tr>";
	for ($i = 0 ; $i < $nbr_siteInsert ; $i++){
		print "<tr>	<td class='seq' align='right'>".$insertion[$i]['DR_Left_Flank']."</td>";
		print "<td class='seq' align='center'>".$insertion[$i]['Direct_Repeat']."</td>";
    	print "<td class='seq' align='left'>".$insertion[$i]['DR_Rigth_Flank']."</td>";
    	print "<td class='seq' align='center'>".$insertion[$i]['Direct_Repeat_Length']."</td>";
		print "</tr>";
	}  // Fin du for
}
print "</table>";

?>
	</div>
   	<div class="piedSection"></div>       
	</section>
	<section>
    <div><p class='entete_propriete'>DNA sequence </p>
<?php 
$form_dna_seq = wordwrap($dna_seq,100,"<br />\n",true);
print "<div class='seq'>$form_dna_seq </div>";
?>    
	</div>
   	<div class="piedSection"></div>    
    </section>

<?php 
if ($recode!=NULL){
	print "<section><div class='entete_propriete'>";
	print "<span class='entete_propriete'>Recoding section </span>";
	print "<ul><li><span class='entete_propriete'>Recoding by </span>".$recode."</li><li><span class='entete_propriete_decal'>Frame </span>".$frame."</li><li><span class='entete_propriete_decal'>Type </span>".$type."</li><li><span class='entete_propriete_decal'>Experimentally demonstrated </span>".$exp_demontred."</li></ul>";
	print "<br><p class='entete_propriete'>Stimulators : </p>";
	print "<ul><li><span class='entete_propriete'>Shine-Dalgarno sequence : </span>".$shine."</li><li><span class='entete_propriete_decal'>Secondary structure : </span>".$structure."</li></ul></div>";
	print "<br><p class='entete_propriete'>Recoding motif : </p>";	
		
	if ($recoding_image!=NULL){
		print "<div id='imag-droite'>";
		print"<img src='../drawings/$recoding_image' width='490px'>";
		print "</div>";
	}
	
	if (!empty($recoding_seq)){
		for ($i=0; (($sous_chaine_seq= mb_substr($recoding_seq,$i,50))!='');$i=$i+50){
			$sous_chaine_annot = mb_substr($recoding_annot,$i,50);
			print "<div class='seq'>$sous_chaine_seq </div>";
			print "<div class='seq'>$sous_chaine_annot </div>";
		}
	}
}

print "<div class='piedSection'></div></section>";
?>    

    <section>
    <div class="enteteSection">
	<span class='entete_propriete'>Protein section</span>    
	<div id="droite"><span class='entete_propriete'>ORF number : </span><?php 
		$num_orf = calcul_nbr_orf($ID_ET);
		print $num_orf;
		 ?></div>
	</div>
    <p>&nbsp;</p>
<?php    
	if ($num_orf >0){
		$reqORF = "SELECT * FROM `orf`
				  LEFT JOIN `tnp_chemestry` ON `Tnp_chemestry_ID_Tnp_chemestry` = tnp_chemestry.`ID_Tnp_chemestry`
				  LEFT JOIN `tnp_description` ON `Tnp_description_ID_Tnp_description` = tnp_description.`ID_Tnp_description`
				  LEFT JOIN `ag_description` ON `AG_description_ID_AG_description` = ag_description.`ID_AG_description`
				  LEFT JOIN `pg_function` ON `PG_function_ID_PG_function` = pg_function.`ID_PG_function`
				WHERE orf.`Element_transposable_ID_ET` like '$ID_ET'" ;
		$result = execute_sql_new($cnx,$reqORF);
	}
	for ($i=$num_orf;$i>0;$i--){
		$orf = mysqli_fetch_array($result);
		print "<span class='entete_propriete'>ORF </span>".$orf['ORF_rank']."<br>";
		print "<table><tr><th colspan='2' scope='col'>Length</th><th width='17%'>Begin</th><th width='17%'>End</th><th width='17%'>Strand</th><th width='17%'>Fusion ORF</th></tr>";
		print "<tr>";
		$orf_length = abs($orf['ORF_End'] - $orf['ORF_Begin']) + 1 ;
		print "<td align='center'>".$orf_length." bp</td>";
		print "<td align='center'>".$orf['ORF_Length_AA']." aa</td>";
		print "<td align='center'>".$orf['ORF_Begin']."</td>";
		print "<td align='center'>".$orf['ORF_End']."</td>";
		$strand =($orf['ORF_Strand']==1) ? '+' : '-' ; 
		print "<td align='center'>".$strand."</td>";
		$fusion =($orf['ORF_Frameshift']==1) ? 'Yes' : 'No';
		print "<td align='center'>".$fusion."</td>";
		print "</tr></table>";
		if ($orf['ORF_function']=='Tnp'){
			print "<span class='entete_propriete'>ORF function : </span> Transposase<br>";
			if ($orf['Tnp_chemestry_ID_Tnp_chemestry']== NULL){
				print "<span class='entete_propriete'> Description : </span>".$orf['part_transposase'];
			}else{
				print "<span class='entete_propriete'> Chemistry : </span>".$orf['chemestry'];
			}
		}elseif($orf['ORF_function']=='AG'){
		print "<span class='entete_propriete'>ORF function : </span> Accessory Gene<br>";
		print "<span class='entete_propriete'> AG : </span>".$orf['description'];
		}elseif ($orf['ORF_function']=='PG'){
		print "<span class='entete_propriete'>ORF function : </span> Passenger Gene<br>";
		print "<span class='entete_propriete'> Annotation : </span>".$orf['PG_annotation'];
		print "<span class='entete_propriete_decal'>Description : </span>".$orf['function'];		
		}
		
		print "<p class='entete_propriete'>ORF sequence : </p>";
		$form_orf_seq = wordwrap($orf['ORF_Sequence'],100,"<br />\n",true);
		print "<div class='seq'>$form_orf_seq </div>";
		print "<p>&nbsp;</p>";
		print "<span class='entete_propriete'> Blast result :</span>".$orf['ORF_Blast_Result'];

		if ($orf['ORF_Comment']) print "<span class='entete_propriete_decal'>Comments : </span>".$orf['ORF_Comment'];		
		print "<div class='piedSection'></div>";
	}
mysqli_close($cnx);
?>	
   	<div class="piedSection"></div>    
    </section>
    
    <section>
		<div class="enteteSection">
			<span class='entete_propriete'>Comments</span>
		</div>

		<div class="texte">
			<?php print nl2br($comment); ?>
		</div>

		<div class="piedSection"></div>  

		</section>   
		
		<div class="enteteSection">
			<span class='entete_propriete'>References</span>
		</div>
		
		<div class="texte">
			<?php print nl2br($reference); ?>
		</div>
		<div class="piedSection"></div>    
    </section> 
</article>

<?php
include_once('../include/footer.inc.php');
	} else {
		erreur_val("name", "Sequence IS not found");
	}
	} else {
			erreur_val("name", "Sequence IS not found");
		}
	} else {
		erreur_val("name", "Sequence not found");
	}
?>

</div> <!-- Fin du div page -->
</body>
</html>