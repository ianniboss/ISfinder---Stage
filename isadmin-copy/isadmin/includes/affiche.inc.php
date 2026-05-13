<?php
// Fonction qui affiche les liens
function sort_link($text, $order, $champrech, $col) {
    global $order_by, $order_dir;

    if (!$order) {
        $order = $text;
    }
    if ($champrech && $col) {
        $link = '<a href="liste.php?tri=' . $order . '&champrecherche=' . $champrech . '&champ=' . $col . '">' . $text . '</a>';
    } else {
        $link = '<a href="liste.php?tri=' . $order . '">' . $text . '</a>';
    }

/*
        if($order_by==$order && $order_dir=='ASC')
             $link .= '&inverse=true';
        $link .= '"';
        if($order_by==$order && $order_dir=='ASC')
            $link .= ' class="order_asc"';
        elseif($order_by==$order && $order_dir=='DESC')
             $link .= ' class="order_desc"';
*/
//      $link .= '">' . $text . '</a>';

    return $link;
}

// Affichage du lien pour la taxonomie
function ncbi_origin_link($origin) {
    $texteori = "";
    $oritab = array();

    $oritab = explode(" ", $origin);
/*      for ($k=2;$k<count($oritab);$k++) {
            $texteori=$texteori." ".$oritab[$k];
        }*/
    $origin_link = "<a href=\"http://www.ncbi.nlm.nih.gov/Taxonomy/Browser/wwwtax.cgi?name=" . $oritab[0] . (isset($oritab[1]) ? "+" . $oritab[1] : "") . "\" target=\"_blank\">" . $oritab[0] . (isset($oritab[1]) ? " " . $oritab[1] : "") . "</a>";
/*      $origin_link = "<a href=\"http://www.ncbi.nlm.nih.gov/Taxonomy/Browser/wwwtax.cgi?name=";
        $origin_link .= $oritab[0]."+".$oritab[1]."\" target=\"_blank\">";
        $origin_link.= $oritab[0]." ".$oritab[1]."</a>";
        */
//   Décommenter pour avoir le nom d'hote complet
//      $origin_link .= $texteori;
    return $origin_link;
}

// Requete pour récupérer Origin d'un IS $IDET
function is_origin($cnx, $IDET) {
    $req = "SELECT `Host` FROM `element_transposable_has_host` ETHH
                    LEFT JOIN `element_transposable` ON `ID_ET` = ETHH.`Element_transposable_ID_ET`
                    LEFT JOIN `host` ON `Host_ID_host` = host.`ID_host`
                    WHERE  `Element_transposable_ID_ET` = $IDET AND `Origin` = 1";
    $result_origin = execute_sql($cnx, $req);

    $originvar = mysqli_result($result_origin);
//  $originvar = (mysqli_num_rows($result_origin)==0) ? "" : mysqli_result($result_origin,0);
    return $originvar;
}

// Requete pour récupérer le nom de l'iso à partir de ID_iso
function is_iso($cnx, $iso) {
    $req = "SELECT `ET_name` FROM `element_transposable` WHERE `ID_ET` like '$iso'";
    $result_iso = execute_sql($cnx, $req);
//  $name_iso= mysqli_result($result_iso,0);
    $name_iso = (mysqli_num_rows($result_iso) == 0) ? "" : mysqli_result($result_iso);
    return $name_iso;
}

// Requete pour récupérer le ou les synonymes à partir de IDET
function is_syn($cnx, $ID_ET) {
    $req_syn = "SELECT `Synonyme` FROM `synonyme` WHERE synonyme.`Element_transposable_ID_ET` like '$ID_ET'";
    $result = execute_sql($cnx, $req_syn);
    return $result;
}

// Requete générique - renvoie une valeur
function is_champ($cnx, $champ_select, $table, $champ_ID, $like) {
    $req_syn = "SELECT `$champ_select` FROM `$table` WHERE `$table`.`$champ_ID` like '$like' LIMIT 1";
    $result = execute_sql($cnx, $req_syn);
    $champ_result = (mysqli_num_rows($result) == 0) ? '' : mysqli_result($result, 0);
    return $champ_result;
}

