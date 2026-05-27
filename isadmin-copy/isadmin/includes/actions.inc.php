<?php
// Récupération des données de la base ISsubmit dans des variables de SESSION
function recup_data($ident, $name, $bdd) {
    $retour = "1";
    $cnx = connexion($bdd);
    // La recherche de la fiche MGE tient compte du parametre passé, soit le nom soit ID_ET de l'IS
    $condition = ($ident == '') ? "`ET_name` like '" . $name . "'" : "`ID_ET` like '" . $ident . "'";

    if ($cnx) {
        // La table `base` n'existe que dans ISsubmit (pas dans isfinder) — JOIN conditionnel
        $join_base = ($bdd !== 'isfinder') ? "LEFT JOIN `base` ON `base_ID_Base` = `ID_Base`" : "";

        $reqIS = "SELECT * FROM `element_transposable` ET
                LEFT JOIN `family` FAM
                ON `Family_ID_Family` = `ID_Family`
                LEFT JOIN `groups` GRP
                ON `Groups_ID_Groups` = `ID_Groups`
                $join_base
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
                WHERE $condition LIMIT 1";


        /* Execution de la requette et si résultat, alors on continue */
        $result = execute_sql($cnx, $reqIS);
        if (mysqli_num_rows($result) != 1) {
            mysqli_close($cnx);
            $retour = "0";
            $_SESSION['error'] = "Problem dans recupdata";
            header('Location: ../liste.php?list=1');
            exit();
        } else {
            $is = mysqli_fetch_assoc($result);
            foreach ($is as $index => $valeur) {
                // PHP 8.5 Fix: Session keys must be strings at root level. Skip numeric indices.
                if (!is_numeric($index)) {
                    // PHP 8.5 Fix: strip_tags only accepts strings
                    $_SESSION[$index] = is_string($valeur) ? strip_tags($valeur) : $valeur;
                }
            }
        }
        is_submiter($cnx, $_SESSION['ID_ET']);

        $origin = is_origin($cnx, $_SESSION['ID_ET']);
        $origintab = explode(" ", $origin);
        // PHP 8.5 Fix: Check if second part of origin exists
        $_SESSION['Origin'] = $origintab[0] . (isset($origintab[1]) ? " " . $origintab[1] : "");


        $hosts = is_hosts($cnx, $_SESSION['ID_ET']);
        $_SESSION['Hosts'] = "";
        $i = 0;
        while ($host = mysqli_fetch_array($hosts)) {
            $i++;
            $_SESSION['Hosts'] = $_SESSION['Hosts'] . $host['Host'] . "\n";
            $_SESSION['ID_host'][$i] = $host['ID_host'];
        }

        $site = unserialize(is_champX($cnx, '*', 'et_insertion_site', 'Element_transposable_ID_ET', $_SESSION['ID_ET'], ''));
        $_SESSION['nb_site'] = (empty($site) || !is_array($site)) ? 0 : count($site);
        // PHP 8.5 Fix: Correct loop condition ( < instead of <= ) and add array guard
        for ($i = 0; $i < $_SESSION['nb_site'] && is_array($site); $i++) {
            if (isset($site[$i]) && is_array($site[$i])) {
                foreach ($site[$i] as $champ => $valeur) {
                    $_SESSION[$champ . $i] = $valeur;
                }
            }
        }

        $ORF = unserialize(is_champX($cnx, '*', 'orf', 'Element_transposable_ID_ET', $_SESSION['ID_ET'], ''));
        $_SESSION['nb_orf'] = (empty($ORF) || !is_array($ORF)) ? 0 : count($ORF);
        $nb_orf = $_SESSION['nb_orf'];
        // PHP 8.5 Fix: Added array guards for ORF loop
        for ($i = 1; $i <= $_SESSION['nb_orf'] && is_array($ORF); $i++) {
            if (isset($ORF[$i - 1]) && is_array($ORF[$i - 1])) {
                foreach ($ORF[$i - 1] as $champ => $valeur) {
                    $_SESSION[$champ . $i] = $valeur;
                }
            }
        }
        mysqli_close($cnx);
    } else {
        $retour = "0";
    }

    return ($retour);
}

