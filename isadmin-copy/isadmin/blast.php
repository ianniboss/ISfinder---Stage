<?php
// session_start();
// session_destroy();
require_once('includes/entete.inc.php');
require_once('includes/aside.inc.php');
?>

<article>
<!--		<div class="ecran">contenu de mon &eacutecran</div> -->
<div class="menuProg">
<?php                       // Menu avec onglet
$liste_program = array("blastp","blastx","tblastn","tblastx");
$prog = (isset($_GET['prog_blast']) && in_array($_GET['prog_blast'], $liste_program)) ? $_GET['prog_blast'] : "blastn"; 
echo ($prog == "blastn") ? "<span class='onglet onglet-actif'>blastn</span>" : "<a class='onglet' href='blast.php?prog_blast=blastn'>blastn</a>";
echo ($prog == "blastp") ? "<span class='onglet onglet-actif'>blastp</span>" : "<a class='onglet' href='blast.php?prog_blast=blastp'>blastp</a>";
echo ($prog == "blastx") ? "<span class='onglet onglet-actif'>blastx</span>" : "<a class='onglet' href='blast.php?prog_blast=blastx'>blastx</a>";
echo ($prog == "tblastn") ? "<span class='onglet onglet-actif'>tblastn</span>" : "<a class='onglet' href='blast.php?prog_blast=tblastn'>tblastn</a>";
echo ($prog == "tblastx") ? "<span class='onglet onglet-actif'>tblastx</span>" : "<a class='onglet' href='blast.php?prog_blast=tblastx'>tblastx</a>";
?>
</div>
	<section>
    <form enctype="multipart/form-data" action="blast/ncbiIS.php" method="POST">
        <h4>Job Title: <INPUT NAME="title" VALUE="" SIZE="60">  </h4>
 <fieldset id=query>
    <legend>Enter Query Sequence</legend>
    <ul>
	<li>
	Paste your sequence in FASTA format :   
	</li><li>
	<textarea name="seq" class="seq" rows=8 cols=100></textarea>
    <input type="button" class="btn-droit" value="Clear" onclick="this.form.elements['seq'].value=''">
	</li><li>
	Or, upload file <INPUT TYPE="file" NAME="seqfile" />
	</li></ul>
  </fieldset>        
  <fieldset id=query>
    <legend>Choose ...</legend>
    <ul>
		<li>
        <label for="database">Database :</label>
		<SELECT style='width:150px' NAME="database"> 
		<OPTION value="isfinder" selected>ISfinder
		<OPTION value="ISsub" >ISsubmit
		<OPTION value="ISwait" >ISwait
		</SELECT> 
        </li>
        <?php
        if ($prog=="blastn"){
			echo "<li><label for=programme class='li-large'>Programme :</label>";
			echo "<input type='radio' id='prog' name='prog' value='blastn' checked/>&nbsp;blastn&nbsp;";
//			echo "<input type='radio' name='prog' value='megabl' />&nbsp;Megablast&nbsp;</li>";
		}else{
			echo "<input name='prog' type='hidden'' value=$prog />";
			 }
		?>
    </ul>
  </fieldset> 
  <div class="boutonblast">
      <input type="image" title="Input" onClick="images/bouton-blastdown.gif" onMouseUp="images/bouton-blastover.gif" onMouseOver="blast/images/bouton-blastover.gif" value="submit" src="images/bouton-blast.gif" height="25" width="97" alt="BLAST">
  </div>
  
  <fieldset id=query>
    <legend>Algorithm parameters</legend>
<ul><li>
    <label for=alignment style="width:160px">Alignment view options:</label>
    <select name="alignment" style="width:250px" >
    <option VALUE="0"> pairwise
    <option VALUE="1"> query-anchored showing identities
    <option VALUE="2"> query-anchored no identities
    <option VALUE="3"> flat query-anchored, show identities
    <option VALUE="4"> flat query-anchored, no identities
    <option VALUE="5"> XML Blast output
    <option VALUE="6"> tabular
    <option value="7"> tabular with comment lines
    <option value="10"> Comma-separated values
    </select>
</li></ul>
<ul class='li-haut'><li>
	    <label for=wordsize>Word size :</label>
		<SELECT NAME="wordsize" > 
