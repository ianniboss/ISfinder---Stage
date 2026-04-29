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
    	<h3> Gestion des laboratoires</h3>
<?php
/*	$liste = sqlSelect('plateau');
	foreach ($liste as $un) {	$listePlateau[$un['id_labo']] = $un['sigle']; }*/
	$chercheIntitule = getVar('chercheIntitule', '');
/*	$condition = getVar('condition', 'tous');
	$conditions = array('indefini'=>"publications.id_plateau is null", 'tous'=>"publications.id_plateau is not null");
	foreach ($listePlateau as $id_labo=>$sigle) {	$conditions[$sigle] = "publications.id_plateau=".$id_labo; }*/
	define('ID_09', "Id ^");				define('ID_90', "Id v");
	define('SIGLE_az', "Sigle");			define('SIGLE_za', "Sigle");
	define('INTITULE_az', "Intitulé ^");	define('INTITULE_za', "Intitulé v");
	define('CODE_az', "Code unité ^");		define('CODE_za', "Code unité v");
	$tri = getVar('tri', ID_09);
	$tris = array(	ID_09=>"id_labo",			ID_90=>"id_labo desc",
					SIGLE_az=>"sigle",			SIGLE_za=>"sigle desc",
					INTITULE_az=>"intitule",	INTITULE_za=>"intitule desc",
					CODE_az=>"code_unite",		CODE_za=>"code_unite desc" );
	$lignespage = getVar('lignespage', 20);
	$numpage = ( $lignespage=='infini' ? '1' : getVar('numpage', '1') );
	$req = 'SELECT labos.id_labo as id_labo, labos.sigle as sigle, labos.logo as logo, labos.intitule as intitule, labos.code_unite as code, ';
	$req.= '	   traduction.mot_fr as lien_fr, traduction.mot_en as lien_en';
	$req.= ' FROM labos';
	$req.= ' LEFT OUTER JOIN traduction ON labos.id_traduire_lien_externe=traduction.id_traduction';
/*	$req.= ' WHERE '.$conditions[$condition];*/
	if ($chercheIntitule!='') {	$req.= ' WHERE labos.intitule LIKE "%'.$chercheIntitule.'%"';	}
	$req.= ' ORDER BY '.$tris[$tri];
	$liste = sqlRequete($req);
	$nbrLignes = count($liste);
	$nbrPages = (int)ceil($nbrLignes/$lignespage);
	if ($numpage>$nbrPages) {	$numpage = $nbrPages; }
	$listeAffichee = ( $lignespage=='infini' ? $liste : array_slice($liste, (($numpage-1)*$lignespage), $lignespage) );
	echo( commencerFormulaire('recherche') );
	echo( creerChampID('tri', $tri).creerChampID('lignespage', $lignespage).creerChampID('condition', $condition).creerChampID('numpage', $numpage) );
	echo( creerChampID('chercheIntitule', $chercheIntitule) );
	// Afficher les filtres
/*	echo('Filtres :&nbsp;');
	echo( $condition=='indefini' ? 'Indéfini' : creerFormBouton('condition', 'indefini') );
	echo( "&nbsp;" );
	foreach ($listePlateau as $id_labo=>$sigle) {
		echo( $condition==$sigle ? $sigle : creerFormBouton('condition', $sigle) );
		echo( "&nbsp;" );
	}
	echo( $condition=='tous' ? 'Tous' : creerFormBouton('condition', 'tous') );*/
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
	$entetes[] = 'Sigle<br />'.creerFormBouton('tri', SIGLE_az).' '.creerFormBouton('tri', SIGLE_za);
	$entetes[] = 'Logo<br />'.creerFormBouton('tri', LOGO_az).' '.creerFormBouton('tri', LOGO_za);
	$entetes[] = 'Intitule<br />'.creerFormBouton('tri', INTITULE_az).' '.creerFormBouton('tri', INTITULE_za).creerChampTexte('chercheIntitule', 'Recherche dans les intitulés', $chercheIntitule, 20, TRUE, FALSE);
	$entetes[] = 'Code Unité<br />'.creerFormBouton('tri', CODE_az).' '.creerFormBouton('tri', CODE_za);
	$entetes[] = 'Lien externe FR ';
	$entetes[] = 'Lien externe EN ';
	echo('<br />Commandes :&nbsp;');
	echo( creerLienBouton('Ajouter un laboratoire', 'labo.php?id=AJOUT') );
	if (is_array($listeAffichee)) {
		echo('<table class="liste"><tr>');
		foreach ($entetes as $une) {	echo('<th>'.$une.'</th>'); }
		echo('</tr>');
		foreach ($listeAffichee as $une) {
			echo('<tr><td>');
			echo( creerLienBouton('MAJ-'.$une['id_labo'], 'labo.php?id='.$une['id_labo']) );
			echo('</td><td>');
			echo($une['sigle']);
			echo('</td><td>');
			echo($une['logo']);
			echo('</td><td>');
			echo( tronqueTexte($une['intitule'], 15, 15) );
			echo('</td><td>');
			echo($une['code']);
			echo('</td><td>');
			echo( tronqueTexte($une['lien_fr'], 15, 15) );
			echo('</td><td>');
			echo( tronqueTexte($une['lien_en'], 15, 15) );
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