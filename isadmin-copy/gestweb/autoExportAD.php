<?php
require_once('entete.inc.php');
require_once('menu.inc.php');
require_once('function_ssh.inc.php');
?>
  <div class="content">
  <H4><CENTER>Export de l'annuaire vers le site cbi-toulouse.fr </CENTER></H4>
  <?php
  
function mail_attachment($filename, $path, $mailto, $from_mail, $from_name, $replyto, $subject, $message) {
    $file = $path.$filename;
    $file_size = filesize($file);
    $handle = fopen($file, "r");
    $content = fread($handle, $file_size);
    fclose($handle);
    $content = chunk_split(base64_encode($content));
    $uid = md5(uniqid(time()));
    $header = "From: ".$from_name." <".$from_mail.">\r\n";
    $header .= "Reply-To: ".$replyto."\r\n";
    $header .= "MIME-Version: 1.0\r\n";
    $header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
    $header .= "This is a multi-part message in MIME format.\r\n";
    $header .= "--".$uid."\r\n";
    $header .= "Content-type:text/plain; charset=iso-8859-1\r\n";
    $header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $header .= $message."\r\n\r\n";
    $header .= "--".$uid."\r\n";
    $header .= "Content-Type: application/octet-stream; name=\"".$filename."\"\r\n"; // use different content types here
    $header .= "Content-Transfer-Encoding: base64\r\n";
    $header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
    $header .= $content."\r\n\r\n";
    $header .= "--".$uid."--";
    return (mail($mailto, $subject, "", $header));
}


