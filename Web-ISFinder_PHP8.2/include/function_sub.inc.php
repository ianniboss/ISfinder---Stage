<?php
// Checks if the variable is a nucleotide sequence (DNA)
function estdna($chaine) {
    return (preg_match("/[^acgtnACGTN]/", $chaine)) ? false : true;
}

// Checks if the variable is a protein sequence
function estprot($chaine) {
    return (preg_match("/[^ACDEFGHIKLMNOPQRSTUVWY*acdefghiklmnopqrstuvwy]/", $chaine)) ? false : true;
}

// Calculates sequence ends
function is_ends($seq, $extr) {
    switch ($extr) {
        case "left":
            $extremite = substr($seq, 0, 50);
            break;
        case "right":
            $extremite = substr($seq, -50, 50);
            // Reverse-complement right end
            $extremite = strrev($extremite);
            $extremite = strtr($extremite, "atcgATCG", "tagcTAGC");
            break;
        case "LE":
            $extremite = substr($seq, 0, 100);
            break;
        case "RE":
            $extremite = substr($seq, -100, 100);
            break;
    }
    return $extremite;
}  
?>