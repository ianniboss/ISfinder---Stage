<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html>
<head>
<title> ISadmin </title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link type="text/css" rel="stylesheet" href="/isadmin/styles/styles.css" media="screen">
<link type="text/css" rel="stylesheet" href="/isadmin/styles/blast.css" media="screen">

<!--[if lt IE 9]>
    <script src="html5.js"></script>
    <![endif]-->
    <script>
    <!--
    function MM_reloadPage(init) {  //reloads the window if Nav4 resized
    if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
        document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}\
    else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
    }
    MM_reloadPage(true);
    //-->
    </script>
</head>
<body>
<div id="page">
<header>

</header>
<nav>
    <h2 class="accessibility">choix des bases de données</h2>
<?php
// Récupération des bases cochées lorsqu'il y a eu un changement sur une checkbox
if (isset($_POST['base'])) {
    $_SESSION['base'] = $_POST['base'];
} elseif (!isset($_SESSION['base'])) {
    $_SESSION['base'][0] = "ISSub";
    //$_SESSION['base'] = (!isset($_SESSION['base'])) ? $_SESSION['base'][0] : "ISSub" ;
}
?>

<!-- Création d'un formulaire sans bouton d'envoi
    Il y a action chaque fois qu'une case est cochée ou décochée
    l'action consiste à réafficher la page
-->
<form action="<?php $_SERVER['PHP_SELF']; ?>" method="post" name="base">
    <ul>
        <li class="base_IS"><input type="checkbox" name="base[]" onchange="document.forms['base'].submit();" <?php echo (isset($_SESSION['base']) && (in_array("IS", $_SESSION['base']))) ? "checked" : ""; ?> value="IS">&nbsp;Base IS</li>
        <li class="base_ISSub"><input type="checkbox" name="base[]" onchange="document.forms['base'].submit();" <?php echo (isset($_SESSION['base']) && (in_array("ISSub", $_SESSION['base']))) ? "checked" : ""; ?> value="ISSub">&nbsp;Base ISSub</li>
        <li class="base_ISWait"><input type="checkbox" name="base[]" onchange="document.forms['base'].submit();" <?php echo (isset($_SESSION['base']) && (in_array("ISWait", $_SESSION['base']))) ? "checked" : ""; ?> value="ISWait">&nbsp;Base ISWait</li>
        <li class="base_ISTrash"><input type="checkbox" name="base[]" onchange="document.forms['base'].submit();" <?php echo (isset($_SESSION['base']) && (in_array("ISTrash", $_SESSION['base']))) ? "checked" : ""; ?> value="ISTrash">&nbsp;Base ISTrash</li>
    </ul>
    </form>

</nav>
