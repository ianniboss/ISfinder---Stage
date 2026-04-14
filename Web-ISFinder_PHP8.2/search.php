<?php
session_start();
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
$nav_en_cours='tools';
include('include/menu.inc.php'); ?>

<article>
<!--		<div class="ecran">contenu de mon &eacutecran</div> -->
	<section>
    <form action="scripts/search-db.php" method="POST" name="search">
		<h2>IS Database Search</h2>
		<hr/>
        <h3>Search in all fields: <INPUT NAME="tout" VALUE="" SIZE="40">  </h3>
 <fieldset id=search1>
    <legend>Essential fields</legend>
    <ul>
    	<li>
        <label for=nom>Name ( & synonyms) :</label>
  		<SELECT NAME="namecond"> 
  		<OPTION value="begin">begin_with 
  		<OPTION value="contains" selected>contains 
  		<OPTION value="end">end_with 
  		<OPTION value="egal">equal_to </SELECT> 
  		<INPUT NAME="name" VALUE="">
  		</li><li>
        <label for=MGEtype>MGE type :</label>
		<SELECT NAME="MGEtype"> 
		<OPTION value="IS">IS 
        <OPTION value="MITE">MITE 
		<OPTION value="MIC">MIC 
		<OPTION value="tIS">tIS 
		<OPTION value="transposon">Transposon
        <OPTION value="all" selected>All</SELECT>         
        </li><li>
        <label for=family>Family :</label>
		<SELECT NAME="familycond"> 
		<OPTION value="begin">begin_with 
		<OPTION value="contains" selected>contains 
		<OPTION value="end">end_with 
		<OPTION value="egal">equal_to </SELECT> 
		<INPUT NAME="family" VALUE="" SIZE="20">
        </li><li>
        <label for=group>Group :</label>
		<SELECT NAME="grpcond"> 
		<OPTION value="begin">begin_with 
		<OPTION value="contains" selected>contains 
		<OPTION value="end">end_with 
		<OPTION value="egal">equal_to </SELECT> 
		<INPUT NAME="grp" VALUE="" SIZE="20">
        </li><li>
        <label for=group>Origin/Host :</label>
  		<SELECT NAME="hostcond"> 
		<OPTION value="begin">begin_with 
		<OPTION value="contains" selected>contains 
		<OPTION value="end">end_with 
		<OPTION value="egal">equal_to </SELECT> 
		<INPUT NAME="host" VALUE="" SIZE="20">
        </li><li>
        <label for=accesNumb>Accession Number :</label>
		<SELECT NAME="accessioncond"> 
		<OPTION value="begin">begin_with 
		<OPTION value="contains" selected>contains 
		<OPTION value="end">end_with 
		<OPTION value="egal">equal_to </SELECT> 
		<INPUT NAME="accession" VALUE=""><br>
        </li>
    </ul>
  </fieldset>        
  <fieldset id=search2>
    <legend>Additional fields</legend>
    <ul>
		<li>
        <label for=group>Left End :</label>
		<SELECT NAME="ir_lcond"> 
		<OPTION value="begin">begin_with 
		<OPTION value="contains" selected>contains 
		<OPTION value="end">end_with 
		<OPTION value="egal">equal_to </SELECT> 
		<INPUT NAME="ir_l" VALUE="" SIZE="20">
        </li><li>
        <label for=group>Right End :</label>
		<SELECT NAME="ir_rcond"> 
		<OPTION value="begin">begin_with 
		<OPTION value="contains" selected>contains 
		<OPTION value="end">end_with 
		<OPTION value="egal">equal_to </SELECT> 
		<INPUT NAME="ir_r" VALUE="" SIZE="20">
        </li><li>
        <label for=ir>IR :</label>
		<SELECT NAME="ircond"> 
		<OPTION value="sup">>= 
		<OPTION value="inf">=< 
		<OPTION value="egal" selected>equal_to </SELECT> 
		<INPUT NAME="ir" VALUE="" SIZE="20">
        </li><li>
        <label for=dr>DR :</label>
		<SELECT NAME="drcond"> 
		<OPTION value="sup">>= 
		<OPTION value="inf">=< 
		<OPTION value="egal" selected>equal_to </SELECT> 
		<INPUT NAME="dr" VALUE="" SIZE="20">
        </li><li>
        <label for=orfSize>ORF size :</label>
		<SELECT NAME="orfsizecond"> 
		<OPTION value="sup">>= 
		<OPTION value="inf">=< 
		<OPTION value="egal" selected>equal_to </SELECT> 
		<INPUT NAME="orfSize" VALUE="" SIZE="20">
        </li><li>
        <label for=orfFunction>ORF function :</label>
		<SELECT NAME="orffunctioncond"> 
		<OPTION value="begin">begin_with 
		<OPTION value="contains" selected>contains 
		<OPTION value="end">end_with 
		<OPTION value="egal">equal_to </SELECT> 
		<INPUT NAME="orfFunction" VALUE="" SIZE="20">
        </li><li>
        <label for=lenght>Length :</label>
		<SELECT NAME="lengthcond"> 
		<OPTION value="sup">>= 
		<OPTION value="inf">=< 
		<OPTION value="egal" selected>equal_to </SELECT> 
		<INPUT NAME="length" VALUE="" SIZE="20">
   		</li><li>
        <label for=frameshift>Frameshift :</label>
        <input type="radio" id="frameshift" name="frameshift" value="1" />&nbsp;With&nbsp;
        <input type="radio" name="frameshift" value="0" />&nbsp;Without&nbsp;
        <input type="radio" name="frameshift" value="2" checked/>&nbsp;No choice
        </li>
    </ul>
  </fieldset> 
  <fieldset id=search1>
    <legend>Output parameters</legend>
    <ul>
		<li>
        <label for=output>Output parameters :</label>
        </li><li>
        <input type="radio" id="output" name="output" value="0" checked/>&nbsp;Standard : Name, Synonyms, Iso, Family, Group, Origin, Length, IR, DR, ORF, Accession Number.<br>
        <input type="radio" id="output" name="output" value="1" />&nbsp;Hosts :&nbsp;Name, Family, Group, Origin, Hosts<br>
        <input type="radio" id="output" name="output" value="2" />&nbsp;Insertion site :&nbsp;Name, Family, Group, Insertion site<br>
        <input type="radio" id="output" name="output" value="3" />&nbsp;Ends IR : Name, Family, Group, Left end, Right end<br>
        <input type="radio" id="output" name="output" value="4" />&nbsp;Ends : Name, Family, Group, Left end, Right end<br>
		</li>
    </ul>
  </fieldset> 
  
    	<div class="piedSection">
			<ul>
			<li><input type="submit" name="Onsubmit" value="Submit"></li>
			<li><INPUT TYPE="RESET" VALUE="Reset Defaults"></li>
			</ul>				
		</div>
    </form>
	</section>
</article>

<?php include('include/footer.inc.php'); ?>

</div> <!-- Fin du div page -->
</body>
</html>