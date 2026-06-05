<?php
if (isset($_GET['champrecherche'])) {
    $champrecherche = $_GET['champrecherche'];
    $_SESSION['champrecherche'] = $champrecherche;
    $champ = $_GET['champ'];
    $_SESSION['champ'] = $champ;
}
?>

<aside>
    <h2 class="accessibility">Encart </h2>
    <section>
        <h3><a href="/isadmin/liste.php?list=1">Lister</a></h3>
        <hr/>
    </section>
    <section>
        <form id="ISrecherche" action="/isadmin/scripts/recherche.php" method="post">
            <label for="champrecherche">Rechercher</label>
            <input type="text" name="champrecherche" id="champrecherche" value="<?php echo isset($_SESSION['champrecherche']) ? $_SESSION['champrecherche'] : ""; ?>" size="8">
            <input type="submit" value="Ok" name="submitrecherche" />
            <input type="radio" name="champ" value="Element" checked="checked" />Element<br />
            <input type="radio" name="champ" value="Famille" <?php echo (isset($_SESSION['champ']) && ($_SESSION['champ'] == "Famille")) ? "checked='checked'" : ""; ?> />Famille<br />
            <input type="radio" name="champ" value="Groupe" <?php echo (isset($_SESSION['champ']) && ($_SESSION['champ'] == "Groupe")) ? "checked='checked'" : ""; ?> />Groupe<br />
        </form>
        <hr/>
    </section>
    <section>
        <h3>Submiters</h3>
        <form id="submiterRecherche" action="/isadmin/scripts/recherche.php" method="post">
            <input size="8" type="text" name="champSubmiterRecherche" />
            <input type="submit" value="Ok" name="lancerecherche" />
        </form>
        <hr/>
    </section>
    <section>
        <h3><a href="/isadmin/liste_request_names.php">Demande de nom</a></h3>
        <hr/>
    </section>
    <section>
        <h3>Nom attribué</h3>
        <form id="nameAttributedRecherche" action="/isadmin/scripts/recherche.php" method="post">
            <input size="8" type="text" name="champNameAttrRecherche" />
            <input type="submit" value="Ok" name="nameAttibuted" />
        </form>
        <hr/>
    </section>
    <section>
        <h3><a href="/isadmin/blast.php">Blast</a></h3>
        <hr/>
    </section>
    <section>
        <h3><a href="/isadmin/scripts/is_end.php" target="_blank">IS Ends</a></h3>
        <hr/>
    </section>
    <section>
        <h3><a href="/isadmin/export_csv.php">Exportation</a></h3>
        <hr/>
    </section>
    <section>
        <h3><a href="http://astun.ibcg.biotoul.fr/phpmyadmin" target="_blank">PhpMyAdmin</a></h3>
        <hr/>
    </section>
    <section>
        <h3><a href="https://secure.ibcg.biotoul.fr/awstats/awstats.pl?config=www-is.biotoul.fr&configdir=/etc/awstats" target="_blank">Statistiques</a></h3>
        <hr/><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
        <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
    </section>
</aside>