// Requete générique - Renvoie un tableau
function is_champX($cnx, $champ_select, $table, $champ_ID, $like, $ordre) {
    $champ_selectionne = ($champ_select == "*") ? "*" : "`$champ_select`";
    if ($ordre) {
        $req_syn = "SELECT $champ_selectionne FROM `$table` WHERE $table.`$champ_ID` like '$like' ORDER BY `$ordre`";
    } else {
        $req_syn = "SELECT $champ_selectionne FROM `$table` WHERE $table.`$champ_ID` like '$like'";
    }
    $result = execute_sql($cnx, $req_syn);
    if (($nb_result = mysqli_num_rows($result)) == 0) {
        $champ_result = '';
    } else {
        for ($i = 0; $i < $nb_result; $i++) {
            $champ_result[$i] = mysqli_fetch_array($result, MYSQLI_ASSOC);
        }
    }
    return serialize($champ_result);    // serialize puis unserialize pour passer un tableau d'un script à l'autre
}

// Requete pour récupérer le ou les parents à partir de IDET dans base IS pas pour ISsubmit
function is_parent($cnx, $ID_ET) {
    $reqParent = "SELECT `ET_name` FROM `element_transposable`
                LEFT JOIN `parent_link` ON parent_link.`Element_transposable_ID_ET` =$ID_ET
                WHERE  `ID_ET` = Element_transposable_parent_ID_ET";
    $result = execute_sql($cnx, $reqParent);
    return $result;
}

// Requete pour récupérer le ou les hosts à partir de IDET
function is_hosts($cnx, $ID_ET) {
    $req_hosts = "SELECT `ID_host`,`Host` FROM `element_transposable_has_host` ETHH
                LEFT JOIN `element_transposable` ON `ID_ET` = ETHH.`Element_transposable_ID_ET`
                LEFT JOIN `host` ON `Host_ID_host` = host.`ID_host`
                WHERE  `Element_transposable_ID_ET` = $ID_ET";
    $result = execute_sql($cnx, $req_hosts);
    return $result;
}

// Requete pour récupérer le submiter à partir de IDET
function is_submiter($cnx, $ID_ET) {
    $req_submiter = "SELECT * FROM `submission` SUB
                    LEFT JOIN `element_transposable` ON `ID_ET` = SUB.`Element_transposable_ID_ET`
                    LEFT JOIN `submiters` ON `Submiters_ID_Submiter` = submiters.`ID_Submiter`
                    WHERE  `Element_transposable_ID_ET` = $ID_ET";
    $result = execute_sql($cnx, $req_submiter);
    if ($submiter = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        foreach ($submiter as $index => $valeur) {
            // PHP 8.5 Fix: Session keys must be strings at root level. Skip numeric indices.
            if (!is_numeric($index)) {
                $_SESSION[$index] = $valeur;
            }
        }
    }

    // $champ_result = ($nb_result = mysql_num_rows($result)==0) ? '' : mysqli_fetch_array($result,MYSQL_BOTH) ;
    // return serialize($champ_result);     // serialize puis unserialize pour passer un tableau d'un script à l'autre
}

// Recherche du nombre d'ORF dans un IS
function calcul_nbr_orf($ID_ET) {
    $req_orf = "SELECT `ID_ORF` FROM `orf` WHERE orf.`Element_transposable_ID_ET` like '$ID_ET'";
    $result = execute_sql($cnx, $req_orf);
    $nbr = mysqli_num_rows($result);

    return $nbr;
}

// couleur de background en fonction de la base
function base_color($base_en_cours) {
    switch ($base_en_cours) {
        case "ISSub":
            $background = "#cc6666";
            break;
        case "ISWait":
            $background = "#cc9999";
            break;
        case "ISTrash":
            $background = "#ff6666";
            break;
        case "ISsubmiters":
            $background = "#9ca8ba";
            break;
        default:
            $background = "#6b9bb3";
    }
    return $background;
}

