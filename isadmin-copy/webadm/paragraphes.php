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
    	<h3> Gestion des paragraphes</h3>
<?php
	$liste = sqlSelect('plateau');
	foreach ($liste as $un) {	$listePlateau[$un['idx']] = $un['sigle']; }
	$condition = getVar('condition', 'tous');
	$conditions = array('indefini'=>"id_plateau is null", 'tous'=>"id_plateau is not null");
	foreach ($listePlateau as $idx=>$sigle) {	$conditions[$sigle] = "id_plateau=".$idx; }
	define('ID_09', "Id ^");			define('ID_90', "Id v");
	define('MAJ_09', "vieux devant");	define('MAJ_90', "récents devant");
	define('FR_az', "FR ^");			define('FR_za', "FR v");
	define('EN_az', "EN ^");			define('EN_za', "EN v");
	$tri = getVar('tri', ID_09);
	$tris = array(ID_09=>"id_paragraphe", ID_90=>"id_paragraphe desc",
				MAJ_09=>"horodateur_maj", MAJ_90=>"horodateur_maj desc",
				FR_az=>"texte_fr", FR_za=>"texte_fr desc",
				EN_az=>"texte_en", EN_za=>"texte_en desc" );
	$lignespage = getVar('lignespage', 20);
	$page = ( $lignespage=='infini' ? '1' : getVar('page', '1') );
	$liste = sqlSelect('paragraphes', $conditions[$condition], $tris[$tri]);
	$nbrLignes = count($liste);
	$nbrPages = (int)ceil($nbrLignes/$lignespage);
	if ($page>$nbrPages) {	$page = $nbrPages; }
	$listeAffichee = ( $lignespage=='infini' ? $liste : array_slice($liste, (($page-1)*$lignespage), $lignespage) );
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
	$entetes = array();	// creerFormBouton($nom='action', $valeur='OK')
	$param = '&lignespage='.$lignespage.'&condition='.$condition.'&page='.$page;
	$entetes[] = 'ID<br />'.creerFormBouton('tri', ID_09).' '.creerFormBouton('tri', ID_90);
	$entetes[] = 'Plateau ';
	$entetes[] = 'Mise à jour<br />'.creerFormBouton('tri', MAJ_09).' '.creerFormBouton('tri', MAJ_90);
	$entetes[] = 'Texte français<br />'.creerFormBouton('tri', FR_az).' '.creerFormBouton('tri', FR_za);
	$entetes[] = 'Texte anglais<br />'.creerFormBouton('tri', EN_az).' '.creerFormBouton('tri', EN_za);
	echo('<br />Commandes :&nbsp;');
	echo( creerLienBouton('Ajouter un paragraphe', 'paragraphe.php?id=AJOUT') );
	if (is_array($listeAffichee)) {
		echo('<table class="liste"><tr>');
		foreach ($entetes as $une) {	echo('<th>'.$une.'</th>'); }
		echo('</tr>');
		foreach ($listeAffichee as $une) {
			echo('<tr><td>');
			echo( creerLienBouton('MAJ-'.$une['id_paragraphe'], 'paragraphe.php?id='.$une['id_paragraphe']) );
			echo('</td><td>');
			echo($listePlateau[$une['id_plateau']]);
			echo('</td><td>');
			echo(horodatageSQLLisible($une['horodateur_maj']));
			echo('</td><td>');
			echo(substr($une['texte_fr'],0,250).'&hellip;');
			echo('</td><td>');
			echo(substr($une['texte_en'],0,250).'&hellip;');
			echo('</td></tr>');
		}
		echo('</table>');
	} else {
		echo('<p>Pas d\'élément à afficher</p>');
	}
	echo( terminerFormulaire('') );
?>   
    </section>
<!--		<div class="ecran">contenu de mon &eacutecran</div> -->
</article>

<?php
require_once($Root.'pied.inc.php');
?>