<?php
//_______________________________La variable est une séquence nucléotidique _______________
function estdna($chaine){
	return (preg_match("/[^acgtnACGTN]/", $chaine)) ? false : true;
}

//_______________________________La variable est une séquence proteique _______________
function estprot($chaine){
	return (preg_match("/[^ACDEFGHIKLMNOPQRSTUVWY*acdefghiklmnopqrstuvwy]/", $chaine)) ? false : true;
}
//______________ Calcul des extrémités ____________________
function is_ends($seq,$extr) {
	switch ($extr) {
		case "left":
			$extremite = substr($seq, 0, 50) ;
			break ;
		case "right":
			$extremite = substr($seq, -50, 50) ;
					// Transformation de Right End en son réverse-complémentaire
			$extremite = strrev($extremite);
			$extremite = strtr($extremite,"atcgATCG","tagcTAGC");
			break ;
		case "LE":
			$extremite = substr($seq, 0, 100) ;
			break ;
		case "RE":
			$extremite = substr($seq, -100, 100) ;
			break ;
	}
	return $extremite ;
}  
?>