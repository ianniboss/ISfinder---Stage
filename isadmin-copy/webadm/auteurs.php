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
    	<h3> Gestion des auteurs </h3>	
<?php
	$action = getVar('action', '');
	$id = (int)getVar('id', '0');
	if (substr($action, 0, 3)=="MAJ") {	// Modification !
		$id = (int)(substr($action, 4));
		$action = "MAJ";
	}
	if (in_array($action, array('AJOUT', 'MAJ', 'Valider'))) {	// Formulaire doit/vient d'être affiché, récupère valeurs
		$defaut = array('idx'=>0, 'nom'=>'', 'lien'=>'');
		if ($id>0) {	// vérifie si id existe !
			$defaut=sqlTrouve('auteurs', 'idx='.$id);
			if (!is_array($defaut)) {	$id = 0; }	// id introuvable
		}
		//$champIdPlateau =	getVar('champIdPlateau', $defaut['id_plateau']);
		//if (is_null($champIdPlateau)) {	$champIdPlateau = 0; }
		$champNom =		getVar('champNom', $defaut['nom']);
		$champLien =	getVar('champLien', $defaut['lien']);
		if ($action=='Valider') {	// Validation demandée (données formulaire à récupérer et mise à jour
			$messErr = array();
			if ($champNom=='') {	// Nom ne peut pas être vide
				$messErr[] = 'Le nom ne peut pas être vide';
			} elseif ($id==0) {	// Vérifie si mot clé existe déjà, uniquemet si ajout
				$autre = sqlTrouve('auteurs', 'nom="'.$champNom.'"');
				if (is_array($autre)) {	// Autre mot clé existe sur ce plateau
					$messErr[] = 'Le nom indiqué est déjà utilisé '.creerLienBouton('(ID='.$autre['idx'].')', '?action=MAJ&id='.$autre['idx']);
				}
			}
			if (count($messErr)>0) {	// Erreurs, les affiche
				echo('<em>Il y a des erreurs</em><ul>');
				foreach ($messErr as $err) {	echo('<li>'.$err.'</li>'); }
				echo('</ul>');
			} else {	// Pas d'erreur : enregistre
				if ($champLien=='') {	// Construit le lien s'il est vide
					list($nm, $prnm) = explode(" ", texteDepuisHTML($champNom));
					$nom = '';		foreach(explode("-", $nm) as $un) {		$nom.= ($nom!=''?'-':'').ucfirst($nm); }
					$prenom = '';	foreach(explode("-", $prnm) as $un) {	$prenom.= ($prenom!=''?'.':'').strtoupper(substr($prnm, 0, 1)); }
					$champLien = 'http://www.ncbi.nlm.nih.gov/pubmed?term='.$nom.'%20'.$prenom.'%5BAuthor%5D';
				}
				// Enregistre l'auteur !
				$donnees = array('nom'=>texteVersHTML($champNom), 'lien'=>$champLien);
				if ($id==0) {	// Ajout d'un élément
					$id = sqlInsert('auteurs', $donnees);
					if ( (is_int($id)) and ($id>0) ) {	echo('<em>Enregistré !</em>'); }
				} else {
					if ( sqlUpdate('auteurs', 'idx='.$id, $donnees)==1 ) {	echo('<em>Enregistré !</em>'); }
				}
				$action = '';
			}
		}
	} else {	// Si $action n'est pas reconnue, on l'ignore !
		$action = '';
	}
	
/*	$liste = sqlSelect('plateau');
	foreach ($liste as $un) {	$listePlateau[$un['idx']] = $un['sigle']; }
	$condition = getVar('condition', 'tous');
	$conditions = array('indefini'=>"id_plateau is null", 'tous'=>"id_plateau is not null");
	foreach ($listePlateau as $idx=>$sigle) {	$conditions[$sigle] = "id_plateau=".$idx; }*/
	define('ID_09', "Id ^");			define('ID_90', "Id v");
	define('NOM_az', "Nom ^");			define('NOM_za', "Nom v");
	define('LIEN_az', "Lien ^");		define('LIEN_za', "Lien v");
	$tri = getVar('tri', ID_09);
	$tris = array(ID_09=>"idx", ID_90=>"idx desc",
				NOM_az=>"nom", NOM_za=>"nom desc",
				LIEN_az=>"lien", LIEN_za=>"lien desc" );
	$lignespage = getVar('lignespage', 20);
	$page = ( $lignespage=='infini' ? '1' : getVar('page', '1') );
	$liste = sqlSelect('auteurs', '', $tris[$tri]);
	$nbrLignes = count($liste);
	$nbrPages = (int)ceil($nbrLignes/$lignespage);
	if ($page>$nbrPages) {	$page = $nbrPages; }
	$listeAffichee = ( $lignespage=='infini' ? $liste : array_slice($liste, (($page-1)*$lignespage), $lignespage) );
	echo( commencerFormulaire('recherche') );
	echo( creerChampID('tri', $tri).creerChampID('lignespage', $lignespage).creerChampID('condition', $condition).creerChampID('numpage', $numpage) );
