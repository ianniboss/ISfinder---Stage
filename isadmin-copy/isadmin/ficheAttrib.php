<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$error = ($_SESSION['error'] ?? "");
session_destroy();
require_once('includes/entete.inc.php');
require_once('includes/aside.inc.php');
require_once("includes/function.inc.php");
require_once("includes/affiche.inc.php");

if ($error) {
    echo "<p class='erreur'>" . $error . "</p><hr/>";
}

$ident_attrib = intval($_GET['ident'] ?? 0);

/* Connexion à la base de données */
$cnx = connexion("ISsubmit");
if (!$cnx) {
    echo "Problème de connexion à la base de données";
} else {
    $requete = "SELECT * FROM `request_names` 
                LEFT JOIN `submiters` SUB
                ON `submiters_ID_Submiter` = SUB.`ID_Submiter`
                LEFT JOIN `type_element_transposable` TET
                ON `MGE_type` = TET.`ID_Type_ET`			
                WHERE `ID_Request_names` = $ident_attrib LIMIT 1";

    /* Execution de la requette et si résultat, alors on continue */
    $result = execute_sql($cnx, $requete);
    if (mysqli_num_rows($result) != 1) {
        header('Location: https://secure.ibcg.biotoul.fr/isadmin/liste_request_names.php?error=Désolé, pas de résultat');
        exit();
    } else {
        $is = mysqli_fetch_array($result);
        foreach ($is as $index => $valeur) {
            $$index = strip_tags($valeur);
        }
    }  // Fin du if resultat (mysqli_num_rows($result) != 1)

    mysqli_close($cnx);

    /* Teste si bact_origin existe déjà dans table nom_type  */
    $cnx = connexion("isfinder");
    if (!$cnx) {
        echo "Problème de connexion à la base de données";
    } else {
        $nomType = bact_origin_exist($cnx, $bact_origin, $MGE_type, "nomType");
        mysqli_close($cnx);
    }
}    // Fin du else il y a connexion
?>

<link type="text/css" rel="stylesheet" href="styles/fiche.css" media="screen" />
<link type="text/css" rel="stylesheet" href="styles/styles.css" media="screen" />
<script type="text/javascript" src="scripts/function.js"></script>

