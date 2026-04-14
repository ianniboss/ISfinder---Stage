<?php
//  Fonction qui affiche les liens
function sort_link($text, $order){
	global $order_by, $order_dir;

	if(!$order)
		$order = $text;

		$link = '<a href="search-db.php?tri=' . $order;
/*		
		if($order_by==$order && $order_dir=='ASC')
		     $link .= '&inverse=true';
		$link .= '"';
		if($order_by==$order && $order_dir=='ASC')
		    $link .= ' class="order_asc"';
		elseif($order_by==$order && $order_dir=='DESC')
		     $link .= ' class="order_desc"';
*/			 
		$link .= '">' . $text . '</a>';

		return $link;
}
//_______Affichage du lien pour la taxonomie____________________________
function ncbi_origin_link($origin) {
		$texteori="";
		$oritab = array();

        $oritab = explode(" ", $origin); 
        for ($k=2;$k<count($oritab);$k++) {
                $texteori=$texteori." ".$oritab[$k];
        }
		$oritab[0] = (!empty($oritab[0])) ? $oritab[0] : "";
		$oritab[1] = (!empty($oritab[1])) ? $oritab[1] : "";
        $origin_link = "<a href=\"http://www.ncbi.nlm.nih.gov/Taxonomy/Browser/wwwtax.cgi?name=";
        $origin_link .= $oritab[0]."+".$oritab[1]."\" target=\"_blank\">".$oritab[0]." ".$oritab[1]."</a>";
//   D�commenter pour avoir le nom d'hote complet  
/*        $origin_link .= $texteori;*/
        return $origin_link;
}
// ___________Requete pour r�cup�rer Origin d'un IS $IDET___________________
function is_origin($IDET) {
	$req = "SELECT `Host` FROM `element_transposable_has_host` ETHH 
					LEFT JOIN `element_transposable` ON `ID_ET` = ETHH.`Element_transposable_ID_ET` 
					LEFT JOIN `host` ON `Host_ID_host` = host.`ID_host` 
					WHERE  `Element_transposable_ID_ET` = $IDET AND `Origin` = 1";
	$result_origin = execute_sql($req);

//	$originvar= mysql_result($result_origin,0);
	$originvar = (mysqli_num_rows($result_origin)==0) ? "" : mysqli_result($result_origin,0);
	
	return $originvar;
}
// ___________Requete pour r�cup�rer le nom de l'iso � partir de ID_iso___________________
function is_iso($iso) {
	$req = "SELECT `ET_name` FROM `element_transposable` WHERE `ID_ET` like '$iso'";
	$result_iso = execute_sql($req);
//	$name_iso= mysql_result($result_iso,0);
	$name_iso = (mysqli_num_rows($result_iso)==0) ? "" : mysqli_result($result_iso,0);
	
	return $name_iso;
}
// ___________Requete pour r�cup�rer le ou les synonymes � partir de IDET___________________
function is_syn($ID_ET) {
	$req_syn = "SELECT `Synonyme` FROM `synonyme` WHERE synonyme.`Element_transposable_ID_ET` like '$ID_ET'";
	$result = execute_sql($req_syn);
	return $result;
}
// ___________Requete g�n�rique - renvoie une valeur ___________________
function is_champ($champ_select,$table,$champ_ID,$like) {
	$req_syn = "SELECT `$champ_select` FROM `$table` WHERE $table.`$champ_ID` like '$like'";
	$result = execute_sql($req_syn);
	$champ_result = (mysqli_num_rows($result)==0) ? '' : mysqli_result($result,0);

	return $champ_result;
}
// ___________Requete g�n�rique - Renvoie un tableau ___________________
function is_champX($champ_select,$table,$champ_ID,$like) {
	$champ_selectionne = ($champ_select=="*") ? "*": "`$champ_select`" ;
	$req_syn = "SELECT $champ_selectionne FROM `$table` WHERE $table.`$champ_ID` like '$like'";
	$result = execute_sql($req_syn);
	if (($nb_result = mysqli_num_rows($result))==0){
		$champ_result = '' ;
	}else{
		for ($i = 0 ; $i < $nb_result ; $i++){
			$champ_result[$i] = mysqli_fetch_array($result,MYSQLI_BOTH);
		}
	}
	return serialize($champ_result);		// serialize puis unserialize pour passer un tableau d'un script � l'autre
}
// ___________Requete pour r�cup�rer le ou les parents � partir de IDET___________________
function is_parent($ID_ET) {
	$reqParent = "SELECT `ET_name` FROM `element_transposable`  
				LEFT JOIN `parent_link` ON parent_link.`Element_transposable_ID_ET` =$ID_ET
				WHERE  `ID_ET` = Element_transposable_parent_ID_ET";
	$result = execute_sql($reqParent);	
	return $result;
}
// ___________Requete pour r�cup�rer le ou les hosts � partir de IDET___________________
function is_hosts($ID_ET) {
	$req_hosts = "SELECT `Host` FROM `element_transposable_has_host` ETHH 
				LEFT JOIN `element_transposable` ON `ID_ET` = ETHH.`Element_transposable_ID_ET` 
				LEFT JOIN `host` ON `Host_ID_host` = host.`ID_host` 
				WHERE  `Element_transposable_ID_ET` = $ID_ET";
	$result = execute_sql($req_hosts);
	return $result;
}
//______________Recherche du nombre d'ORF dans un IS _______________
function calcul_nbr_orf($ID_ET) {
	$req_orf = "SELECT `ID_ORF` FROM `orf` WHERE orf.`Element_transposable_ID_ET` like '$ID_ET'";
	$result = execute_sql($req_orf);
	$nbr= mysqli_num_rows($result);
	
	return $nbr;
}
// __________Affichage du r�sultat ________________________________
function affiche_result($result,$sortie) {
//	$tabPlus = (!empty($_POST['plus']))?$_POST['plus']:null;	comment� le 18/02/16 car semble ne servir � rien
	$i = 1;
	while ($table = mysqli_fetch_array($result)) {
		$namevar = $table["ET_name"];
		$IDET = $table["ID_ET"];
		$familyvar = $table["Family_Name"];
		$groupvar = $table["Group_Name"];		
		$synonvar = (!empty($table["Synonyme"])) ? $table["Synonyme"] : "";
		$isovar = (!empty($table["ID_iso"])) ? $table["ID_iso"] : "";
		$lengthvar = (!empty($table["ET_Length"])) ? $table["ET_Length"] : "";
		$numaccvar = (!empty($table["ET_Accession_number"])) ? $table["ET_Accession_number"] : "";
		$irvar = (!empty($table["IR_Length"])) ? $table["IR_Length"] : "";
		$drvar = (!empty($table["Direct_Repeat_Length"])) ? $table["Direct_Repeat_Length"] : "";
		
		print "<tr><td>$i</td><td><a href='ficheIS.php?name=$namevar' target='_blank'>$namevar</a></td>\n";

					// Family et Group				
		print "<td>$familyvar</td><td>$groupvar</td>\n";
		
		switch ($sortie){
			case 0:
					//  Synonyme
				print "<td>" ;			
				if ($synonvar!='NULL'){
					$result_syn = is_syn($IDET);
					$nbr_syn = 0;
					while($synonym= mysqli_fetch_array($result_syn)){
						($nbr_syn==0) ? print "$synonym[Synonyme]":print "<br>$synonym[Synonyme]";
						$nbr_syn++ ;
					}
				}
				print "</td>" ;
					// Iso
				if ($isovar!='NULL'){
					$name_iso= is_iso($isovar);
					print "<td>$name_iso</td>\n";
				}else {
					print "<td></td>\n";
					}
					// Origin
				$originvar= is_origin($IDET);
				$origin_link=ncbi_origin_link($originvar);
				echo "<td>".$origin_link."</td>\n";
						
				print "<td>$lengthvar</td>\n";
				print "<td>$irvar</td>\n";
				print "<td>$drvar</td>\n";
		
					// ORF Affichage de longueur, begin et end pour chaque orf
				print "<td>" ;
				$result_orfs = unserialize(is_champX("ORF_Length_AA`, `ORF_Begin`, `ORF_End","orf","Element_transposable_ID_ET",$IDET));
				$size = !empty($result_orfs) ? count($result_orfs) : 0;
				for ($j = 0 ; $j < $size ; $j++){
					print $result_orfs[$j]['ORF_Length_AA']." (".$result_orfs[$j]['ORF_Begin']."-".$result_orfs[$j]['ORF_End'].")<BR>";
					}
				print "</td>\n" ;
			
				if ($numaccvar != "ND") {
					print "<td><a href='http://www.ncbi.nlm.nih.gov/nuccore/$numaccvar' target='_blank'>$numaccvar</a></td></tr>\n";
					} else {
					print "<td>$numaccvar</a></td>";
					}
				break;
		
			case 1:
								// Origin
				$originvar= is_origin($IDET);
				$origin_link=ncbi_origin_link($originvar);
				print "<td>".$origin_link."</td>\n";					
								// Hosts			
				print "<td>" ;			
				$result_hosts = is_hosts($IDET);
				
				$nbr_hosts = 0;
				while($hosts = mysqli_fetch_array($result_hosts)){
					
					($nbr_hosts==0) ? print "$hosts[Host]":print "<br>$hosts[Host]";
					$nbr_hosts++ ;
					}
				print "</td>" ;
				break;
			case 2:
				$req_is = "SELECT `Direct_Repeat`, `DR_Left_Flank`, `DR_Rigth_Flank` FROM `et_insertion_site` EIS 
				LEFT JOIN `element_transposable` ON `ID_ET` = EIS.`Element_transposable_ID_ET` 
				WHERE  `Element_transposable_ID_ET` = $IDET";
				$result_is = execute_sql($req_is);
				$nbr_is = 0;
				print "<td class='seq'>" ;					
				while($is = mysqli_fetch_array($result_is)){
					$insertion_seq = "$is[DR_Left_Flank]&nbsp;(&nbsp;$is[Direct_Repeat]&nbsp;)&nbsp;$is[DR_Rigth_Flank]" ;
					($nbr_is==0) ? print $insertion_seq:print "<br>$insertion_seq";
					$nbr_is++ ;
					}
				print "</td>" ;
				break;
			case 3:
				$leftend = $table["Left_End"];
				$rigthend = $table["Rigth_End"];					
				print "<td class='seq'>$leftend</td><td class='seq'>$rigthend</td>\n</td>";
				break;
			case 4:
				$le = $table["LE"];
				$re = $table["RE"];					
				print "<td class='seq'>$le</td><td class='seq'>$re</td>\n</td>";
				break;
			default: break;
		}

	
		print "</tr>\n";
		$i++;
		}
}

?>