// Ecriture des données dans la base ISfinder
function ecrit_data($ident, $name, $base_ecriture) {
    $retour = "1";
    $_SESSION['error'] = "";

    //	$cnx = connect("astun","isadmin",$bdd);
    $cnx = connexion($base_ecriture);

    if ($cnx) {
        // D'abord on vérifie qu'ET_name n'existe pas déjà dans la base
        $reqIS = "SELECT ID_ET FROM `element_transposable` WHERE `ET_name` like '" . mysqli_real_escape_string($cnx, $name) . "' LIMIT 1";
        $result = execute_sql($cnx, $reqIS);
        if (mysqli_num_rows($result) == 0) {
            // Vérification des données
            foreach ($_SESSION as $elt_session => $var_session) {	// on remplit une variable portant le nom du champ 		
                // PHP 8.5 Fix: strip_tags only accepts strings, preserve arrays for legacy logic
                $$elt_session = is_string($var_session) ? strip_tags($var_session) : $var_session;			// pour ne pas écrire tt le tps $_SESSION[]
            }

            /* On teste les champs entrés et s'il y a des erreurs on remplit $_SESSION["error"]  */
            $_SESSION["error"] .= (empty($Firstname) || (preg_match("/^[^a-zA-Z- \éèêç']/", $Firstname))) ? "First name correct is required.</br>" : "";
            $_SESSION["error"] .= (empty($Lastname) || preg_match("/^[^a-zA-Z- \éèêç']/", $Lastname)) ? "Last name correct is required.</br>" : "";
            //			$_SESSION["error"] .= (empty($Institution)||strlen($Institution)<2) ? "Field institution is required.</br>" : "";
            //			$_SESSION["error"] .= (empty($Country)||strlen($Country)<2) ? "Field country is required.</br>" : "";
            $_SESSION["error"] .= (empty($Mail)) ? "e-mail address is required.</br>" : "";
            $_SESSION["error"] .= (empty($ET_name) || strlen($ET_name) < 5 || preg_match("/[^a-zA-Z0-9_]/", $ET_name)) ? "IS name is required with min 5 char.</br>" : "";
            $_SESSION["error"] .= (empty($Origin)) ? "Field origin is required.</br>" : "";
            $_SESSION["error"] .= (empty($Family_ID_Family)) ? "Field family is required.</br>" : "";

            if (filter_var($Mail, FILTER_VALIDATE_EMAIL) === FALSE) {
                $_SESSION["error"] .= "l'adresse e-mail saisie n'est pas valide.</br>";
            }

            /* Pour les séquences DNA et Prot élimination des blancs et retour charriots */
            $car_elim = array("\n", "\r", " ");
            $ET_DNA_Sequence = str_replace($car_elim, "", $ET_DNA_Sequence);
            $_SESSION["error"] .= (empty($ET_DNA_Sequence)) ? "DNA sequence is required.</br>" : "";
            $_SESSION["error"] .= (estdna($ET_DNA_Sequence) != true) ? "Only A, T, C, G and N characters are allowed.</br>" : "";

            if (isset($nb_orf)) {
                for ($i = 1; $i <= $nb_orf; $i++) {
                    $var_dynamique = 'ORF_Sequence' . $i;
                    $$var_dynamique = str_replace($car_elim, "", $$var_dynamique);
                    $_SESSION["error"] .= (isset($$var_dynamique) && estprot($$var_dynamique) != true) ? "Only amino acid and * are allowed.</br>" : "";
                }
            }
            // PHP 8.5 Fix: Prevent Fatal Error if Family doesn't exist
            $sql_family_check = "SELECT ID_Family FROM `family` WHERE `Family_Name` LIKE '" . mysqli_real_escape_string($cnx, $Family_ID_Family) . "' LIMIT 1";
            $res_family_check = execute_sql($cnx, $sql_family_check);
            if (mysqli_num_rows($res_family_check) == 0 && !empty($Family_ID_Family)) {
                $_SESSION["error"] .= "La famille d'IS <b>" . htmlspecialchars($Family_ID_Family) . "</b> n'existe pas dans la base ISfinder ! Veuillez d'abord la créer via phpMyAdmin avant d'approuver cette soumission.</br>";
            }

            // Optional: Also check Groups if provided
            if (!empty($Groups_ID_Groups)) {
                $sql_group_check = "SELECT ID_Groups FROM `groups` WHERE `Group_Name` LIKE '" . mysqli_real_escape_string($cnx, $Groups_ID_Groups) . "' LIMIT 1";
                $res_group_check = execute_sql($cnx, $sql_group_check);
                if (mysqli_num_rows($res_group_check) == 0) {
                    $_SESSION["error"] .= "Le groupe d'IS <b>" . htmlspecialchars($Groups_ID_Groups) . "</b> n'existe pas dans la base ISfinder ! Veuillez d'abord le créer via phpMyAdmin.</br>";
                }
            }


            if ($_SESSION["error"] === "") {
                /*				foreach ($_SESSION as $elt_session => $var_session){			// Pour afficher les variables sur la page web 
                                    echo $elt_session." = ".$var_session."<br>" ;	
                                }
                */
                // Insertion des infos du Submiter s'il n'existe pas dans ISfinder et récupération du ID_Submiter
                $sql_submiter = "SELECT ID_Submiter FROM `submiters` WHERE `Lastname` LIKE '" . mysqli_real_escape_string($cnx, $Lastname) . "' AND `Mail` LIKE '$Mail' LIMIT 1";
                $result = execute_sql($cnx, $sql_submiter);
                if ($submiter = mysqli_fetch_assoc($result)) {
                    $ID_Submiter = $submiter['ID_Submiter'];
                } else {
                    $sql_sub = "INSERT INTO submiters(Firstname, Middlename, Lastname, Institution, Department, Address, Code, Country, Mail, Phone)";
                    $sql_sub .= " VALUES ('" . mysqli_real_escape_string($cnx, $Firstname) . "','" . mysqli_real_escape_string($cnx, $Middlename) . "','" . mysqli_real_escape_string($cnx, $Lastname) . "','" . mysqli_real_escape_string($cnx, $Institution) . "','" . mysqli_real_escape_string($cnx, $Department) . "','" . mysqli_real_escape_string($cnx, $Address) . "','" . mysqli_real_escape_string($cnx, $Code) . "', '" . mysqli_real_escape_string($cnx, $Country) . "', '" . $Mail . "', '" . mysqli_real_escape_string($cnx, $Phone) . "')";

                    $result = execute_sql($cnx, $sql_sub);
                    $ID_Submiter = mysqli_insert_id($cnx);
                }
                // Insertion des infos concernant l'IS et récupération du ID_ET
                // Table element_transposable
                $sql_group = "SELECT ID_Groups FROM `groups` WHERE `Group_Name` LIKE '$Groups_ID_Groups' LIMIT 1";
                $res = execute_sql($cnx, $sql_group);
                $res_grp = mysqli_fetch_assoc($res);
                $group = ($res_grp) ? "'" . $res_grp['ID_Groups'] . "'" : "NULL";
                $group_sql = $group;

                $sql_family = "SELECT ID_Family FROM `family` WHERE `Family_Name` LIKE '$Family_ID_Family' LIMIT 1";
                $res = execute_sql($cnx, $sql_family);
                $res_fam = mysqli_fetch_assoc($res);
                $family = ($res_fam) ? $res_fam['ID_Family'] : "0";
                $family_sql = ($res_fam) ? "'" . $res_fam['ID_Family'] . "'" : "NULL";

                $sql_iso = "SELECT ID_ET FROM `element_transposable` WHERE `ET_Name` LIKE '$ID_iso' LIMIT 1";
                $res = execute_sql($cnx, $sql_iso);
                $res_iso = mysqli_fetch_assoc($res);
                $iso = ($res_iso) ? "'" . $res_iso['ID_ET'] . "'" : "NULL";
                if ($iso == "NULL" && $ID_iso != "") {
                    $_SESSION["error"] = "Attention séquence versé dans ISfinder mais iso=NULL car non trouvé dans la base";
                }

                // PHP 8.5 Fix: Normalize ENUM and nullable fields for SQL insertion
                // We create SQL-ready strings (either 'value' or NULL)
                $recode_sql = ($recode == "NULL" || $recode == "") ? "NULL" : "'" . mysqli_real_escape_string($cnx, $recode) . "'";
                $frame_sql = ($frame == "NULL" || $frame == "") ? "NULL" : "'" . mysqli_real_escape_string($cnx, $frame) . "'";
                $type_sql = ($type == "NULL" || $type == "") ? "NULL" : "'" . mysqli_real_escape_string($cnx, $type) . "'";
                $SD_sql = ($SD == "NULL" || $SD == "") ? "NULL" : "'" . mysqli_real_escape_string($cnx, $SD) . "'";
                $structure_sql = ($structure == "NULL" || $structure == "") ? "NULL" : "'" . mysqli_real_escape_string($cnx, $structure) . "'";
                $exp_demontred_sql = ($exp_demontred == "NULL" || $exp_demontred == "") ? "NULL" : "'" . mysqli_real_escape_string($cnx, $exp_demontred) . "'";
                $transposition_sql = ($Transposition == "NULL" || $Transposition == "") ? "NULL" : "'" . mysqli_real_escape_string($cnx, $Transposition) . "'";

                $blast_result_sql = ($ET_Blast_Result == "") ? "NULL" : "'" . mysqli_real_escape_string($cnx, $ET_Blast_Result) . "'";
                $comments_sql = ($ET_Comments == "") ? "NULL" : "'" . mysqli_real_escape_string($cnx, $ET_Comments) . "'";
                $private_comments_sql = ($ET_Private_comments == "") ? "NULL" : "'" . mysqli_real_escape_string($cnx, $ET_Private_comments) . "'";
                $reference_sql = ($ET_Reference == "") ? "NULL" : "'" . mysqli_real_escape_string($cnx, $ET_Reference) . "'";
                $recoding_seq_sql = ($recoding_seq == "") ? "NULL" : "'" . mysqli_real_escape_string($cnx, $recoding_seq) . "'";
                $recoding_annot_sql = ($recoding_annot == "") ? "NULL" : "'" . mysqli_real_escape_string($cnx, $recoding_annot) . "'";
                $recoding_image_sql = ($recoding_image == "") ? "NULL" : "'" . mysqli_real_escape_string($cnx, $recoding_image) . "'";

                $length_sql = ($ET_Length == "") ? "NULL" : intval($ET_Length);
                $type_id_sql = ($type_element_transposable_ID_Type_ET == "") ? "NULL" : intval($type_element_transposable_ID_Type_ET);
                $partial_sql = intval($ET_partial); // tinyint(1), default 0

                // PHP 8.5 Fix: Ensure Submission_date is a valid date string or NULL
                $submission_date_sql = (empty($Submission_date) || $Submission_date == "0000-00-00") ? "NULL" : "'" . mysqli_real_escape_string($cnx, $Submission_date) . "'";

                $sql_sub = "INSERT INTO element_transposable(Groups_ID_Groups, Family_ID_Family, type_element_transposable_ID_Type_ET, ET_Accession_number, ET_name, ET_Length, ET_partial, ET_DNA_Sequence, Transposition, ET_Blast_Result, ET_Comments, ET_Private_comments, ET_Reference, ID_iso, recode, frame, type, recoding_seq, recoding_annot, SD,structure, exp_demontred, recoding_image )";
                $sql_sub .= " VALUES ($group_sql, $family_sql, $type_id_sql, '" . $ET_Accession_number . "', '" . $ET_name . "', $length_sql, $partial_sql, '" . $ET_DNA_Sequence . "', $transposition_sql, $blast_result_sql, $comments_sql, $private_comments_sql, $reference_sql, $iso, $recode_sql, $frame_sql, $type_sql, $recoding_seq_sql, $recoding_annot_sql, $SD_sql, $structure_sql, $exp_demontred_sql, $recoding_image_sql)";

                $result = execute_sql($cnx, $sql_sub);
                $ID_ET = mysqli_insert_id($cnx);

                // Table synonyme
                if ($Synonyme) {
                    $Synonymes = explode(",", $Synonyme);
                    foreach ($Synonymes as $syn) {
                        $sql_sub = "INSERT INTO synonyme(Element_transposable_ID_ET, Synonyme) VALUES ($ID_ET, '" . $syn . "')";
                        $result = execute_sql($cnx, $sql_sub);
                    }
                }

                // Table is_ends
                // Variable $Ends_casGeneral = 0 si famille IS200/605 ou IS91 ou ISCR ou IS110 mais si le group n'est pas IS1111
                $Ends_casGeneral = ($family == 6 || $family == 21 || $family == 30 || ($family == 2 && $group != "'2'")) ?  0 : 1;
                if ($Ends_casGeneral == 1) {
                    $sql_sub = "INSERT INTO is_ends(Element_transposable_ID_ET, Left_End, Rigth_End, IR_Length, LE, LE_Structure_II, RE, RE_Structure_II, Ends_comments)";
                    $sql_sub .= " VALUES ('" . $ID_ET . "', '" . $Left_End . "', '" . $Rigth_End . "', '" . $IR_Length . "', 'NULL', '" . $LE_Structure_II . "', 'NULL', '" . $RE_Structure_II . "', '" . mysqli_real_escape_string($cnx, $Ends_comments) . "')";
                } else {
                    $sql_sub = "INSERT INTO is_ends(Element_transposable_ID_ET, Left_End, Rigth_End, IR_Length, LE, LE_Structure_II, RE, RE_Structure_II, Ends_comments)";
                    $sql_sub .= " VALUES ('" . $ID_ET . "', 'NULL', 'NULL', '" . $IR_Length . "', '" . $LE . "', '" . $LE_Structure_II . "', '" . $RE . "', '" . $RE_Structure_II . "', '" . mysqli_real_escape_string($cnx, $Ends_comments) . "')";
                }
                $result = execute_sql($cnx, $sql_sub);

                // Table et_insertion_site
                for ($i = 0; $i < $nb_site; $i++) {
                    // Utilisation des variables dynamiques pour générer le nom des variables en fonctin de $i
                    $var_dyn_Direct_Repeat = 'Direct_Repeat' . $i;
                    $Direct_Repeat = $$var_dyn_Direct_Repeat;
                    $var_dyn_Direct_Repeat_Length = 'Direct_Repeat_Length' . $i;
                    $Direct_Repeat_Length = $$var_dyn_Direct_Repeat_Length;
                    $var_dyn_DR_Left_Flank = 'DR_Left_Flank' . $i;
                    $DR_Left_Flank = $$var_dyn_DR_Left_Flank;
                    $var_dyn_DR_Rigth_Flank = 'DR_Rigth_Flank' . $i;
                    $DR_Rigth_Flank = $$var_dyn_DR_Rigth_Flank;
                    $var_dyn_LE_CS = 'LE_CS' . $i;
                    $LE_CS = $$var_dyn_LE_CS;
                    $var_dyn_RE_CS = 'RE_CS' . $i;
                    $RE_CS = $$var_dyn_RE_CS;
                    $var_dyn_LE_CS_Left_Flank = 'LE_CS_Left_Flank' . $i;
                    $LE_CS_Left_Flank = $$var_dyn_LE_CS_Left_Flank;
                    $var_dyn_RE_CS_Rigth_Flank = 'RE_CS_Rigth_Flank' . $i;
                    $RE_CS_Rigth_Flank = $$var_dyn_RE_CS_Rigth_Flank;

                    $sql_sub = "INSERT INTO et_insertion_site(Element_transposable_ID_ET, Direct_Repeat, Direct_Repeat_Length, DR_Left_Flank, DR_Rigth_Flank, LE_CS, RE_CS, LE_CS_Left_Flank, RE_CS_Rigth_Flank)";
                    $sql_sub .= " VALUES ('" . $ID_ET . "', '$Direct_Repeat', '$Direct_Repeat_Length', '$DR_Left_Flank', '$DR_Rigth_Flank', '$LE_CS', '$RE_CS', '$LE_CS_Left_Flank', '$RE_CS_Rigth_Flank')";
                    $result = execute_sql($cnx, $sql_sub);
                }

                // Table parent_link
                $parents = explode(",", $Element_transposable_parent_ID_ET);
                foreach ($parents as $parent) {
                    if (preg_match('/^[a-zA-Z]/', trim($parent))) {		// On supprime espaces en début et fin de chainee et on ne traite pas les lignes vides ou commençant par un nombre
                        $reqIS = "SELECT ID_ET FROM `element_transposable` WHERE `ET_name` like '" . trim($parent) . "' LIMIT 1";
                        $result = execute_sql($cnx, $reqIS);
                        $res_parent = mysqli_fetch_assoc($result);
                        $element = ($res_parent) ? "'" . $res_parent['ID_ET'] . "'" : "";
                        if ($element == "") {
                            $_SESSION["error"] = "Attention séquence versé dans ISfinder mais parent non trouvé dans la base";
                        } else {
                            $sql_sub = "INSERT INTO parent_link(Element_transposable_ID_ET, Element_transposable_parent_ID_ET)";
                            $sql_sub .= " VALUES ('" . $ID_ET . "', $element)";
                            $result = execute_sql($cnx, $sql_sub);
                        }
                    }
                }

                // Table host et element_transposable_has_host
                $liste_hosts = explode("\n", $Hosts);
                $origin = 1;
                foreach ($liste_hosts as $Host) {
                    if (preg_match('/^[a-zA-Z]/', trim($Host))) {		// ne pas traiter les lignes vides ou commençant par un nombre
                        // On cherche si cet Host existe déjà dans la base ISfinder
                        $reqHost = "SELECT ID_host FROM `host` WHERE `Host` like '" . trim($Host) . "' LIMIT 1";
                        $resultHost = execute_sql($cnx, $reqHost);
                        $res_host = mysqli_fetch_assoc($resultHost);
                        if ($res_host) {
                            $ID_host = $res_host['ID_host'];
                        } else {
                            $sql_sub = "INSERT INTO host(Host) VALUES ('" . mysqli_real_escape_string($cnx, trim($Host)) . "')";
                            $res = execute_sql($cnx, $sql_sub);
                            $ID_host = mysqli_insert_id($cnx);
                        }
                        $sql_sub = "INSERT INTO element_transposable_has_host(Element_transposable_ID_ET, Host_ID_host, Origin) VALUES ('" . $ID_ET . "', '" . $ID_host . "', $origin)";
                        $res = execute_sql($cnx, $sql_sub);
                        $origin = 0;
                    }
                }

                // Table orf
                for ($i = 1; $i <= $nb_orf; $i++) {
                    // Utilisation des variables dynamiques pour générer le nom des variables en fonctin de $i
                    $var_dyn_chem = 'Tnp_chemestry_ID_Tnp_chemestry' . $i;
                    $var_dyn_TnpPart = 'Tnp_description_ID_Tnp_description' . $i;
                    $var_dyn_chemAG = 'AG_description_ID_AG_description' . $i;
                    $var_dyn_chemPG = 'PG_function_ID_PG_function' . $i;
                    $var_dyn_begin = 'ORF_Begin' . $i;
                    $var_dyn_end = 'ORF_End' . $i;
                    $var_dyn_seq = 'ORF_Sequence' . $i;
                    $var_dyn_strand = 'ORF_Strand' . $i;
                    $var_dyn_comment = 'ORF_Comment' . $i;
                    $var_dyn_lengthbp = 'ORF_Length_DNA' . $i;
                    $var_dyn_lengthaa = 'ORF_Length_AA' . $i;
                    $var_dyn_partial = 'ORF_partial' . $i;
                    $var_dyn_blastRE = 'ORF_Blast_Result' . $i;
                    $var_dyn_frameshift = 'ORF_Frameshift' . $i;
                    $var_dyn_frameshiftPos = 'ORF_Frameshift_Position' . $i;
                    $var_dyn_function = 'ORF_function' . $i;
                    $var_dyn_functionDescr = 'Function_Description' . $i;
                    $var_dyn_annotation = 'PG_annotation' . $i;
                    // Ces 3 champs doivent être valide ou bien null car table orf avec contrainte
                    $chem = ($$var_dyn_chem < 1 || $$var_dyn_chem > 6) ? 'NULL' : $$var_dyn_chem;
                    $TnpPart = ($$var_dyn_TnpPart < 1 || $$var_dyn_TnpPart > 2) ? 'NULL' : $$var_dyn_TnpPart;
                    $chemAG = ($$var_dyn_chemAG < 1 || $$var_dyn_chemAG > 7) ? 'NULL' : $$var_dyn_chemAG;
                    $chemPG = ($$var_dyn_chemPG < 1 || $$var_dyn_chemPG > 2) ? 'NULL' : $$var_dyn_chemPG;

                    // Valeurs rajoutées suite au passage à MariaDB 10 qui ne gère pas le NULL (ne remplit pas le champ à NULL qd le champ est vide
                    $begin = ($$var_dyn_begin == "") ? 'NULL' : "'" . $$var_dyn_begin . "'";
                    $end = ($$var_dyn_end == "") ? 'NULL' : "'" . $$var_dyn_end . "'";

                    $strand = ($$var_dyn_strand == "") ? 'NULL' : "'" . $$var_dyn_strand . "'";
                    $lengthbp = ($$var_dyn_lengthbp == "") ? 'NULL' : "'" . $$var_dyn_lengthbp . "'";
                    $lengthaa = ($$var_dyn_lengthaa == "") ? 'NULL' : "'" . $$var_dyn_lengthaa . "'";

                    $frameshift = ($$var_dyn_frameshift == "") ? 'NULL' : "'" . $$var_dyn_frameshift . "'";
                    $frameshiftPos = ($$var_dyn_frameshiftPos == "") ? 'NULL' : "'" . $$var_dyn_frameshiftPos . "'";

                    $seq_sql = ($$var_dyn_seq == "") ? "NULL" : "'" . mysqli_real_escape_string($cnx, $$var_dyn_seq) . "'";
                    $partial_sql = ($$var_dyn_partial == "") ? "'0'" : "'" . mysqli_real_escape_string($cnx, $$var_dyn_partial) . "'";
                    $blast_sql = ($$var_dyn_blastRE == "") ? "NULL" : "'" . mysqli_real_escape_string($cnx, $$var_dyn_blastRE) . "'";
                    // PHP 8.5 Fix: Column is an ENUM('Tnp','AG','PG',''), use 'Tnp' if empty
                    $function_sql = ($$var_dyn_function == "") ? "'Tnp'" : "'" . mysqli_real_escape_string($cnx, $$var_dyn_function) . "'";
                    $func_descr_sql = ($$var_dyn_functionDescr == "") ? "NULL" : "'" . mysqli_real_escape_string($cnx, $$var_dyn_functionDescr) . "'";
                    $annot_sql = ($$var_dyn_annotation == "") ? "NULL" : "'" . mysqli_real_escape_string($cnx, $$var_dyn_annotation) . "'";
                    $comment_sql = ($$var_dyn_comment == "") ? "NULL" : "'" . mysqli_real_escape_string($cnx, $$var_dyn_comment) . "'";

                    $sql_sub = "INSERT INTO orf(Element_transposable_ID_ET, Tnp_chemestry_ID_Tnp_chemestry, Tnp_description_ID_Tnp_description, AG_description_ID_AG_description, PG_function_ID_PG_function, ORF_Begin, ORF_End, ORF_Sequence, ORF_rank, ORF_Strand, ORF_Comment, ORF_Length_DNA, ORF_Length_AA, ORF_partial, ORF_Blast_Result, ORF_Frameshift, ORF_Frameshift_Position, ORF_function, Function_Description, PG_annotation)";
                    $sql_sub .= " VALUES ('" . $ID_ET . "', $chem, $TnpPart, $chemAG, $chemPG, $begin, $end, $seq_sql, '" . $i . "', $strand, $comment_sql, $lengthbp, $lengthaa, $partial_sql, $blast_sql, $frameshift, $frameshiftPos, $function_sql, $func_descr_sql, $annot_sql)";

                    $result = execute_sql($cnx, $sql_sub);
                }	// Fin du FOR

                // Table submission
                $date_val = date("Y-m-d");
                $sql_sub = "INSERT INTO submission(Submiters_ID_Submiter, Element_transposable_ID_ET, Submission_date, Validation_Date) VALUES ('" . $ID_Submiter . "', '" . $ID_ET . "', $submission_date_sql, '" . $date_val . "')";
                $result = execute_sql($cnx, $sql_sub);
            } else {
                $retour = "0";
                $_SESSION['error'] .= "Il y a des erreurs dans les champs<br>";
            }
        } else {
            $retour = "0";
            $_SESSION['error'] = "Ce nom d'IS existe déjà dans la base ISfinder<br>";
        }
        mysqli_close($cnx);
    } else {
        $retour = "0";
        $_SESSION['error'] = "Problème de connexion à la base<br>";
    }
    return ($retour);
}

