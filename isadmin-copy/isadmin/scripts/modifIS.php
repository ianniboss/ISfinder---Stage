<?php
// ob_start() must be called BEFORE any output (including includes that output HTML)
// so that header() redirects (lines 282, 293, 296, 300) still work after HTML has been emitted.
ob_start();
session_start();
require_once('../includes/entete.inc.php');
require_once('../includes/aside.inc.php');
require_once("../includes/function.inc.php");
require_once("../includes/affiche.inc.php");
?>
<link type="text/css" rel="stylesheet" href="../styles/styles.css" media="screen" />
<article>
    <section>
        <?php
        /* Pour les register globals off - On récupère les champs soumis tout en supprimant les balises HTML */
        foreach ($_POST as $champ => $valeur) { // on remplit $_SESSION ET une variable portant le nom du champ
            $$champ = $_SESSION[$champ] = trim(stripslashes(htmlspecialchars($valeur))); // pour ne pas écrire tt le tps $_SESSION[]
        }

        echo "<br><br>";

        if (isset($_POST['Onsubmit'])) {
            /* initialisation de la variable des erreurs éventuelles si tt les variables du formulaire ne correspondent aux critères */
            $_SESSION["error"] = "";

            /* On teste les champs entrés et s'il y a des erreurs on remplit $_SESSION["error"]  */
            $_SESSION["error"] .= (preg_match("/[^a-zA-Z- \éèêçà']/", $Firstname)) ? "First name correct is required.</br>" : "";
            $_SESSION["error"] .= (empty($Lastname) || preg_match("/[^a-zA-Z- \éèêçà'ñ]/", $Lastname)) ? "Lastname correct is required.</br>" : "";
            $_SESSION["error"] .= (empty($ET_name) || strlen($ET_name) < 3 || preg_match("/[^a-zA-Z0-9-_]/", $ET_name)) ? "IS name is required with min 3 char.</br>" : "";
            $_SESSION["error"] .= (familleValide($Family_ID_Family) != true) ? "$Family_ID_Family n'est pas une famille valide (Attention à respecter la casse).</br>" : "";

            if (filter_var($Mail, FILTER_VALIDATE_EMAIL) === FALSE) {
                $_SESSION["error"] .= "l'adresse e-mail saisie n'est pas valide.</br>";
            }

            /* Pour les séquences DNA et Prot élimination des blancs et retour charriots */
            $car_elim = array("\n", "\r", " ");
            $ET_DNA_Sequence = str_replace($car_elim, "", $ET_DNA_Sequence);
            $_SESSION["error"] .= (estdna($ET_DNA_Sequence) != true) ? "Only A, T, C, G and N characters are allowed.</br>" : "";

            if (isset($nb_orf) && $nb_orf < 16) {
                for ($i = 1; $i <= $nb_orf; $i++) {
                    $var_dynamique = 'orf' . $i . '_seq';
                    // Initialize to empty string if not set — avoids PHP 8 undefined-variable warning
                    // when the ORF sequence field was not present in $_POST (e.g. after an image upload redirect)
                    if (!isset($$var_dynamique)) {
                        $$var_dynamique = '';
                    }
                    $$var_dynamique = str_replace($car_elim, "", $$var_dynamique);
                    $_SESSION["error"] .= (isset($$var_dynamique) && estprot($$var_dynamique) != true) ? "Only amino acid and * are allowed.</br>" : "";
                }
            }

            /* Si tt est ok ($error est vide) on se connecte à la base */
            if ($_SESSION["error"] === "") {
                foreach ($_SESSION as $elt_session => $var_session) { // on remplit une variable portant le nom du champ
                    if ($elt_session != 'base' && $elt_session != 'ID_host') {
                        $$elt_session = strip_tags($var_session); // pour ne pas écrire tt le tps $_SESSION[]
                    }
                } // Fin du Foreach


                /*		foreach ($_SESSION as $elt_session => $var_session){	// on remplit une variable portant le nom du champ
                        $$elt_session = strip_tags($var_session) ;			// pour ne pas écrire tt le tps $_SESSION[]
        //				echo $elt_session." = ".$var_session."<br>" ;		// Pour afficher les variables sur la page web

                }*/

                /* Connexion à la base de données */
                $cnx = connexion($_SESSION['bdd']);
                if ($cnx) {
                    if ($bdd == "ISsubmit") {

                        // Mise à jour des infos du Submiters
                        $sql_sub = "UPDATE `submiters` SET `Firstname` = '" . mysqli_real_escape_string($cnx, $Firstname) . "', `Middlename` = '" . mysqli_real_escape_string($cnx, $Middlename) . "', `Lastname` = '" . mysqli_real_escape_string($cnx, $Lastname) . "', `Institution` = '" . mysqli_real_escape_string($cnx, $Institution) . "', `Department` = '" . mysqli_real_escape_string($cnx, $Department) . "', `Address` = '" . mysqli_real_escape_string($cnx, $Address) . "', `Code` = '" . mysqli_real_escape_string($cnx, $Code) . "', `Country` = '" . mysqli_real_escape_string($cnx, $Country) . "', `Mail` = '" . $Mail . "', `Phone` = '" . mysqli_real_escape_string($cnx, $Phone) . "' ";
                        $sql_sub .= " WHERE `ID_Submiter` = '$ID_Submiter'";
                        $res = execute_sql($cnx, $sql_sub);

                        // Insertion des infos concernant l'IS

                        // Table parent_link
                        if (existe_ID_ET($cnx, $ID_ET, "parent_link")) {
                            $sql_sub = "UPDATE `parent_link` SET `Element_transposable_parent_ID_ET` = '" . intval($Element_transposable_parent_ID_ET) . "'";
                            $sql_sub .= " WHERE `Element_transposable_ID_ET` = '$ID_ET'";
                        } else {
                            $sql_sub = "INSERT INTO `parent_link`(`Element_transposable_ID_ET`, `Element_transposable_parent_ID_ET`)";
                            $sql_sub .= " VALUES ('" . intval($ID_ET) . "', '" . intval($Element_transposable_parent_ID_ET) . "')";
                        }
                        $res = execute_sql($cnx, $sql_sub);

                        // Table host et element_transposable_has_host
                        // On commence par supprimer tous les hosts de cet element
                        $req_select_hosts = "SELECT `Host_ID_host` FROM `element_transposable_has_host` WHERE `Element_transposable_ID_ET` = $ID_ET";
                        $res = execute_sql($cnx, $req_select_hosts);
                        while ($old_host = mysqli_fetch_row($res)) {
                            $req_suppr_host = "DELETE FROM `host` WHERE `ID_host` = $old_host[0]";
                            $result_supprHote = execute_sql($cnx, $req_suppr_host);
                        }
                        //		puis tous les couples  dans la table element_transposable_has_host
                        //					$req_suppr_ET_hasHost = "DELETE FROM `element_transposable_has_host` WHERE `Element_transposable_ID_ET`= $ID_ET";
                        //					$res=execute_sql($req_suppr_ET_hasHost);

                        $liste_hosts = explode("\n", $Hosts);
                        $i = 0;
                        foreach ($liste_hosts as $host) {
                            if (preg_match('/^[a-zA-Z]/', $host)) { // ne pas traiter les lignes vides ou commençant par un nombre
                                $i++;
                                $origine = ($i == 1) ? 1 : 0; // Le 1er host correspond à l'origine
                                $sql_host = "INSERT INTO host(Host) VALUES ('" . mysqli_real_escape_string($cnx, trim($host)) . "')";
                                $res = execute_sql($cnx, $sql_host);
                                $ID_Host = mysqli_insert_id($cnx);
                                $sql_ethh = "INSERT INTO element_transposable_has_host(Element_transposable_ID_ET, Host_ID_host, Origin) VALUES ('" . $ID_ET . "', '" . $ID_Host . "', $origine)";
                                $res = execute_sql($cnx, $sql_ethh);
                            }
                        } // Fin du Foreach


                    } elseif ($bdd == "isfinder") {
                        // Insertion des infos concernant l'IS
                        // Table parent_link
                        /*				if (existe_ID_ET($ID_ET,"parent_link")){
                                            $sql_sub="UPDATE `parent_link` SET `Element_transposable_parent_ID_ET`= '".$Element_transposable_parent_ID_ET."'" ;
                                            $sql_sub.=" WHERE `Element_transposable_ID_ET` = '$ID_ET'";
                                        }else{
                                            $sql_sub="INSERT INTO `parent_link`(`Element_transposable_ID_ET`, `Element_transposable_parent_ID_ET`)" ;
                                            $sql_sub.=" VALUES ('".$ID_ET."','".$Element_transposable_parent_ID_ET."')";
                                        }
                                        $res=execute_sql($sql_sub);
                        */

                        // Table host et element_transposable_has_host
                        // On commence par supprimer tous les couples de cet element dans la table element_transposable_has_host
                        $req_suppr_ET_hasHost = "DELETE FROM `element_transposable_has_host` WHERE `Element_transposable_ID_ET` = $ID_ET";
                        $res = execute_sql($cnx, $req_suppr_ET_hasHost);

                        $liste_hosts = explode("\n", $Hosts);
                        $i = 0;
                        foreach ($liste_hosts as $host) {
                            if (preg_match('/^[a-zA-Z]/', $host)) { // ne pas traiter les lignes vides ou commençant par un nombre
                                $i++;
                                $origine = ($i == 1) ? 1 : 0; // Le 1er host correspond à l'origine
                                $ID_Host = is_champ($cnx, 'ID_host', 'host', 'Host', mysqli_real_escape_string($cnx, trim($host))); // chercher $host

                                if (!$ID_Host) {
                                    $sql_host = "INSERT INTO host(Host) VALUES ('" . mysqli_real_escape_string($cnx, trim($host)) . "')";
                                    $res = execute_sql($cnx, $sql_host);
                                    $ID_Host = mysqli_insert_id($cnx);
                                }
                                $sql_ethh = "INSERT INTO element_transposable_has_host(Element_transposable_ID_ET, Host_ID_host, Origin) VALUES ('" . $ID_ET . "', '" . $ID_Host . "', $origine)";
                                $res = execute_sql($cnx, $sql_ethh);
                            }
                        } // Fin du Foreach


                    } // Fin du if bdd = isfinder

                    if ($bdd == "isfinder" || $bdd == "ISsubmit") {
                        // Table element_transposable
                        // Traitement spécifique pour gérer la différence de type de champ entre ISsubmit (chaine de caractères) et isfinder (entier)
                        // et gestion de la valeur NULL non prise en charge dans MariaDB (version 10)
                        if ($bdd == "isfinder") {
                            $nom_iso = (!empty($ID_iso)) ? is_champ($cnx, 'ID_ET', 'element_transposable', 'ET_name', trim($ID_iso)) : 'NULL';
                            $famille = (!empty($Family_ID_Family)) ? is_champ($cnx, "ID_Family", "family", "Family_Name", $Family_ID_Family) : 'NULL';
                            $groupe = (!empty($Groups_ID_Groups)) ? is_champ($cnx, "ID_Groups", "groups", "Group_Name", $Groups_ID_Groups) : 'NULL';
                        } else {
                            $nom_iso = (empty($ID_iso)) ? 'NULL' : '"' . trim($ID_iso) . '"';
                            $famille = (empty($Family_ID_Family)) ? 'NULL' : $Family_ID_Family;
                            $groupe = (empty($Groups_ID_Groups)) ? 'NULL' : "'" . $Groups_ID_Groups . "'";
                        }
                        $rec = mysqli_real_escape_string($cnx, $recode);
                        $recode = ($rec != "") ? $rec : 'NULL';
                        // The following fields are ENUM columns where 'NULL' is a valid member in ISsubmit
                        // but NOT in the isfinder database. Sending the quoted string 'NULL' to isfinder
                        // causes MariaDB strict mode to throw "Data truncated".
                        // For all these ENUM fields: treat empty string or the string 'NULL' as SQL NULL (unquoted).
                        $frame_val        = (!empty($frame)        && $frame        !== 'NULL') ? "'" . mysqli_real_escape_string($cnx, $frame)        . "'" : 'NULL';
                        $type_val         = (!empty($type)         && $type         !== 'NULL') ? "'" . mysqli_real_escape_string($cnx, $type)         . "'" : 'NULL';
                        $SD_val           = (!empty($SD)           && $SD           !== 'NULL') ? "'" . mysqli_real_escape_string($cnx, $SD)           . "'" : 'NULL';
                        $structure_val    = (!empty($structure)    && $structure    !== 'NULL') ? "'" . mysqli_real_escape_string($cnx, $structure)    . "'" : 'NULL';
                        $exp_dem_val      = (!empty($exp_demontred) && $exp_demontred !== 'NULL') ? "'" . mysqli_real_escape_string($cnx, $exp_demontred) . "'" : 'NULL';

                        $sql_sub = "UPDATE `element_transposable` SET `Groups_ID_Groups` = $groupe, `Family_ID_Family` = '" . mysqli_real_escape_string($cnx, $famille) . "', `type_element_transposable_ID_Type_ET` = '" . intval($type_element_transposable_ID_Type_ET) . "', `ET_Accession_number` = '" . mysqli_real_escape_string($cnx, $ET_Accession_number) . "', `ET_name` = '" . $ET_name . "', `ET_Length` = '" . intval($ET_Length) . "', `ET_DNA_Sequence` = '" . $ET_DNA_Sequence . "', `Transposition` = '" . $Transposition . "', `ET_Comments` = '" . mysqli_real_escape_string($cnx, $ET_Comments) . "', `ET_Private_comments` = '" . mysqli_real_escape_string($cnx, $ET_Private_comments) . "', `ET_Reference` = '" . mysqli_real_escape_string($cnx, $ET_Reference) . "', `ID_iso` = $nom_iso, `recode` = $recode, `frame` = $frame_val, `type` = $type_val, `SD` = $SD_val, `structure` = $structure_val, `exp_demontred` = $exp_dem_val, `recoding_seq` = '" . mysqli_real_escape_string($cnx, $recoding_seq) . "', `recoding_annot` = '" . mysqli_real_escape_string($cnx, $recoding_annot) . "', `recoding_image` = '" . $recoding_image . "'";
                        $sql_sub .= " WHERE `ID_ET` = '$ID_ET'";
                        $res = execute_sql($cnx, $sql_sub);

                        // Table synonyme
                        if (existe_ID_ET($cnx, $ID_ET, "synonyme")) {
                            $sql_sub = "UPDATE `synonyme` SET `Synonyme` = '" . mysqli_real_escape_string($cnx, $Synonyme) . "'";
                            $sql_sub .= " WHERE `Element_transposable_ID_ET` = '$ID_ET'";
                        } else {
                            $sql_sub = "INSERT INTO `synonyme`(`Element_transposable_ID_ET`, `Synonyme`)";
                            $sql_sub .= " VALUES ('" . $ID_ET . "', '" . mysqli_real_escape_string($cnx, $Synonyme) . "')";
                        }
                        $res = execute_sql($cnx, $sql_sub);


                        // Table is_ends		Calcul des extrémités
                        $Left_End = ($calcul_ends == "Oui") ? is_ends($ET_DNA_Sequence, "left") : $Left_End;
                        $Rigth_End = ($calcul_ends == "Oui") ? is_ends($ET_DNA_Sequence, "right") : $Rigth_End;
                        $LE = ($calcul_ends == "Oui") ? is_ends($ET_DNA_Sequence, "LE") : $LE;
                        $RE = ($calcul_ends == "Oui") ? is_ends($ET_DNA_Sequence, "RE") : $RE;
                        $sql_sub = "UPDATE `is_ends` SET `Left_End` = '" . $Left_End . "', `Rigth_End` = '" . $Rigth_End . "', `IR_Length` = '" . $IR_Length . "', `LE` = '" . mysqli_real_escape_string($cnx, $LE) . "', `LE_Structure_II` = '" . $LE_Structure_II . "', `RE` = '" . mysqli_real_escape_string($cnx, $RE) . "', `RE_Structure_II` = '" . $RE_Structure_II . "', `Ends_comments` = '" . mysqli_real_escape_string($cnx, $Ends_comments) . "'";
                        $sql_sub .= " WHERE `Element_transposable_ID_ET` = '$ID_ET'";

                        $res = execute_sql($cnx, $sql_sub);

                        // Table et_insertion_site
                        for ($j = 0; $j < $_SESSION['nb_site']; $j++) {
                            // Use ?? '' to avoid PHP 8 "Undefined array key" warnings
                            // when the session was not pre-populated (e.g. IS has no existing insertion site yet)
                            $id_site           = $_SESSION[$j . 'ID_ET_Insertion_Site'] ?? '';
                            $Direct_Repeat     = $_SESSION[$j . 'Direct_Repeat']        ?? '';
                            // Direct_Repeat_Length is INT UNSIGNED — empty string is rejected by MariaDB strict mode.
                            // intval('') = 0, which matches the column DEFAULT 0.
                            $Direct_Repeat_Length = intval($_SESSION[$j . 'Direct_Repeat_Length'] ?? 0);
                            $DR_Left_Flank     = $_SESSION[$j . 'DR_Left_Flank']        ?? '';
                            $DR_Rigth_Flank    = $_SESSION[$j . 'DR_Rigth_Flank']       ?? '';
                            $LE_CS             = $_SESSION[$j . 'LE_CS']                ?? '';
                            $RE_CS             = $_SESSION[$j . 'RE_CS']                ?? '';
                            $LE_CS_Left_Flank  = $_SESSION[$j . 'LE_CS_Left_Flank']     ?? '';
                            $RE_CS_Rigth_Flank = $_SESSION[$j . 'RE_CS_Rigth_Flank']   ?? '';

                            if ($id_site) {
                                $sql_sub = "UPDATE `et_insertion_site` SET `Element_transposable_ID_ET` = '" . $ID_ET . "', `Direct_Repeat` = '" . $Direct_Repeat . "' , `Direct_Repeat_Length` = '" . $Direct_Repeat_Length . "', `DR_Left_Flank` = '" . $DR_Left_Flank . "', `DR_Rigth_Flank` = '" . $DR_Rigth_Flank . "', `LE_CS` = '" . $LE_CS . "', `RE_CS` = '" . $RE_CS . "', `LE_CS_Left_Flank` = '" . $LE_CS_Left_Flank . "', `RE_CS_Rigth_Flank` = '" . $RE_CS_Rigth_Flank . "'";
                                $sql_sub .= " WHERE `ID_ET_Insertion_Site` = '$id_site'";
                            } else {
                                $sql_sub = "INSERT INTO et_insertion_site(Element_transposable_ID_ET, Direct_Repeat, Direct_Repeat_Length, DR_Left_Flank, DR_Rigth_Flank, LE_CS, RE_CS, LE_CS_Left_Flank, RE_CS_Rigth_Flank)";
                                $sql_sub .= " VALUES ('" . $ID_ET . "', '" . $Direct_Repeat . "', '" . $Direct_Repeat_Length . "', '" . $DR_Left_Flank . "', '" . $DR_Rigth_Flank . "', '" . $LE_CS . "', '" . $RE_CS . "', '" . $LE_CS_Left_Flank . "', '" . $RE_CS_Rigth_Flank . "')";
                            }
                            $res = execute_sql($cnx, $sql_sub);
                        } // fin du for


                        // Table orf
                        for ($i = 1; $i <= $nb_orf; $i++) {
                            $id_orf = $_SESSION['ID_ORF' . $i];
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
                            $var_dyn_blast = 'ORF_Blast_Result' . $i;
                            $var_dyn_frameshift = 'ORF_Frameshift' . $i;
                            $var_dyn_frameshiftPos = 'ORF_Frameshift_Position' . $i;
                            $var_dyn_function = 'ORF_function' . $i;
                            $var_dyn_description = 'Function_Description' . $i;
                            $var_dyn_annotation = 'PG_annotation' . $i;

                            $chem = ($$var_dyn_chem == "") ? 'NULL' : "'" . $$var_dyn_chem . "'";
                            $TnpPart = ($$var_dyn_TnpPart == "") ? 'NULL' : "'" . $$var_dyn_TnpPart . "'";
                            $chemAG = ($$var_dyn_chemAG == "") ? 'NULL'  : "'" . $$var_dyn_chemAG . "'";
                            $chemPG = ($$var_dyn_chemPG == "") ?  'NULL' : "'" . $$var_dyn_chemPG . "'";

                            $begin = ($$var_dyn_begin == "") ? 'NULL' : "'" . $$var_dyn_begin . "'";
                            $end = ($$var_dyn_end == "") ? 'NULL' : "'" . $$var_dyn_end . "'";

                            $strand = ($$var_dyn_strand == "") ? 'NULL' : "'" . $$var_dyn_strand . "'";
                            $lengthbp = ($$var_dyn_lengthbp == "") ? 'NULL' : "'" . $$var_dyn_lengthbp . "'";
                            $lengthaa = ($$var_dyn_lengthaa == "") ? 'NULL' : "'" . $$var_dyn_lengthaa . "'";

                            $frameshift = ($$var_dyn_frameshift == "") ? 'NULL' : "'" . $$var_dyn_frameshift . "'";
                            $frameshiftPos = ($$var_dyn_frameshiftPos == "") ? 'NULL' : "'" . $$var_dyn_frameshiftPos . "'";

                            $problem = ($chem != 'NULL' && $TnpPart != 'NULL') ? "Attention $ET_name, orf $i : chemistry et Description non null tous les 2" : "";

                            if ($id_orf) {
                                $sql_sub = "UPDATE `orf` SET `Element_transposable_ID_ET` = '" . $ID_ET . "', `Tnp_chemestry_ID_Tnp_chemestry` = $chem, `Tnp_description_ID_Tnp_description` = $TnpPart, `AG_description_ID_AG_description` = $chemAG, `PG_function_ID_PG_function` = $chemPG, `ORF_Begin` = $begin, `ORF_End` = $end, `ORF_Sequence` = '" . $$var_dyn_seq . "', `ORF_rank` = '" . $i . "', `ORF_Strand` = $strand, `ORF_Comment` = '" . mysqli_real_escape_string($cnx, $$var_dyn_comment) . "', `ORF_Length_DNA` = $lengthbp, `ORF_Length_AA` = $lengthaa, `ORF_partial` = '" . $$var_dyn_partial . "', `ORF_Blast_Result` = '" . $$var_dyn_blast . "', `ORF_Frameshift` = $frameshift, `ORF_Frameshift_Position` = $frameshiftPos, `ORF_function` = '" . $$var_dyn_function . "', `Function_Description` = '" . $$var_dyn_description . "', `PG_annotation` = '" . mysqli_real_escape_string($cnx, $$var_dyn_annotation) . "'";
                                $sql_sub .= " WHERE `ID_ORF` = '$id_orf'";
                            } else {
                                $sql_sub = "INSERT INTO orf(Element_transposable_ID_ET, Tnp_chemestry_ID_Tnp_chemestry, Tnp_description_ID_Tnp_description, AG_description_ID_AG_description, PG_function_ID_PG_function, ORF_Begin, ORF_End, ORF_Sequence, ORF_rank, ORF_Strand, ORF_Comment, ORF_Length_DNA, ORF_Length_AA, ORF_Frameshift, ORF_function, PG_annotation)";
                                $sql_sub .= " VALUES ('" . $ID_ET . "', $chem, $TnpPart, $chemAG, $chemPG, $begin, $end, '" . $$var_dyn_seq . "', '" . $i . "', $strand, '" . mysqli_real_escape_string($cnx, $$var_dyn_comment) . "', $lengthbp, $lengthaa, $frameshift, '" . $$var_dyn_function . "', '" . mysqli_real_escape_string($cnx, $$var_dyn_annotation) . "')";
                            }

                            $res = execute_sql($cnx, $sql_sub);
                        } // Fin du FOR

                        // Nettoyer table orf si le nb d'orf a diminué
                        $sql_suppr = "DELETE FROM `orf` WHERE `Element_transposable_ID_ET` = $ID_ET AND `ORF_rank` > $nb_orf";
                        $res = execute_sql($cnx, $sql_suppr);
                    } else { // Fin du if bdd = ISfinder ou ISsubmit
                        $_SESSION["error"] .= "Problème : base de données inconnue";
                    }

                    // Fermeture de la connexion et retour à la liste
                    mysqli_close($cnx);
                } else {
                    $_SESSION["error"] .= "Problème de connexion à la base de données";
                }
                //	Tout c'est bien passé : effacement des variables de session et retour à la liste
                $base = $_SESSION['bdd'];
                $_SESSION = array();
                echo "La fiche a été modifiée avec succès. <a href='../liste.php?problem=$problem'> Retour liste...</a>";

                // Si formulaire soumis mais il y a des erreurs	on retourne au formulaire sans l'effacer
            } else {
                header("Location: ../ficheIS.php?ident=$ID_ET&bdd=$base&val_session=1");
            }

            /* Si formulaire pas soumis on remplit les variables de session et on retourne au formulaire sans l'effacer après l'action demandée : +1 site d'insertion, changement du nbr d'ORF, upload de fichier */
        } else {
            $ident = $_SESSION['ID_ET'];
            $base = $_SESSION['bdd'];

            switch ($_POST['DynModif']) {
                case 1:                // Changement du nbr de site d'insertion et retour à la ficheIS
                    $_SESSION['nb_site'] += 1;
                    header("Location: ../ficheIS.php?ident=$ident&bdd=$base&val_session=1" . "#InsertionSite");
                    break;
                case 2:                // Changement du nbr d'ORF et retour à la ficheIS
                    header("Location: ../ficheIS.php?ident=$ident&bdd=$base&val_session=1" . "#Orf");
                    break;
                case 3:                // Chargement du fichier recoding_image et retour à la ficheIS
                    upload();
                    header("Location: ../ficheIS.php?ident=$ident&bdd=$base&val_session=1" . "#Recoding");
                    break;
            }
        }

        ?>
    </section>
</article>

</div> <!-- Fin du div page -->
</body>

</html>
