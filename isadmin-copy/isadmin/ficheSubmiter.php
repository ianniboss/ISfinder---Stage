<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('includes/entete.inc.php');
require_once('includes/aside.inc.php');
require_once("includes/function.inc.php");
require_once("includes/affiche.inc.php");

if (!empty($_SESSION['error'])) {
    echo "<p class='erreur'>" . $_SESSION['error'] . "</p><hr/>";
}

/* $_SESSION['Lastname'] =	($_GET['Lastname']) ? strip_tags($_GET['Lastname']) : $_SESSION['Lastname'];
$name = $_SESSION['Lastname'] ;
$condition = "`Lastname` like '".$name."'" ;
*/

// ID_Submiter
$_SESSION['ID_Submiter'] = (isset($_GET['ID_Submiter']) && $_GET['ID_Submiter']) ? strip_tags($_GET['ID_Submiter']) : (isset($_SESSION['ID_Submiter']) ? $_SESSION['ID_Submiter'] : "");
$ID_Submiter = $_SESSION['ID_Submiter'];
$condition = "`ID_Submiter` like '" . $ID_Submiter . "'";

$_SESSION['bdd'] = $bdd = "isfinder";

/* Connexion à la base de données */
$cnx = connexion($bdd);
if (!$cnx) {
    echo "Problème de connexion à la base de données";
} else {
    $reqSubmiter = "SELECT * FROM `submiters` WHERE $condition";
    /* Execution de la requette et si résultat, alors on continue */
    $result = execute_sql($cnx, $reqSubmiter);
    if (mysqli_num_rows($result) != 1) {
        header('Location: https://secure.ibcg.biotoul.fr/isadmin');
        exit();
    } else {
        $is = mysqli_fetch_array($result);
        foreach ($is as $index => $valeur) {
            // Correction PHP 8.5 : Ignorer les index numériques pour éviter l'erreur "Skipping numeric key" dans $_SESSION
            if (!is_numeric($index)) {
                $_SESSION[$index] = strip_tags($valeur);
            }
        }

        mysqli_close($cnx);
    }  // Fin du if resultat (mysqli_num_rows($result) != 1)
}    // Fin du else il y a connexion

$background = base_color("ISsubmiters");
$fond_base = 'class="base_ISsubmiters"';  // couleur de background des <TH> 
?>

<!--    <link type="text/css" rel="stylesheet" href="styles/ficheMGE.css" media="screen" />      -->
<link type="text/css" rel="stylesheet" href="styles/fiche.css" media="screen" />
<script type="text/javascript" src="scripts/function.js"></script>

<article style="background-color:<?php echo $background; ?>">
    <!--		<div class="ecran">contenu de mon &eacutecran></div> -->
    <section>

        <form method="post" action="scripts/modifSubmiter.php" name="ficheSubmiter">

            <fieldset id="submitter">
                <legend>Submitter information</legend>
                <ul>
                    <li>
                        <label for="nom">First Name :</label>
                        <input type="text" name="Firstname" required value="<?php echo isset($_SESSION['Firstname']) ? $_SESSION['Firstname'] : ""; ?>" size="25" maxlength=60>
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
                        <input type="text" name="Institution" value="<?php echo isset($_SESSION['Institution']) ? $_SESSION['Institution'] : ""; ?>" size=80 required maxlength=100>
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
                        <input type="text" name="Country" value="<?php echo isset($_SESSION['Country']) ? $_SESSION['Country'] : ""; ?>" size="27" required maxlength=60>
                    </li>
                </ul>
                <ul>
                    <li>
                        <label for="courriel">e-mail address :</label>
                        <input type="email" name="Mail" value="<?php echo isset($_SESSION['Mail']) ? $_SESSION['Mail'] : ""; ?>" size="40" required maxlength=80>
                    </li>
                    <li>
                        <label for="tel">Telephone :</label>
                        <input type="text" name="Phone" value="<?php echo isset($_SESSION['Phone']) ? $_SESSION['Phone'] : ""; ?>" size="20" maxlength=60>
                    </li>
                </ul>
            </fieldset>

            <div class="piedSection">
                <ul>
                    <li><input type="submit" name="Onsubmit" value="Submit"></li>
                    <li><input type="reset" name="reset" value="Reset Defaults" onclick="loadPage(window.location.pathname, 0);"></li>
                </ul>
            </div>
        </form>

    </section>
</article>

</div><!-- Fin du div page de entete.php-->
</body>
</html>