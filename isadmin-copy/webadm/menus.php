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
    	<h3> Gestion des menus </h3>	
<?php
	$action = getVar('action', '');
	$id = (int)getVar('id', '0');
	if (substr($action, 0, 3)=="MAJ") {	// Modification !
		$id = (int)(substr($action, 4));
		$action = "MAJ";
	}
	if (in_array($action, array('AJOUT', 'MAJ', 'Valider'))) {	// Formulaire doit/vient d'être affiché, récupère valeurs
		$defaut = array('idx'=>0, 'id_plateau'=>0, 'description'=>'');
		if ($id>0) {	// vérifie si id existe !
			$defaut=sqlTrouve('menu_tit', 'idx='.$id);
			if (!is_array($defaut)) {	// id introuvable
				$id = 0;
			}
		}
		$champIdPlateau =	getVar('champIdPlateau', $defaut['id_plateau']);
		if (is_null($champIdPlateau)) {	$champIdPlateau = 0; }
		$champDescription =		getVar('champDescription', $defaut['description']);
		if ($action=='Valider') {	// Validation demandée (données formulaire à récupérer et mise à jour
			$messErr = array();
			$donnees = array('id_plateau'=>($champIdPlateau>0?$champIdPlateau:NULL), 'description'=>texteVersHTML($champDescription) );
			if ($id==0) {	// Ajout d'un élément
				$id = sqlInsert('menu_tit', $donnees);
				if ( (is_int($id)) and ($id>0) ) {	echo('<em>Enregistré !</em>'); }
			} else {
				if ( sqlUpdate('menu_tit', 'idx='.$id, $donnees)==1 ) {	echo('<em>Enregistré !</em>'); }
			}
			$action = '';
		}
	} else {	// Si $action n'est pas reconnue, on l'ignore !
		$action = '';
	}
	
	$liste = sqlSelect('plateau');
	$listePlateau = array();
	foreach ($liste as $un) {	$listePlateau[$un['idx']] = $un['sigle']; }
	$condition = getVar('condition', 'tous');
	$conditions = array('tous'=>"idx>0", 'indefini'=>"id_plateau is null");
	$derouleConditions = array('tous'=>"Tous", 'indefini'=>"Indéfini");
	foreach ($listePlateau as $id_plateau=>$sigle) {
		$conditions[$sigle] = "id_plateau=".$id_plateau;
		$derouleConditions[$sigle] = $sigle;
	}
	define('ID_09', "Id ^");					define('ID_90', "Id v");
	define('DESCRIPTION_az', "Description ^");	define('DESCRIPTION_za', "Description v");
	$tri = getVar('tri', ID_09);
	$tris = array(ID_09=>"idx", ID_90=>"idx desc", DESCRIPTION_az=>"description", DESCRIPTION_za=>"description desc" );
	$lignespage = getVar('lignespage', 20);
	$page = ( $lignespage=='infini' ? '1' : getVar('page', '1') );
	$liste = sqlSelect('menu_tit', $conditions[$condition], $tris[$tri]);
	$nbrLignes = count($liste);
	$nbrPages = (int)ceil($nbrLignes/$lignespage);
	if ($page>$nbrPages) {	$page = $nbrPages; }
	$listeAffichee = ( $lignespage=='infini' ? $liste : array_slice($liste, (($page-1)*$lignespage), $lignespage) );
	echo( commencerFormulaire('recherche') );
	echo( creerChampID('tri', $tri).creerChampID('lignespage', $lignespage).creerChampID('condition', $condition).creerChampID('numpage', $numpage) );
	//echo( creerChampID('chercheAuteur', $chercheAuteur).creerChampID('chercheTitre', $chercheTitre) );
	// Afficher les lignes par pages
	echo('<br />'.$nbrLignes.' lignes / lignes par page :&nbsp;');
	if ($action=="") {
		$listeNbLignes = array(5, 10, 15, 20, 30, 50, 100);
		foreach ($listeNbLignes as $nbr) {
			echo( $lignespage==$nbr ? $nbr : creerFormBouton('lignespage', $nbr) );
			echo( "&nbsp;" );
		}
		echo( $lignespage=='infini' ? '&infin;' : creerFormBouton('lignespage', '&infin;') );
	} else {
		echo('<i>gestion des pages désactivée pendant la modification</i>');
	}
	// Afficher les pages
	if ($lignespage!='infini') {
		if ($action=="") {
		echo('<br />Page :');
			for ($nbr=1 ; $nbr<=$nbrPages ; $nbr++) {
				echo( "&nbsp;" );
				echo( $page==$nbr ? $nbr : creerFormBouton('numpage', $nbr) );
			}
		}
	}
	$entetes = array();
	if ($action=="") {	// Entetes avec TRI
		$entetes[] = 'ID<br />'.creerFormBouton('tri', ID_09).' '.creerFormBouton('tri', ID_90);
		$entetes[] = 'Plateau<br />'.creerListeDeroulante('condition', $derouleConditions, $condition, true);
		$entetes[] = 'Description<br />'.creerFormBouton('tri', DESCRIPTION_az).' '.creerFormBouton('tri', DESCRIPTION_za);
	} else {	// Entete sans TRI (modification !)
		$entetes[] = 'ID';
		$entetes[] = 'Plateau ';
		$entetes[] = 'Description';
	}
	if ($action=="") {
		echo('<br />Commandes :&nbsp;');
		$param = '&lignespage='.$lignespage.'&condition='.$condition.'&page='.$page.'&tri='.$tri;
		echo( creerFormBouton('action', 'AJOUT') );
	}
	if (is_array($listeAffichee)) {
		echo('<table class="liste"><tr>');
		foreach ($entetes as $une) {	echo('<th>'.$une.'</th>'); }
		echo('</tr>');
		if ( ($action!='') and ($id==0) ) {	// AJOUT d'une traduction, en haut du tableau
			echo( creerChampID('id', $id) );
			echo('<tr><td>');
			echo( creerFormBouton('action', 'Valider') );
			echo('</td><td>');
			$valeurs = array_merge(array('0'=>"Non défini (tous plateaux)"), $listePlateau);
			echo( creerListeDeroulante('champIdPlateau', $valeurs, $champIdPlateau, FALSE) );
			echo('</td><td>');
			echo( creerChampTexte('champDescription', "", $champDescription, 80, FALSE) );
			echo('</td></tr>');
		}
		foreach ($listeAffichee as $une) {
			if ( ($action!='') and ($id==(int)$une['idx']) ) {	// Modifie l'ID 				
				echo('<tr><td>');
				echo( creerChampID('id', $id) );
				echo($id.'<br />'. creerFormBouton('action', 'Valider') );
				echo('</td><td>');
				$valeurs = array_merge(array('0'=>"Non défini (tous plateaux)"), $listePlateau);
				echo( creerListeDeroulante('champIdPlateau', $valeurs, $champIdPlateau, FALSE) );
				echo('</td><td>');
				echo( creerChampTexte('champDescription', "", $champDescription, 80, FALSE) );
				echo('</td></tr>');
			} else {	// Ne modifie pas, donc affiche
				echo('<tr><td>');
				if ($action=="") {	// affiche leS boutonS MAJ seulement si pas déjà en MAJ
					echo( creerFormBouton('action', 'MAJ-'.$une['idx']) );							// Modification du TITRE du menu
					echo( creerLienBouton('Modifier le contenu !', 'menu.php?menu='.$une['idx']) );	// Modification des OPTIONS du menu
				} else {
					echo($une['idx']);
				}
				echo('</td><td>');
				echo($listePlateau[$une['id_plateau']]);
				echo('</td><td>');
				echo($une['description']);
				echo('</td></tr>');
			}
		}
		echo('</table>');
	} else {
		echo('<p>Pas d\'élément à afficher</p>');
	}
?>
    </section>
<!--		<div class="ecran">contenu de mon &eacutecran</div> -->
</article>

<?php
require_once($Root.'pied.inc.php');
?>