<?php
$Root = './';
require_once($Root.'init_sql.inc.php');
require_once($Root.'traductions.inc.php');
require_once($Root.'init.inc.php');
$pageTitre = '';
require_once($Root.'entete.inc.php');
?>

<article>
	<h2> Intranet METi / LITC </h2>
    <section>
    	<h3> Modification d'un paragraphe </h3>	
<?php
	$action = getVar('action', '');
	$id = (int)getVar('id', 'AJOUT');
	if ($id=='AJOUT') {	$id = 0; }
	$defaut = array('id_paragraphe'=>0, 'id_plateau'=>0, 'texte_fr'=>'', 'texte_en'=>'');
	if ($id>0) {	// vérifie si id existe !
		$defaut=sqlTrouve('paragraphes', 'id_paragraphe='.$id);
		if (!is_array($defaut)) {	// id introuvable
			$id = 0;
		}
	}
	$champIdPlateau =	getVar('champIdPlateau', $defaut['id_plateau']);
	if (is_null($champIdPlateau)) {	$champIdPlateau = 0; }
	$champTexteFR =		getVar('champTexteFR', texteDepuisHTML($defaut['texte_fr']));
	$champTexteEN =		getVar('champTexteEN', texteDepuisHTML($defaut['texte_en']));
	if ( ($action=='Dupliquer') and ($id<>0) ) {	// Duplique, donc défini $id à 0 pour forcer l'ajout d'un paragraphe avec les mêmes infos !
		$id = 0;
		$action = 'Valider';
	}
	if ($action=='Valider') {	// Validation demandée (données formulaire à récupérer et mise à jour
		$donnees = array('id_plateau'=>($champIdPlateau>0?$champIdPlateau:NULL), 'horodateur_maj'=>date("Y-m-d H:i:s"), 'texte_fr'=>texteVersHTML($champTexteFR), 'texte_en'=>texteVersHTML($champTexteEN));
		if ($id==0) {	// Ajout d'un élément
			$id = sqlInsert('paragraphes', $donnees);
			if ( (is_int($id)) and ($id>0) ) {
				echo('<em>Enregistré !</em>');
				$action = '';
			}
		} else {
			if ( sqlUpdate('paragraphes', 'id_paragraphe='.$id, $donnees)==1 ) {
				echo('<em>Enregistré !</em>');
				$action = '';
			}
		}
	}
	
	$liste = sqlSelect('plateau');
	foreach ($liste as $un) {	$listePlateau[$un['idx']] = $un['sigle']; }
	
	echo( commencerFormulaire('Ajouter') );
	echo( creerChampID('id', $id) );
	echo('<table class="liste">');
		echo('<tr><th> ID </th><td> ');
			if ($id==0) {
				echo('NOUVEAU !');
			} else {
				echo($id);
				echo('&nbsp;'.creerFormBouton('action', 'Dupliquer').' <- Duplique ce paragraphe avec un nouveau numéro !');
			}
		echo(' </td></tr>');
		echo('<tr><th> Plateau </th><td> ');
			$valeurs = array_merge(array('0'=>"Non défini (tous plateaux)"), $listePlateau);
			echo( creerListeDeroulante('champIdPlateau', $valeurs, $champIdPlateau, FALSE) );
		echo(' </td></tr>');
		echo('<tr><th> Texte français </th><td> ');
			echo( creerChampTexteArea('champTexteFR', "", $champTexteFR, 80, 30, TRUE) );
		echo(' </td></tr>');
		echo('<tr><th> Texte anglais </th><td> ');
			echo( creerChampTexteArea('champTexteEN', "", $champTexteEN, 80, 30, TRUE) );
		echo(' </td></tr>');
		echo('<tr><th colspan="2"> '.creerFormBouton('action', 'Valider').' </th></tr>');
	echo('</table>');
	echo( terminerFormulaire() );
?>
    </section>
<!--		<div class="ecran">contenu de mon &eacutecran</div> -->
</article>

<?php
require_once($Root.'pied.inc.php');
?>