<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('includes/entete.inc.php');
require_once('includes/aside.inc.php');
require_once("includes/function.inc.php");
require_once("includes/affiche.inc.php");

$nb_orf = (isset($_SESSION['nb_orf']) && ($_SESSION['nb_orf'] < 16) && ($_SESSION['nb_orf'] >= 0)) ? intval($_SESSION['nb_orf']) : 0;

if (!empty($_SESSION['error'])) {
    echo "<p class='erreur'>" . $_SESSION['error'] . "</p><hr/>";
}
// $_SESSION['ET_name'] =	($_GET['name']) ? strip_tags($_GET['name']) : $_SESSION['ET_name'];
$_SESSION['bdd'] = (isset($_GET['bdd']) && $_GET['bdd']) ? strip_tags($_GET['bdd']) : (isset($_SESSION['bdd']) ? $_SESSION['bdd'] : "");
$_SESSION['ID_ET'] = (isset($_GET['ident']) && ctype_digit($_GET['ident'])) ? $_GET['ident'] : (isset($_SESSION['ID_ET']) ? $_SESSION['ID_ET'] : "");

// $name = $_SESSION['ET_name'];
$bdd = $_SESSION['bdd'];
$ident = $_SESSION['ID_ET'];

if (intval($_GET['val_session'] ?? 0) != 1) {            // val_session = 1 On garde les valeurs de $_SESSION sinon on lit les données dans la base 

    // La recherche de la fiche MGE tient compte du parametre passé, soit le nom soit ID_ET de l'IS
    //	$condition = ($_GET('ident') == '') ? "`ET_name` like '".$name."'" : "`ID_ET` like '".$ident."'";
    $condition = "`ID_ET` = '" . $ident . "'";

    /* Connexion à la base de données */
    $cnx = connexion($bdd);
    if (!$cnx) {
        // traitement de l'erreur ;
        echo "Problème de connexion à la base de données";
    } else {
        if ($bdd == "isfinder") {
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
					  WHERE $condition LIMIT 1";
        } else {
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
					  WHERE $condition LIMIT 1";
        }

        /* Execution de la requette et si résultat, alors on continue */
        $result = execute_sql($cnx, $reqIS);
        if (mysqli_num_rows($result) != 1) {
            header('Location: https://secure.ibcg.biotoul.fr/isadmin');
            exit();
        } else {
            $is = mysqli_fetch_array($result);

            foreach ($is as $index => $valeur) {
                $_SESSION[$index] = strip_tags($valeur);
            }

            is_submiter($cnx, $_SESSION['ID_ET']);        // récupere le submiter à partir de IDET résulta écrit dans $_SESSION

            $origin = is_origin($cnx, $_SESSION['ID_ET']);
            $origintab = explode(" ", $origin);
            $_SESSION['Origin']    = $origintab[0] . " " . $origintab[1];

            $hosts = is_hosts($cnx, $_SESSION['ID_ET']);
            $i = 0;
            $_SESSION['Hosts'] = $origin;
            while ($host = mysqli_fetch_array($hosts)) {
                if ($host['Host'] != $origin) {
                    $_SESSION['Hosts'] .= "\n";
                    $i++;
                    $_SESSION['Hosts'] = $_SESSION['Hosts'] . $host['Host'];
                    $_SESSION['ID_host'][$i] = $host['ID_host'];
                }
            }

            $_SESSION['ID_iso'] = (isset($_SESSION['ID_iso'])) ?  $_SESSION['ID_iso'] : "";

            $site = unserialize(is_champX($cnx, '*', 'et_insertion_site', 'Element_transposable_ID_ET', $_SESSION['ID_ET'], ''));
            $_SESSION['nb_site'] = (empty($site) || !is_array($site)) ? 0 : count($site);
            // PHP 8.5 Fix: Correct loop boundary and add array guards
            for ($i = 0; $i < $_SESSION['nb_site'] && is_array($site); $i++) {
                if (isset($site[$i]) && is_array($site[$i])) {
                    foreach ($site[$i] as $champ => $valeur) {
                        // PHP 8.5 Fix: Root session keys must be strings
                        $key = "site_" . $i . $champ;
                        $_SESSION[$key] = $valeur;
                    }
                }
            }

            $ORF = unserialize(is_champX($cnx, '*', 'orf', 'Element_transposable_ID_ET', $_SESSION['ID_ET'], 'ORF_rank'));
            $_SESSION['nb_orf'] = (empty($ORF) || !is_array($ORF)) ? 0 : count($ORF);
            $nb_orf = $_SESSION['nb_orf'];
            // PHP 8.5 Fix: Correct loop and add guards
            for ($i = 1; $i <= $_SESSION['nb_orf'] && is_array($ORF); $i++) {
                if (isset($ORF[$i - 1]) && is_array($ORF[$i - 1])) {
                    foreach ($ORF[$i - 1] as $champ => $valeur) {
                        $_SESSION[$champ . $i] = $valeur;
                    }
                }
            }

            if ($bdd == "isfinder") {            // Dans isfinder il peut y avoir plusieurs enregistrement de parents et synonyme pour 1 IS
                // Dans ISfinder l'iso est un integer et non un varchar 
                // la structure de la BDD ISfinder est différente d'ISsubmit aussi pour groupe et famille
                $parent = unserialize(is_champX($cnx, 'Element_transposable_parent_ID_ET', 'parent_link', 'Element_transposable_ID_ET', $_SESSION['ID_ET'], ''));
                $_SESSION['parents'] = "";
                for ($i = 0; $i < count($parent) && $parent; $i++) {
                    $_SESSION['parents'] = $_SESSION['parents'] . $parent[$i]['Element_transposable_parent_ID_ET'] . " ";
                }

                $result_syn = (!empty($is['Synonyme'])) ? is_syn($cnx, $_SESSION['ID_ET']) : null;
                if ($result_syn) {
                    $syn = mysqli_fetch_array($result_syn);
                    $_SESSION['Synonyme'] = $syn['Synonyme'];
                    while ($syn = mysqli_fetch_array($result_syn)) {
                        $_SESSION['Synonyme'] = $_SESSION['Synonyme'] . ", " . $syn['Synonyme'];
                    }
                }

                $iso = (isset($_SESSION['ID_iso'])) ? is_champ($cnx, 'ET_Name', 'element_transposable', 'ID_ET', trim($_SESSION['ID_iso'])) : "NULL";
            }

            mysqli_close($cnx);
        }  // Fin du if resultat (mysqli_num_rows($result) != 1)
    }    // Fin du else il y a connexion
}    // fin du val_session != 1

