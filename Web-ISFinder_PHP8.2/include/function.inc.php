<?php
// Verification of the Referer for variables passed in POST
if (!empty($_SERVER['HTTP_REFERER'])
    && substr($_SERVER['HTTP_REFERER'], 8, strlen($_SERVER['SERVER_NAME'])) != $_SERVER['SERVER_NAME']) {
    $_POST = [];
}

// Email addresses
function addressMail($prenomDest, $nomDest, $domaineDest) {
    $domaine = (!empty($domaineDest)) ? $domaineDest : "utoulouse.fr";
    $nom = (!empty($prenomDest)) ? $prenomDest . "." . $nomDest : $nomDest;
    $adresse = $nom . "@" . $domaine;
    return $adresse;
}

// Database Connection
// Supports both shorthand (1 arg: DB name) and full (4 args: host, user, db, password) forms.
// The shorthand form uses the server's known credentials.
function connexion($host, $user = null, $bdd = null, $mdp = null) {
    // Shorthand: connexion("ISfinder") or connexion("ISsubmit")
    if ($user === null) {
        $dbname = $host;
        $host   = 'localhost';
        $user   = 'isfinder';
        $mdp    = 'mCjMPEJ_16';
        $bdd    = $dbname;
    }
    $connecte = mysqli_connect($host, $user, $mdp, $bdd) or die("Server connection error");
    mysqli_select_db($connecte, $bdd) or die("connection error to the database" . $bdd);
    mysqli_query($connecte, "SET NAMES 'utf8'");
    return $connecte;
}

// SQL Error Handling
function erreur_sql($connect, $res, $requete) {
    if ($res === false) {
        echo "Sorry, request invalidates.<br> Please contact the administrator of the site<br>";
        echo "<a href='javascript:history.go(-1)'>Back</a><br>";
        exit;
    }
}

// Search form character length error
function erreur_car($chaine, $erreur) {
    if ($erreur == 0) {
        echo "<h3>Query failed, a minimum of 3 characters is required for \" $chaine \".</h3>";
    } else {
        echo "<h3>Query failed.</h3>";
    }
    echo "<a href='javascript:history.go(-1)'>Back</a><br>";
}

// Search form essential field error
function erreur_ess_field() {
    echo "<h3>Query failed, at least 1 essential field is required.</h3>";
    echo "<a href='javascript:history.go(-1)'>Back</a><br>";
}

// Submission form empty field error
function erreur_sub($chaine, $mess) {
    if ($mess == 0) {
        echo "<h3>Submission failed, \" $chaine \"  field is not valid.</h3>";
    } else {
        echo "<h3>Submission failed, \" $chaine \"  field is required.</h3>";
    }
    echo "<a href='javascript:history.go(-1)'>Back</a><br>";
}

// Submission form invalid field value error
function erreur_val($chaine, $cause) {
    echo "<h3>Submission failed, \" $chaine \"  field is not valid.<br>$cause</h3>";
    echo "<a href='javascript:history.go(-1)'>Back</a><br>";
}

// Submission form invalid IS name error
function erreur_nom_is() {
    echo "<h3>Submission failed,this is name is not valid</h3>";
    echo "<a href='/is/is_name_attrib.html'>Click here to go to the IS Name Attribution form.</a><br>";
}

// Execute query
function execute_sql($req) {
    $connect = connexion("ISfinder");
    $res = mysqli_query($connect, $req);
    if ($res === false) {
        erreur_sql($connect, $res, $req);
        exit;
    }
    return $res;
}

// Execute query (PHP7 version from 28042022)
function execute_sql_new($connect, $req) {
    $res = mysqli_query($connect, $req);
    if ($res === false) {
        erreur_sql($connect, $res, $req);
        exit;
    }
    return $res;
}

// Replacement of mysql_result which has no equivalent in PHP7
function mysqli_result($res, $row = 0, $col = 0) { 
    $numrows = mysqli_num_rows($res); 
    if ($numrows && $row <= ($numrows - 1) && $row >= 0) {
        mysqli_data_seek($res, $row);
        $resrow = (is_numeric($col)) ? mysqli_fetch_row($res) : mysqli_fetch_assoc($res);
        if (isset($resrow[$col])) {
            return $resrow[$col];
        }
    }
    return false;
}

// Query construction based on parameters
function ecrit_requette($condition, $variable) {
    if (!$variable) {
        $partie = "";
    } else {
        if ($condition == "contains") {
            $partie = "like \"%$variable%\"";
        } else if ($condition == "begin") {
            $partie = "like '$variable%'";
        } else if ($condition == "end") {
            $partie = "like '%$variable'";
        } else if ($condition == "egal") {
            $partie = "= \"$variable\"";
        } else if ($condition == "inf") {
            $partie = "<= \"$variable\"";
        } else if ($condition == "sup") {
            $partie = ">= \"$variable\"";
        } else {
            die("Erreur fatale, requete non conforme!");
        }
        return $partie;
    }
}

// Query to retrieve numerical value of MGE Type
function req_mgeType($mge) {
    if ($mge == "all") {
        $mge_numerique = 0;
    } else {
        $temp = connexion("ISfinder");    
        $req_mge = "SELECT `ID_Type_ET` FROM `type_element_transposable` WHERE `Type_ET` LIKE '$mge'";
        $result = execute_sql_new($temp, $req_mge);    
        $mge_numerique = mysqli_result($result, 0);
        mysqli_close($temp);
    }
    return $mge_numerique;
}

// Sequence display formatting
function affiche_seq($phrase, $entete) {
    if (($phrase == "NONE") or ($phrase == "-") or !$phrase) {
        echo "<p>";
    } else {
        echo "<table border='0' width='600'><tr><th bgcolor='c2b09a'>";
        echo "<b>$entete: </b></th></tr><tr><td><tt>";
        for ($i = 0; $i < strlen($phrase); $i += 60) {
            $temp = substr($phrase, $i, 60);
            echo "$temp<br>";
        }
        echo "</tt><br></td></tr></table>";
    }
}

// Encodage to display accents in HTML without losing HTML tags
function encodaccent($chaine) {
    $chaine = htmlentities($chaine);
    $chaine = htmlspecialchars_decode($chaine);
    return $chaine;
}
?>