<!DOCTYPE html>
<html>
<head>
    <title>General features</title>
    <meta charset="utf-8" /> 
    <meta name="author" content="Jo" />
    <meta name="keywords" content="IS, Insertion Sequence" />
    <link type="text/css" rel="stylesheet" href="styles/styles.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="styles/menu.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="styles/about.css" media="screen" />
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
</head>
<body>
<div id="page">
    <header>
    </header>

    <?php 
    $nav_en_cours = 'infos';
    include_once('include/menu.inc.php');
    include_once ("include/function.inc.php");
    ?>

    <article>
        <!-- <div class="ecran">contenu de mon écran</div> -->
        <section>
            <h2>Reserved blocks of IS numbers previously attributed (Stanford University listing)</h2>
            <hr/>            <fieldset>
                <div>
                    <p>We recommend the use of the new nomenclature for additionnal sequences.</p>
                    <p> Note : some of these numbers appear to have been used by people who have not been registered. Please consult the general database. </p>
                    <?php include('reserved_blocks.html'); ?>
                </div>
                <div></div>   
            </fieldset>
        </section>
    </article>

    <?php include_once('include/footer.inc.php'); ?>

</div> <!-- Fin du div page -->
</body>
</html>