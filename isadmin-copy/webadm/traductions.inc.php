<?php
/* Passage par les fonctions de base car fonctions.inc.php a besoin de traductions.inc.php plus que l'inverse...
 * Pour ajouter une langue, il faut :
 * - ajouter les champs correspondants dans chaque tableau ci-dessous
 * - ajouter ligne 12 la langue à NE PAS remplacer ; par exemple ($Page_Langue!="sp") pour Espagnol
 * - ajouter le champ mot_sp (par exemple pour Espagnol) dans la table "traduction"
 *      (et ajouter les traductions correspondantes !)
*/
if (isset($_REQUEST['langue'])) {           $Page_Langue = $_REQUEST['langue']; }
elseif (isset($_SESSION['Page_Langue'])) {  $Page_Langue = $_SESSION['Page_Langue']; }
else {                                      $Page_Langue = ''; }
if ($Page_Langue!="en") {                   $Page_Langue = "fr"; }
$_SESSION['Page_Langue'] = $Page_Langue;

$Langues_Disponibles = array("fr"=>"Fran&ccedil;ais", "en"=>"English");
$NOMBRE_LANGUES = count($Langues_Disponibles);
$LangueTailleJoursCourt = array("fr"=>3, "en"=>3);
$LangueTailleMoisCourt = array("fr"=>4, "en"=>3);
$LangueListeJours = array("fr"=>array(1=>'Lundi',2=>'Mardi',3=>'Mercredi',4=>'Jeudi',5=>'Vendredi',6=>'Samedi',7=>'Dimanche'),
                          "en"=>array(1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday'));
$LangueListeMois = array("fr"=>array(1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'),
                         "en"=>array(1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'));
$listeJours = $LangueListeJours[$Page_Langue];
$listeMois = $LangueListeMois[$Page_Langue];
?>