<!DOCTYPE html>
<html>
<head>
    <title>ISfinder</title>
    <meta charset="utf-8" /> 
    <meta name="author" content="Jo" />
    <meta name="keywords" content="IS, Insertion Sequence" />
    <link type="text/css" rel="stylesheet" href="styles/styles.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="styles/nomenclature.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="styles/menu.css" media="screen" />
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <script src="scripts/nomenclature.js" type="text/javascript"></script>
</head>
<body>
<div id="page">
    <header>
    </header>

    <?php 
    $nav_en_cours = 'infos';
    include_once('include/menu.inc.php');
    include_once('include/function.inc.php');
    include_once('include/function_nomenclature.inc.php');

    // 1ere Lettre choisit (a par défaut) et type de bestiole choisit (IS par défaut)
    $lettre_choisi = (empty($_GET['lettre'])) ? 'a' : htmlspecialchars($_GET['lettre']);
    $lettre = (ctype_alpha($lettre_choisi) && strlen($lettre_choisi) == 1) ? $lettre_choisi : 'a';

    $tab_typeGere = array("1", "2", "4"); // type 1 = IS, type 2 = MITE, type 4 = MIC
    $type = (!empty($_GET['type']) && in_array($_GET['type'], $tab_typeGere)) ? intval($_GET['type']) : 1;
    $tab_hostGere = array("1", "2", "3"); // host 1 = Bacteria   host 2 = Metagenomic data  host 3 = Virus
    $host = (!empty($_GET['host']) && in_array($_GET['host'], $tab_hostGere)) ? intval($_GET['host']) : 1;
    $champrecherche = (isset($_GET['champrecherche'])) ? htmlspecialchars($_GET['champrecherche']) : "";
    $champ = (isset($_GET['champ'])) ? htmlspecialchars($_GET['champ']) : "";
    $cocher = ($champrecherche != "") ? 0 : 1; // Permet de savoir s'il faut cocher le type de bestiole (non coché 
                                               // si affichage vient du formulaire NomenclatureRecherche
    ?>

    <article>
        <!-- <div class="ecran">contenu de mon écran</div> -->
        <section>
            <h2>List of names currently attributed</h2>
            <hr/>
            <!-- Choix de la bestiole : on recharge la page avec lettre et hote déjà sélectionnée si changement de bestiole -->
            <div class="new_div">
                <ul class="bouton_ligne">
                    <li class="bouton_ligne">
                        <input value="1" name="bestiole" type="radio" <?php echo check('type', 1, $cocher); ?> onClick="reLoadPage3Param(window.location.pathname, '<?php echo $lettre; ?>', this.value, '<?php echo $host; ?>')" />
                        <label for="is">IS names</label>
                    </li>
                    <li>
                        <input value="2" name="bestiole" type="radio" <?php echo check('type', 2, $cocher); ?> onClick="reLoadPage3Param(window.location.pathname, '<?php echo $lettre; ?>', this.value, '<?php echo $host; ?>')" />
                        <label for="mite">MITE names</label>
                    </li>
                    <li>
                        <input value="4" name="bestiole" type="radio" <?php echo check('type', 4, $cocher); ?> onClick="reLoadPage3Param(window.location.pathname, '<?php echo $lettre; ?>', this.value, '<?php echo $host; ?>')" />
                        <label for="mic">MIC names</label>
                    </li>
                </ul> 
            </div>
            <br/>
            <!-- Choix du type d'hote : on recharge la page avec lettre et bestiole déjà sélectionnée si changement d'hote (Bacteria / Meta data / Virus) -->
            <div class="new_div">
                <ul class="bouton_ligne">
                    <li class="bouton_ligne">
                        <input value="1" name="host" type="radio" <?php echo check('host', 1, $cocher); ?> onClick="reLoadPage3Param(window.location.pathname, '<?php echo $lettre; ?>', '<?php echo $type; ?>', this.value)" />
                        <label for="bacteria">Bacteria</label>
                    </li>
                    <li>
                        <input value="2" name="host" type="radio" <?php echo check('host', 2, $cocher); ?> onClick="reLoadPage3Param(window.location.pathname, '<?php echo $lettre; ?>', '<?php echo $type; ?>', this.value)" />
                        <label for="metagenomic">Metagenomic data</label>
                    </li>
                    <li>
                        <input value="3" name="host" type="radio" <?php echo check('host', 3, $cocher); ?> onClick="reLoadPage3Param(window.location.pathname, '<?php echo $lettre; ?>', '<?php echo $type; ?>', this.value)" />
                        <label for="virus">Virus</label>
                    </li>
                </ul>
            </div>
            <div>            
                <fieldset class="recherche_listNCA">           
                    <form id="NomenclatureRecherche" action="scripts/recherche.php" method="post">
                        <input type="hidden" name="nom_script" value="list_names_attributed.php" /> 
                        <li>
                            <label for="champrecherche">Search</label>
                            <input type="text" name="champrecherche" id="champrecherche" value="" size="12">        
                            <input type="submit" value="Submit" name="submitrecherche" />
                        </li>
                        <li>
                            <input type="radio" name="champ" value="origin" checked="checked" />Origin<br />
                        </li>
                        <li>
                            <input type="radio" name="champ" value="names" />Names<br />
                        </li>
                    </form>      
                </fieldset>
            </div>        
            <fieldset>
                <?php
                // Affichage des lettres A-Z avec lien pour chaque lettre, rechargeant la page avec lettre, type de bestiole sélectionné, et type d'hôte
                // la lettre sélectionnée est en gras sauf si l'affichage vient du formulaire NomenclatureRecherche
                $liens_lettre = "";
                for ($i = 97; $i < 123; $i++) {
                    $liens_lettre .= (chr($i) == $lettre && $champrecherche == "") ? "<a class='lettre_select' " : "<a ";
                    $liens_lettre .= "href=\"list_names_attributed.php?lettre=" . chr($i) . "&type=" . $type . "&host=" . $host . "\">";
                    $liens_lettre .= strtoupper(chr($i));
                    $liens_lettre .= "</a>";
                    if ($i != 122) {
                        $liens_lettre .= "&nbsp;-&nbsp;";
                    }
                }
                echo $liens_lettre;
                ?>
                <p>&nbsp;</p>
                <p>* Names attributed by IS finder ("x "for names attributed by ISFinder and "r" for ISs named or renamed by us)</p>
                <p>(1) For ISs names attributed by IS finder, date of registration, for others, date of GenBank submission or date of publication.</p>
                <?php	
                // Préparation de la requete
                if ($host == 3) {
                    $organism = 'virus';
                } else if ($host == 2) {
                    $organism = 'meta';
                } else { 
                    $organism = 'bact';
                }

                $condition = ($champ == "origin") ? "`bact_origin` LIKE '%$champrecherche%'" : "`ET_name` LIKE '%$champrecherche%'";
                $where = ($champrecherche == "") ? "`bact_origin` like '$lettre%' AND `type_element_transposable_ID_Type_ET` = $type AND `organism` like '$organism'" : $condition;
                $req = "SELECT `bact_origin`, `ET_name`, `Qui`, `date_attribution`, CONCAT(`Firstname`,' ',`Lastname`), CONCAT(`Institution`, IF(`Department`!='', ', ', '  '), `Department`, IF(`Address`!='', ', ', ''), `Address`, IF(`Code`!='', ', ', ' '), `Code`, IF(`Country`!='', ', ', ''), `Country`), `comments`
                        FROM `nom_type`, `current_names`, `name_attribution`, `submiters`
                        WHERE $where
                        AND `submiters_ID_submiter` = `ID_Submiter`
                        AND `current_names_ID_Current_names` = `ID_Current_names`
                        AND `nom_type_ID_nom_type` = `ID_nom_type`
                        ORDER BY `bact_origin`, `ET_name` ASC";

                // Connexion à la base et execution de la requette  
                $cnx = connexion("ISfinder");		
                $result = execute_sql_new($cnx, $req);
                $nombre = mysqli_num_rows($result);

                // Affichage résultat
                if ($nombre > 0) {
                    print $nombre . " results\n";
                    print "<table><tr><th class='large'>Origin</th><th>Names</th><th class='large'>*</th><th class='large'>Date</th><th class='large'>Registrant</th><th>Location at registr.</th><th>Comments</th></tr>\n";
                    $bact_origin_prec = "";
                    for ($i = 0; $i < $nombre; $i++) {			
                        $nomenclature = mysqli_fetch_array($result);
                        // On affiche l'Origin que si c'est le premier IS d'une liste et on affiche un trait sauf si c'est le tout premier résultat
                        $bact_origin = $nomenclature['bact_origin'];
                        $bact_origin_affiche = ($bact_origin_prec != $bact_origin) ? $bact_origin : "";			
                        if ($bact_origin_prec != $bact_origin && $i > 0) { 
                            print "<tr><td colspan='7'><hr></td></tr>"; 
                        }			
                        $bact_origin_prec = $bact_origin;
                        
                        $name = $nomenclature['ET_name'];
                        $qui = $nomenclature['Qui'];
                        $date = $nomenclature['date_attribution'];
                        $registrant = $nomenclature[4];
                        $adress = $nomenclature[5];
                        $comments = $nomenclature['comments'];
                        $exist = exist($name); // Est-ce que ce nom est dans la base ? pour mettre un lien ou pas
                        $nameLienIS = ($exist != 0) ? "<a href='scripts/ficheIS.php?ident=$exist' target='_blank'>$name</a>" : $name;
                        print "<tr><td>$bact_origin_affiche</td><td>$nameLienIS</td><td>$qui</td><td>$date</td><td>$registrant</td><td>$adress</td><td>$comments</td></tr>";
                    }
                    print "</table>";
                } else { 
                    print "<h2><br/>No result</h2>";
                }
                mysqli_close($cnx);
                ?>
            </fieldset>
        </section>
    </article>

    <?php include_once('include/footer.inc.php'); ?>
</div> <!-- Fin du div page -->
</body>
</html>