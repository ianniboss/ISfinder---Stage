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
    	<h3> Gestion des publications</h3>
<?php
	$liste = sqlSelect('plateau');
	foreach ($liste as $un) {	$listePlateau[$un['idx']] = $un['sigle']; }
	$chercheAuteur = getVar('chercheAuteur', '');
	$chercheTitre = getVar('chercheTitre', '');
	$condition = getVar('condition', 'tous');
	$conditions = array('indefini'=>"publications.id_plateau is null", 'tous'=>"publications.id_plateau is not null");
	foreach ($listePlateau as $idx=>$sigle) {	$conditions[$sigle] = "publications.id_plateau=".$idx; }
	define('ID_09', "Id ^");			define('ID_90', "Id v");
	define('MAJ_09', "vieux devant");	define('MAJ_90', "récents devant");
	define('FR_az', "FR ^");			define('FR_za', "FR v");
	define('EN_az', "EN ^");			define('EN_za', "EN v");
	$tri = getVar('tri', ID_09);
	$tris = array(ID_09=>"idx", ID_90=>"idx desc",
				MAJ_09=>"annee", MAJ_90=>"annee desc",
				TITRE_az=>"titre", TITRE_za=>"titre desc",
				REF_az=>"ref_journal", REF_za=>"ref_journal desc",
				LABO_az=>"labo_nom", LABO_za=>"labo_nom desc" );
	$lignespage = getVar('lignespage', 20);
	$numpage = ( $lignespage=='infini' ? '1' : getVar('numpage', '1') );
	$req = 'SELECT publications.idx as idx, publications.id_plateau as id_plateau, publications.auteurs as auteurs, publications.annee as annee, ';
	$req.= '	   publications.titre as titre, publications.ref_journal as ref_journal, publications.lien_externe as lien_externe, ';
	$req.= '	   CONCAT(labos.sigle," (",labos.code_unite,")") as labo_nom';
	$req.= ' FROM publications';
	$req.= ' LEFT OUTER JOIN labos ON publications.id_labo=labos.id_labo';
	$req.= ' WHERE '.$conditions[$condition];
	if ($chercheAuteur!='') {	$req.= ' AND publications.auteurs LIKE "%'.$chercheAuteur.'%"';	}
	if ($chercheTitre!='') {	$req.= ' AND publications.titre LIKE "%'.$chercheTitre.'%"';	}
	$req.= ' ORDER BY '.$tris[$tri];
	$liste = sqlRequete($req);
	$nbrLignes = count($liste);
	$nbrPages = (int)ceil($nbrLignes/$lignespage);
	if ($numpage>$nbrPages) {	$numpage = $nbrPages; }
	$listeAffichee = ( $lignespage=='infini' ? $liste : array_slice($liste, (($numpage-1)*$lignespage), $lignespage) );
	echo( commencerFormulaire('recherche') );
	echo( creerChampID('tri', $tri).creerChampID('lignespage', $lignespage).creerChampID('condition', $condition).creerChampID('numpage', $numpage) );
	echo( creerChampID('chercheAuteur', $chercheAuteur).creerChampID('chercheTitre', $chercheTitre) );
	// Afficher les filtres
	echo('Filtres :&nbsp;');
	echo( $condition=='indefini' ? 'Indéfini' : creerFormBouton('condition', 'indefini') );
	echo( "&nbsp;" );
	foreach ($listePlateau as $idx=>$sigle) {
		echo( $condition==$sigle ? $sigle : creerFormBouton('condition', $sigle) );
		echo( "&nbsp;" );
	}
	echo( $condition=='tous' ? 'Tous' : creerFormBouton('condition', 'tous') );
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
				//   	idx TRI		id_plateau FILTRE		auteurs RECHERCHE	 	annee TRI	titre TRI+RECHERCHE		ref_journal TRI 	lien_externe 	id_labo TRI
	$entetes = array();
	$entetes[] = 'ID<br />'.creerFormBouton('tri', ID_09).' '.creerFormBouton('tri', ID_90);
	$entetes[] = 'Plateau ';
	$entetes[] = 'Auteurs<br />'.creerChampTexte('chercheAuteur', 'Recherche dans les auteurs', $chercheAuteur, 20, TRUE, FALSE);
	$entetes[] = 'Année<br />'.creerFormBouton('tri', MAJ_09).' '.creerFormBouton('tri', MAJ_90);
	$entetes[] = 'Titre<br />'.creerFormBouton('tri', TITRE_az).' '.creerFormBouton('tri', TITRE_za)
				.creerChampTexte('chercheTitre', 'Recherche dans les titres', $chercheTitre, 20, TRUE, FALSE);
	$entetes[] = 'Journal<br />'.creerFormBouton('tri', REF_az).' '.creerFormBouton('tri', REF_za);
	$entetes[] = 'Lien externe ';
	$entetes[] = 'Laboratoire<br />'.creerFormBouton('tri', LABO_az).' '.creerFormBouton('tri', LABO_za);
	echo('<br />Commandes :&nbsp;');
	echo( creerLienBouton('Ajouter une publication', 'publication.php?id=AJOUT') );
	if (is_array($listeAffichee)) {
		echo('<table class="liste"><tr>');
		foreach ($entetes as $une) {	echo('<th>'.$une.'</th>'); }
		echo('</tr>');
		foreach ($listeAffichee as $une) {
			echo('<tr><td>');
			echo( creerLienBouton('MAJ-'.$une['idx'], 'publication.php?id='.$une['idx']) );
			echo('</td><td>');
			echo($listePlateau[$une['id_plateau']]);
			echo('</td><td>');
			echo(substr($une['auteurs'],0,40).'&hellip;');
			echo('</td><td>');
			echo($une['annee']);
			echo('</td><td>');
			echo(substr($une['titre'],0,40).'&hellip;');
			echo('</td><td>');
			echo($une['ref_journal']);
			echo('</td><td>');
			echo($une['lien_externe']);
			echo('</td><td>');
			echo($une['labo_nom']);
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