/*	Ce script ne fait que créer leS fichierS CSV pour export des comptes vers l'annuaire AD !
	Le fichier généré est de la forme :
	ID;nom;prénom;email;05 61 33 55 44;LBME;position (grade);0 pour permanent, 1 pour temporaire
	Les ID doivent toujours rester identiques et commencer à 40000 !
	Dans cette version, les lignes sans nom, prénom, mail ou labo valide sont ignorées
	Le labo ne peut contenir que LBME, LMGM ou IFR (modifié en "CBI")
	Les téléphones sont mis sous la forme 01 61 33 5x xx
	*/
    $requete = 'SELECT Labo.comptes.id_cbi as id, '
        .'Labo.comptes.prenom as prenom, '
        .'Labo.comptes.nom as nom, '
        .'reseau.equipes.nom_AD as equipe, '
        .'CASE WHEN Labo.comptes.id_labo=8 THEN "OUI" ELSE "NON" END as iscbi, '
        .'CASE WHEN Labo.emploi.temporaire=0 THEN "OUI" ELSE "NON" END as perm, '
        .'CASE WHEN Labo.comptes.annuaire=0 THEN "OUI" ELSE "NON" END as nonannu, '
        .'Labo.emploi.grade as grade, '
        .'CASE WHEN Labo.appartenance.appartenance>"" THEN Labo.appartenance.appartenance ELSE " " END as employeur, '
        .'Labo.comptes.email as mail, '
        .'CASE WHEN Labo.comptes.email_perso>"" THEN Labo.comptes.email_perso ELSE " " END as email_perso, '
        .'Labo.comptes.poste as poste, '
        .'CASE WHEN Labo.comptes.date_depart IS NULL THEN CONCAT(" / permanent.e / WID= ",Labo.comptes.id_cbi) '
        .'ELSE CONCAT(" / ",Labo.comptes.date_depart," / WID= ",Labo.comptes.id_cbi) END as commentaires, '
        .'Labo.comptes.login as login '
        .'FROM Labo.comptes LEFT OUTER JOIN Labo.comptes_equipes ON Labo.comptes.id=Labo.comptes_equipes.id_compte '
        .'LEFT OUTER JOIN reseau.equipes ON Labo.comptes_equipes.id_equipe=reseau.equipes.idx '
        .'LEFT OUTER JOIN Labo.emploi ON Labo.comptes.id_job=Labo.emploi.id_job '
        .'LEFT OUTER JOIN Labo.appartenance ON Labo.comptes.id_appartenance=Labo.appartenance.idx '
        .'WHERE Labo.comptes.id_statut=3 AND Labo.comptes.id_labo IN (1,2,8) '
        .'ORDER BY Labo.comptes.nom, Labo.comptes_equipes.quotite DESC LIMIT 0,999';
    $listeAnnuaire = sqlRequete($requete);
	// Ensuite préparation du fichier CSV
	$fichier = "";	// Contenu du fichier texte !
	$nb_ligne = 0;
	echo("Résultat de l'export :<ul>");
        $exID = 0;
        $equipesSecondaires = array();
        $ligneFichier = "";
	foreach ($listeAnnuaire as $tableau) {
            if ($tableau['id']==$exID) {    // ID identique au précédent : ajoute aux équipes secondaires
                $equipesSecondaires[] = $tableau['equipe'];
            } else {        // ID différent, donc on constitue la ligne (avant et après l'équipe...
                if ($ligneFichier!="") {    // Si la ligne a été créée lors de l'itération précédente, on l'ajoute au fichier
                    $fichier.= str_replace("[EQUIPES_SECONDAIRES]", ( count($equipesSecondaires)>0 ? implode(',', $equipesSecondaires) : " " ), $ligneFichier);
                    $ligneFichier = "";
                }   // Ensuite, on peut constituer la ligne suivante...
		$nomvar = html_entity_decode( trim($tableau["nom"]), ENT_COMPAT, 'UTF-8') ;	// On supprime toujours les guillemets en trop, ainsi que les espaces avant et après les chaînes !
		$prenomvar = html_entity_decode( trim($tableau["prenom"]), ENT_COMPAT, 'UTF-8');
		$equipevar = html_entity_decode( trim($tableau["equipe"]), ENT_COMPAT, 'UTF-8');
		$emailvar = html_entity_decode( trim($tableau["mail"]), ENT_COMPAT, 'UTF-8');
                $email_perso = html_entity_decode( trim($tableau["email_perso"]), ENT_COMPAT, 'UTF-8'); if ($email_perso=="") { $email_perso = " "; }
		$postevar = trim(str_replace(array("'",'"',' ','-','.','_',':','*','='), '', html_entity_decode($tableau["poste"], ENT_COMPAT, 'UTF-8')));	// supprime la ponctuation..
		// Mise en forme du tel
                    if ( (strlen($postevar))<4 ) {	$postevar = "5".$postevar; }	// Ajoute le 5 si le n° de poste ne fait que 3 chiffres
                    if ( (strlen($postevar))<5 ) {	$postevar = "056133".$postevar; }	// Ajoute le 056133 s'il n'en fait que 4.
                    $postevar = implode(" ", str_split($postevar, 2));	// Ajoute les espaces tous les 2 chiffres
                    if ($postevar=="") {    $postevar = " ";  }
		// Suite...
                $grade = html_entity_decode( $tableau['grade'], ENT_COMPAT, 'UTF-8');   if ($grade=="") {  $grade = " ";  }
                if ($tableau["appartenance"]<>"") {  $positionvar.= ", ".html_entity_decode($tableau["appartenance"], ENT_COMPAT, 'UTF-8');  }
		if ( ($nomvar=="") or ($prenomvar=="") or ($emailvar=="") ) {
			echo("<li><strong>n°$id ignoré</strong> : nom ".($nomvar==""?"manquant":"= ".$nomvar).", prénom ".($prenomvar==""?"manquant":"= ".$prenomvar).", mail ");
			echo(($emailvar==""?"manquant":"= ".$emailvar).", tel = $postevar</li>");
		}
                else	{	// Toutes les données obligatoires sont indiquées : ajoute la ligne dans le fichier !
			echo("<li>n°$id : $prenomvar, $nomvar, $emailvar</li>");
			$nb_ligne++;
                        $ligneFichier.= $prenomvar.';'.$nomvar.';'.$equipevar.';'.$tableau['iscbi'].';'.$tableau['perm'].';NON; ;'.$tableau['nonannu']
                                .';'.$tableau['grade'].';'.$tableau['employeur'].';'.$emailvar.';'.$email_perso.';'.$postevar.';[EQUIPES_SECONDAIRES]'.$tableau['commentaires'].';'.$tableau['login']."\n";
		}
            }
	}
        if ($ligneFichier!="") {    // S'il reste une ligne à afficher !
            $fichier.= str_replace("[EQUIPES_SECONDAIRES]", ( count($equipesSecondaires)>0 ? implode(',', $equipesSecondaires) : " " ), $ligneFichier);
        }   // Ensuite, on peut constituer la ligne suivante...
	echo("<li><strong>$nb_ligne</strong> Lignes exportées</li>");
	setlocale(LC_TIME, 'fr_FR');
	$cheminLocal = "/var/www/html/intranet/secure/gestweb/";
	$racineSite = "https://secure.ibcg.biotoul.r/gestweb/";
	$dossierLocal = "export/";	// Emplacement des fichiers locaux
	$serveurDistant = "194.57.136.10";
	$portDistant = "50023";
	$userDistant = "lab0546";
	$passDistant = "tnA23klV";
	$cheminDistant = "/lab0546/cbi-toulouse.fr/www/admin/upload-intervenants/";	// Emplacement des fichiers d'upload des intervenants
	$nomFichier = "fichier4.csv";
	$nomFichierSauv = "fichier4_.csv";
	$fichierDestination = $cheminLocal.$dossierLocal.$nomFichier;
	$fichierDestinationSauv = $cheminLocal.$dossierLocal.$nomFichierSauv;
	$lienFichierCSV = $dossierLocal.'fichier4.csv';	// http://intranet.ibcg.biotoul.fr/export/annuaire_IBCG.csv
	$fichierSauv = $nomFichier.'.old';	// fichier.csv.old
	// Copier CONF vers CONF.OLD
	if (file_exists($fichierDestination)) {
		echo("<li>Sauvegarde du fichier précédent ($fichierDestination -> $fichierDestinationSauv) : ");
		if (!copy($fichierDestination, $fichierDestinationSauv)) {
			echo("Echec");
		} else {
			echo("Réussi");
		}
		echo("</li>");
	}
	// Créer CONF
	echo("<li>Création du fichier actuel (Annuaire -> $fichierDestination) : ");
	$handle = fopen($fichierDestination, 'w');
	if ($handle===FALSE) {	// le fichier ne peut pas être ouvert
		echo("Echec d'ouverture, contactez l'administrateur !</li>");
	} else {	// Il est ouvert, on écrit dedans
		$resultat = fwrite($handle, $fichier);
		if ($resultat===FALSE) {	// Le fichier ne peut pas être écrit
			echo("Echec d'écriture, contactez l'administrateur !</li>");
		} else {	// le fichier a été écrit, on continue
			echo("Réussi !</li>");
			fclose($handle);
		}
	}
        // Copie sur CBI-TOULOUSE.FR
        //$resultat1 = sshCommande('cbi-toulouse', "scp ".." ".$cheminDistant.". && echo oui || echo non");
        //$resultat1 = sshFTP('cbi-toulouse', $cheminLocal.$dossierLocal.$nomFichier, $cheminDistant.$nomFichier) ;
        //        echo("<li>Copie vers CBI-TOULOUSE.FR : ".$resultat1."</li>" );
        
        // Envoi par mail (pièce jointe !
        echo("<li>Envoi par Mail :");
        $resultat = mail_attachment($nomFichier, $cheminLocal.$dossierLocal, "biguet@biotoul.fr", "hp-admin@ibcg.biotoul.fr", "HP-Admin", "hp-admin@ibcg.biotoul.fr",
                        "[Gestweb] Fichier d'annuaire pour import CBI",
                        "Voici le fichier CSV à importer sur le site <a href='http://cbi-toulouse.fr/admin/update_intervenants'>www.cbi-toulouse.fr</a>");
        echo(($resultat?"Réussi":"Echec...")."</li>");

	echo("</ul>");         
        echo("Clic-droit pour télécharger <a href='export/fichier4.csv'>le fichier</a>, à importer sur le site <a href='http://cbi-toulouse.fr/admin/update_intervenants' target='_blank'>cbi-toulouse.fr</a>");
      
?>


</div>
<?php
require_once('pied.inc.php');
?>