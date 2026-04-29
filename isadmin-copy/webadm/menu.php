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
    	<h3> Gestion des items d'un menu </h3>	
<?php
	$action = getVar('action', '');
	$id = (int)getVar('id', 0);
	$id_traduction = (int)getVar('id_traduction', 0);
	$menuDemande = (int)getVar('menu', 1);
	if (substr($action, 0, 3)=="MAJ") {	// Modification !
		$id = (int)(substr($action, 4));
		$action = "MAJ";
	}
	$champIdMenu =		getVar('champIdMenu', $defaut['id_menu']);		if (is_null($champIdMenu)) {	$champIdMenu = $menuDemande; }	// Utilisé pour déplacer vers un autre menu !
	$infoMenuTit = sqlTrouve('menu_tit', 'idx='.$champIdMenu);	var_dump($action);	var_dump($champIdMenu);
	if (!is_array($infoMenuTit)) {	// Menu inconnu, ne fait rien d'autre !
		echo('<h4> inconnu </h4>');
		echo('<a href="menus.php" target="_self">Retour à la liste</a>');
	} else {
		$champIDDeplaceMenu = getVar('champIDDeplaceMenu', 0);
		$champIDCopieMenu = getVar('champIDCopieMenu', 0);
		if ($champIDDeplaceMenu!=0) {	// Gère déplacement d'un menu
			$infoNouveauMenuTit = sqlSelect('menu_tit', 'idx='.$champIDDeplaceMenu);
			if (is_array($infoNouveauMenuTit)) {	// Nouveau menu existe
				$champIdMenu = $champIDDeplaceMenu;		// L'ID menu est modifié
				$champIdParent = 0;						// Le parent est effacé (sinon incohérence !)
				$infoMenuTit = $infoNouveauMenuTit;		// Les infos du menu sont remplacées
			} else {	$champIDDeplaceMenu = 0; }	// Nouveau menu n'existe pas, annule la demande
		} elseif ($champIDCopieMenu!=0) {	// Gère copie vers un autre menu
			$infoNouveauMenuTit = sqlSelect('menu_tit', 'idx='.$champIDCopieMenu);
			if (is_array($infoNouveauMenuTit)) {	// Nouveau menu existe
				$champIdMenu = $champIDCopieMenu;		// L'ID menu est modifié
				$champIdParent = 0;						// Le parent est effacé (sinon incohérence !)
				$id = 0;					// EN PLUS,	on crée un nouvel item dans l'autre menu !
				$infoMenuTit = $infoNouveauMenuTit;		// Les infos du menu sont remplacées
			} else {	$champIDCopieMenu = 0; }	// Nouveau menu n'existe pas, annule la demande
		}
		echo('<h4> '.$infoMenuTit['description'].' </h4>');
		if (in_array($action, array('AJOUT', 'MAJ', 'Valider', 'Déplacer', 'Copier'))) {	// Formulaire doit/vient d'être affiché, récupère valeurs
			$defaut = array('idx'=>0, 'id_menu'=>1, 'ordre'=>0, 'page'=>'', 'mot'=>'', 'id_parent'=>0);
			if ($id>0) {	// vérifie si id existe !
				$defaut=sqlTrouve('menu_opt', 'idx='.$id);
				if (!is_array($defaut)) {	// id introuvable
					$id = 0;
				} else {	// id trouvé, lis l'id_traduction correspondant au mot
					$infoTrad = sqlTrouve('traduction', 'mot_cle="'.$champMot.'"');
					if (is_array($infoTrad)) {	$id_traduction = $infoTrad['id_traduction']; }
				}
			}
			if ( (in_array($action, array('Déplacer', 'Copier'))) and ($id==0) ) {	$action = ''; }	// Déplacement OU copie demandée, mais ID pas indiqué : annule déplacement ou copie !
			$champOrdre =		(int)getVar('champOrdre', $defaut['ordre']);
			$champPage =		getVar('champPage', texteDepuisHTML($defaut['page']));
			$champMot =			( $id_traduction==0 ? getVar('champMot', texteDepuisHTML($defaut['mot'])) : $infoTrad['mot_cle'] );	// Ne lis champMot que si id_traduction existe pas !
			$champIdParent =	getVar('champIdParent', $defaut['id_parent']);	if (is_null($champIdParent)) {	$champIdParent = 0; }
			// Ajoute champs FR et EN de traduction du texte
			$defautTrad = array('mot_cle'=>'', 'mot_fr'=>'', 'mot_en'=>'');
			if ($champMot!="") {	// vérifie si traduction existe !
				$defautTrad=sqlTrouve('traduction', 'mot_cle="'.$champMot.'"');
				if (!is_array($defautTrad)) {	$champMot = ""; }	// id introuvable
			}
			$champMotFR =		getVar('champMotFR', texteDepuisHTML($defautTrad['mot_fr']));
			$champMotEN =		getVar('champMotEN', texteDepuisHTML($defautTrad['mot_en']));
			if (in_array($action, array('Valider', 'Déplacer', 'Copier'))) {	// Validation demandée (données formulaire à récupérer et mise à jour
				// Détermine l'id_plateau pour la traduction, en fonction de celui du menu_tit dans lequel est menu_opt
				$infoMenuTit = sqlTrouve('menu_tit', 'idx='.$champIdMenu);
				if (is_array($infoMenuTit)) {	$id_plateau = $infoMenuTit['id_plateau']; }
				$donneesTrad = array('id_plateau'=>($id_plateau>0?$id_plateau:NULL), 'mot_fr'=>texteVersHTML($champMotFR), 'mot_en'=>texteVersHTML($champMotEN) );
				// D'abord on ajoute ou modifie la traduction !
				if ($id_traduction==0) {	// Ajout d'une traduction
					if ($champMot=="") {	$champMot = versMotCle($champMotFR); }	// Si le mot clé de traduction est vide, le crée à partir du mot français
					if ($champMot=="") {	$champMot = "menu_id_".$id; }			// S'il est toujours vide (mot français sans lettre !), lui donne une valeur qui a toutes les chances d'être unique
					$donneesTrad['mot_cle'] = $champMot;
					$id_traduction = sqlInsert('traduction', $donneesTrad);
					if ( (is_int($id_traduction)) and ($id_traduction>0) ) {	echo('<em>Enregistré !</em>'); }
				} else {	// Modifie la traduction (pas le mot clé !)
					if ( sqlUpdate('traduction', 'id_traduction='.$id_traduction, $donneesTrad)==1 ) {	echo('<em>Enregistré !</em>'); }
				}
				// Ensuite on ajoute ou modifie l'option de menu
				$donnees = array('id_menu'=>$champIdMenu, 'ordre'=>$champOrdre, 'page'=>texteVersHTML($champPage), 'mot'=>texteVersHTML($champMot), 'id_parent'=>($champIdParent>0?$champIdParent:NULL) );
				if ($id==0) {	// Ajout d'un élément
					$id = sqlInsert('menu_opt', $donnees);
					if ( (is_int($id)) and ($id>0) ) {	echo('<em>Enregistré !</em>'); }
				} else {
					if ( sqlUpdate('menu_opt', 'idx='.$id, $donnees)==1 ) {	echo('<em>Enregistré !</em>'); }
				}
				if ($action=='Valider') {	$action = ''; }	// On ne supprime $action QUE si l'enregistrement est fait !
			}
		} else {	// Si $action n'est pas reconnue, on l'ignore !
			$action = '';
		}
		// Liste les menus possibles (ceux différents du menu affiché !)
		$listeMenusPossibles = array();
		$listeMenus = sqlSelect('menu_tit', 'menu_tit.idx<>'.$champIdMenu);
		foreach ($listeMenus as $unMenu) {	$listeMenusPossibles[$unMenu['idx']] = $unMenu['description']; }
		// Liste les plateaux
		$liste = sqlSelect('plateau');
		$listePlateau = array();
		foreach ($liste as $un) {	$listePlateau[$un['idx']] = $un['sigle']; }
		// Liste les ID_parents possibles (ID des items du menu, sauf l'item modifié !)
		$liste = sqlRequete('SELECT menu_opt.idx as idx, traduction.mot_fr as mot_fr FROM menu_opt LEFT OUTER JOIN traduction ON menu_opt.mot=traduction.mot_cle WHERE id_menu='.$champIdMenu.' AND idx<>'.$id.' ORDER BY traduction.mot_fr');
		$listeParentsPossibles = array('0'=>'Racine');
		foreach ($liste as $un) {	$listeParentsPossibles[$un['idx']] = $un['idx'].' - '.$un['mot_fr']; }
		// Construction de la requete pour faire la liste des items
		$requeteDebut = 'SELECT menu_opt.idx as idx, menu_opt.id_menu as id_menu, menu_opt.id_parent as id_parent, menu_opt.ordre as ordre,';
		$requeteDebut.= ' menu_opt.page as page, menu_opt.mot as mot, traduction.mot_fr as mot_fr, traduction.mot_en as mot_en';
		$requeteDebut.= ' FROM menu_opt LEFT OUTER JOIN traduction ON menu_opt.mot=traduction.mot_cle';
		$requeteDebut.= ' WHERE id_menu='.$champIdMenu.' AND id_parent';
		$requeteFin = ' ORDER BY ordre';
		var_dump($requeteDebut); var_dump($champIdMenu); var_dump($requeteFin);
		$liste = sqlRequete($requeteDebut.' IS NULL'.$requeteFin);
		$listeAffichee = array();
		foreach ($liste as $un) {	// Parcours la liste pour ajouter sous_menus dans le bon ordre, va jusqu'à une profondeur de 4 niveaux "seulement"
			$listeAffichee[] = $un;
			$sous_menu = sqlSelect('menu_opt', 'id_parent='.$un['idx'], 'ordre');
			if (is_array($sous_menu)) {
				foreach($sous_menu as $deux) {
					$listeAffichee[] = $deux;
					$sous_menu2 = sqlSelect('menu_opt', 'id_parent='.$deux['idx'], 'ordre');
					if (is_array($sous_menu2)) {
						foreach($sous_menu2 as $trois) {
							$listeAffichee[] = $trois;
							$sous_menu3 = sqlSelect('menu_opt', 'id_parent='.$trois['idx'], 'ordre');
							if (is_array($sous_menu3)) {
								foreach($sous_menu3 as $quatre) {
									$listeAffichee[] = $quatre;
								}
							}
						}
					}
				}
			}
		}
		echo( commencerFormulaire('recherche') );
		echo( creerChampID('condition', $condition).creerChampID('champIdMenu', $champIdMenu) );	var_dump($champIdMenu);
		$entetes = array('ID', 'ID Parent', 'Ordre', 'Page', 'Mot clé', 'Français', 'Anglais');
		if ($action=="") {
			echo('<br />Commandes :&nbsp;'. creerFormBouton('action', 'AJOUT') );
		}
		if (is_array($listeAffichee)) {
			echo('<table class="liste"><tr>');
			foreach ($entetes as $une) {	echo('<th>'.$une.'</th>'); }
			echo('</tr>');
			if ( ($action!='') and ($id==0) ) {	// AJOUT d'un item, en haut du tableau
				echo( creerChampID('id', $id) );
				echo('<tr><td>');
				echo( creerFormBouton('action', 'Valider') );
				echo('</td><td>');
				if (count($listeParentsPossibles)>1) {
					echo( creerListeDeroulante('champIdParent', $listeParentsPossibles, $champIdParent, FALSE) );
				} else {
					echo( 'RACINE'. creerChampID('champIdParent', '0') );
				}
				echo('</td><td>');
				echo( creerChampTexte('champOrdre', "Indiquez l'ordre voulu pour intercaller", $champOrdre, 10, FALSE) );
				echo('</td><td>');
				echo( creerChampTexte('champPage', "Laisser vide pour qu'il n'y ait pas de lien sur cet item", $champPage, 40, FALSE) );
				echo('</td><td>');
				echo( creerChampTexte('champMot', "", $champMot, 40, FALSE) );
				echo('</td><td>');
				echo( creerChampTexte('champMotFR', "", $champMotFR, 40, FALSE) );
				echo('</td><td>');
				echo( creerChampTexte('champMotEN', "", $champMotEN, 40, FALSE) );
				echo('</td></tr>');
			}
			foreach ($listeAffichee as $une) {
				if ( ($action!='') and ($id==(int)$une['idx']) ) {	// Modifie l'ID 				
					echo('<tr><td>');
					echo( creerChampID('id', $id).creerChampID('id_traduction', $id_traduction).creerChampID('champMot', $champMot) );
					echo($id.'<br />'. creerFormBouton('action', 'Valider') );
					echo('</td><td>');
					if (count($listeParentsPossibles)>1) {
						echo( creerListeDeroulante('champIdParent', $listeParentsPossibles, $champIdParent, FALSE) );
					} else {
						echo( 'RACINE'. creerChampID('champIdParent', '0') );
					}
					echo('</td><td>');
					echo( creerChampTexte('champOrdre', "Indiquez l'ordre voulu pour intercaller", $champOrdre, 10, FALSE) );
					echo('</td><td>');
					echo( creerChampTexte('champPage', "Laisser vide pour qu'il n'y ait pas de lien sur cet item", $champPage, 40, FALSE) );
					echo('</td><td>');
					echo( creerChampTexte('champMot', "", $champMot, 40, FALSE) );
					echo('</td><td>');
					echo( creerChampTexte('champMotFR', "", $champMotFR, 40, FALSE) );
					echo('</td><td>');
					echo( creerChampTexte('champMotEN', "", $champMotEN, 40, FALSE) );
					echo('</td></tr>');
				} else {	// Ne modifie pas, donc affiche, propose Déplacer et Copier
					echo('<tr><td>');
					if ($action=='') {	// affiche leS boutonS MAJ seulement si pas déjà en MAJ
						echo( creerFormBouton('action', 'MAJ-'.$une['idx']) );							// Modification de l'item
						echo( creerFormBouton('action', 'Déplacer') );									// Déplace vers autre menu
						echo( creerFormBouton('action', 'Copier') );									// Copie vers autre menu
					} elseif ($action=='Déplacer') {	// Liste déroulante des menus cible pour déplacement
						echo( 'Déplacer vers '. creerListeDeroulante('champIDDeplaceMenu', $listeMenusPossibles, $champIDMenu, FALSE) );
					} elseif ($action=='Copier') {		// Liste déroulante des menus cible pour copie
						echo( 'Copier vers '. creerListeDeroulante('champIDCopieMenu',  array_merge(array($champIdMenu=>"Dupliquer la ligne !"), $listeMenusPossibles), $champIDMenu, FALSE) );
					} else {
						echo($une['idx']);
					}
					echo('</td><td>');
					echo( (($une['id_parent']==0)?'RACINE':$une['id_parent']) );
					echo('</td><td>');
					echo($une['ordre']);
					echo('</td><td>');
					echo($une['page']);
					echo('</td><td>');
					echo($une['mot']);
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
	}
?>
    </section>
<!--		<div class="ecran">contenu de mon &eacutecran</div> -->
</article>

<?php
require_once($Root.'pied.inc.php');
?>