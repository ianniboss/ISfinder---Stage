<!DOCTYPE html>
<html>
<head>
    <title>ISfinder</title>
    <meta charset="utf-8" /> 
    <meta name="author" content="Jo" />
    <meta name="keywords" content="IS, Insertion Sequence" />
    <link type="text/css" rel="stylesheet" href="styles/styles.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="styles/menu.css" media="screen" />
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
</head>
<body>
<div id="page">
    <header>
    </header>

    <?php
    $nav_en_cours = 'home';
    include_once('include/menu.inc.php');
    ?>

    <article>
        <img src="images/IS-garde.png" width="1024" height="531" border="0" usemap="#Map_logo" class="image">

        <map name="Map_logo">
            <area shape="rect" coords="15,445,150,510" href="https://lmgm.cbi-toulouse.fr/en/home/" target="_blank">
            <area shape="rect" coords="920,430,1000,520" href="http://www.cnrs.fr/index.php" target="_blank">
        </map>

        <?php
        include_once("include/function.inc.php");

        $cnx = connexion("localhost", "isfinder", "ISfinder", "mCjMPEJ_16");

        $sql_request = "SELECT `Validation_Date` FROM `submission` ORDER BY `Validation_Date` DESC";

        $date_sub = "";

        if ($cnx && ($result = execute_sql_new($cnx, $sql_request))) {
            $row = mysqli_fetch_row($result);
            if ($row && isset($row[0])) {
                $date_sub = $row[0];
            }
        }

        mysqli_close($cnx);
        ?>

        <p class="lastmaj">
            Last Database Update :&nbsp;
            <?php echo !empty($date_sub) ? $date_sub : ""; ?>
        </p>
    </article>

    <?php include_once('include/footer.inc.php'); ?>

</div> <!-- Fin du div page -->
</body>
</html>