// Affichage du résultat
function affiche_resultSub($cnx, $result, $fond, $bdd) {
    $i = 1;
    while ($table = mysqli_fetch_array($result)) {
        $namevar = $table["ET_name"];
        $IDET = $table["ID_ET"];
        $familyvar = $table["Family_ID_Family"];
        $groupvar = $table["Groups_ID_Groups"];
        $numaccvar = (!empty($table["ET_Accession_number"])) ? $table["ET_Accession_number"] : "";
        $date = $table["Submission_date"];

        print "<tr><td $fond>$i</td><td></td>";
        print "<td><a href=\"scripts/actions.php?IDET=" . $IDET . "&action=suppr&bdd=" . $bdd . "\" title=\"Suppression !!!\"";
        echo " onclick=\"return validsuppr()\"><img src=\"images/suppr.png\" border=\"0\"></td>\n";
        print "<td><a href=\"scripts/actions.php?IDET=" . $IDET . "&action=sub&bdd=" . $bdd . "\" title=\"Transfert dans ISsub!\"";
        echo " onclick=\"return validsub()\"><img src=\"images/sub.gif\" border=\"0\"></td>\n";
        print "<td><a href=\"scripts/actions.php?IDET=" . $IDET . "&action=wait&bdd=" . $bdd . "\" title=\"Mise en attente avant publication!\"";
        echo " onclick=\"return validwait()\"><img src=\"images/wait.gif\" border=\"0\"></td>\n";
        print "<td><a href=\"scripts/actions.php?IDET=" . $IDET . "&action=trash&bdd=" . $bdd . "\" title=\"Mise en attente avant correction\"";
        echo " onclick=\"return validtrash()\"><img src=\"images/trash.gif\" border=\"0\"></td>\n";
        print "<td><a href=\"scripts/actions.php?IDET=" . $IDET . "&action=ok&bdd=" . $bdd . "\" title=\"Validation de la soumission!\"";
        echo " onclick=\"return validIS()\"><img src=\"images/ok.gif\" border=\"0\">&nbsp;</td>\n";

                    // Name de l'IS
        print "<td><a href='ficheIS.php?ident=$IDET&bdd=$bdd'>$namevar</a></td>\n";

                    // Num Accession
        if ($numaccvar != "ND") {
            print "<td><a href='http://www.ncbi.nlm.nih.gov/nuccore/$numaccvar' target='_blank'>$numaccvar</a></td>";
        }

                    // Family et Group
        print "<td>$familyvar</td><td>$groupvar</td>\n";

                    // Origin
        $originvar = is_origin($cnx, $IDET);
        $origin_link = ncbi_origin_link($originvar);
        print "<td>" . $origin_link . "</td>\n";

                    // Source et Date
        is_submiter($cnx, $IDET);
//      $submiter = unserialize(is_submiter($IDET));
        $source = $_SESSION['Lastname'] ?? "Unknown";
        print "<td>$source</td><td>$date</td>\n";

        print "</tr>\n";
        $i++;
    }
}

// Affichage du résultat pour ISfinder
function affiche_resultIS($cnx, $result, $fond, $bdd) {
    $i = 1;
    while ($table = mysqli_fetch_array($result)) {
        $namevar = $table["ET_name"];
        $IDET = $table["ID_ET"];
        $familyvar = $table["Family_Name"];
        $groupvar = $table["Group_Name"];
        $numaccvar = (!empty($table["ET_Accession_number"])) ? $table["ET_Accession_number"] : "";
        $date = $table["Submission_date"];

        print "<tr><td $fond>$i</td><td></td><td></td>";
                                // Name de l'IS
        print "<td><a href='ficheIS.php?ident=$IDET&bdd=$bdd'>$namevar</a></td>\n";

                    // Num Accession
        if ($numaccvar != "ND") {
            print "<td><a href='http://www.ncbi.nlm.nih.gov/nuccore/$numaccvar' target='_blank'>$numaccvar</a></td>";
        }

                    // Family et Group
        print "<td>$familyvar</td><td>$groupvar</td>\n";

                    // Origin
        $originvar = is_origin($cnx, $IDET);
        $origin_link = ncbi_origin_link($originvar);
        print "<td>" . $origin_link . "</td>\n";

                    // Source et Date
        is_submiter($cnx, $IDET);
//      $submiter = unserialize(is_submiter($IDET));
        $source = $_SESSION['Lastname'] ?? "Unknown";
        print "<td>$source</td><td>$date</td>\n";

        print "</tr>\n";
        $i++;
    }
}

