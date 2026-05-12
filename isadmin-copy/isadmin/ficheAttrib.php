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
                        <input type="text" NAME="Firstname" value="<?php echo $Firstname; ?>" size="25" maxlength=60>
                    </li>
                    <li>
                        <label for="mname">Middle Name :</label>
                        <input type="text" NAME="Middlename" value="<?php echo $Middlename; ?>" size="20" maxlength=60>
                    </li>
                    <li>
                        <label for="lname">Last Name :</label>
                        <input type="text" NAME="Lastname" value="<?php echo $Lastname; ?>" size="25" required maxlength=60>
                    </li>
                </ul>
                <ul>
                    <li>
                        <label for="institut">Institution :</label>
                        <input type="text" NAME="Institution" value="<?php echo $Institution; ?>" size=80 maxlength=100>
                    </li>
                    <li>
                        <label for="depart">Department :</label>
                        <input type="text" NAME="Department" value="<?php echo $Department; ?>" size=80 maxlength=100>
                    </li>
                    <li>
                        <label for="address">Postal address :</label>
                        <input type="text" NAME="Address" value="<?php echo $Address; ?>" size=80 maxlength=100>
                    </li>
                </ul>
                <ul>
                    <li>
                        <label for="postCode">Postal/ZIP code :</label>
                        <input type="text" NAME="Code" value="<?php echo $Code; ?>" size="25" maxlength=60>
                    </li>
                    <li>
                        <label for="country">Country :</label>
                        <input type="text" NAME="Country" value="<?php echo $Country; ?>" size="27" maxlength=60>
                    </li>
                </ul>
                <ul>
                    <li>
                        <label for="courriel">e-mail address :</label>
                        <input type="email" NAME="Mail" required value="<?php echo $Mail; ?>" size="40" maxlength=80>
                    </li>
                    <li>
                        <label for="tel">Telephone :</label>
                        <input type="text" NAME="Phone" value="<?php echo $Phone; ?>" size="20" maxlength=60>
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
                        <select name="Qui">
                            <option value="a">auto-attribué</option>
                            <option value="r">renommé </option>
                            <option value="x" selected>isfinder </option>
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
                                <td><input type="text" NAME="num" value="<?php echo $i; ?>" size=3 ></td>
                                <td><input type="text" NAME="ET_name<?php echo $i; ?>" required value="<?php echo ""; ?>" size=15 maxlength=20></td>
                                <td><select name="organism<?php echo $i; ?>">
                                        <option value="1" selected>Bacteria</option>
                                        <option value="2">Metagenomic</option>
                                        <option value="3">Virus </option>
                                    </SELECT> </td>
                                <td><input type="text" NAME="comment<?php echo $i; ?>" value="" size=95 maxlength=128></td>
                            </tr>
                        <?php } ?>
                    </table>
                <?php } else { ?>
                    <div>
                        <label class="label_gros" for="MGEtype">Qui :</label>
                        <select name="Qui">
                            <option value="a">auto-attribué</option>
                            <option value="r">renommé </option>
                            <option value="x" selected>isfinder </option>
                        </SELECT>
                    </div>
                    <ul>
                        <li>
                            <label class="label_gros" for="nom">Origine :</label>
                            <input type="text" NAME="bact_origin" value="<?php echo $bact_origin; ?>" size="40" maxlength=80>
                        </li>
                        <li>
                            <label class="label_gros_decal" for="NomType">Nom Type :</label>
                            <input type="text" NAME="nomType" value="" size="10" maxlength=20>
                        </li>
                        <li>
                            <label class="label_gros" for="nom">Nv taxo :</label>
                            <input type="text" NAME="new_taxo" value="<?php echo $new_taxo; ?>" size="40" maxlength=80>
                        </li>
                        <li>
                            <label class="label_gros_decal" for="comment_NomType">Commentaire :</label>
                            <input type="text" NAME="comment" value="" size="40" maxlength=100>
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
                                <td><input type="text" NAME="num" value="<?php echo $i; ?>" size=3 ></td>
                                <td><input type="text" NAME="ET_name<?php echo $i; ?>" required value="<?php echo ""; ?>" size=15 maxlength=20></td>
                                <td><select name="organism<?php echo $i; ?>">
                                        <option value="bact" selected>Bacteria</option>
                                        <option value="meta">Metagenomic</option>
                                        <option value="virus">Virus </option>
                                    </SELECT> </td>
                                <td><input type="text" NAME="comments<?php echo $i; ?>" value="" size=95 maxlength=128></td>
                            </tr>
                        <?php } ?>
                    </table>
                <?php } ?>
            </fieldset>

            <div class="piedSection">
                <ul>
                    <li><input type="submit" name="OnsubAttribName" value="Submit" onclick="return Confirmer(this)"></li>
                    <li><input type="reset" name="reset" value="Reset Defaults" onclick="loadPage(window.location.pathname, 0);"></li>
                </ul>
            </div>
        </form>

    </section>
</article>

</div><!-- Fin du div page -->
</body>
</html>