<?php
	switch ($prog) {
		case "blastn":
			$tab_wordsize = array(7,11,15);
			$def='11';
			break;
		case "megabl":
			$tab_wordsize = array(16,20,24,28,32,48,64,128,256);
			break;
		default:
			$tab_wordsize = array(2,3);
			$def='3';
	}
	foreach ($tab_wordsize as $value){
		echo ($value == $def) ? "<OPTION value=$value selected> $value" : "<OPTION value=$value> $value";
	}

?>
		</SELECT>
    </li><li><label for="evalue" class="li-large">Evalue : </label><INPUT NAME="expect" VALUE="10.0" SIZE="8">
</li></ul>
 <?php
 if ($prog != "tblastx"){
	 echo "<ul class='li-haut'><li>" ;
	 echo "<label for=gapopen>Gap open :</label>";
	
	 switch ($prog) {
		case "blastn":
		echo '<SELECT style="width:180px" id="gapcosts" defval="5 2" NAME="gapcosts">';
		echo '<option value="5 2" selected>Existence: 5 &nbsp;&nbsp;Extension: 2</option>';
		echo '<option value="2 2">Existence: 2 &nbsp;&nbsp;Extension: 2</option>';
		echo '<option value="1 2">Existence: 1 &nbsp;&nbsp;Extension: 2</option>';
		echo '<option value="0 2">Existence: 0 &nbsp;&nbsp;Extension: 2</option>';
		echo '<option value="2 1">Existence: 2 &nbsp;&nbsp;Extension: 1</option>';
		echo '<option value="1 1">Existence: 1 &nbsp;&nbsp;Extension: 1</option>';
		break;
		case "megabl":
		echo '<SELECT style="width:180px" id="gapcosts" defval="" NAME="gapcosts">';
		echo '<option value="0 0" selected>Linear</option>';
		echo '<option value="5 2">Existence: 5 &nbsp;&nbsp;Extension: 2</option>';
		echo '<option value="2 2">Existence: 2 &nbsp;&nbsp;Extension: 2</option>';
		echo '<option value="1 2">Existence: 1 &nbsp;&nbsp;Extension: 2</option>';
		echo '<option value="0 2">Existence: 0 &nbsp;&nbsp;Extension: 2</option>';
		echo '<option value="2 1">Existence: 2 &nbsp;&nbsp;Extension: 1</option>';
		echo '<option value="1 1">Existence: 1 &nbsp;&nbsp;Extension: 1</option>';
		break;
		default:
		echo '<SELECT style="width:185px" id="gapcosts" defval="11 1" NAME="gapcosts">';
		echo '<option value="11 2">Existence: 11 &nbsp;&nbsp;Extension: 2</option>';
		echo '<option value="10 2">Existence: 10 &nbsp;&nbsp;Extension: 2</option>';
		echo '<option value="9 2">Existence: 9 &nbsp;&nbsp;Extension: 2</option>';
		echo '<option value="8 2">Existence: 8 &nbsp;&nbsp;Extension: 2</option>';
		echo '<option value="7 2">Existence: 7 &nbsp;&nbsp;Extension: 2</option>';
		echo '<option value="6 2">Existence: 6 &nbsp;&nbsp;Extension: 2</option>';
		echo '<option value="13 1">Existence: 13 &nbsp;&nbsp;Extension: 1</option>';
		echo '<option value="12 1">Existence: 12 &nbsp;&nbsp;Extension: 1</option>';
		echo '<option value="11 1" selected>Existence: 11 &nbsp;&nbsp;Extension: 1</option>';
		echo '<option value="10 1">Existence: 10 &nbsp;&nbsp;Extension: 1</option>';
		echo '<option value="9 1">Existence: 9 &nbsp;&nbsp;Extension: 1</option>';
	 }
	 echo '</select>';
	 echo "</li></ul>" ;
 }
	echo "<ul class='li-haut'><li>";

	echo "<label style='width:160px' for='filtre'>Filter query sequence : </label><INPUT type='checkbox' NAME='filtre' value='filtre'>";
	echo "</li></ul>";
?>

  </fieldset> 
  
    	<div class="piedSection">
<!--			<ul>
			<li><INPUT TYPE="RESET" VALUE="Reset Defaults"></li>
			</ul>				
-->		</div>
    </form>
	</section>
</article>

<?php
require_once('includes/pied.inc.php');
?>
</div> <!-- Fin du div page -->
</body>
</html>