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
<?php
$idEqL = getVar("equipe", "toutes");
$vers = getVar("version", "toutes");

$requete = 'SELECT tblicence.*, tbequipe.nomEq as nomEq, CASE WHEN Vers_maj=0 THEN Version_Endnote ELSE Vers_maj END as version,';
$requete.= ' CASE WHEN Vers_maj=0 THEN date_instal ELSE date_instal_maj END as date_installation';			
$requete.= ' FROM tblicence';
$requete.= ' LEFT OUTER JOIN tbequipe ON tbequipe.idEq=tblicence.idEqL';
if ( ($idEqL!="toutes") or ($vers!="toutes") ) {	$requete.= ' WHERE ';	}
if ($idEqL != "toutes") {							$requete.= 'nomEq ="'.$idEqL.'"';	}
if ( ($idEqL!="toutes") and ($vers!="toutes") ) {	$requete.= ' AND ';	}
if ($vers != "toutes") {							$requete.= '(Version_Endnote LIKE "'.$vers.'" OR Vers_maj LIKE "'.$vers.'")';	}
$requete.= ' ORDER BY nomEq, version';
// on construit et execute la requete SQL
$tableau = sqlRequete($requete);
/*
$nbr_result= mysql_num_rows($req) ;

for ($i=0;i<$nbr_result;$i++){
	if (($vers!='%') && ($resultat[$i]>$vers)){
		unset($resultat[$i]);
	}
}

*/
?>
      <table>
  <tr>
    <th>Equipe</th>
    <th>Nom machine</th>
    <th>Utilisateur</th>
    <th>IP machine</th>
    <th>OS</a></th>
    <th>Version</th>
    <th>Date d'installation</th>
  </tr>
<?php
$nbr_ligne = 0;
foreach ($tableau as $ligne){
	  echo "<tr><td align='right'>" .$ligne['nomEq']."</td>" ;
	  echo "    <td align='center'>".$ligne['nom_machine']."</td>" ;
	  echo "    <td align='center'>".$ligne['nom_user']."</td>" ;
	  echo "    <td align='center'>".$ligne['num_ip']."</td>" ;
	  echo "    <td align='center'>".$ligne['Mac/Win']."</td>" ;
	  echo "    <td align='center'>".$ligne['version']."</td>" ;
	  echo "    <td align='center'>".$ligne['date_installation']."</td></tr>" ;
	  
	  $nbr_ligne++;
}
if ($idEqL != "toutes"){
	echo "nombre de licences de l'équipe ".$idEqL ;
	if ($vers != "%"){
		echo " pour la version ".$vers." : ".$nbr_ligne ;
	}else{
		echo " : ".$nbr_ligne ;
	}

}else{
	echo "nombre de licences du labo" ;
		if ($vers != "%"){
			echo " pour la version ".$vers." : ".$nbr_ligne ;
		}else{
			echo " : ".$nbr_ligne ;
		}
}
?>
  
  </div>
<?php
require_once($Root.'pied.inc.php');
?>