$base_name = ($bdd == "isfinder") ? "IS" : (isset($_SESSION['Base_Name']) ? $_SESSION['Base_Name'] : "");
$background = base_color($base_name);
$fond_base = 'class="base_' . $base_name . '"';         // couleur de background des <TH> en fonction de la base
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

            <fieldset id="submitter">
                <legend>Submitter information</legend>
                <ul>
                    <li>
                        <label for="nom">First Name :</label>
                        <input type="text" name="Firstname" value="<?php echo isset($_SESSION['Firstname']) ? $_SESSION['Firstname'] : ""; ?>" size="25" maxlength=60>
                    </li>
                    <li>
                        <label for="mname">Middle Name :</label>
                        <input type="text" name="Middlename" value="<?php echo isset($_SESSION['Middlename']) ? $_SESSION['Middlename'] : ""; ?>" size="20" maxlength=60>
                    </li>
                    <li>
                        <label for="lname">Last Name :</label>
                        <input type="text" name="Lastname" value="<?php echo isset($_SESSION['Lastname']) ? $_SESSION['Lastname'] : ""; ?>" size="25" required maxlength=60>
                    </li>
                </ul>
                <ul>
                    <li>
                        <label for="institut">Institution :</label>
                        <input type="text" name="Institution" value="<?php echo isset($_SESSION['Institution']) ? $_SESSION['Institution'] : ""; ?>" size=80 maxlength=100>
                    </li>
                    <li>
                        <label for="depart">Department :</label>
                        <input type="text" name="Department" value="<?php echo isset($_SESSION['Department']) ? $_SESSION['Department'] : ""; ?>" size=80 maxlength=100>
                    </li>
                    <li>
                        <label for="address">Postal address :</label>
                        <input type="text" name="Address" value="<?php echo isset($_SESSION['Address']) ? $_SESSION['Address'] : ""; ?>" size=80 maxlength=100>
                    </li>
                </ul>
                <ul>
                    <li>
                        <label for="postCode">Postal/ZIP code :</label>
                        <input type="text" name="Code" value="<?php echo isset($_SESSION['Code']) ? $_SESSION['Code'] : ""; ?>" size="25" maxlength=60>
                    </li>
                    <li>
                        <label for="country">Country :</label>
                        <input type="text" name="Country" value="<?php echo isset($_SESSION['Country']) ? $_SESSION['Country'] : ""; ?>" size="27" maxlength=60>
                    </li>
                </ul>
                <ul>
                    <li>
                        <label for="courriel">e-mail address :</label>
                        <input type="email" name="Mail" value="<?php echo isset($_SESSION['Mail']) ? $_SESSION['Mail'] : ""; ?>" size="40" maxlength=80>
                    </li>
                    <li>
                        <label for="tel">Telephone :</label>
                        <input type="text" name="Phone" value="<?php echo isset($_SESSION['Phone']) ? $_SESSION['Phone'] : ""; ?>" size="20" maxlength=60>
                    </li>
                </ul>
            </fieldset>

            <fieldset id="infoIS">
                <legend>General Information about MGE</legend>
                <ul>
                    <li>
                        <label for="isname">IS name :</label>
                        <input type="text" name="ET_name" value="<?php echo isset($_SESSION['ET_name']) ? $_SESSION['ET_name'] : ""; ?>" size=15 required maxlength=20>
                    </li>
                    <li>
                        <label for="family">Family :</label>
                        <input type="text" name="Family_ID_Family" value="<?php if ($bdd == "isfinder") { echo isset($_SESSION['Family_Name']) ?  $_SESSION['Family_Name'] : ""; } else { echo isset($_SESSION['Family_ID_Family']) ?  $_SESSION['Family_ID_Family'] : ""; } ?>" size=15 maxlength=20>
                    </li>
                    <li>
                        <label for="group">Group :</label>
                        <input type="text" name="Groups_ID_Groups" value="<?php if ($bdd == "isfinder") { echo isset($_SESSION['Group_Name']) ?  $_SESSION['Group_Name'] : ""; } else { echo isset($_SESSION['Groups_ID_Groups']) ?  $_SESSION['Groups_ID_Groups'] : ""; } ?>" size=15 maxlength=20>
                    </li>
                </ul>
                <ul>
                    <li>
                        <label for="MGEtype">MGE type :</label>
                        <select name="type_element_transposable_ID_Type_ET">
                            <option value="1" selected <?php if (isset($_SESSION['type_element_transposable_ID_Type_ET']) && $_SESSION['type_element_transposable_ID_Type_ET'] == "1") echo 'selected="selected"'; ?>>IS </option>
                            <option value="2" <?php if (isset($_SESSION['type_element_transposable_ID_Type_ET']) && $_SESSION['type_element_transposable_ID_Type_ET'] == "2") echo 'selected="selected"'; ?>>MITE </option>
                            <option value="4" <?php if (isset($_SESSION['type_element_transposable_ID_Type_ET']) && $_SESSION['type_element_transposable_ID_Type_ET'] == "4") echo 'selected="selected"'; ?>>MIC </option>
                            <option value="5" <?php if (isset($_SESSION['type_element_transposable_ID_Type_ET']) && $_SESSION['type_element_transposable_ID_Type_ET'] == "5") echo 'selected="selected"'; ?>>tIS </option>
                            <option value="3" <?php if (isset($_SESSION['type_element_transposable_ID_Type_ET']) && $_SESSION['type_element_transposable_ID_Type_ET'] == "3") echo 'selected="selected"'; ?>>Transposon</option>
                        </select>
                    </li>
                    <li>
                        <label for="related_elt">Related element(s) separated by a comma :</label>
                        <input type="text" name="Element_transposable_parent_ID_ET" value="<?php echo isset($_SESSION['Element_transposable_parent_ID_ET']) ? $_SESSION['Element_transposable_parent_ID_ET'] : ""; ?>" size=50 maxlength=100>
                        <!-- Base IS : for ($i = 0 ; $i < $nbr_parent ; $i++){ echo isset($_SESSION['Element_transposable_parent_ID_ET'][$i][0]) ? $_SESSION['Element_transposable_parent_ID_ET'][$i][0]."  " : "";} -->
                    </li>
                </ul>
                <ul>
                    <li>
                        <label for="isoform">Isoform :</label>
                        <input type="text" name="ID_iso" value="<?php echo ($bdd == "isfinder") ? $iso : $_SESSION['ID_iso']; ?>" size=15 maxlength=20>
                    </li>
                    <li>
                        <label for="synonym">Synonym(s) separated by a comma :</label>
                        <input type="text" name="Synonyme" value="<?php echo isset($_SESSION['Synonyme']) ? $_SESSION['Synonyme'] : ""; ?>" size=50 maxlength=100>
                    </li>
                </ul>

                <table>
                    <tr><th <?php echo $fond_base; ?>>Accession number</th><th <?php echo $fond_base; ?>>Transposition</th><th <?php echo $fond_base; ?>>Origin</th><th <?php echo $fond_base; ?>>Hosts (separated by a return - First=Origin)</th></tr>
                    <tr>
                        <td><input type="text" name="ET_Accession_number" value="<?php echo isset($_SESSION['ET_Accession_number']) ? $_SESSION['ET_Accession_number'] : ""; ?>" size=17 maxlength=25></td>
                        <td><select name="Transposition">
                                <option value="ND" <?php if (isset($_SESSION['Transposition']) && $_SESSION['Transposition'] == "ND") echo 'selected="selected"'; ?>>ND </option>
                                <option value="Y" <?php if (isset($_SESSION['Transposition']) && $_SESSION['Transposition'] == "Y") echo 'selected="selected"'; ?>>Yes </option>
                                <option value="N" <?php if (isset($_SESSION['Transposition']) && $_SESSION['Transposition'] == "N") echo 'selected="selected"'; ?>>No </option>
                            </select> </td>
                        <td><input type="text" name="Origin" value="<?php echo isset($_SESSION['Origin']) ? $_SESSION['Origin'] : ""; ?>" disabled="disabled" size=45 maxlength=100></td>
                        <td><textarea cols=45 rows=2 name="Hosts"><?php echo isset($_SESSION['Hosts']) ? $_SESSION['Hosts'] : ""; ?></textarea></td>
                    </tr>
                </table>

                <section>
                    <div class="enteteSection">
                        <span class='entete_propriete'>DNA section</span>
                    </div>
                    <label for="islength">IS Length :</label>
                    <input type="text" name="ET_Length" value="<?php echo isset($_SESSION['ET_Length']) ? $_SESSION['ET_Length'] : ""; ?>" size=15 maxlength=20>
                    <div class="entete_propriete">Ends</div>
                    <div class="entete_propriete_decal">General case</div>
                    <ul>
                        <li>
                            <label for="irlength">IR Length :</label>
                            <input type="text" name="IR_Length" value="<?php echo isset($_SESSION['IR_Length']) ? $_SESSION['IR_Length'] : ""; ?>" size=15 maxlength=20>
                        </li>
                        <li>
                            <label for="calcul_ends">Calcul ends :</label>
                            <input type="radio" name="calcul_ends" value="Oui" />Oui&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="radio" name="calcul_ends" value="Non" checked="checked" />Non&nbsp;
                        </li>
                    </ul>
                    <ul>
                        <li>
                            <label for="irl" class="label_court">IRL :</label>
                            <input type="text" class="seq" name="Left_End" value="<?php echo isset($_SESSION['Left_End']) ? $_SESSION['Left_End'] : ""; ?>" size=60 maxlength=100>
                        </li>
                        <li>
                            <label for="irr" class="label_court">IRR :</label>
                            <input type="text" class="seq" name="Rigth_End" value="<?php echo isset($_SESSION['Rigth_End']) ? $_SESSION['Rigth_End'] : ""; ?>" size=60 maxlength=100>
                        </li>
                    </ul>
                    <hr />

                    <div class="entete_propriete_decal">Single strand case</div>
                    <ul>
                        <li>
                            <label for="le" class="label_large">Left end / oriIS :</label>
                            <input type="text" class="seq" name="LE" value="<?php echo isset($_SESSION['LE']) ? $_SESSION['LE'] : ""; ?>" size=102 maxlength=150>
                        </li>
                        <li>
                            <label for="LEstII">Struct. II :</label>
                            <select name="LE_Structure_II">
                                <option value="1" <?php if (isset($_SESSION['LE_Structure_II']) && $_SESSION['LE_Structure_II'] == "1") echo 'selected="selected"'; ?>>Yes </option>
                                <option value="0" <?php if (isset($_SESSION['LE_Structure_II']) && $_SESSION['LE_Structure_II'] == "0") echo 'selected="selected"'; ?>>No </option>
                            </select>
                        </li>
                        <li>
                            <label for="re" class="label_large">Right end /terIS :</label>
                            <input type="text" class="seq" name="RE" value="<?php echo isset($_SESSION['RE']) ? $_SESSION['RE'] : ""; ?>" size=102 maxlength=150>
                        </li>
                        <li>
                            <label for="LEstII">Struct. II :</label>
                            <select name="RE_Structure_II">
                                <option value="1" <?php if (isset($_SESSION['RE_Structure_II']) && $_SESSION['RE_Structure_II'] == "1") echo 'selected="selected"'; ?>>Yes </option>
                                <option value="0" <?php if (isset($_SESSION['RE_Structure_II']) && $_SESSION['RE_Structure_II'] == "0") echo 'selected="selected"'; ?>>No </option>
                            </select>
                        </li>
                    </ul>
                    <ul>
                        <li>
                            <label for="ends_comments">Ends comments :</label>
                            <textarea cols=100 name="Ends_comments"><?php echo isset($_SESSION['Ends_comments']) ? $_SESSION['Ends_comments'] : ""; ?></textarea>
                        </li>
                    </ul>

                    <a name="InsertionSite" id="InsertionSite"></a>
                    <div class="entete_propriete">Insertion site</div>
                    <div class="entete_propriete_decal">General case</div>
                    <table>
                        <tr><th <?php echo $fond_base; ?>>Left flank</th><th <?php echo $fond_base; ?>>Direct repeat</th><th <?php echo $fond_base; ?>>Right flank</th><th <?php echo $fond_base; ?>>DR Length</th></tr>
                        <?php           // Boucle pour afficher le nombre de sites voulus
                        for ($j = 0; $j < $_SESSION['nb_site']; $j++) { ?>
                            <tr>
                                <td><input type="text" class="seq" name="<?php echo $j; ?>DR_Left_Flank" value="<?php echo isset($_SESSION[$j . 'DR_Left_Flank']) ? $_SESSION[$j . 'DR_Left_Flank'] : ""; ?>" size=40 maxlength=50></td>
                                <td><input type="text" class="seq" name="<?php echo $j; ?>Direct_Repeat" value="<?php echo isset($_SESSION[$j . 'Direct_Repeat']) ? $_SESSION[$j . 'Direct_Repeat'] : ""; ?>" size=40 maxlength=120></td>
                                <td><input type="text" class="seq" name="<?php echo $j; ?>DR_Rigth_Flank" value="<?php echo isset($_SESSION[$j . 'DR_Rigth_Flank']) ? $_SESSION[$j . 'DR_Rigth_Flank'] : ""; ?>" size=40 maxlength=50></td>
                                <td><input type="text" name="<?php echo $j; ?>Direct_Repeat_Length" value="<?php echo isset($_SESSION[$j . 'Direct_Repeat_Length']) ? $_SESSION[$j . 'Direct_Repeat_Length'] : ""; ?>" size=5 maxlength=10></td>
                            </tr>
                        <?php }                // Fin de la boucle for qui affiche les sites d'insertions : general case
                        ?>
                    </table>

                    <div class="entete_propriete_decal">Single strand case</div>
                    <table>
                        <tr><th <?php echo $fond_base; ?>>Left flank</th><th <?php echo $fond_base; ?>>LE cleavage site</th><th <?php echo $fond_base; ?>>Right flank</th><th <?php echo $fond_base; ?>>RE cleavage site</th></tr>
                        <?php           // Boucle pour afficher le nombre de sites voulus
                        for ($j = 0; $j < $_SESSION['nb_site']; $j++) { ?>
                            <tr>
                                <td><input type="text" class="seq" name="<?php echo $j; ?>LE_CS_Left_Flank" value="<?php echo isset($_SESSION[$j . 'LE_CS_Left_Flank']) ? $_SESSION[$j . 'LE_CS_Left_Flank'] : ""; ?>" size=40 maxlength=50 /></td>
                                <td><input type="text" class="seq" name="<?php echo $j; ?>LE_CS" value="<?php echo isset($_SESSION[$j . 'LE_CS']) ? $_SESSION[$j . 'LE_CS'] : ""; ?>" size=23 maxlength=20 /></td>
                                <td><input type="text" class="seq" name="<?php echo $j; ?>RE_CS_Rigth_Flank" value="<?php echo isset($_SESSION[$j . 'RE_CS_Rigth_Flank']) ? $_SESSION[$j . 'RE_CS_Rigth_Flank'] : ""; ?>" size=40 maxlength=50 /></td>
                                <td><input type="text" class="seq" name="<?php echo $j; ?>RE_CS" value="<?php echo isset($_SESSION[$j . 'RE_CS']) ? $_SESSION[$j . 'RE_CS'] : ""; ?>" size=23 maxlength=20 /></td>
                            </tr>
                        <?php }                // Fin de la boucle for qui affiche les sites d'insertions : single strand case
                        ?>
                        <tr>
                            <td colspan="4"><img src='images/plus.jpg' alt='Insertion site' onclick="document.getElementById('DynModif').value='1' ; document.forms['ficheIS'].submit();" /></td>
                        </tr>
                    </table>

                    <div class="entete_propriete">DNA sequence</div>
                    <div class="seq"><textarea cols=100 rows=3 name="ET_DNA_Sequence"><?php echo isset($_SESSION['ET_DNA_Sequence']) ? $_SESSION['ET_DNA_Sequence'] : ""; ?></textarea> </div>
                    <div class="piedSection"></div>
                </section>

                <a name="Recoding"></a>
                <div class="entete_propriete">Recoding section</div>
                <label for="Recodingby" class="entete_propriete_decal">Recoding by :</label>
                <select name="recode">
                    <option value="NULL" selected <?php if (isset($_SESSION['recode']) && ($_SESSION['recode'] == "NULL" || $_SESSION['recode'] == "")) echo 'selected="selected"'; ?>> </option>
                    <option value="frameshift" <?php if (isset($_SESSION['recode']) && $_SESSION['recode'] == "frameshift") echo 'selected="selected"'; ?>>frameshift </option>
                    <option value="selenocysteine" <?php if (isset($_SESSION['recode']) && $_SESSION['recode'] == "selenocysteine") echo 'selected="selected"'; ?>>selenocysteine </option>
                    <option value="pyrrolysine" <?php if (isset($_SESSION['recode']) && $_SESSION['recode'] == "pyrrolysine") echo 'selected="selected"'; ?>>pyrrolysine </option>
                </select>
                <label for="frame" class="entete_propriete_decal">Frame :</label>
                <select name="frame">
                    <option value="NULL" selected <?php if (isset($_SESSION['frame']) && ($_SESSION['frame'] == "NULL" || $_SESSION['frame'] == "")) echo 'selected="selected"'; ?>> </option>
                    <option value="-1" <?php if (isset($_SESSION['frame']) && $_SESSION['frame'] == "-1") echo 'selected="selected"'; ?>>-1 </option>
                    <option value="+1" <?php if (isset($_SESSION['frame']) && $_SESSION['frame'] == "+1") echo 'selected="selected"'; ?>>+1 </option>
                </select>
                <label for="type" class="entete_propriete_decal">Type :</label>
                <select name="type">
                    <option value="NULL" selected <?php if (isset($_SESSION['type']) && ($_SESSION['type'] == "NULL" || $_SESSION['type'] == "")) echo 'selected="selected"'; ?>> </option>
                    <option value="translational" <?php if (isset($_SESSION['type']) && $_SESSION['type'] == "translational") echo 'selected="selected"'; ?>>translational</option>
                    <option value="transcriptional" <?php if (isset($_SESSION['type']) && $_SESSION['type'] == "transcriptional") echo 'selected="selected"'; ?>>transcriptional</option>
                    <option value="unknow" <?php if (isset($_SESSION['type']) && $_SESSION['type'] == "unknow") echo 'selected="selected"'; ?>>unknow</option>
                </select>
                <label for="exp_demontred" class="entete_propriete_decal">Experimentally demonstrated :</label>
                <select name="exp_demontred">
                    <option value="NULL" selected <?php if (isset($_SESSION['exp_demontred']) && ($_SESSION['exp_demontred'] == "NULL" || $_SESSION['exp_demontred'] == "")) echo 'selected="selected"'; ?>> </option>
                    <option value="Yes" <?php if (isset($_SESSION['exp_demontred']) && $_SESSION['exp_demontred'] == "Yes") echo 'selected="selected"'; ?>>Yes</option>
                    <option value="No" <?php if (isset($_SESSION['exp_demontred']) && $_SESSION['exp_demontred'] == "No") echo 'selected="selected"'; ?>>No</option>
                </select>

                <div class="entete_propriete_decal">Stimulators : </div>
                <label for="Shine" class='entete_propriete_decal'>Shine-Dalgarno sequence : </label>
                <select name="SD">
                    <option value="NULL" selected <?php if (isset($_SESSION['SD']) && ($_SESSION['SD'] == "NULL" || $_SESSION['SD'] == "")) echo 'selected="selected"'; ?>> </option>
                    <option value="Yes" <?php if (isset($_SESSION['SD']) && $_SESSION['SD'] == "Yes") echo 'selected="selected"'; ?>>Yes</option>
                    <option value="No" <?php if (isset($_SESSION['SD']) && $_SESSION['SD'] == "No") echo 'selected="selected"'; ?>>No</option>
                </select>
                <label for="structure" class='entete_propriete_decal'>Secondary structure : </label>
                <select name="structure">
                    <option value="NULL" selected <?php if (isset($_SESSION['structure']) && ($_SESSION['structure'] == "NULL" || $_SESSION['structure'] == "")) echo 'selected="selected"'; ?>> </option>
                    <option value="No-structure" <?php if (isset($_SESSION['structure']) && $_SESSION['structure'] == "No-structure") echo 'selected="selected"'; ?>>No-structure</option>
                    <option value="stem-loop" <?php if (isset($_SESSION['structure']) && $_SESSION['structure'] == "stem-loop") echo 'selected="selected"'; ?>>stem-loop</option>
                    <option value="pseudoknot" <?php if (isset($_SESSION['structure']) && $_SESSION['structure'] == "pseudoknot") echo 'selected="selected"'; ?>>pseudoknot</option>
                </select>

                <div class="entete_propriete_decal">Recoding motif : </div>
                <div class="seq"><textarea cols=100 rows=3 name="recoding_seq"><?php echo isset($_SESSION['recoding_seq']) ?  $_SESSION['recoding_seq'] : ""; ?></textarea> </div>
                <div class="seq"><textarea cols=100 rows=3 name="recoding_annot"><?php echo isset($_SESSION['recoding_annot']) ?  $_SESSION['recoding_annot'] : ""; ?></textarea> </div>

                <input type="hidden" name="MAX_FILE_SIZE" value="200000" />
                <label for="Recoding_image" class='entete_propriete_decal'>Recoding image (file .jpg) :</label>
                <?php echo $_SESSION['recoding_image'] ?>;
                <input type="file" name="recoding_image" size=20 maxlength=30 />
                <input type="button" name="rec_image" value="Upload" onclick="document.getElementById('DynModif').value='3' ; document.forms['ficheIS'].submit();" />
                <?php if (!empty($_SESSION['recoding_image_error'])) {
                    echo "<p class='erreur'>" . $_SESSION['recoding_image_error'];
                } ?>

                <?php
                if ($_SESSION['recoding_image'] != NULL) {
                    $recoding_image = $_SESSION['recoding_image'];
                    $taille = getimagesize('drawings/' . $recoding_image);
                    $largeur = ($taille[0] < 800) ? $taille[0] : 800;
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
                    <label for="orfnumber">ORF number :</label>
                    <!--		<select name = "nb_orf"  onchange = "loadPage(window.location.pathname,this.value,0);" />  
_________________Si changement du nombre d'orf on soumet le formulaire pour récupérer les variables de session _______________
mais sans utiliser le bouton Onsubmit ( Attention à ne pas nommer le bouton de soumission "submit" sinon ce script ne fonctionne plus -->
                    <select name="nb_orf" onchange="document.getElementById('DynModif').value='2' ; document.forms['ficheIS'].submit();" />
                    <script language="javascript">
                        liste_nombre(0, 16, <?php echo $nb_orf; ?>);
                    </script>
                    </select>
                    <?php           // Boucle pour afficher le nombre d'orf sélectionné

                    if ($nb_orf != 0) {
                        for ($i = 1; $i <= $nb_orf; $i++) {
                    ?>
                            <div class="entete_propriete">ORF <?php print $i ?> :</div>
                            <table>
                                <tr>
                                    <th <?php echo $fond_base; ?> colspan="2" scope="col">Length</th>
                                    <th <?php echo $fond_base; ?>>Begin</th>
                                    <th <?php echo $fond_base; ?>>End</th>
                                    <th <?php echo $fond_base; ?>>Strand</th>
                                    <th <?php echo $fond_base; ?>>Fusion ORF</th>
                                </tr>
                                <tr>
                                    <td><input type="text" name="ORF_Length_DNA<?php echo $i; ?>" align="right" value="<?php echo isset($_SESSION['ORF_Length_DNA' . $i]) ? $_SESSION['ORF_Length_DNA' . $i] : ""; ?>" size=20 maxlength=50> bp &nbsp;</td>
                                    <td><input type="text" name="ORF_Length_AA<?php echo $i; ?>" alt="aa" value="<?php echo isset($_SESSION['ORF_Length_AA' . $i]) ? $_SESSION['ORF_Length_AA' . $i] : ""; ?>" size=20 maxlength=50> aa &nbsp;</td>
                                    <td><input type="text" name="ORF_Begin<?php echo $i; ?>" value="<?php echo isset($_SESSION['ORF_Begin' . $i]) ? $_SESSION['ORF_Begin' . $i] : ""; ?>" size=20 maxlength=120></td>
                                    <td><input type="text" name="ORF_End<?php echo $i; ?>" value="<?php echo isset($_SESSION['ORF_End' . $i]) ? $_SESSION['ORF_End' . $i] : ""; ?>" size=20 maxlength=50></td>
                                    <td><input type="text" name="ORF_Strand<?php echo $i; ?>" value="<?php echo isset($_SESSION['ORF_Strand' . $i]) ? $_SESSION['ORF_Strand' . $i] : ""; ?>" size=10 maxlength=10></td>
                                    <td><input type="text" name="ORF_Frameshift<?php echo $i; ?>" value="<?php echo isset($_SESSION['ORF_Frameshift' . $i]) ? $_SESSION['ORF_Frameshift' . $i] : ""; ?>" size=10 maxlength=10></td>
                                </tr>
                            </table>

                            <div class="entete_propriete">ORF function :
                                <span id="function">
                                    <!-- L'affichage de la div suivante dépend (fonction JS Affiche_div) de la fonction ORF sélectionnée ici -->

                                    <select name="ORF_function<?php echo $i; ?>" class="ORF_function" onchange="Affiche_div('functionORF_<?php echo $i; ?>',this.value+'_'+<?php echo $i; ?>)" />

                                    <option value="">«Choice»</option>
                                    <option value="Tnp" <?php if (isset($_SESSION['ORF_function' . $i]) && $_SESSION['ORF_function' . $i] == "Tnp") echo 'selected = "selected"'; ?>>Transposase</option>
                                    <option value="AG" <?php if (isset($_SESSION['ORF_function' . $i]) && $_SESSION['ORF_function' . $i] == "AG") echo 'selected = "selected"'; ?>>Accessory gene</option>
                                    <option value="PG" <?php if (isset($_SESSION['ORF_function' . $i]) && $_SESSION['ORF_function' . $i] == "PG") echo 'selected = "selected"'; ?>>Passenger gene</option>

                                    </select>
                                </span>

                                <!-- Groupe de 3 div : la fonction Affiche_div permet d'en passer une à display: inline
	On teste aussi les variables de session si le form a déjà été soumis pour afficher les select ayant déjà une valeur -->
                                <div class="ORF_function" id="functionORF_<?php echo $i; ?>">
                                    <div id="Tnp_<?php echo $i . '"';
                                                echo (isset($_SESSION['ORF_function' . $i]) && $_SESSION['ORF_function' . $i] == "Tnp") ? 'style= "display:inline"' : 'style= "display:none"' ?>>
                                        <label for="Tnp">&nbsp; Chemistry :</label>
                                        <select name="Tnp_chemestry_ID_Tnp_chemestry<?php echo $i; ?>">
                                            <option value="">«Choice»</option>
                                            <option value="1" <?php if (isset($_SESSION['Tnp_chemestry_ID_Tnp_chemestry' . $i]) && $_SESSION['Tnp_chemestry_ID_Tnp_chemestry' . $i] == "1") echo 'selected = "selected"'; ?>>DDE</option>
                                            <option value="2" <?php if (isset($_SESSION['Tnp_chemestry_ID_Tnp_chemestry' . $i]) && $_SESSION['Tnp_chemestry_ID_Tnp_chemestry' . $i] == "2") echo 'selected = "selected"'; ?>>DEDD</option>
                                            <option value="3" <?php if (isset($_SESSION['Tnp_chemestry_ID_Tnp_chemestry' . $i]) && $_SESSION['Tnp_chemestry_ID_Tnp_chemestry' . $i] == "3") echo 'selected = "selected"'; ?>>Y1</option>
                                            <option value="4" <?php if (isset($_SESSION['Tnp_chemestry_ID_Tnp_chemestry' . $i]) && $_SESSION['Tnp_chemestry_ID_Tnp_chemestry' . $i] == "4") echo 'selected = "selected"'; ?>>Y2</option>
                                            <option value="5" <?php if (isset($_SESSION['Tnp_chemestry_ID_Tnp_chemestry' . $i]) && $_SESSION['Tnp_chemestry_ID_Tnp_chemestry' . $i] == "5") echo 'selected = "selected"'; ?>>Serine</option>
                                            <option value="6" <?php if (isset($_SESSION['Tnp_chemestry_ID_Tnp_chemestry' . $i]) && $_SESSION['Tnp_chemestry_ID_Tnp_chemestry' . $i] == "6") echo 'selected = "selected"'; ?>>Unknow</option>
                                        </select>
                                        <label for="Tnp">&nbsp; Description :</label>
                                        <select name="Tnp_description_ID_Tnp_description<?php echo $i; ?>">
                                            <option value="">«Choice»</option>
                                            <option value="1" <?php if (isset($_SESSION['Tnp_description_ID_Tnp_description' . $i]) && $_SESSION['Tnp_description_ID_Tnp_description' . $i] == "1") echo 'selected = "selected"'; ?>>First part of the transposase</option>
                                            <option value="2" <?php if (isset($_SESSION['Tnp_description_ID_Tnp_description' . $i]) && $_SESSION['Tnp_description_ID_Tnp_description' . $i] == "2") echo 'selected = "selected"'; ?>>Second part of the transposase</option>
                                        </select>
                                    </div>
                                    <div id="AG_<?php echo $i . '"';
                                                echo (isset($_SESSION['ORF_function' . $i]) && $_SESSION['ORF_function' . $i] == "AG") ? 'style= "display:inline"' : 'style= "display:none"' ?>>
                                        <label for="AG">&nbsp; AG :</label>
                                        <select name="AG_description_ID_AG_description<?php echo $i; ?>">
                                            <option value="">«Choice»</option>
                                            <option value="1" <?php if (isset($_SESSION['AG_description_ID_AG_description' . $i]) && $_SESSION['AG_description_ID_AG_description' . $i] == "1") echo 'selected = "selected"'; ?>>IS21 helper</option>
                                            <option value="2" <?php if (isset($_SESSION['AG_description_ID_AG_description' . $i]) && $_SESSION['AG_description_ID_AG_description' . $i] == "2") echo 'selected = "selected"'; ?>>TnpB</option>
                                            <option value="3" <?php if (isset($_SESSION['AG_description_ID_AG_description' . $i]) && $_SESSION['AG_description_ID_AG_description' . $i] == "3") echo 'selected = "selected"'; ?>>IS66 TnpA</option>
                                            <option value="4" <?php if (isset($_SESSION['AG_description_ID_AG_description' . $i]) && $_SESSION['AG_description_ID_AG_description' . $i] == "4") echo 'selected = "selected"'; ?>>IS66 TnpB</option>
                                            <option value="5" <?php if (isset($_SESSION['AG_description_ID_AG_description' . $i]) && $_SESSION['AG_description_ID_AG_description' . $i] == "5") echo 'selected = "selected"'; ?>>IS91 integrase_resolvase</option>
                                            <option value="6" <?php if (isset($_SESSION['AG_description_ID_AG_description' . $i]) && $_SESSION['AG_description_ID_AG_description' . $i] == "6") echo 'selected = "selected"'; ?>>Tn3 resolvase</option>
                                            <option value="7" <?php if (isset($_SESSION['AG_description_ID_AG_description' . $i]) && $_SESSION['AG_description_ID_AG_description' . $i] == "7") echo 'selected = "selected"'; ?>>Other</option>
                                        </select>
                                    </div>
                                    <div id="PG_<?php echo $i . '"';
                                                echo (isset($_SESSION['ORF_function' . $i]) && $_SESSION['ORF_function' . $i] == "PG") ? 'style= "display:inline"' : 'style= "display:none"' ?>>
                                        <label for="PG">&nbsp; Description :</label>
                                        <select name="PG_function_ID_PG_function<?php echo $i; ?>">
                                            <option value="">«Choice»</option>
                                            <option value="1" <?php if (isset($_SESSION['PG_function_ID_PG_function' . $i]) && $_SESSION['PG_function_ID_PG_function' . $i] == "1") echo 'selected = "selected"'; ?>>Antibiotic resistance</option>
                                            <option value="2" <?php if (isset($_SESSION['PG_function_ID_PG_function' . $i]) && $_SESSION['PG_function_ID_PG_function' . $i] == "2") echo 'selected = "selected"'; ?>>Transcriptional Regulator factor</option>
                                        </select>
                                        <p>&nbsp;</p>
                                        <label for="Annotation">Annotation :</label>
                                        <input type="text" name="PG_annotation<?php echo $i; ?>" value="<?php echo isset($_SESSION['PG_annotation' . $i]) ? $_SESSION['PG_annotation' . $i] : ""; ?> " size=100 maxlength=150>
                                    </div>
                                </div>
                            </div>
                            <div class="entete_propriete">ORF sequence</div>
                            <div class="seq"><textarea cols=100 rows=3 name="ORF_Sequence<?php echo $i; ?>"><?php echo isset($_SESSION['ORF_Sequence' . $i]) ? $_SESSION['ORF_Sequence' . $i] : ""; ?></textarea> </div>

                            <div class="entete_propriete">Blast result</div>
                            <div><textarea cols=100 name="ORF_Blast_Result<?php echo $i; ?>"><?php echo isset($_SESSION['ORF_Blast_Result' . $i]) ? $_SESSION['ORF_Blast_Result' . $i] : ""; ?></textarea> </div>

                            <div class="entete_propriete">ORF comments</div>
                            <div><textarea cols=100 name="ORF_Comment<?php echo $i; ?>"><?php echo isset($_SESSION['ORF_Comment' . $i]) ? $_SESSION['ORF_Comment' . $i] : ""; ?></textarea> </div>
                    <?php
                        }                // Fin de la boucle for qui affiche les ORF
                    }                // Fin du if ($nb_orf !=0)
                    ?>


                </section>
                <section>
                    <div class="enteteSection">
                        <span class='entete_propriete'>Comments</span>
                    </div>
                    <textarea cols=100 name="ET_Comments"><?php echo isset($_SESSION['ET_Comments']) ? $_SESSION['ET_Comments'] : ""; ?></textarea>
                </section>
                <section>
                    <div class="enteteSection">
                        <span class='entete_propriete'>References</span>
                    </div>
                    <textarea cols=100 name="ET_Reference"><?php echo isset($_SESSION['ET_Reference']) ? $_SESSION['ET_Reference'] : ""; ?></textarea>
                </section>
                <section>
                    <div class="enteteSection">
                        <span class='entete_propriete'>Private comments</span>
                    </div>
                    <textarea cols=100 name="ET_Private_comments"><?php echo isset($_SESSION['ET_Private_comments']) ? $_SESSION['ET_Private_comments'] : ""; ?></textarea>
                </section>

            </fieldset>
            <div class="piedSection">
                <ul>
                    <li><input type="submit" name="Onsubmit" value="Submit" onclick="return Confirmer(this)"></li>
                    <li><input type="reset" name="reset" value="Reset Defaults" onclick="loadPage(window.location.pathname, 0);"></li>
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
                    <input type="file" name="recoding_image" size=20 maxlength=30 />    
                    <input type="submit" name="rec_image" value="Upload" />
                    <?php if (!empty($_SESSION['recoding_image_error'])) { echo "<p class='erreur'>".$_SESSION['recoding_image_error'] ;} ?>
                    </fieldset>
                </form> 
                                        -->

    </section>
</article>

</div><!-- Fin du div page -->
</body>

</html>