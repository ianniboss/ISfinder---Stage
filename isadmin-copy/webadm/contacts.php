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
    	<h3> Gestion des contacts</h3>
<?php
	$liste = sqlSelect('plateau');
	$listePlateau = array();	foreach ($liste as $un) {	$listePlateau[$un['idx']] = $un['sigle']; }
/*	$chercheIntitule = getVar('chercheIntitule', '');*/
	$condition = getVar('condition', 'tous');
	$conditions = array('tous'=>"contacts.idx>0", 'indefini'=>"contacts.id_plateau is null");
	$derouleConditions = array('tous'=>"Tous", 'indefini'=>"Indéfini");
	foreach ($listePlateau as $id_plateau=>$sigle) {
		$conditions[$sigle] = "contacts.id_plateau=".$id_plateau;
		$derouleConditions[$sigle] = $sigle;
	}
	define('ID_09', "Id ^");			define('ID_90', "Id v");
	define('NOM_az', "Nom ^");			define('NOM_za', "Nom v");
	define('PRENOM_az', "Prénom ^");	define('PRENOM_za', "Prénom v");
	define('TEL_az', "Tel ^");			define('TEL_za', "Tel v");
	define('MAIL_az', "Mail ^");		define('MAIL_za', "Mail v");
	$tri = getVar('tri', ID_09);
	$tris = array(	ID_09=>"idx",			ID_90=>"idx desc",
					NOM_az=>"nom",			NOM_za=>"nom desc",
					PRENOM_az=>"prenom",	PRENOM_za=>"prenom desc",
					TEL_az=>"tel",			TEL_za=>"tel desc",
					MAIL_az=>"mail",		MAIL_za=>"mail desc" );
	$lignespage = getVar('lignespage', 20);
	$numpage = ( $lignespage=='infini' ? '1' : getVar('numpage', '1') );
	$req = 'SELECT contacts.idx as idx, plateau.sigle as plateau, contacts.nom as nom, contacts.prenom as prenom, contacts.contactable as contactable, ';
	$req.= '	   traduction.mot_fr as fonction, contacts.tel as tel, contacts.mail as mail, contacts.photo as photo';
	$req.= ' FROM contacts';
	$req.= ' LEFT OUTER JOIN plateau			ON contacts.id_plateau=plateau.idx';
	$req.= ' LEFT OUTER JOIN contact_fonction	ON contacts.id_fonction=contact_fonction.idx';
	$req.= ' LEFT OUTER JOIN traduction			ON contact_fonction.description=traduction.mot_cle';
	$req.= ' WHERE '.$conditions[$condition];
//	if ($chercheIntitule!='') {	$req.= ' WHERE contacts.intitule LIKE "%'.$chercheIntitule.'%"';	}
	$req.= ' ORDER BY '.$tris[$tri];
	$liste = sqlRequete($req);
	$nbrLignes = count($liste);
	$nbrPages = (int)ceil($nbrLignes/$lignespage);
	if ($numpage>$nbrPages) {	$numpage = $nbrPages; }
	$listeAffichee = ( $lignespage=='infini' ? $liste : array_slice($liste, (($numpage-1)*$lignespage), $lignespage) );
	echo( commencerFormulaire('recherche') );
	echo( creerChampID('tri', $tri).creerChampID('lignespage', $lignespage).creerChampID('condition', $condition).creerChampID('numpage', $numpage) );
	echo( creerChampID('chercheIntitule', $chercheIntitule) );
	// Afficher les lignes par pages
	echo('<br />'.$nbrLignes.' lignes / lignes par page :&nbsp;');
	$listeNbLignes = array(5, 10, 15, 20, 30, 50, 100);
	foreach ($listeNbLignes as $nbr) {
		echo( $lignespage==$nbr ? $nbr : creerFormBouton('lignespage', $nbr) );
		echo( "&nbsp;" );
	}
	echo( $lignespage=='infini' ? '&infin;' : creerFormBouton('lignespage', '&infin;') );
	// Afficher les pages
	if ($lignespage!='infini') {
		echo('<br />Page :');
		for ($nbr=1 ; $nbr<=$nbrPages ; $nbr++) {
			echo( "&nbsp;" );
			echo( $page==$nbr ? $nbr : creerFormBouton('numpage', $nbr) );
		}
	}
				//   	id_labo TRI		id_plateau FILTRE		auteurs RECHERCHE	 	annee TRI	titre TRI+RECHERCHE		ref_journal TRI 	lien_externe 	id_labo TRI
	$entetes = array();
	$entetes[] = 'ID<br />'.creerFormBouton('tri', ID_09).' '.creerFormBouton('tri', ID_90);
	$entetes[] = 'Plateau<br />'.creerListeDeroulante('condition', $derouleConditions, $condition, true);
	$entetes[] = 'Nom<br />'.creerFormBouton('tri', NOM_az).' '.creerFormBouton('tri', NOM_za);
	$entetes[] = 'Prénom<br />'.creerFormBouton('tri', PRENOM_az).' '.creerFormBouton('tri', PRENOM_za);
	$entetes[] = 'Contactable';
	$entetes[] = 'Fonction';
	$entetes[] = 'Tel<br />'.creerFormBouton('tri', TEL_az).' '.creerFormBouton('tri', TEL_za);
	$entetes[] = 'Mail<br />'.creerFormBouton('tri', MAIL_az).' '.creerFormBouton('tri', MAIL_za);
	$entetes[] = 'Photo';
	echo('<br />Commandes :&nbsp;');
	echo( creerLienBouton('Ajouter un contact', 'contact.php?id=AJOUT') );
	if (is_array($listeAffichee)) {
		echo('<table class="liste"><tr>');
		foreach ($entetes as $une) {	echo('<th>'.$une.'</th>'); }
		echo('</tr>');
		foreach ($listeAffichee as $une) {
			echo('<tr><td>');
			echo( creerLienBouton('MAJ-'.$une['idx'], 'contact.php?id='.$une['idx']) );
			echo('</td><td>');
			echo($une['plateau']);
			echo('</td><td>');
			echo($une['nom']);
			echo('</td><td>');
			echo($une['prenom']);
			echo('</td><td>');
			echo( ($une['contactable']=="1"?'OUI':'---') );
			echo('</td><td>');
			echo($une['fonction']);
			echo('</td><td>');
			echo($une['tel']);
			echo('</td><td>');
			echo($une['mail']);
			echo('</td><td>');
			echo($une['photo']);
			echo('</td></tr>');
		}
	} else {
		echo('<p>Pas d\'élément à afficher</p>');
	}
	echo('</table>');
	echo( terminerFormulaire('') );
?>   
    </section>
<!--		<div class="ecran">contenu de mon &eacutecran</div> -->
</article>

<?php
require_once($Root.'pied.inc.php');
?>