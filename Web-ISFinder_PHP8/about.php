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
$nav_en_cours='about';
include('include/menu.inc.php');
Include_once ("include/function.inc.php");

?>

<article>
<!--		<div class="ecran">contenu de mon &eacutecran</div> -->
	<section>
		<h2>ABOUT</h2>
		<hr/>
        

 <fieldset>
   <div class="new_div">
   <p>This database provides a list of insertion  sequences (IS) isolated from bacteria and archae. It is organized into  individual files containing their general features (name, size, origin,  family.....) as well as their DNA and potential protein sequences. Some of the  entries have been identified as individual elements by their insertion  activities but a growing number are included from their description in  sequenced bacterial genomes. It also includes certain transposons, in  particular, members of the Tn<em>3</em> family  of replicative transposons.</p>
   <p><br>
     The search engine permits the retrieval and  display of individual and groups of ISs based on a combination of their general  features. </p>
   <p>Two  levels of search are available.The simple <a href="search.php">search</a> option enables the user to sort       elements using a limited number of basic items whereas the extensive search offers an additional set of possibilities such as  comparisons of the sequences of terminal inverted repeats and a variety of  different layout displays. Built in links are provided to a number of related  and complementary databases and analysis packages. 
     </p>
   <p>&nbsp;</p>
   <p>At  present, only individual sequences can be downloaded one by one for comparison.  An on-line <a href="https://isfinder.biotoul.fr/blast.php">BLAST</a> facility is available and in future versions direct access to  additional analytical tools will be provided on line. </p>
   <p>&nbsp;</p>
   <p>The ISfinder platform also includes a genome  browser, ISbrowser  (P. Kichenaradja), allowing visualization of IS in certain genomes and ISsaga,  an ensemble of software designed to assist in genome annotation of IS (A. Varani). </p>
   <div>
     <div> </div>
     <div> </div>
   </div>
   <p>&nbsp;</p>
   <p><strong>Permission:</strong>It is not permitted to download the ISfinder  database without written authorization. Moreover, it is also not permitted at  any time to distribute the database to third parties either individually or as  part of web-based software. </p>
   <p>&nbsp;</p>
   <p>Direct <a href="submission.php">submission</a> of ISs is encouraged using the on-line form provided.
   </div>
 </fieldset>
	</section>
</article>

<?php include('include/footer.inc.php'); ?>
</div> <!-- Fin du div page -->
</body>
</html>