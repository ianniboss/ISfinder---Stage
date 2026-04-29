<?PHP
/**
 * Ces fonctions remplacent le module craklib, qui est défectueux.
 **/
define('VIDE', "");
define('CHECK_DICO_CHEMIN', '/var/www/html/intranet/secure/dicos');
define('CHECK_MOTPASSE_MINI', 10);			// Taille minimum du mot de passe
define('CHECK_MOTPASSE_MAXI', 16);			// Taille maximum du mot de passe
define('CHECK_MOTPASSE_TYPES', 3);			// Nombre minimum de type de caractères autorisés
define('CHECK_MOTPASSE_CONSECUTIVE', 6);	// Nombre maximum de caractères consécutifs du même type
define('CHECK_MOTPASSE_IDENTIQUE', 2);		// Nombre maximum de caractères identiques

/**
* checkRegles : renvoie une chaine de caractères explicitant les règles dictées par les variables
**/
function checkRegles() {
	return "Le mot de passe doit contenir entre ".CHECK_MOTPASSE_MINI." et ".CHECK_MOTPASSE_MAXI." caractères, ".CHECK_MOTPASSE_TYPES." types de caractères "
                . "(minuscules, majuscules, chiffres, ponctuation), il ne doit pas contenir plus de ".CHECK_MOTPASSE_CONSECUTIVE." caractères de même type consécutifs,"
                . " ni plus de ".CHECK_MOTPASSE_IDENTIQUE." caractères identiques consécutifs, il ne doit contenir ni espace, ni lettre accentuée,"
                . " il ne doit pas être présent dans un dictionnaire.";
}

/**
* checkMotPasse : vérifie si un mot de passe respecte les critères suivants :
* - longueur comprise entre CHECK_MOTPASSE_MINI et CHECK_MOTPASSE_MAXI
* - au moins CHECK_MOTPASSE_TYPES types de caractères sur 4 (minuscules, majuscules, chiffres, ponctuation
* - pas plus de CHECK_MOTPASSE_CONSECUTIVE caractères consécutifs du même type
* - pas plus de CHECK_MOTPASSE_IDENTIQUE caractères consécutifs identiques
* - ne pas contenir de lettre accentuée ni d'espace
* - est égale à $password2
* Variables d'entrées :
* @param	string              $password       Mot de passe à vérifier, si ce n'est pas une chaine, renvoie FALSE
* @param	string              $password2      Confirmation du mot de passe, doit être égal à $password,
*                                                   Si le mot de passe est valide, sera convertit en mot de passe acceptable pour une commande SSH !
* @param	array of string     $refus          Variable contenant la liste des motifs de refus, la variable n'est pas modifiée s'il n'y a pas de refus
* @param	string              $exclusion      Mot à exclure des valeurs acceptables, variable ommise si le type ne correspond pas
* @param	array of string     $exclusion      Liste de mots à exclure des valeurs acceptables, variable ommise si le type ne correspond pas
* Valeurs de retour :
* - Renvoie la valeur TRUE si le mot de passe correspond, FALSE sinon.
* - Renvoie aussi un tableau de chaines de caractère dans la variable $refus, qui contiens la liste des motifs pour lesquels le mot de passe est refusé.
**/

                function ajoutMotPasse($motpasse, &$liste) {   // Ajoute le mot de passe indiqué à la liste, uniquement s'il n'en fait pas déjà partie !
                    $motpasse = strtolower($motpasse);
                    if ( ($motpasse!="") and (!in_array($motpasse, $liste)) ) {  $liste[] = $motpasse;  }
                }