// Suppression d'un élément de la base ISsubmit
function suppression($ident, $name, $bdd) {
    $retour = "1";
    $cnx = connexion($bdd);
    // La recherche de la fiche MGE tient compte du parametre passé, soit le nom soit ID_ET de l'IS
    $condition = ($ident == '') ? "`ET_name` like '" . $name . "'" : "`ID_ET` like '" . $ident . "'";

    if ($cnx) {
        // Récupération de l'ident du submiter et Suppression du submiter
        // PHP 8.5 Fix: Ensure $ident is not empty to avoid malformed SQL queries
        if (!empty($ident)) {
            $reqIS = "SELECT `Submiters_ID_Submiter` FROM `submission` WHERE `Element_transposable_ID_ET`= $ident LIMIT 1";
            $result = execute_sql($cnx, $reqIS);
            $submiter = mysqli_fetch_row($result);

            // PHP 8.5 Fix: Check if a submiter exists before deletion to prevent "array offset on null" error
            if ($submiter && isset($submiter[0])) {
                $reqIS = "DELETE FROM `submiters` WHERE `ID_Submiter` = $submiter[0] LIMIT 1";
                $result = execute_sql($cnx, $reqIS);
            }

            // Récupération des ident des hosts et Suppression des hosts dans la table host
            $reqHost = "SELECT `Host_ID_host` FROM `element_transposable_has_host` WHERE `Element_transposable_ID_ET`= $ident";
            $result = execute_sql($cnx, $reqHost);
            while ($hote = mysqli_fetch_row($result)) {
                // PHP 8.5 Fix: Ensure host ID is present
                if (isset($hote[0])) {
                    $reqDelHost = "DELETE FROM `host` WHERE `ID_host` = $hote[0]";
                    execute_sql($cnx, $reqDelHost);
                }
            }

            // Suppression de l'élément dans la table element_transposable
            // PHP 8.5 Fix: Final deletion with guard
            $reqDelET = "DELETE FROM `element_transposable` WHERE `ID_ET`= $ident LIMIT 1";
            execute_sql($cnx, $reqDelET);
        }

        mysqli_close($cnx);
    } else {
        $retour = "0";
        $_SESSION['error'] = "Problème de connexion à la base";
    }

    return ($retour);
}