// Affichage du résultat pour la table Submiters
function affiche_submiters($result, $fond) {
    $i = 1;
    while ($table = mysqli_fetch_array($result)) {
        $ID_Submiter = $table["ID_Submiter"];
        $lastname = $table["Lastname"];
        $firstname = $table["Firstname"];
        $institution = $table["Institution"];
        $country = $table["Country"];
        $mail = $table["Mail"];

        print "<tr><td $fond>$i</td><td><a href='ficheSubmiter.php?ID_Submiter=$ID_Submiter'>$lastname</a></td>";
        print "<td>$firstname</td><td>$institution</td><td>$country</td><td>$mail</td>";
        print "</tr>\n";
        $i++;
    }
}

// Affichage de la liste des noms demandés
function affiche_attrib_nom($result, $bdd) {
    $i = 1;
    while ($table = mysqli_fetch_array($result)) {
        $ident = $table["ID_Request_names"];
        $bact_origin = $table["bact_origin"];
        $nom = $table["Lastname"];
        $prenom = $table["Firstname"];
        $nbr_names = $table["nbr_names"];
        $Type_ET = $table["Type_ET"];
        $date = $table["date_demande"];

        print "<tr><td class='request'><a href='ficheAttrib.php?ident=$ident'>$i</a></td>\n";
        print "<td><a href=\"scripts/suppr_request_name.php?bdd=$bdd&ID_Request=" . $ident . "\" title=\"Suppression !!!\"";
        echo " onclick=\"return validsuppr()\"><img src=\"images/suppr.png\" border=\"0\"></td>\n";
        print "<td></td>";
                    // Nom et Prénom
        print "<td>$nom</td><td>$prenom</td>\n";

                    // Origine de la bestiole
        print "<td>$bact_origin</td>\n";

                    // Nbr nom demandé
        print "<td>$nbr_names</td>\n";

                    // MGE_type et Date
        print "<td>$Type_ET</td><td>$date</td>\n";

        print "</tr>\n";
        $i++;
    }
}

// Affichage de la liste des noms déjà attribués
function affiche_nom_attribue($result, $bdd) {
    $i = 1;
    while ($table = mysqli_fetch_array($result)) {
        $ident = $table["ID_Current_names"];
        $bact_origin = $table["bact_origin"];
        $ET_name = $table["ET_name"];
        $nom = $table["Lastname"];
        $prenom = $table["Firstname"];
        $date = $table["date_attribution"];

        print "<tr><td class='request'><a href='ficheNomAttrib.php?ident=$ident'>$i</a></td>\n";

        print "<td><a href=\"scripts/suppr_request_name.php?bdd=$bdd&ID_Request=" . $ident . "\" title=\"Suppression !!!\"";
        echo " onclick=\"return validsuppr()\"><img src=\"images/suppr.png\" border=\"0\"></td>\n";

                                // ET_name
        print "<td>$ET_name</td>\n";

                    // Origine de la bestiole
        print "<td>$bact_origin</td>\n";

                    // Nom et Prénom
        print "<td>$nom</td><td>$prenom</td>\n";

                    //  Date
        print "<td>$date</td>\n";

        print "</tr>\n";
        $i++;
    }
}
?>
