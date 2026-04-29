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
    	<h3> Modification d'un laboratoire </h3>	
<?php
	$action = getVar('action', '');
	$id = (int)getVar('id', 'AJOUT');
	if ($id=='AJOUT') {	$id = 0; }
	// Champs de la table labos
	$defaut = array('id_plateau'=>0, 'sigle'=>'', 'logo'=>'', 'intitule'=>'', 'code_unite'=>'', 'id_traduire_lien_externe'=>0);
	if ($id>0) {	// vérifie si id existe !
		$defaut=sqlTrouve('labos', 'id_labo='.$id);
		if (!is_array($defaut)) {	$id = 0; }	// id introuvable
	}
	$champSigle =		getVar('champSigle', texteDepuisHTML($defaut['sigle']));
	$champLogo =		getFichier('champLogo', 'logo_'.$champSigle, '', TYPE_FICHIER_IMAGE, 'upload', TRUE);	var_dump($champLogo);
	if ( is_numeric($champLogo) ) {	$champLogo = $defaut['logo']; }
	elseif (is_string($champLogo)) {	// Copie ensuite le fichier sur LUSTOU !
		ftpCopieFichier('upload/'.$champLogo, 'lustou.ibcg.biotoul.fr', 'webadmin', '');
	}
	$champIntitule =	getVar('champIntitule', texteDepuisHTML($defaut['intitule']));
	$champCodeUnite =	getVar('champCodeUnite', texteDepuisHTML($defaut['code_unite']));
	$champIdLien =		getVar('champIdLien', $defaut['id_traduire_lien_externe']);
	// Champs de la table Traduire (liens externes)
	if ( ($id>0) and ($champIdLien>0) ) {	// Il y a une traduction pour le lien
		$infoLien = sqlTrouve('traduction', 'id_traduction='.$champIdLien);
		$defautLien = array('mot_fr'=>$infoLien['mot_fr'], 'mot_en'=>$infoLien['mot_en']);
	} else {	// Pas de traduction pour le lien
		$defautLien = array('mot_fr'=>'', 'mot_en'=>'');
	}
	$champLienFR =		getVar('champLienFR', texteDepuisHTML($defautLien['mot_fr']));
	$champLienEN =		getVar('champLienEN', texteDepuisHTML($defautLien['mot_en']));
	if ($action=='Valider') {	// Validation demandée (données formulaire à récupérer et mise à jour
		// Gère d'abord le lien externe !
		$donneesLien = array('id_plateau'=>NULL, 'mot_fr'=>$champLienFR, 'mot_en'=>$champLienEN);
		if ($champIdLien==0) {	// Ajout
			$donneesLien['mot_cle'] = 'lien_'.strtolower($champSigle);
			$champIdLien = sqlInsert('traduction', $donneesLien);
			if ( (is_int($champIdLien)) and ($champIdLien>0) ) {	echo('<em>Lien ajoutée,</em> '); }
		} else {	// Modification
			if ( sqlUpdate('traduction', 'id_traduction='.$champIdLien, $donneesLien)==1 ) {	echo('<em>Lien enregistré,</em> '); }
			$action = '';
		}
		$donnees = array('sigle'=>texteVersHTML($champSigle), 'logo'=>texteVersHTML($champLogo), 'intitule'=>texteVersHTML($champIntitule),
					'code_unite'=>texteVersHTML($champCodeUnite), 'id_traduire_lien_externe'=>($champIdLien>0?$champIdLien:NULL));
		if ($id==0) {	// Ajout d'un élément
			$id = sqlInsert('labos', $donnees);
			if ( (is_int($id)) and ($id>0) ) {	echo('<em>Laboratoire ajouté !</em>'); }
			$action = '';
		} else {	// Modification d'une publi existante
			if ( sqlUpdate('labos', 'id_labo='.$id, $donnees)==1 ) {	echo('<em>Laboratoire enregistré !</em>'); }
			$action = '';
		}
	}
	
		// 	$defaut = array('idx'=>0, 'id_plateau'=>0, 'auteurs'=>'', 'annee'=>'', 'titre'=>'', 'ref_journal'=>'', 'lien_externe'=>'', 'id_labo'=>0);
	echo( commencerFormulaire('Ajouter') );
	echo( creerChampID('id', $id) );
	echo('<table class="liste">');
		echo('<tr><th> ID </th><td> ');
			echo( ($id==0 ? 'NOUVEAU !' : $id ).creerChampID('id', $id) );
		echo(' </td></tr>');
		echo('<tr><th> Sigle </th><td> ');
			echo( creerChampTexte('champSigle', "", $champSigle, 10, FALSE) );
		echo(' </td></tr>');
		echo('<tr><th> Logo </th><td> ');
			echo( creerChampFichier('champLogo', 'upload/'.$champLogo, 50) );
		echo(' </td></tr>');
		echo('<tr><th> Intitulé </th><td> ');
			echo( creerChampTexte('champIntitule', "", $champIntitule, 80, FALSE) );
		echo(' </td></tr>');
		echo('<tr><th> Code Unité </th><td> ');
			echo( creerChampTexte('champCodeUnite', "", $champCodeUnite, 10, FALSE) );
		echo(' </td></tr>');
		echo('<tr><th> Lien externe français </th><td> ');
			echo( creerChampID('champIdLien', $champIdLien).creerChampTexte('champLienFR', "", $champLienFR, 80, FALSE) );
		echo(' </td></tr>');
		echo('<tr><th> Lien externe anglais </th><td> ');
			echo( creerChampTexte('champLienEN', "", $champLienEN, 80, FALSE) );
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