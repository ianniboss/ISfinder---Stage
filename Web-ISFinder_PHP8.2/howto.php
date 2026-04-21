<!DOCTYPE html>
<html>
<head>
    <title>ISfinder</title>
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
    $nav_en_cours = 'about';
    include_once('include/menu.inc.php');
    include_once ("include/function.inc.php");
    ?>

    <article>
        <!-- <div class="ecran">contenu de mon écran</div> -->
        <section>
            <h2>HOW TO ...</h2>
            <hr/>

            <fieldset>
                <div>
                    <h2>Citations:</h2>
                    <p>
                        <strong>For ISfinder please cite</strong>: Siguier P. et al. (2006) ISfinder: the reference centre for bacterial insertion sequences. Nucleic Acids Res. 34: D32-D36 
                        (<a href="http://www.ncbi.nlm.nih.gov/pubmed/16381877" target="_blank">link pubmed</a>) and the database URL (http://www-is.biotoul.fr).<br>
                    </p>
                    <p>
                        <strong>For ISbrowser please cite:</strong> Kichenaradja P. et al. (2010) ISbrowser: an extension of ISfinder for visualizing insertion sequences in prokaryotic genomes. Nucleic Acids Res. 38: D62-D68 
                        (<a href="http://www.ncbi.nlm.nih.gov/pubmed/19906702" target="_blank">link pubmed</a>).
                    </p>
                    <p>
                        <strong>For ISsaga please cite:</strong> Varani A. et al. (2011) ISsaga: an ensemble of web-based methods for high throughput identification and semi-automatic annotation of insertion sequences in prokaryotic genomes, Genome Biology 2011, 12:R30 
                        (<a href="https://pubmed.ncbi.nlm.nih.gov/21443786/" target="_blank">link pubmed</a>).
                    </p>
                    <p>&nbsp;</p>
                </div>
                <div>
                    <h2>Request an attribution number:</h2>
                    <p>Many journals now ask contributors to obtain an attribution number for IS elements before publication. To do this: </p>
                    <ul class="niveau1">
                        <li class="niveau1">determine whether your sequence is already registered under another name:
                            <ul class="niveau2">
                                <li class="niveau2">Go to: <a href="https://isfinder.biotoul.fr/blast.php" target="_blank">using the database/ analysis</a></li>
                                <li class="niveau2">Perform a BLAST analysis against the entire base (use <a href="https://isfinder.biotoul.fr/blast.php?prog_blast=blastp" target="_blank">blastp</a> first) by simply pasting in your sequence (remove filter) </li>
                                <li class="niveau2">If the protein sequence is more than 98% similar and/or the DNA sequence is more than 95% similar to anything in ISfinder, your sequence is an isoform and does not require a separate attribution. You should use the name given in the database but send us a note of the bacterial species in which you found it.</li>
                                <li class="niveau2">If the sequence is not in ISfinder but there are related sequences, this will indicate which family your IS belongs to. </li>
                                <li class="niveau2">Fill in the online form <a href="https://isfinder.biotoul.fr/request_name_form.php" target="_blank">Submission/request a name</a></li>
                                <li class="niveau2">You will receive an email with the name usually within 5 working days. </li>
                            </ul> 
                            <p>(the <a href="https://isfinder.biotoul.fr/nomenclature.php" target="_blank">information/ nomenclature</a> section also describes the nomenclature system now in use / Nomenclature; lists the blocks of IS names previously attributed in the old University of Stanford system / reserved blocks of IS previously attributed…..- this system is no longer in use; and lists all current names attributed by us <a href="https://isfinder.biotoul.fr/list_names_attributed.php" target="_blank">/ list names currently attributed</a>; NOTE: not all the ISs with attributed names are yet in the database) </p>
                        </li>
                    </ul>
                    <ul class="niveau1">
                        <li class="niveau1">submit your sequence 
                            <p><strong>One of the major bottlenecks is that not all sequence information for ISs for which we have attributed a name appears in ISfinder. We rely on you to enrich ISfinder by submitting your own sequences. Please help. </strong></p>
                            <ul class="niveau2">
                                <li class="niveau2">Before submitting, make sure you have a registered name</li>
                                <li class="niveau2">Go to: <a href="https://isfinder.biotoul.fr/submission.php" target="_blank">Submission/Submit </a>an IS in the online form</li>
                                <li class="niveau2">Newly submitted ISs are added to the public database about once a week. </li>   
                            </ul>
                        </li>
                    </ul>
                    <p>You may also ask us to withhold your IS from the public data base until you wish it to be released (but please don't forget to recontact us). All sequences will be transferred to the public data base after a period of six months from submission. </p>
                </div>   
            </fieldset>
        </section>
    </article>

    <?php include_once('include/footer.inc.php'); ?>
</div> <!-- Fin du div page -->
</body>
</html>