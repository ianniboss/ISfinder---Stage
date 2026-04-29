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
    	<h3> Modification d'un contact </h3>	
<?php
	$listeFonctions = array("NULL"=>'Ne pas la préciser');
	$liste = sqlRequete('SELECT contact_fonction.idx AS idx, traduction.mot_fr AS mot_fr FROM contact_fonction LEFT OUTER JOIN traduction ON contact_fonction.description=traduction.mot_cle');
	foreach($liste as $un) {	$listeFonctions[$un['idx']] = $un['mot_fr']; }
	$liste = sqlSelect('plateau');
	$listePlateau = array();
	foreach ($liste as $un) {	$listePlateau[$un['idx']] = $un['sigle']; }
	$listeContactables = array(0=>"---", 1=>"OUI");
	$action = getVar('action', '');
	$id = (int)getVar('id', 'AJOUT');
	if ($id=='AJOUT') {	$id = 0; }
	// Champs de la table labos
	$defaut = array('id_plateau'=>0, 'nom'=>'', 'prenom'=>'', 'contactable'=>0, 'id_fonction'=>0, 'tel'=>'', 'mail'=>'', 'photo'=>'');
	if ($id>0) {	// vérifie si id existe !
		$defaut=sqlTrouve('contacts', 'idx='.$id);
		if (!is_array($defaut)) {	$id = 0; }	// id introuvable
	}
	$champIdPlateau =	getVar('champIdPlateau', $defaut['id_plateau']);
	$champNom =			getVar('champNom', texteDepuisHTML($defaut['nom']));
	$champPrenom =		getVar('champPrenom', texteDepuisHTML($defaut['prenom']));
	$champContactable =	getVar('champContactable', texteDepuisHTML($defaut['contactable']));
	$champIdFonction =	getVar('champIdFonction', $defaut['id_fonction']);
	$champTel =			getVar('champTel', texteDepuisHTML($defaut['tel']));
	$champMail =		getVar('champMail', texteDepuisHTML($defaut['mail']));
	if ( ($champMail=="") and ($champTel=="") ) {	$champContactable = 0; }
	$champPhoto =		getFichier('champPhoto', 'photo_'.$champPhoto, '', TYPE_FICHIER_IMAGE, 'upload', TRUE);
	if ( is_numeric($champPhoto) ) {	$champPhoto = $defaut['photo']; }
	elseif (is_string($champPhoto)) {	// Copie ensuite le fichier sur LUSTOU !
		// ftpCopieFichier('upload/'.$champPhoto, 'lustou.ibcg.biotoul.fr', 'webadmin', '');
	}
	if ($action=='Valider') {	// Validation demandée (données formulaire à récupérer et mise à jour
		$donnees = array('id_plateau'=>($champIdPlateau>0?$champIdPlateau:NULL), 'nom'=>texteVersHTML($champNom), 'prenom'=>texteVersHTML($champPrenom), 'contactable'=>$champContactable,
					'id_fonction'=>($champIdFonction>0?$champIdFonction:NULL), 'tel'=>texteVersHTML($champTel), 'mail'=>texteVersHTML($champMail), 'photo'=>$champPhoto);
		if ($id==0) {	// Ajout d'un élément
			$id = sqlInsert('contacts', $donnees);
			if ( (is_int($id)) and ($id>0) ) {	echo('<em>Contact ajouté !</em>'); }
			$action = '';
		} else {	// Modification d'un contact existant
			if ( sqlUpdate('contacts', 'idx='.$id, $donnees)==1 ) {	echo('<em>Contact enregistré !</em>'); }
			$action = '';
		}
	}
	
	// 	$donnees = array( 'contactable'=>$champContactable,
	//				'id_fonction'=>($champIdFonction>0?$champIdFonction:NULL), 'tel'=>texteVersHTML($champTel), 'mail'=>texteVersHTML($champMail), 'photo'=>$champPhoto);
	echo( commencerFormulaire('Ajouter').creerChampID('id', $id) );
	echo('<table class="liste">');
		echo('<tr><th> ID </th><td> ');
			echo( ($id==0 ? 'NOUVEAU !' : $id ).creerChampID('id', $id) );
		echo(' </td></tr>');
		echo('<tr><th> Plateau </th><td> ');
			echo( creerListeDeroulante('champIdPlateau', $listePlateau, $champIdPlateau, FALSE) );
		echo(' </td></tr>');
		echo('<tr><th> Nom </th><td> ');
			echo( creerChampTexte('champNom', "", $champNom, 50, FALSE) );
		echo(' </td></tr>');
		echo('<tr><th> Prénom </th><td> ');
			echo( creerChampTexte('champPrenom', "", $champPrenom, 50, FALSE) );
		echo(' </td></tr>');
		echo('<tr><th> Contactable </th><td> ');
			echo( creerListeDeroulante('champContactable', $listeContactables, $champContactable, FALSE) );
		echo(' </td></tr>');
		echo('<tr><th> Fonction </th><td> ');
			echo( creerListeDeroulante('champIdFonction', $listeFonctions, $champIdFonction, FALSE) );
		echo(' </td></tr>');
		echo('<tr><th> Téléphone </th><td> ');
			echo( creerChampTexte('champTel', "", $champTel, 20, FALSE) );
		echo(' </td></tr>');
		echo('<tr><th> Mail </th><td> ');
			echo( creerChampTexte('champMail', "", $champMail, 80, FALSE) );
		echo(' </td></tr>');
		echo('<tr><th> Logo </th><td> ');
			echo( creerChampFichier('champLogo', 'upload/'.$champLogo, 50) );
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