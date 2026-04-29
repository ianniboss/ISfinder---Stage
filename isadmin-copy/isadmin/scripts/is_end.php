<?php
require_once('../includes/entete.inc.php');
require_once('../includes/aside.inc.php');
?>
<html>
<head>
<title>View ends</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body bgcolor=#FFFFFF>
<article>
<section>

<form action="<?php $_SERVER['PHP_SELF']?>" method="POST">
<fieldset id=query>
    <legend>Enter your sequence</legend>
    <ul><li>
	Paste your sequence :   
	</li><li>
	<textarea name="seq_submit" class="seq" rows=8 cols=100></textarea>
    </li><li>
    <input type="button" class="btn-droit" value="Clear" onclick="this.form.elements['seq_submit'].value=''">
	</li></ul>
    <ul><li> 
		<input type="submit" name="btnEnvoi" class="btn-droit" value="Submit">
	</li></ul>
  </fieldset>                
</form>
	<div class="piedSection">
	</div>
</section>
<?php
if(isset($_POST['btnEnvoi'])){
	$is_seq2 = strtoupper($_POST['seq_submit']);

	if (empty($is_seq2)){
        echo "<script>";
        echo "alert('Please input the sequence')\n";
        echo "javascript:window.close();"; 
        echo "</script>";
        exit ;
	}

	$is_seq=preg_replace("/[^ACGT]/","",$is_seq2);
	if (strlen($is_seq) < strlen($is_seq2)*0.9){
        echo "<script>"; 
        echo "alert('Please input a valid nucleotide sequence')\n"; 
        echo "javascript:window.close();"; 
        echo "</script>"; 
		exit ; 
	}
#
#       On supprime les retours chariots, les line feeds, les espaces et les chiffres
#
$is_seq = preg_replace("/\r/","",$is_seq);
$is_seq = preg_replace("/\n/","",$is_seq);
$is_seq=preg_replace("/ /","",$is_seq);
$is_seq=preg_replace("/[1-9]/","",$is_seq);
$ir_l = substr($is_seq, 0, 50) ;
$ir_r = substr($is_seq, -50, 50) ;

# Transformation de Right End en son réverse-complémentaire
$ir_r = strrev($ir_r);
$ir_r = strtr($ir_r,"ATCG","TAGC");

print "<h2>Left end   </h2>";
print " <HR>";
print "<TT class='blast'>";
print "$ir_l <br>";
print "$ir_r <br>";
print "</TT>";
print " <HR>";
print "<h2>Right end  </h2>";
}
?>
</article>

<?php
require_once('../includes/pied.inc.php');
?>
