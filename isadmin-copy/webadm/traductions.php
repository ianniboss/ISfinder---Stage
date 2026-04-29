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
    	<h3> Gestion des traductions </h3>	
<?php
	$action = getVar('action', '');
	$id = (int)getVar('id', '0');
	if (substr($action, 0, 3)=="MAJ") {	// Modification !
		$id = (int)(substr($action, 4));
		$action = "MAJ";
	}
	if (in_array($action, array('AJOUT', 'MAJ', 'Valider'))) {	// Formulaire doit/vient d'être affiché, récupère valeurs
		$defaut = array('id_traduction'=>0, 'id_plateau'=>0, 'mot_cle'=>'', 'mot_fr'=>'', 'mot_en'=>'');
		if ($id>0) {	// vérifie si id existe !
			$defaut=sqlTrouve('traduction', 'id_traduction='.$id);
			if (!is_array($defaut)) {	// id introuvable
				$id = 0;
			}
		}
		$champIdPlateau =	getVar('champIdPlateau', $defaut['id_plateau']);
		if (is_null($champIdPlateau)) {	$champIdPlateau = 0; }
		$champMotCle =		getVar('champMotCle', $defaut['mot_cle']);
		$champMotFR =		getVar('champMotFR', $defaut['mot_fr']);
		$champMotEN =		getVar('champMotEN', $defaut['mot_en']);
		if ($action=='Valider') {	// Validation demandée (données formulaire à récupérer et mise à jour
			$messErr = array();
			if ($champMotCle=='') {	// Mot clé ne peut pas être vide
				$messErr[] = 'Le mot clé ne peut pas être vide';
			} elseif ($id==0) {	// Vérifie si mot clé existe déjà, uniquemet si ajout
				$autre = sqlTrouve('traduction', 'mot_cle="'.$champMotCle.'" and id_plateau'.(($champIdPlateau>0)?'='.$champIdPlateau:' is null'));
				if (is_array($autre)) {	// Autre mot clé existe sur ce plateau
					$messErr[] = 'Le mot clé indiqué est déjà utilisé pour le même plateau '.creerLienBouton('(ID='.$autre['id_traduction'].')', '?action=MAJ&id='.$autre['id_traduction']);
				}
			}
			if (count($messErr)>0) {	// Erreurs, les affiche
				echo('<em>Il y a des erreurs</em><ul>');
				foreach ($messErr as $err) {	echo('<li>'.$err.'</li>'); }
				echo('</ul>');
			} else {	// Pas d'erreur : enregistre
				$donnees = array('id_plateau'=>($champIdPlateau>0?$champIdPlateau:NULL), 'mot_fr'=>$champMotFR, 'mot_en'=>$champMotEN);
				if ($id==0) {	// Ajout d'un élément
					$donnees['mot_cle'] = $champMotCle;
					$id = sqlInsert('traduction', $donnees);
					if ( (is_int($id)) and ($id>0) ) {	echo('<em>Enregistré !</em>'); }
				} else {
					if ( sqlUpdate('traduction', 'id_traduction='.$id, $donnees)==1 ) {	echo('<em>Enregistré !</em>'); }
				}
				$action = '';
			}
		}
	} else {	// Si $action n'est pas reconnue, on l'ignore !
		$action = '';
	}
	
	$liste = sqlSelect('plateau');
	foreach ($liste as $un) {	$listePlateau[$un['idx']] = $un['sigle']; }
	$condition = getVar('condition', 'tous');
	$conditions = array('indefini'=>"id_plateau is null", 'tous'=>"id_plateau is not null");
	foreach ($listePlateau as $idx=>$sigle) {	$conditions[$sigle] = "id_plateau=".$idx; }
	define('ID_09', "Id ^");			define('ID_90', "Id v");
	define('MOT_az', "Mot ^");			define('MOT_za', "Mot v");
	define('FR_az', "FR ^");			define('FR_za', "FR v");
	define('EN_az', "EN ^");			define('EN_za', "EN v");
	$tri = getVar('tri', ID_09);
	$tris = array(ID_09=>"id_traduction", ID_90=>"id_traduction desc",
				MOT_az=>"mot_cle", MOT_za=>"mot_cle desc",
				FR_az=>"mot_fr", FR_za=>"mot_fr desc",
				EN_az=>"mot_en", EN_za=>"mot_en desc" );
	$lignespage = getVar('lignespage', 20);
	$page = ( $lignespage=='99999' ? '1' : getVar('numpage', '1') );
	$liste = sqlSelect('traduction', $conditions[$condition], $tris[$tri]);
	$nbrLignes = count($liste);
	$nbrPages = (int)ceil($nbrLignes/$lignespage);
	if ($page>$nbrPages) {	$page = $nbrPages; }
	$listeAffichee = ( ($lignespage==INFINI) ? $liste : array_slice($liste, (($page-1)*$lignespage), $lignespage) );
	echo( commencerFormulaire('recherche') );
	echo( creerChampID('tri', $tri).creerChampID('lignespage', $lignespage).creerChampID('condition', $condition).creerChampID('numpage', $page) );
	echo( creerChampID('chercheAuteur', $chercheAuteur).creerChampID('chercheTitre', $chercheTitre) );
	// Afficher les filtres
	echo('Filtres :&nbsp;');
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
	echo( $condition=='tous' ? 'Tous' : creerFormBouton('condition', 'tous') );
	// Afficher les lignes par pages
	echo('<br />'.$nbrLignes.' lignes / lignes par page :&nbsp;');
	if ($action=="") {
		$listeNbLignes = array(5, 10, 15, 20, 30, 50, 100);
		foreach ($listeNbLignes as $nbr) {
			echo( $lignespage==$nbr ? $nbr : creerFormBouton('lignespage', $nbr) );
			echo( "&nbsp;" );
		}
		echo( $lignespage=='99999' ? '99999' : creerFormBouton('lignespage', '99999') );
	} else {
		echo('<i>gestion des pages désactivée pendant la modification</i>');
	}
	// Afficher les pages
	if ($lignespage!='99999') {
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
		$param = '&lignespage='.$lignespage.'&condition='.$condition.'&page='.$page;
		$entetes[] = 'ID<br />'.creerFormBouton('tri', ID_09).' '.creerFormBouton('tri', ID_90);
		$entetes[] = 'Plateau ';
		$entetes[] = 'Mot clé<br />'.creerFormBouton('tri', MOT_az).' '.creerFormBouton('tri', MOT_za);
		$entetes[] = 'Mot français<br />'.creerFormBouton('tri', FR_az).' '.creerFormBouton('tri', FR_za);
		$entetes[] = 'Mot anglais<br />'.creerFormBouton('tri', EN_az).' '.creerFormBouton('tri', EN_za);
	} else {	// Entete sans TRI (modification !)
		$entetes[] = 'ID';
		$entetes[] = 'Plateau ';
		$entetes[] = 'Mot clé';
		$entetes[] = 'Mot français';
		$entetes[] = 'Mot anglais';
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
			echo( creerChampTexte('champMotCle', "Le mot clé permet d'identifier le texte dans le code HTML, il ne pourra pas être modifié par la suite !", $champMotCle, 20, FALSE) );
			echo('</td><td>');
			echo( creerChampTexte('champMotFR', "", $champMotFR, 20, FALSE) );
			echo('</td><td>');
			echo( creerChampTexte('champMotEN', "", $champMotEN, 20, FALSE) );
			echo('</td></tr>');
		}
		foreach ($listeAffichee as $une) {
			if ( ($action!='') and ($id==(int)$une['id_traduction']) ) {	// Modifie l'ID 				
				echo('<tr><td>');
				echo( creerChampID('id', $id) );
				echo($id.'<br />'. creerFormBouton('action', 'Valider') );
				echo('</td><td>');
				$valeurs = array_merge(array('0'=>"Non défini (tous plateaux)"), $listePlateau);
				echo( creerListeDeroulante('champIdPlateau', $valeurs, $champIdPlateau, FALSE) );
				echo('</td><td>');
				echo($une['mot_cle'] . creerChampID('champMotCle', $une['mot_cle']) );
				echo('</td><td>');
				echo( creerChampTexte('champMotFR', "", $champMotFR, 20, FALSE) );
				echo('</td><td>');
				echo( creerChampTexte('champMotEN', "", $champMotEN, 20, FALSE) );
				echo('</td></tr>');
			} else {	// Ne modifie pas, donc affiche
				echo('<tr><td>');
				if ($action=="") {	// affiche le bouton MAJ seulement si pas déjà en MAJ
					echo( creerFormBouton('action', 'MAJ-'.$une['id_traduction']) );
				} else {
					echo($une['id_traduction']);
				}
				echo('</td><td>');
				echo($listePlateau[$une['id_plateau']]);
				echo('</td><td>');
				echo($une['mot_cle']);
				echo('</td><td>');
				echo($une['mot_fr']);
				echo('</td><td>');
				echo($une['mot_en']);
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