<article>
    <!--		<div class="ecran">contenu de mon &eacutecran></div> -->
    <section>

        <form action="scripts/attrib_name.php" enctype="application/x-www-form-urlencoded" method="POST" name="attrib_name">
            <input type="hidden" name="MGE_type" value="<?php echo $MGE_type; ?>"/>
            <input type="hidden" name="nbr_names" value="<?php echo $nbr_names; ?>"/>
            <input type="hidden" name="ID_Request_names" value="<?php echo $ident_attrib; ?>"/>
            <input type="hidden" name="date_demande" value="<?php echo $date_demande; ?>"/>

            <fieldset id="submitter">
                <legend>Submitter information</legend>
                <ul>
                    <li>
                        <label for="nom">First Name :</label>
                        <INPUT TYPE="text" NAME="Firstname" VALUE="<?php echo $Firstname; ?>" SIZE="25" MAXLENGTH=60>
                    </li>
                    <li>
                        <label for="mname">Middle Name :</label>
                        <INPUT TYPE="text" NAME="Middlename" VALUE="<?php echo $Middlename; ?>" SIZE="20" MAXLENGTH=60>
                    </li>
                    <li>
                        <label for="lname">Last Name :</label>
                        <INPUT TYPE="text" NAME="Lastname" VALUE="<?php echo $Lastname; ?>" SIZE="25" required MAXLENGTH=60>
                    </li>
                </ul>
                <ul>
                    <li>
                        <label for="institut">Institution :</label>
                        <INPUT TYPE="text" NAME="Institution" VALUE="<?php echo $Institution; ?>" SIZE=80 MAXLENGTH=100>
                    </li>
                    <li>
                        <label for="depart">Department :</label>
                        <INPUT TYPE="text" NAME="Department" VALUE="<?php echo $Department; ?>" SIZE=80 MAXLENGTH=100>
                    </li>
                    <li>
                        <label for="address">Postal address :</label>
                        <INPUT TYPE="text" NAME="Address" VALUE="<?php echo $Address; ?>" SIZE=80 MAXLENGTH=100>
                    </li>
                </ul>
                <ul>
                    <li>
                        <label for="postCode">Postal/ZIP code :</label>
                        <INPUT TYPE="text" NAME="Code" VALUE="<?php echo $Code; ?>" SIZE="25" MAXLENGTH=60>
                    </li>
                    <li>
                        <label for="country">Country :</label>
                        <INPUT TYPE="text" NAME="Country" VALUE="<?php echo $Country; ?>" SIZE="27" MAXLENGTH=60>
                    </li>
                </ul>
                <ul>
                    <li>
                        <label for="courriel">e-mail address :</label>
                        <INPUT TYPE="email" NAME="Mail" required VALUE="<?php echo $Mail; ?>" SIZE="40" MAXLENGTH=80>
                    </li>
                    <li>
                        <label for="tel">Telephone :</label>
                        <INPUT TYPE="text" NAME="Phone" VALUE="<?php echo $Phone; ?>" SIZE="20" MAXLENGTH=60>
                    </li>
                </ul>
            </fieldset>

            <fieldset id="infoIS">
                <legend>Name attribution</legend>
                <?php if ($nomType) { ?>
                    <input type="hidden" name="nomType" value="<?php echo $nomType; ?>"/>
                    <input type="hidden" name="bact_origin" value="<?php echo $bact_origin; ?>"/>

                    <div>
                        <label class="label_gros" for="Qui">Qui :</label>
                        <SELECT NAME="Qui">
                            <OPTION value="a">auto-attribué</option>
                            <OPTION value="r">renommé </option>
                            <OPTION value="x" selected>isfinder </option>
                        </SELECT>
                    </div>
                    <ul>
                        <li>
                            <span class="label_gros"> Origine : </span><?php echo $bact_origin; ?>
                        </li>
                        <li>
                            <span class='label_gros_decal'>Nom Type : </span><?php echo $nomType; ?>
                        </li>
                    </ul>
                    <ul>
                        <li>
                            <span class='label_gros_decal'>MGE_Type : </span><?php echo $Type_ET; ?>
                        </li>
                        <li>
                            <span class='label_gros_decal'>Date de la demande : </span><?php echo $date_demande; ?>
                        </li>
                    </ul>
                    <ul>
                        <li>
                            <span class='label_gros'>Commentaire de l'auteur : </span><?php echo $comments_author ?>
                        </li>
                    </ul>
                    <table>
                        <tr><th>N°</th><th>ET_name</th><th>Organisme</th><th>Commentaire</th></tr>
                        <?php for ($i = 1; $i <= $nbr_names; $i++) { ?>
                            <tr>
                                <td><INPUT TYPE="text" NAME="num" VALUE="<?php echo $i; ?>" SIZE=3 ></td>
                                <td><INPUT TYPE="text" NAME="ET_name<?php echo $i; ?>" required VALUE="<?php echo ""; ?>" SIZE=15 MAXLENGTH=20></td>
                                <td><SELECT NAME="organism<?php echo $i; ?>">
                                        <OPTION value="1" selected>Bacteria</option>
                                        <OPTION value="2">Metagenomic</option>
                                        <OPTION value="3">Virus </option>
                                    </SELECT> </td>
                                <td><INPUT TYPE="text" NAME="comment<?php echo $i; ?>" VALUE="" SIZE=95 MAXLENGTH=128></td>
                            </tr>
                        <?php } ?>
                    </table>
                <?php } else { ?>
                    <div>
                        <label class="label_gros" for="MGEtype">Qui :</label>
                        <SELECT NAME="Qui">
                            <OPTION value="a">auto-attribué</option>
                            <OPTION value="r">renommé </option>
                            <OPTION value="x" selected>isfinder </option>
                        </SELECT>
                    </div>
                    <ul>
                        <li>
                            <label class="label_gros" for="nom">Origine :</label>
                            <INPUT TYPE="text" NAME="bact_origin" VALUE="<?php echo $bact_origin; ?>" SIZE="40" MAXLENGTH=80>
                        </li>
                        <li>
                            <label class="label_gros_decal" for="NomType">Nom Type :</label>
                            <INPUT TYPE="text" NAME="nomType" VALUE="" SIZE="10" MAXLENGTH=20>
                        </li>
                        <li>
                            <label class="label_gros" for="nom">Nv taxo :</label>
                            <INPUT TYPE="text" NAME="new_taxo" VALUE="<?php echo $new_taxo; ?>" SIZE="40" MAXLENGTH=80>
                        </li>
                        <li>
                            <label class="label_gros_decal" for="comment_NomType">Commentaire :</label>
                            <INPUT TYPE="text" NAME="comment" VALUE="" SIZE="40" MAXLENGTH=100>
                        </li>
                    </ul>
                    <ul>
                        <li>
                            <span class='label_gros'>MGE_Type : </span><?php echo $Type_ET; ?>
                        </li>
                        <li>
                            <span class='label_gros_decal'>Date de la demande : </span><?php echo $date_demande; ?>
                        </li>
                    </ul>
                    <ul>
                        <li>
                            <span class='label_gros'>Commentaire de l'auteur : </span><?php echo $comments_author ?>
                        </li>
                    </ul>
                    <table>
                        <tr><th>N°</th><th>ET_name</th><th>Organisme</th><th>Commentaire</th></tr>
                        <?php for ($i = 1; $i <= $nbr_names; $i++) { ?>
                            <tr>
                                <td><INPUT TYPE="text" NAME="num" VALUE="<?php echo $i; ?>" SIZE=3 ></td>
                                <td><INPUT TYPE="text" NAME="ET_name<?php echo $i; ?>" required VALUE="<?php echo ""; ?>" SIZE=15 MAXLENGTH=20></td>
                                <td><SELECT NAME="organism<?php echo $i; ?>">
                                        <OPTION value="bact" selected>Bacteria</option>
                                        <OPTION value="meta">Metagenomic</option>
                                        <OPTION value="virus">Virus </option>
                                    </SELECT> </td>
                                <td><INPUT TYPE="text" NAME="comments<?php echo $i; ?>" VALUE="" SIZE=95 MAXLENGTH=128></td>
                            </tr>
                        <?php } ?>
                    </table>
                <?php } ?>
            </fieldset>

            <div class="piedSection">
                <ul>
                    <li><input type="submit" name="OnsubAttribName" value="Submit" onclick="return Confirmer(this)"></li>
                    <li><INPUT TYPE="reset" name="reset" VALUE="Reset Defaults" onclick="loadPage(window.location.pathname, 0);"></li>
                </ul>
            </div>
        </form>

    </section>
</article>

</div><!-- Fin du div page -->
</body>
</html>