//	echo( creerChampID('chercheAuteur', $chercheAuteur).creerChampID('chercheTitre', $chercheTitre) );
	// Afficher les filtres
/*	echo('Filtres :&nbsp;');
	if ($action=="") {
		echo( $condition=='indefini' ? 'Indéfini' : creerFormBouton('condition', 'indefini') );
		echo( "&nbsp;" );
		foreach ($listePlateau as $idx=>$sigle) {
			echo( $condition==$sigle ? $sigle : creerFormBouton('condition', $sigle) );
			echo( "&nbsp;" );
		}
	} else {
		echo('<i>désactivés pendant la modification</i>');
	}
	echo( $condition=='tous' ? 'Tous' : creerFormBouton('condition', 'tous') );*/
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
//		$param = '&lignespage='.$lignespage.'&condition='.$condition.'&page='.$page;
		$param = '&lignespage='.$lignespage.'&page='.$page;
		$entetes[] = 'ID<br />'.creerFormBouton('tri', ID_09).' '.creerFormBouton('tri', ID_90);
		$entetes[] = 'Nom<br />'.creerFormBouton('tri', NOM_az).' '.creerFormBouton('tri', NOM_za);
		$entetes[] = 'Lien<br />'.creerFormBouton('tri', LIEN_az).' '.creerFormBouton('tri', LIEN_za);
	} else {	// Entete sans TRI (modification !)
		$entetes[] = 'ID';
		$entetes[] = 'Nom';
		$entetes[] = 'Lien';
	}
	if ($action=="") {
		echo('<br />Commandes :&nbsp;');
//		$param = '&lignespage='.$lignespage.'&condition='.$condition.'&page='.$page.'&tri='.$tri;
		$param = '&lignespage='.$lignespage.'&page='.$page.'&tri='.$tri;
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
/*			$valeurs = array_merge(array('0'=>"Non défini (tous plateaux)"), $listePlateau);
			echo( creerListeDeroulante('champIdPlateau', $valeurs, $champIdPlateau, FALSE) );
			echo('</td><td>');*/
			echo(' <em>Respectez la syntaxe "NOM P." !</em><br /> ');
			echo( creerChampTexte('champNom', "", $champNom, 20, FALSE) );
			echo('</td><td>');
			echo(' <em>Le lien sera généré automatiquement s&acute;il reste vide, vous pourrez le corriger ensuite.</em><br /> ');
			echo( creerChampTexte('champLien', "", $champLien, 80, FALSE) );
			echo('</td></tr>');
		}
		foreach ($listeAffichee as $une) {
			if ( ($action!='') and ($id==(int)$une['idx']) ) {	// Modifie l'ID 				
				echo('<tr><td>');
				echo( creerChampID('id', $id) );
				echo($id.'<br />'. creerFormBouton('action', 'Valider') );
				echo('</td><td>');
/*				$valeurs = array_merge(array('0'=>"Non défini (tous plateaux)"), $listePlateau);
				echo( creerListeDeroulante('champIdPlateau', $valeurs, $champIdPlateau, FALSE) );
				echo('</td><td>');*/
				echo(' <em>Respectez la syntaxe "NOM P." !</em><br /> ');
				echo( creerChampTexte('champNom', "", $champNom, 20, FALSE) );
				echo('</td><td>');
				echo(' <em>Le lien sera généré automatiquement s&acute;il est vide.</em><br /> ');
				echo( creerChampTexte('champLien', "", $champLien, 80, FALSE) );
				echo('</td></tr>');
			} else {	// Ne modifie pas, donc affiche
				echo('<tr><td>');
				if ($action=="") {	// affiche le bouton MAJ seulement si pas déjà en MAJ
					echo( creerFormBouton('action', 'MAJ-'.$une['idx']) );
				} else {
					echo($une['idx']);
				}
				echo('</td><td>');
/*				echo($listePlateau[$une['id_plateau']]);
				echo('</td><td>');*/
				echo($une['nom']);
				echo('</td><td>');
				echo($une['lien']);
				if ($une['lien']!='') {	echo('&nbsp;'.creerLienBouton('Suivre...', $une['lien'], "Cliquer ici pour vérifier le lien dans un nouvel onglet !", FALSE) ); }
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