// ─── Suppression safe dans ISfinder ────────────────────────────────────────
// Supprime uniquement les enregistrements liés à l'IS dans ISfinder.
// IMPORTANT : ne supprime PAS le submiter ni les hosts partagés avec d'autres IS.
// Seules les tables enfants propres à cet ID_ET sont nettoyées.
function suppression_isfinder($ident, $bdd) {
    $retour = "1";
    $cnx = connexion($bdd);

    if (!$cnx) {
        return "0";
    }

    if (empty($ident) || !is_numeric($ident)) {
        mysqli_close($cnx);
        return "0";
    }

    // Suppression des tables liées (ne pas toucher à submiters ni host globaux)
    $tables_liees = [
        "DELETE FROM `synonyme` WHERE `Element_transposable_ID_ET` = $ident",
        "DELETE FROM `is_ends` WHERE `Element_transposable_ID_ET` = $ident",
        "DELETE FROM `et_insertion_site` WHERE `Element_transposable_ID_ET` = $ident",
        "DELETE FROM `orf` WHERE `Element_transposable_ID_ET` = $ident",
        "DELETE FROM `parent_link` WHERE `Element_transposable_ID_ET` = $ident",
        "DELETE FROM `element_transposable_has_host` WHERE `Element_transposable_ID_ET` = $ident",
        "DELETE FROM `submission` WHERE `Element_transposable_ID_ET` = $ident",
        // Suppression de l'IS lui-même en dernier
        "DELETE FROM `element_transposable` WHERE `ID_ET` = $ident LIMIT 1",
    ];

    foreach ($tables_liees as $sql) {
        execute_sql($cnx, $sql);
    }

    mysqli_close($cnx);
    return $retour;
}

