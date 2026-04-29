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
    	<h3> Modification d'une publication </h3>	
<?php
	$listePlateau = array('0'=>"Non défini (tous plateaux)");
	foreach (sqlSelect('plateau', '', 'sigle') as $un) {	$listePlateau[$un['idx']] = $un['sigle']; }
	$listeLabo = array(array('0'=>"Non défini"));
	foreach (sqlSelect('labos', '', 'sigle') as $un) {		$listeLabo[$un['id_labo']] = $un['sigle'].' ('.$un['code_unite'].' ; ID '.$un['id_labo'].')'; }
	$listeAnnees = array();
	for ($an=(date("Y")-7) ; $an<=date("Y") ; $an++) {		$listeAnnees[$an] = $an; }
	$action = getVar('action', '');
	$id = (int)getVar('id', 'AJOUT');
	if ($id=='AJOUT') {	$id = 0; }
	$defaut = array('idx'=>0, 'id_plateau'=>0, 'auteurs'=>'', 'annee'=>'', 'titre'=>'', 'ref_journal'=>'', 'lien_externe'=>'', 'id_labo'=>0);
	if ($id>0) {	// vérifie si id existe !
		$defaut=sqlTrouve('publications', 'idx='.$id);
		if (!is_array($defaut)) {	$id = 0; }	// id introuvable
	}
	// Champs de la table publications
	$champIdPlateau =		getVar('champIdPlateau', $defaut['id_plateau']);
	if (is_null($champIdPlateau)) {	$champIdPlateau = 0; }
	$champAnnee =			getVar('champAnnee', $defaut['annee']);
	$champTitre =			getVar('champTitre', texteDepuisHTML($defaut['titre']));
	$champRefJournal =		getVar('champRefJournal', $defaut['ref_journal']);
	$champLienExterne =		getVar('champLienExterne', $defaut['lien_externe']);
	$champIdLabo =			getVar('champIdLabo', $defaut['id_labo']);
	if (is_null($champIdLabo)) {	$champIdLabo = 0; } 
	$champIdAuteur =		( $id>0 ? (int)(getVar('champIdAuteur', 0)) : 0 );	// Peut prendre les valeurs 0 pour rien, 'ajout', ou un nombre>0, reste à 0 pour ne rien faire si la publi est nouvelle
	// Gestion des auteurs
	if ($id>0) {	// Uniquement s'il y a un ID
		// Lis la liste des auteurs liés à la publi
		$requeteAuteursPubli = 'SELECT ln_auteur_publi.id_auteur as idx, auteurs.nom as nom, ln_auteur_publi.ordre as ordre';
		$requeteAuteursPubli.= ' FROM ln_auteur_publi LEFT OUTER JOIN auteurs ON ln_auteur_publi.id_auteur=auteurs.idx';
		$requeteAuteursPubli.= ' WHERE id_publi='.$id.' ORDER BY ln_auteur_publi.ordre';
		$liste = sqlRequete($requeteAuteursPubli);
		$listeAuteursPubli = array();
		foreach ($liste as $un) {	$listeAuteursPubli[$un['idx']] = $un; }
		// Champs de l'auteur ajouté : ignore les champs si la publi est nouvelle
		$champNouvelAuteurNom =		getVar('champNouvelAuteurNom', '');
		$champNouvelAuteurLien =	getVar('champNouvelAuteurLien', '');
		if ($champIdAuteur=='ajout') {	// Ajout d'un auteur !
			$champIdAuteur = 0;
			if ($champNouvelAuteurNom!='') {	// Bosse seulement si le nom n'est pas vide
				$auteurExiste = sqlTrouve('auteurs', 'nom="'.$champNouvelAuteurNom.'"');
				if (is_array($auteurExiste)) {	// L'auteur existe déjà, récupère son id !
					$champIdAuteur = $auteurExiste['idx'];
					echo('<br /><em>L&acute;auteur existait déjà !</em>');
				} else {	// L'auteur n'existe pas encore, continue l'ajout
					if ($champNouvelAuteurLien=='') {	// Construit le lien s'il est vide
						list($nm, $prnm) = explode(" ", texteDepuisHTML($champNouvelAuteurNom));
						$nom = '';		foreach(explode("-", $nm) as $un) {		$nom.= ($nom!=''?'-':'').ucfirst($nm); }
						$prenom = '';	foreach(explode("-", $prnm) as $un) {	$prenom.= ($prenom!=''?'.':'').strtoupper(substr($prnm, 0, 1)); }
						$champNouvelAuteurLien = 'http://www.ncbi.nlm.nih.gov/pubmed?term='.$nom.'%20'.$prenom.'%5BAuthor%5D';
					}
					// Enregistre l'auteur !
					$donneesAuteur = array('nom'=>texteVersHTML($champNouvelAuteurNom), 'lien'=>$champNouvelAuteurLien);
					$champIdAuteur = sqlInsert('auteurs', $donneesAuteur);		// $champIdAuteur>0 indique qu'il faut insérer l'auteur !
					if ( (is_numeric($champIdAuteur)) and ($champIdAuteur>0) ) {	echo('<br /><em>Auteur enregistré !</em>'); }
				}
				$champNouvelAuteurNom = '';
				$champNouvelAuteurLien = '';
			}
		}
		if ( (is_numeric($champIdAuteur)) and ($champIdAuteur>0) ) {	// Ajout d'un auteur existant forcément à la fin
			if (array_key_exists($champIdAuteur,$listeAuteursPubli)) {	// Auteur déjà présent sur la publi, n'en tiens pas compte
				echo('<br /><em>L&acute;auteur est déjà lié à la publication !</em>');
			} else {
				$resultat = sqlInsert('ln_auteur_publi', array('id_auteur'=>$champIdAuteur, 'id_publi'=>$id, 'ordre'=>(count($listeAuteursPubli)+1)));
				if ( (is_numeric($resultat)) and ($resultat>0) ) {	echo('<br /><em>Auteur ajouté à la liste !</em>'); }
			}
		}
		if ( (substr($action, 0, 8)=='Permuter') and ($id>0) ) {	// action = "Permuter 5" : permute les auteurs d'ordre 5 et 6 ; uniquement si on est pas sur une nouvelle publi
			$numero = (int)substr($action, 9);	// numéro à Permuter avec le suivant !
			$infoLien1 = sqlTrouve('ln_auteur_publi', 'id_publi='.$id.' and ordre='.$numero);
			$infoLien2 = sqlTrouve('ln_auteur_publi', 'id_publi='.$id.' and ordre='.($numero+1));
			if ( (is_array($infoLien1)) and (is_array($infoLien2)) ) {	// Continue seulement si les liens existent
				$resultat1 = sqlUpdate('ln_auteur_publi', 'id_auteur='.$infoLien1['id_auteur'].' and id_publi='.$id, array('ordre'=>($numero+1)));
				$resultat2 = sqlUpdate('ln_auteur_publi', 'id_auteur='.$infoLien2['id_auteur'].' and id_publi='.$id, array('ordre'=>$numero));
				if ( ($resultat1==1) and ($resultat2==1) ) {	echo('<br /><em>Auteurs permutés !</em>'); }
			}
			$action = '';
		}
		if ( (substr($action, 0, 9)=='Supprimer') and ($id>0) ) {	// action = "Supprimer 5" : supprime l'auteur d'ordre 5 ; uniquement si on est pas sur une nouvelle publi
			$numero = (int)substr($action, 10);	// numéro à supprimer
			$resultat = sqlDelete('ln_auteur_publi', 'id_publi='.$id.' and ordre='.$numero);
			if ($resultat==1) {	echo('<br /><em>Auteur supprimé de la publication !</em>'); }
			$ordre = 1;
			foreach ($listeAuteursPubli as $idx=>$un) {	// Change les numéros d'ordre des auteurs restants
				$resultat = sqlUpdate('ln_auteur_publi', 'id_auteur='.$idx.' and id_publi='.$id, array('ordre'=>$ordre));
				$ordre+= 1;
			}
			$action = '';
		}
		// Relie la liste des auteurs liés à la publi !
		$liste = sqlRequete($requeteAuteursPubli);
		$listeAuteursPubli = array();
		foreach ($liste as $un) {	$listeAuteursPubli[$un['idx']] = $un; }
	}
	if ($action=='Valider') {	// Validation demandée (données formulaire à récupérer et mise à jour
		// Commence par construire la liste des auteurs telle qu'elle sera enregistrée dans la table Publi (pour recherches)
		$tousAuteurs = '';
		if (count($champListeAuteur)>0) {
			$liste = array();
			foreach ($listeAuteursPubli as $unId=>$un) {	$liste = $un['nom']; }
			$tousAuteurs = implode(', ', $liste);
		}
		$donnees = array('id_plateau'=>($champIdPlateau>0?$champIdPlateau:NULL), 'auteurs'=>$tousAuteurs, 'annee'=>(int)($champAnnee),
					'titre'=>texteVersHTML($champTitre), 'ref_journal'=>$champRefJournal, 'lien_externe'=>$champLienExterne,
					'id_labo'=>($champIdLabo>0?$champIdLabo:NULL));
		if ($id==0) {	// Ajout d'un élément
			$id = sqlInsert('publications', $donnees);
			if ( (is_int($id)) and ($id>0) ) {	echo('<em>Publication ajoutée !</em>'); }
			$action = '';
		} else {	// Modification d'une publi existante
			if ( sqlUpdate('publications', 'idx='.$id, $donnees)==1 ) {	echo('<em>Publication enregistrée !</em>'); }
			$action = '';
		}
	}
	
		// 	$defaut = array('idx'=>0, 'id_plateau'=>0, 'auteurs'=>'', 'annee'=>'', 'titre'=>'', 'ref_journal'=>'', 'lien_externe'=>'', 'id_labo'=>0);
	echo( commencerFormulaire('Ajouter') );
	echo( creerChampID('id', $id) );
	echo('<table class="liste">');
		echo('<tr><th> ID </th><td> ');
			echo( ($id==0 ? 'NOUVEAU !' : $id ) );
		echo(' </td></tr>');
		echo('<tr><th> Plateau </th><td> ');
			echo( creerListeDeroulante('champIdPlateau', $listePlateau, $champIdPlateau, FALSE) );
		echo(' </td></tr>');
		echo('<tr><th> Auteurs </th><td> ');		////////// GESTION DIFFERENTES !!!!!
			if ($id>0) {	// N'affiche les champs que si on est sur une publi existante !)
				// Prend tous les auteurs
				$listeAuteurs = sqlSelect('auteurs', '', 'auteurs.nom');
				$listeAuteursHorsPubli = array('ajout'=>"(Ajouter l'auteur indiqué ci-dessous)");
				foreach($listeAuteurs as $un) {	// conserver les id_auteur HORS publi uniquement
					if (!array_key_exists($un['idx'], $listeAuteursPubli)) {	$listeAuteursHorsPubli[$un['idx']] = $un['nom']; }
				}
				foreach ($listeAHR as $un) {	$listeAuteursHorsPubli[$un['id_auteur']] = $un['nom']; }
				echo( 'Sélectionnez pour ajouter un auteur : '.creerListeDeroulante('champIdAuteur', $listeAuteursHorsPubli, $champIdAuteur, TRUE) );
				echo( '<br /><em>Nom</em> : '.creerChampTexte('champNouvelAuteurNom', "", $champNouvelAuteurNom, 30, FALSE, FALSE) );
				echo( '<br /><em>Lien</em> : '.creerChampTexte('champNouvelAuteurLien', "", $champNouvelAuteurLien, 30, FALSE, FALSE) );
				echo('<hr>Liste ordonnée des auteurs de cette publication :<ul>');
				$nbrAuteur = 0;
 				$champListeAuteur = "";
				foreach ($listeAuteursPubli as $unId=>$un) {
					echo('<li> '.$un['ordre'].' - '.$un['nom'].' (ID='.$unId.')&nbsp;-&nbsp;');
					$champListeAuteur.= ($champListeAuteur==""?"":", ").$un['nom'];
					$nbrAuteur+= 1;
					if ( $nbrAuteur<count($listeAuteursPubli) ) {	// Propose permutation sauf sur le dernier
						echo( creerFormBouton('action', 'Permuter-'.$un['ordre']).'&nbsp;-&nbsp;');
					}
					echo( creerFormBouton('action', 'Supprimer-'.$un['ordre']).' </li>');
				}
				echo('</ul>');
				echo("Voici la liste des auteurs, telle qu'elle sera utilisée pour la recherche dans les publications&nbsp;:<br /><i>".$champListeAuteur."</i>");
				echo( creerChampID('champListeAuteur', $champListeAuteur) );
			} else {
				echo('<i> La gestion des auteurs n&acute;est pas possible au moment de l&acute;ajout de la publication : enregistrez-la d&acute;abord&hellip; </i>');
			}
		echo(' </td></tr>');
		echo('<tr><th> Année </th><td> ');
			echo( creerListeDeroulante('champAnnee', $listeAnnees, $champAnnee, FALSE) );
		echo(' </td></tr>');
		echo('<tr><th> Titre </th><td> ');
			echo( creerChampTexteArea('champTitre', "", $champTitre, 80, 3, FALSE) );
		echo(' </td></tr>');
		echo('<tr><th> Référence Journal </th><td> ');
			echo( creerChampTexteArea('champRefJournal', "", $champRefJournal, 80, 1, FALSE) );
		echo(' </td></tr>');
		echo('<tr><th> Lien externe </th><td> ');
			echo( creerChampTexteArea('champLienExterne', "Indiquez uniquement le numéro PubMed, le reste du lien sera ajouté automatiquement !", $champLienExterne, 80, 1, FALSE) );
		echo(' </td></tr>');
		echo('<tr><th> Laboratoire </th><td> ');
			echo( creerListeDeroulante('champIdLabo', $listeLabo, $champIdLabo, FALSE) );
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