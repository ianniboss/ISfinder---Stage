<?php
$Root = "../";
// On défini les constantes d'accè à la base, car elles ne seront pas redéfinies dans le fichier entete.inc.php
define('DB_user',"licence");
define('DB_password',"tuKR6WIvzl0eCSfU");
define('DB_bdd', "Endnote");
require_once($Root.'entete.inc.php');
require_once($Root.'menu.inc.php');
?>
  <div class="content">
  <h2> Licences Endnote </h2>
  
<form method="post" action="listeEndnote.php">

      <table  border="0" cellspacing="0" cellpadding="10">
        <tr>
        <td><div class="div_avec_fond">Equipe :</div></td>
        <td><select name="equipe">
          <option value="toutes">All</option>
<?php
		$tableau = sqlSelect('tbequipe', '', 'nomEq');
		foreach($tableau as $tab) {
                echo "<option value='".$tab['nomEq']."'>".$tab['nomEq']."</option>";
                }
?>
        </select></td>

        <td ><div class="div_avec_fond">Version :</div></td>
		
        <td ><select name="version">
		  <option selected value="toutes">All</option> 
<?php
		$liste_version = array(7,8,9,10,11,12,13,14,15,16,17,18);
        foreach ($liste_version as &$version){
                echo "<option value='$version'>$version</option>";
                }
?>
		  </select></td>
        <td >	<input name="Envoyer" type="submit" value="Ok" /></td>
        </tr>

      </table>
      </form>
  
  </div>
<?php
require_once($Root.'pied.inc.php');
?>