// ─── Écriture dans ISsubmit (renvoi depuis ISfinder) ───────────────────────
// Écrit les données d'une fiche ISfinder dans ISsubmit avec base_ID_Base = 1 (ISSub).
// Les données doivent avoir été chargées en SESSION via recup_data() au préalable.
function ecrit_data_issub($ident, $name, $base_ecriture) {
    $retour = "1";
    $cnx = connexion($base_ecriture);

    if (!$cnx) {
        $_SESSION['error'] = "Problème de connexion à ISsubmit<br>";
        return "0";
    }

    // Vérifie que le nom n'existe pas déjà dans ISsubmit
    $reqIS = "SELECT ID_ET FROM `element_transposable` WHERE `ET_name` LIKE '" . mysqli_real_escape_string($cnx, $name) . "' LIMIT 1";
    $result = execute_sql($cnx, $reqIS);
    if (mysqli_num_rows($result) > 0) {
        $_SESSION['error'] = "Ce nom d'IS existe déjà dans ISsubmit<br>";
        mysqli_close($cnx);
        return "0";
    }

    // Récupération des variables SESSION peuplées par recup_data()
    foreach ($_SESSION as $elt_session => $var_session) {
        $$elt_session = is_string($var_session) ? strip_tags($var_session) : $var_session;
    }

    // Recherche ou insertion du submiter dans ISsubmit
    $sql_submiter = "SELECT ID_Submiter FROM `submiters` WHERE `Lastname` LIKE '" . mysqli_real_escape_string($cnx, $Lastname) . "' AND `Mail` LIKE '$Mail' LIMIT 1";
    $result = execute_sql($cnx, $sql_submiter);
    if ($submiter = mysqli_fetch_assoc($result)) {
        $ID_Submiter = $submiter['ID_Submiter'];
    } else {
        $sql_sub = "INSERT INTO submiters(Firstname, Middlename, Lastname, Institution, Department, Address, Code, Country, Mail, Phone)";
        $sql_sub .= " VALUES ('" . mysqli_real_escape_string($cnx, $Firstname) . "','" . mysqli_real_escape_string($cnx, $Middlename) . "','" . mysqli_real_escape_string($cnx, $Lastname) . "','" . mysqli_real_escape_string($cnx, $Institution) . "','" . mysqli_real_escape_string($cnx, $Department) . "','" . mysqli_real_escape_string($cnx, $Address) . "','" . mysqli_real_escape_string($cnx, $Code) . "', '" . mysqli_real_escape_string($cnx, $Country) . "', '" . $Mail . "', '" . mysqli_real_escape_string($cnx, $Phone) . "')";
        execute_sql($cnx, $sql_sub);
        $ID_Submiter = mysqli_insert_id($cnx);
    }

    // Normalisation des champs ENUM/nullable pour ISsubmit
    $recode_sql      = ($recode == "NULL" || $recode == "")      ? "NULL" : "'" . mysqli_real_escape_string($cnx, $recode) . "'";
    $frame_sql       = ($frame == "NULL" || $frame == "")        ? "NULL" : "'" . mysqli_real_escape_string($cnx, $frame) . "'";
    $type_sql        = ($type == "NULL" || $type == "")          ? "NULL" : "'" . mysqli_real_escape_string($cnx, $type) . "'";
    $SD_sql          = ($SD == "NULL" || $SD == "")              ? "NULL" : "'" . mysqli_real_escape_string($cnx, $SD) . "'";
    $structure_sql   = ($structure == "NULL" || $structure == "") ? "NULL" : "'" . mysqli_real_escape_string($cnx, $structure) . "'";
    $exp_sql         = ($exp_demontred == "NULL" || $exp_demontred == "") ? "NULL" : "'" . mysqli_real_escape_string($cnx, $exp_demontred) . "'";
    $transposition_sql = ($Transposition == "NULL" || $Transposition == "") ? "NULL" : "'" . mysqli_real_escape_string($cnx, $Transposition) . "'";
    $length_sql      = ($ET_Length == "")   ? "NULL" : intval($ET_Length);
    $type_id_sql     = ($type_element_transposable_ID_Type_ET == "") ? "NULL" : intval($type_element_transposable_ID_Type_ET);
    $partial_sql_et  = intval($ET_partial);
    $submission_date_sql = (empty($Submission_date) || $Submission_date == "0000-00-00") ? "NULL" : "'" . mysqli_real_escape_string($cnx, $Submission_date) . "'";
    $blast_result_sql    = ($ET_Blast_Result == "") ? "NULL" : "'" . mysqli_real_escape_string($cnx, $ET_Blast_Result) . "'";
    $comments_sql        = ($ET_Comments == "") ? "NULL" : "'" . mysqli_real_escape_string($cnx, $ET_Comments) . "'";
    $reference_sql       = ($ET_Reference == "") ? "NULL" : "'" . mysqli_real_escape_string($cnx, $ET_Reference) . "'";

    // Insertion de l'IS dans ISsubmit avec base_ID_Base = 1 (ISSub)
    $sql_insert = "INSERT INTO element_transposable(Groups_ID_Groups, Family_ID_Family, type_element_transposable_ID_Type_ET, base_ID_Base, ET_Accession_number, ET_name, ET_Length, ET_partial, ET_DNA_Sequence, Transposition, ET_Blast_Result, ET_Comments, ET_Reference, recode, frame, type, SD, structure, exp_demontred)";
    $sql_insert .= " VALUES ('" . mysqli_real_escape_string($cnx, $Groups_ID_Groups) . "', '" . mysqli_real_escape_string($cnx, $Family_ID_Family) . "', $type_id_sql, 1, '" . mysqli_real_escape_string($cnx, $ET_Accession_number) . "', '" . mysqli_real_escape_string($cnx, $ET_name) . "', $length_sql, $partial_sql_et, '" . mysqli_real_escape_string($cnx, $ET_DNA_Sequence) . "', $transposition_sql, $blast_result_sql, $comments_sql, $reference_sql, $recode_sql, $frame_sql, $type_sql, $SD_sql, $structure_sql, $exp_sql)";

    execute_sql($cnx, $sql_insert);
    $new_ID_ET = mysqli_insert_id($cnx);

    if (!$new_ID_ET) {
        $_SESSION['error'] = "Problème lors de l'insertion dans ISsubmit<br>";
        mysqli_close($cnx);
        return "0";
    }

    // Table submission
    $date_val = date("Y-m-d");
    $sql_sub = "INSERT INTO submission(Submiters_ID_Submiter, Element_transposable_ID_ET, Submission_date, Validation_Date) VALUES ('$ID_Submiter', '$new_ID_ET', $submission_date_sql, '$date_val')";
    execute_sql($cnx, $sql_sub);

    // Table synonyme
    if (!empty($Synonyme)) {
        $Synonymes = explode(",", $Synonyme);
        foreach ($Synonymes as $syn) {
            $sql_sub = "INSERT INTO synonyme(Element_transposable_ID_ET, Synonyme) VALUES ($new_ID_ET, '" . mysqli_real_escape_string($cnx, trim($syn)) . "')";
            execute_sql($cnx, $sql_sub);
        }
    }

    // Table is_ends
    $sql_sub = "INSERT INTO is_ends(Element_transposable_ID_ET, Left_End, Rigth_End, IR_Length, LE_Structure_II, RE_Structure_II, Ends_comments)";
    $sql_sub .= " VALUES ('$new_ID_ET', '" . mysqli_real_escape_string($cnx, $Left_End) . "', '" . mysqli_real_escape_string($cnx, $Rigth_End) . "', '" . mysqli_real_escape_string($cnx, $IR_Length) . "', '" . mysqli_real_escape_string($cnx, $LE_Structure_II) . "', '" . mysqli_real_escape_string($cnx, $RE_Structure_II) . "', '" . mysqli_real_escape_string($cnx, $Ends_comments) . "')";
    execute_sql($cnx, $sql_sub);

    // Table host
    $liste_hosts = explode("\n", $Hosts);
    $origin = 1;
    foreach ($liste_hosts as $Host) {
        if (preg_match('/^[a-zA-Z]/', trim($Host))) {
            $reqHost = "SELECT ID_host FROM `host` WHERE `Host` LIKE '" . mysqli_real_escape_string($cnx, trim($Host)) . "' LIMIT 1";
            $resultHost = execute_sql($cnx, $reqHost);
            $res_host = mysqli_fetch_assoc($resultHost);
            if ($res_host) {
                $ID_host = $res_host['ID_host'];
            } else {
                execute_sql($cnx, "INSERT INTO host(Host) VALUES ('" . mysqli_real_escape_string($cnx, trim($Host)) . "')");
                $ID_host = mysqli_insert_id($cnx);
            }
            $sql_sub = "INSERT INTO element_transposable_has_host(Element_transposable_ID_ET, Host_ID_host, Origin) VALUES ('$new_ID_ET', '$ID_host', $origin)";
            execute_sql($cnx, $sql_sub);
            $origin = 0;
        }
    }

    mysqli_close($cnx);
    return $retour;
}

function envoyerMail($nomIS, $courriel) {
    $retour = "1";
    $signature = "\r\n\r\nRegards\r\nPatricia Siguier\r\n\r\n------------------\r\nPatricia Siguier\r\n";
    $signature .= "Curator of ISFinder : https://www-is.biotoul.fr/\r\n------------------\r\n";
    $cc = addressMail("Patricia", "Siguier", "univ-tlse3.fr");
    $headers = "From: " . addressMail('webadmin', "", "") . "\r\n";
    $headers .= "CC: " . $cc . "\r\n";
    $headers .= "Content-Type: text/plain; charset=\"utf-8\"\r\n";
    $headers .= "X-Mailer: PHP/ISFinder\r\n";
    $subject = "[ISfinder] " . $nomIS . " has been added to the ISfinder database";
    $texte = "         Summary of your submission on ISFinder\n\n";
    $texte .= "We are pleased to inform you that the insertion sequence " . $nomIS . " that you submitted to ISFinder\n";
    $texte .= "has now been added to the public database.\n";
    $texte .= "Thank you for your help in enriching ISFinder.\n";
    $texte .= $signature;
    mail($courriel, $subject, $texte, $headers);
    return ($retour);
}
?>