function checkMotPasse($password, &$password2, &$refus=array(), $exclusion=NULL) {
        $tab =   array('0', '1', '2', '3', '4', '5', '7', '!', '@');
        $tabR1 = array('o', 'i', 'z', 'e', 'a', 's', 'l', 'l', 'a');
        $tabR2 = array('o', 'l', 'z', 'e', 'a', 's', 'l', 'i', 'a');
        $carSpeciauxAutorises = array('\\',     '!',        '"',        '#',     '$',       '%',        '&',        "'",        '(',        ')', 
            '*',        '+',        ',',        '-',        '.',        '/',     ':',       ';',        '<',        '=',        '>',        '?', 
            '@',        '[',        ']',        '^',        '{',        '|',     '}',       '~',        '_');
        /*$carSpeciauxModifSSH = array('\\\\',    '\0041',    '\0042',    '\0043', '\0044',   '\0045',    '\0046',    "\0047",    '\0050',    '\0051', 
            '\0052',    '\0053',    '\0054',    '\0055',    '\0056',    '\0057', '\0072',   '\0073',    '\0074',    '\0075',    '\0076',    '\0077', 
            '\0100',    '\0133',    '\0135',    '\0136',    '\0173',    '\0174', '\0175',   '\0176',    '\005f');*/
        $carSpeciauxModifSSH = array('\\\\',     '\!',        '\"',        '\#',     '\$',       '\%',        '\&',        "\'",        '\(',        '\)', 
            '\*',        '\+',        '\,',        '\-',        '\.',        '\/',     '\:',       '\;',        '\<',        '\=',        '\>',        '\?', 
            '\@',        '\[',        '\]',        '\^',        '\{',        '\|',     '\}',       '\~',        '\_');
	$refus = array();
        $classes = array('chiffres'=>    preg_match("/[0-9]/",$password),
                    'minuscules'=>  preg_match("/[a-z]/",$password),
                    'majuscules'=>  preg_match("/[A-Z]/",$password),
                    'speciaux'=>    preg_match('/[!\\\\#&$%()*+",.\-_;<>=?@{}|\[\]\^\/~]/', $password),
                    'interdits'=>   preg_match('/[^a-zA-Z0-9!\\\\#&$%()*+",.\-_;<>=?@{}|\[\]\^\/~]/', $password)); 
        $classeCaractere = ( $classes['chiffres']?1:0 ) + ( $classes['minuscules']?1:0 )  + ( $classes['majuscules']?1:0 ) + ( $classes['speciaux']?1:0 );
        if ($password==VIDE) {                              $refus[] = "Le mot de passe est vide !";  }
        elseif (strlen($password)<CHECK_MOTPASSE_MINI) {    $refus[] = "Le mot de passe doit contenir au moins ".CHECK_MOTPASSE_MINI." caractères !";     }
        elseif (strlen($password)>CHECK_MOTPASSE_MAXI) {    $refus[] = "Le mot de passe doit contenir ".CHECK_MOTPASSE_MAXI." caractères maximum !";     }
        if ($classeCaractere<CHECK_MOTPASSE_TYPES) {        $refus[] = "Le mot de passe doit contenir ".CHECK_MOTPASSE_TYPES." classes de caractères sur 4 !";  }
        if (preg_match("/".str_repeat("[a-z]",CHECK_MOTPASSE_CONSECUTIVE)."/",$password)) {  $refus[] = "Trop de minuscules consécutives";	}
        if (preg_match("/".str_repeat("[A-Z]",CHECK_MOTPASSE_CONSECUTIVE)."/",$password)) {  $refus[] = "Trop de majuscules consécutives";	}
        if (preg_match("/".str_repeat("[0-9]",CHECK_MOTPASSE_CONSECUTIVE)."/",$password)) {  $refus[] = "Trop de chiffres consécutifs";	}
        foreach (count_chars($password, 1) as $i => $val) {
            if ($val>CHECK_MOTPASSE_IDENTIQUE) {
                $refus[] = "Trop de caractères identiques consécutifs (".chr($i).")";
            }
        }
        if ($classes['interdits']) {        $refus[] = "Le mot de passe ne doit contenir que des lettres, chiffres, ou caractères parmi&nbsp;: \ ! \" # $ % & ' ( ) * + , - _ . / : ; < = > ? @ [ ] ^ { | } ~";  }
        if ($password2==VIDE) {             $refus[] = "Le mot de passe n'a pas été confirmé !";  } 
        elseif ($password!=$password2) {    $refus[] = "Les mots de passe ne correspondent pas !";  }
        if ($refus!=array()) {      // Il y a une erreur, donc le mot de passe n'est pas sùur, et on ne vérifie pas dans les dicos
            $retour = FALSE;
        } else {    // On ne vérifie dans les dicos que si le mot de passe est conforme aux règles !
            $listePassword = array();
            ajoutMotPasse($password, $listePassword);
            ajoutMotPasse(str_replace($tab, $tabR1, $password), $listePassword);
            ajoutMotPasse(str_replace($tab, $tabR2, $password), $listePassword);
            $autrePassword = preg_replace('/[^a-zA-Z0-9]/', '', $password);
            ajoutMotPasse($autrePassword, $listePassword); 
            ajoutMotPasse(str_replace($tab, $tabR1, $autrePassword), $listePassword);   
            ajoutMotPasse(str_replace($tab, $tabR2, $autrePassword), $listePassword);  
            $trouve = FALSE;                 // Basculera à TRUE si le mot de passe est trouvé...
            // Vérifie d'abord dans les exclusions
            if (is_string($exclusion)) {    $exclusion = array($exclusion);  }
            if (is_array($exclusion)) { // S'il y a bien un tableau d'exclusions à contrôler
                foreach($exclusion as $exclu) { // Parcours la liste
                    if (is_string($exclu)) {    // Si c'est une chaine'
                            foreach($listePassword as $pass) {  // Pour chaque mot de passe de la liste
                                if (strpos($exclu, $pass)!==FALSE) {  $trouve = TRUE;  }
                            }
                    }
                }
            }
            if ($trouve) {  // On ne regarde pas dans les dicos car le mot de passe contient un des mots exclus
                    $refus[] = "Le mot de passe contient un mot exclu (vos noms, prénoms... etc...), soyez plus inventif...";
            } else {     // Le mot de passe ne contient pas un des mots exclus :  Récupère la liste des dicos disponibles dans le dossier "dicos"
                    $fichiers = scandir(CHECK_DICO_CHEMIN."/");
                    foreach ($fichiers as $fic) {	// Parcours les fichiers .TXT du dossier contenant les dicos...
                            if ( (!$trouve) and (substr($fic,0,1)!=".") and (strtolower(pathinfo($fic, PATHINFO_EXTENSION))=="txt") ) {
                                    $resDico = fopen(CHECK_DICO_CHEMIN."/".$fic, "r");
                                    if ($resDico) { 
                                        while (!feof($resDico) and (!$trouve)) {
                                            $buffer = fgets($resDico, 1024);
                                            foreach ($listePassword as $pass) { if (strpos($buffer, $pass)!==FALSE) { $trouve = TRUE; } }
                                        }
                                        fclose($resDico);
                                    }
                            }
                    }
            }
            if ($trouve) {  $refus[] = "Le mot de passe a été trouvé dans un dictionnaire, soyez plus inventif...";  }
            $retour = !$trouve; // On renvoie TRUE si le mot de passe n'a pas été trouve (conforme) FALSE s'il l'a été...
            $password2 = str_replace($carSpeciauxAutorises, $carSpeciauxModifSSH, $password2);  // On modifie $password2 pour qu'il soit utilisable en ligne de commande ssh...
            var_dump($password2);
        }
	return $retour;
}

?>