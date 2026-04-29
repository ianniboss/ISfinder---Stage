<!DOCTYPE html>
<html>
<head>
    <title>ISfinder</title>
    <meta charset="utf-8" /> 
    <meta name="author" content="Jo" />
    <meta name="keywords" content="IS, Insertion Sequence" />
    <link type="text/css" rel="stylesheet" href="styles/styles.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="styles/about.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="styles/menu.css" media="screen" />
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
</head>
<body>
<div id="page">
    <header>
    </header>

    <?php 
    $nav_en_cours = 'about';
    include_once('include/menu.inc.php');
    include_once('include/function.inc.php');
    ?>

    <article>
        <!-- <div class="ecran">contenu de mon écran</div> -->
        <section>
            <h2>CREDITS</h2>
            <hr/>

            <fieldset>
                <div class="new_div">
                    <p align="justify">
                        The ISfinder database is curated by 
                        <a href="mailto:<?php echo addressMail('Patricia', 'Siguier', ''); ?>">Patricia Siguier</a> (LMGM), 
                        Edith Gourbeyre (LMGM), 
                        <a href="mailto:<?php echo addressMail('', 'mc2126', 'georgetown.edu'); ?>">Mick Chandler</a> (LMGM) 
                        and <a href="mailto:<?php echo addressMail('', 'mahillon', 'mbla.ucl.ac.be'); ?>">Jacques Mahillon</a> (UCL).<br>
                        It was initiated by Alain Gaekle (UCL) and Fredéric Rodriguez (IBCG) and further developed by 
                        <a href="mailto:<?php echo addressMail('Jocelyne', 'Perochon', ''); ?>">Jocelyne Pérochon</a> (IBCG), 
                        Philippe Azema (IBCG) and Laurent Lestrade (IBCG) with help from Michele Boschet (LMGM). 
                        ISfinder was initiated by <a href="mailto:<?php echo addressMail('', 'mahillon', 'mbla.ucl.ac.be'); ?>">Jacques Mahillon</a>.
                    </p>
                    <p align="justify">
                        This site is administered, maintained and upgraded by 
                        <a href="mailto:<?php echo addressMail('Jocelyne', 'Perochon', ''); ?>">Jocelyne Pérochon</a> (IBCG) 
                        and was designed by David Villa (IBCG).
                    </p>
                    <div align="justify">
                        Further information concerning technical aspects of this site can be obtained from 
                        <a href="mailto:<?php echo addressMail('Patricia', 'Siguier', ''); ?>">Patricia Siguier</a>, 
                        <a href="mailto:<?php echo addressMail('Jocelyne', 'Perochon', ''); ?>">Jocelyne Pérochon</a>, 
                        and <a href="mailto:<?php echo addressMail('', 'mc2126', 'georgetown.edu'); ?>">Mick Chandler</a>
                    </div>
                    <div>
                        <div> </div>
                    </div>
                    <p align="justify">&nbsp;</p>
                    <h2 align="justify">Warning &amp; Disclaimer</h2>
                    <p align="justify">
                        By accessing this computer system you are consenting to system monitoring for law enforcement and other purposes. 
                        Unauthorized use of, or access to, this computer system may subject you to criminal prosecution and penalties. <br>
                        The information, opinions, data, and statements contained herein are not necessarily those of the CNRS and should not be interpreted, acted on or represented as such. <br>
                        The CNRS and FNRS and their employees and contractors do not make any warranty, express or implied, including the warranties of merchantability and fitness for a particular purpose with respect to documents available from this server. 
                        In addition, the CNRS and FNRS and their employees and contractors assume no legal liability for the accuracy, completeness, or usefulness of any information or process disclosed herein and do not represent that use of such information or process would not infringe on privately owned rights. <br>
                        Reference herein to any specific commercial product, process, or service by trade name, trademark, manufacturer, or otherwise, does not necessarily constitute or imply its endorsement, recommendation, or favoring by the CNRS or any of its employees or contractors. <br>
                        Permission to reproduce documents from this server may be required.
                    </p>
                    <p>&nbsp;</p>
                </div>
            </fieldset>
        </section>
    </article>

    <?php include_once('include/footer.inc.php'); ?>
</div> <!-- Fin du div page -->
</body>
</html>