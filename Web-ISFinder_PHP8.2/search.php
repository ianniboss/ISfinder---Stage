<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
    <title>ISfinder</title>
    <meta charset="utf-8" /> 
    <meta name="author" content="Jo" />
    <meta name="keywords" content="IS, Insertion Sequence" />
    <link type="text/css" rel="stylesheet" href="styles/styles.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="styles/menu.css" media="screen" />
    <link type="text/css" rel="stylesheet" href="styles/search.css" media="screen" />
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
</head>
<body>
<div id="page">
    <header>
    </header>

    <?php 
    $nav_en_cours = 'tools';
    include_once('include/menu.inc.php');
    ?>

    <article>
        <!-- <div class="ecran">contenu de mon écran</div> -->
        <section>
            <form action="scripts/search-db.php" method="POST" name="search">
                <h2>IS Database Search</h2>
                <hr/>
                <h3>Search in all fields: <input name="tout" value="" size="40"></h3>

                <fieldset id="search1">
                    <legend>Essential fields</legend>
                    <ul>
                        <li>
                            <label for="nom">Name (& synonyms) :</label>
                            <select name="namecond"> 
                                <option value="begin">begin_with</option> 
                                <option value="contains" selected>contains</option> 
                                <option value="end">end_with</option> 
                                <option value="egal">equal_to</option> 
                            </select> 
                            <input id="nom" name="name" value="">
                        </li>
                        <li>
                            <label for="MGEtype">MGE type :</label>
                            <select id="MGEtype" name="MGEtype"> 
                                <option value="IS">IS</option> 
                                <option value="MITE">MITE</option> 
                                <option value="MIC">MIC</option> 
                                <option value="tIS">tIS</option> 
                                <option value="transposon">Transposon</option>
                                <option value="all" selected>All</option>
                            </select>         
                        </li>
                        <li>
                            <label for="family">Family :</label>
                            <select name="familycond"> 
                                <option value="begin">begin_with</option> 
                                <option value="contains" selected>contains</option> 
                                <option value="end">end_with</option> 
                                <option value="egal">equal_to</option> 
                            </select> 
                            <input id="family" name="family" value="" size="20">
                        </li>
                        <li>
                            <label for="group">Group :</label>
                            <select name="grpcond"> 
                                <option value="begin">begin_with</option> 
                                <option value="contains" selected>contains</option> 
                                <option value="end">end_with</option> 
                                <option value="egal">equal_to</option> 
                            </select> 
                            <input id="group" name="grp" value="" size="20">
                        </li>
                        <li>
                            <label for="group">Origin/Host :</label>
                            <select name="hostcond"> 
                                <option value="begin">begin_with</option> 
                                <option value="contains" selected>contains</option> 
                                <option value="end">end_with</option> 
                                <option value="egal">equal_to</option> 
                            </select> 
                            <input id="host" name="host" value="" size="20">
                        </li>
                        <li>
                            <label for="accesNumb">Accession Number :</label>
                            <select name="accessioncond"> 
                                <option value="begin">begin_with</option> 
                                <option value="contains" selected>contains</option> 
                                <option value="end">end_with</option> 
                                <option value="egal">equal_to</option> 
                            </select> 
                            <input id="accesNumb" name="accession" value=""><br>
                        </li>
                    </ul>
                </fieldset>        

                <fieldset id="search2">
                    <legend>Additional fields</legend>
                    <ul>
                        <li>
                            <label for="group">Left End :</label>
                            <select name="ir_lcond"> 
                                <option value="begin">begin_with</option> 
                                <option value="contains" selected>contains</option> 
                                <option value="end">end_with</option> 
                                <option value="egal">equal_to</option> 
                            </select> 
                            <input id="ir_l" name="ir_l" value="" size="20">
                        </li>
                        <li>
                            <label for="group">Right End :</label>
                            <select name="ir_rcond"> 
                                <option value="begin">begin_with</option> 
                                <option value="contains" selected>contains</option> 
                                <option value="end">end_with</option> 
                                <option value="egal">equal_to</option> 
                            </select> 
                            <input id="ir_r" name="ir_r" value="" size="20">
                        </li>
                        <li>
                            <label for="ir">IR :</label>
                            <select name="ircond"> 
                                <option value="sup">&gt;=</option> 
                                <option value="inf">&lt;=</option> 
                                <option value="egal" selected>equal_to</option> 
                            </select> 
                            <input id="ir" name="ir" value="" size="20">
                        </li>
                        <li>
                            <label for="dr">DR :</label>
                            <select name="drcond"> 
                                <option value="sup">&gt;=</option> 
                                <option value="inf">&lt;=</option> 
                                <option value="egal" selected>equal_to</option> 
                            </select> 
                            <input id="dr" name="dr" value="" size="20">
                        </li>
                        <li>
                            <label for="orfSize">ORF size :</label>
                            <select name="orfsizecond"> 
                                <option value="sup">&gt;=</option> 
                                <option value="inf">&lt;=</option> 
                                <option value="egal" selected>equal_to</option> 
                            </select> 
                            <input id="orfSize" name="orfSize" value="" size="20">
                        </li>
                        <li>
                            <label for="orfFunction">ORF function :</label>
                            <select name="orffunctioncond"> 
                                <option value="begin">begin_with</option> 
                                <option value="contains" selected>contains</option> 
                                <option value="end">end_with</option> 
                                <option value="egal">equal_to</option> 
                            </select> 
                            <input id="orfFunction" name="orfFunction" value="" size="20">
                        </li>
                        <li>
                            <label for="lenght">Length :</label>
                            <select name="lengthcond"> 
                                <option value="sup">&gt;=</option> 
                                <option value="inf">&lt;=</option> 
                                <option value="egal" selected>equal_to</option> 
                            </select> 
                            <input id="lenght" name="length" value="" size="20">
                        </li>
                        <li>
                            <label for="frameshift">Frameshift :</label>
                            <input type="radio" id="frameshift" name="frameshift" value="1" />&nbsp;With&nbsp;
                            <input type="radio" name="frameshift" value="0" />&nbsp;Without&nbsp;
                            <input type="radio" name="frameshift" value="2" checked/>&nbsp;No choice
                        </li>
                    </ul>
                </fieldset> 

                <fieldset id="search1">
                    <legend>Output parameters</legend>
                    <ul>
                        <li>
                            <label for="output">Output parameters :</label>
                        </li>
                        <li>
                            <input type="radio" id="output0" name="output" value="0" checked/>&nbsp;Standard : Name, Synonyms, Iso, Family, Group, Origin, Length, IR, DR, ORF, Accession Number.<br>
                            <input type="radio" id="output1" name="output" value="1" />&nbsp;Hosts : Name, Family, Group, Origin, Hosts<br>
                            <input type="radio" id="output2" name="output" value="2" />&nbsp;Insertion site : Name, Family, Group, Insertion site<br>
                            <input type="radio" id="output3" name="output" value="3" />&nbsp;Ends IR : Name, Family, Group, Left end, Right end<br>
                            <input type="radio" id="output4" name="output" value="4" />&nbsp;Ends : Name, Family, Group, Left end, Right end<br>
                        </li>
                    </ul>
                </fieldset> 
          
                <div class="piedSection">
                    <ul>
                        <li><input type="submit" name="Onsubmit" value="Submit"></li>
                        <li><input type="reset" value="Reset Defaults"></li>
                    </ul>				
                </div>
            </form>
        </section>
    </article>

    <?php include_once('include/footer.inc.php'); ?>

</div> <!-- Fin du div page -->
</body>
</html>