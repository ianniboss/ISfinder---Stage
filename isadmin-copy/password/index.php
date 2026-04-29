<?php
@session_start();
define("BOUTON_OK_IDENTIFIE","Valider");
define("BOUTON_OK_CHANGE","Changer le mot de passe");
$alphanum = array();
for ($i=48 ; $i<=57 ; $i++) {	$alphanum[] = chr($i);	}
for ($i=65 ; $i<=90 ; $i++) {	$alphanum[] = chr($i);	}
for ($i=97 ; $i<=122 ; $i++) {	$alphanum[] = chr($i);	}
/*function remplaceGuillemets($texte) {
	return(str_replace(array('<','>'),array('&lt;','&gt;'),$texte));
}*/
$Root = "../";
require_once($Root.'function_ldap.inc.php');
require_once($Root.'function_ssh.inc.php');
require_once($Root.'entete.inc.php');
//require_once($Root.'menu.inc.php');
?><nav></nav>
  <div class="content">
  <h2> Modification de votre mot de passe </h2>
  
	<?php
	$etape = getVar('etape',0);
	if ( ($etape==1) or ($etape==3) ) {  
		$champUtilisateur = getVar('champUtilisateur',getPGS('uid',''));
		$champMotPasse = getVar('champMotPasse',"");
		$action = getVar('action','');
		$messageErreur = array();
		if ($etape==3) {	// Etape 3 - Changement du mot de passe                      
			$champMotPasseNouveau = getVar('champMotPasseNouveau',"");
			$champMotPasseConfirme = getVar('champMotPasseConfirme',"");
			$code = getVar('code','');
			$codeValidation = md5($champUtilisateur);
			if ($code=='') {	// Code de validation non indiquÃ© pour l'Ã©tape 3
				$messageErreur[] = "Vous devez d'abord demander &agrave; recevoir un mail de validation de la demande.";
			}
			if ($code!=$codeValidation) {	// Code de validation incorrect
				$messageErreur[] = "Vous devez utiliser le lien compet du mail de validation de la demande.";
			}
			if (count($messageErreur)>0) {	// DÃ©jÃ  une erreur, obligatoirement dus Ã  un code de validation erronnÃ© Ã  l'Ã©tape 3, donc retour Ã  l'Ã©tape 1
				$etape = 1;
			}
		}
		switch ($action) {
			case BOUTON_OK_IDENTIFIE:	// VÃ©rifie l'authentification !
				if ($champUtilisateur=="") {
					$messageErreur[] = "Vous devez indiquer un nom d'utilisateur.";
				} elseif ($champMotPasse=="") {
					$messageErreur[] = "Vous devez indiquer votre mot de passe.";
				}
				if (count($messageErreur)==0) {		// Utilisateur & Mot de passe indiquÃ©s !
					$infoLDAP = ldapAuthentifieUtilisateur($champUtilisateur,$champMotPasse);
					if (is_null($infoLDAP)) {
						$messageErreur[] = "Acc&egrave;s &agrave; l'annuaire impossible, r&eacute;essayez plus tard.";
					} elseif ($infoLDAP!=TRUE) {
						$messageErreur[] = "Utilisateur et/ou mot de passe erronn&eacute;s.";
					} else {	// Authentification correcte : Envoyer le mail puis passer Ã  l'Ã©tape 2 !
						$infoUtilisateur = ldapTrouveUtilisateur($champUtilisateur,array('uid','mail','mailforwardingaddress','displayname'));
						$codeValidation = md5($infoUtilisateur['uid']);
						$mailLien = "http://intranet.ibcg.biotoul.fr/password/index.php?etape=3&uid=".$infoUtilisateur['uid']."&code=".$codeValidation;
						$mailExpediteur = "Administrateur IBCG <hpadmin@ibcg.biotoul.fr>";
						$mailEntetes = "From:".$mailExpediteur."\r\n";
						$mailEntetes.= "Cc:Administrateur IBCG <hpadmin@ibcg.biotoul.fr>\r\n";
						$mailDestinataire = $infoUtilisateur['displayname']." <".$infoUtilisateur['mail'].">";
						$mailSujet = "Demande de changement de mot de passe pour ".$infoUtilisateur['displayname'];
						$mailMessage = "Vous avez demandÃ© Ã  modifier votre mot de passe LDAP de l'IBCG.\r\n";
						$mailMessage.= "Si vous n'Ãªtes pas Ã  l'origine de cette demande, contactez IMMEDIATEMENT le service informatique de l'IBCG !\r\n\r\n";
						$mailMessage.= "Vous allez pouvoir accÃ©der Ã  l'Ã©tape suivante en cliquant sur le lien ci-dessous.\r\n\r\n";
						$mailMessage.= "Si votre logiciel de messagerie ne vous permet pas de cliquer simplement dessus,\r\n";
						$mailMessage.= " copiez le dans son intÃ©gralitÃ© pour le collez dans la barre d'adresse de votre navigateur.\r\n\r\n";
						$mailMessage.= "$mailLien\r\n";
						if (mail($mailDestinataire,$mailSujet,$mailMessage,$mailEntetes)) {	// Mail envoyÃ© : passe Ã  l'Ã©tape suivante !
							$etape = 2;
						} else {	// ProblÃ¨me d'envoi du mail, affiche message et reste sur la page du dÃ©but...
							$messageErreur[] = "Le message n'a pas pu &ecirc;tre envoy&eacute;.";
						}
					}
				}
				break;
			case BOUTON_OK_CHANGE:
				$classeCaractere = ( (preg_match("/[0-9]/",$champMotPasseNouveau)) ? 1 : 0 )
					+ ( (preg_match("/[a-z]/",$champMotPasseNouveau)) ? 1 : 0 ) + ( (preg_match("/[A-Z]/",$champMotPasseNouveau)) ? 1 : 0 )
					+ ( (strlen(str_replace($alphanum,array_fill(0,count($alphanum),''),$champMotPasseNouveau))>0) ? 1 : 0 );
				if ($champMotPasse=="") {
							$messageErreur[] = "Vous devez indiquer votre ancien mot de passe.";
				} else {	// Mot de passe indiquÃ©, continue les vÃ©rifications :
					$infoLDAP = ldapAuthentifieUtilisateur($champUtilisateur,$champMotPasse);
					if (is_null($infoLDAP)) {
							$messageErreur[] = "Acc&egrave;s Ã  l'annuaire impossible, r&eacute;essayez plus tard.";
					} elseif ($infoLDAP!=TRUE) {
							$messageErreur[] = "Nom d'utilisateur et/ou mot de passe erronn&eacute;s.";
					} else {	// Authentification correcte : Continuer les vÃ©rifications
						if ($champMotPasseNouveau=="") {
							$messageErreur[] = "Vous devez indiquer votre nouveau mot de passe.";
						} elseif (strlen($champMotPasseNouveau)<10) {
							$messageErreur[] = "Le nouveau mot de passe est trop court.";
						} elseif (strlen($champMotPasseNouveau)>16) {
							$messageErreur[] = "Le nouveau mot de passe est trop long.";
						} elseif (strpos($champMotPasseNouveau," ")!==FALSE) {
							$messageErreur[] = "Le nouveau mot de passe ne doit pas contenir d'espace.";
						} elseif ($classeCaractere<3) {
							$messageErreur[] = "Le nouveau mot de passe n'a que ".$classeCaractere." classe".( ($classeCaractere>1) ? "s" : "" )." de caract&egrave;res.";
						} elseif ($champMotPasseConfirme=="") {
							$messageErreur[] = "Vous devez confirmer votre nouveau mot de passe.";
						} elseif ($champMotPasse==$champMotPasseNouveau) {
							$messageErreur[] = "Votre nouveau mot de passe doit &ecirc;tre diff&eacute;rent de l'ancien.";
						} elseif ($champMotPasseNouveau!=$champMotPasseConfirme) {
							$messageErreur[] = "Le nouveau mot de passe n'a pas &eacute;t&eacute; correctement confirm&eacute;.";
						}
					}
				}
				if (count($messageErreur)==0) {		// Toutes les vÃ©rifications sont passÃ©es avec succÃ¨s : essai de changer le mot de passe
					$carDebut = array ('\\', '!', '"', '#', '$', '%', '&', "'", '(', ')', '*',
							   '+', ',', '-', '.', '/', ':', ';', '<', '=', '>', '?',
							   '@', '[', ']', '^', '{', '|', '}', '~');
					$carFin = array('\\\\', '\0041', '\0042', '\0043', '\0044', '\0045', '\0046', "\0047", '\0050', '\0051', '\0052',
							'\0053', '\0054', '\0055', '\0056', '\0057', '\0072', '\0073', '\0074', '\0075', '\0076', '\0077',
							'\0100', '\0133', '\0135', '\0136', '\0173', '\0174', '\0175', '\0176');
					$passwd = str_replace($carDebut,$carFin,$champMotPasseNouveau);
					$sshPass = str_replace($carDebut,$carFin,SSH_pass);
					$commande = '/bin/echo -e "'.$passwd.'\n'.$passwd.'" | /usr/sbin/smbldap-passwd '.$champUtilisateur;
					$retourSSH = sshCommande($commande);
					if (is_null($retourSSH['out'])) {	// Erreur SSH
						$messageErreur[] = $retourSSH['erreur'];
					} else {	// Pas d'erreur SSH : VÃ©rifie si le mot de passe a pu Ãªtre changÃ©
						if (ldapAuthentifieUtilisateur($champUtilisateur,$champMotPasseNouveau)) {    // VÃ©rifie si le mot de passe a pu Ãªtre changÃ© :
							$etape = 4;	// C'est bon : Ã©tape suivante et mail de confirmation
							$infoUtilisateur = ldapTrouveUtilisateur($champUtilisateur,array('uid','mail','mailforwardingaddress','displayname'));
							$mailExpediteur = "Administrateur IBCG <hpadmin@ibcg.biotoul.fr>";
							$mailEntetes = "From:".$mailExpediteur."\r\n";
							$mailEntetes.= "Cc:Administrateur IBCG <hpadmin@ibcg.biotoul.fr>\r\n";
							$mailDestinataire = $infoUtilisateur['displayname']." <".$infoUtilisateur['mail'].">";
							$mailSujet = "Changement de mot de passe pour ".$infoUtilisateur['displayname'];
							$mailMessage = "Vous avez modifiÃ© votre mot de passe LDAP de l'IBCG.\r\n";
							$mailMessage.= "N'oubliez pas de le modifier partout oÃ¹ vous pourriez l'avoir enregistrÃ© (navigateurs internet, logiciels de messagerie...)\r\n\r\n";
							$mailMessage.= "Par sÃ©curitÃ© :\r\n- ne l'Ã©crivez pas,\r\n";
							$mailMessage.= "- ne le communiquez Ã  personne,\r\n";
							mail($mailDestinataire,$mailSujet,$mailMessage,$mailEntetes);
						} else {	// Le nouveau mot de passe n'a pas pu Ãªtre changÃ©, explique pourquoi et quoi faire.
							if (ldapAuthentifieUtilisateur($champUtilisateur,$champMotPasse)) {    // Le mot de passe n'a pas Ã©tÃ© changÃ©
							$messageErreur[] = "Le nouveau mot de passe a &eacute;t&eacute; refus&eacute; par le serveur<br /><strong>Essayez-en un autre</strong>";
							/* $messageErreur[] = "Commande : (".$commande.")";
							$messageErreur[] = "Codes de retour : (".$retourSSH['out'].")";
							$messageErreur[] = "Codes d'erreur : (".$retourSSH['erreur'].")"; */
							} else {	// Le mot de passe a Ã©tÃ© changÃ© mais incorrectement !!
								$messageErreur[] = "Probl&egrave;me de changement de mot de passe : contactez le Service Informatique !";
							}
						}
					}
				}
				break;
		}
	}
	switch ($etape) {
		case 0:
	?>
		<h1> Aide </h1>
		<p> Cette page a pour seul et unique but de vous permettre de modifier votre mot de passe sur l'annuaire LDAP de l'IBCG&nbsp;:
		<br /> Si vous &ecirc;tes arriv&eacute; l&agrave; par erreur, n'insistez pas et refermez cette page ! </p>
		<h3> Si vous avez oubli&eacute; votre mot de passe, rendez-vous au <a href="mailto:sinfo@ibcg.biotoul.fr">service informatique de l'IBCG</a>. </h3>
		<h2> &Eacute;tapes &agrave; suivre : </h2>
		<p><strong> &Eacute;tape 1&nbsp;:</strong> Vous identifier afin de valider la demande de changement de mot de passe. </p>
		<p><strong> &Eacute;tape 2&nbsp;:</strong> Dans votre messagerie &eacute;lectronique : cliquer sur le lien indiqu&eacute; dans le message qui vous aura &eacute;t&eacute; envoy&eacute;. </p>
		<p><strong> &Eacute;tape 3&nbsp;:</strong> Indiquer votre ancien mot de passe, ainsi que le nouveau et le confirmer. </p>
		<p><strong> &Eacute;tape 4&nbsp;:</strong> Utiliser un autre service de l'IBCG (messagerie par exemple) pour v&eacute;rifier que votre nouveau mot de passe est bien pris en compte. </p>
		<p class="green_button"> <a href="?etape=1">Cliquez ICI pour commencer !</a> </p>
	<?php
			break;
		case 1:
	?>
		<h1> &Eacute;tape 1 - Identification </h1>
		<?php	echoErreurs($messageErreur);	?>
		<form title="Identification" action="<? echo($_SERVER['PHP_SELF']); ?>" method="post">
			<input type="hidden" name="etape" value="<?php echo($etape); ?>" />
			<table><tr>
				<td class="tete"> Nom d'utilisateur </td>
				<td> <input type="text" name="champUtilisateur" value="<?php echo($champUtilisateur); ?>" /> </td>
			</tr><tr>
				<td class="tete"> Mot de passe </td>
				<td> <input type="password" name="champMotPasse" value="" /> </td>
			</tr><tr>
				<td colspan="2" class="bouton"> <input type="submit" name="action" value="<?php echo(BOUTON_OK_IDENTIFIE); ?>"> </td>
			</tr></table>
		</form>
	<?php
		break;
		case 2:
	?>
		<h1> &Eacute;tape 2 - Consultation de votre messagerie </h1>
		<p> Vous devez recevoir un message&nbsp;:</p>                                        
		<ul>
			<li> Provenant de : <?php echo(remplaceGuillemets($mailExpediteur)); ?> </li>
			<li> A l'adresse : <?php echo(remplaceGuillemets($mailDestinataire)); ?></li>
			<li> Ayant pour sujet :  <?php echo(remplaceGuillemets($mailSujet)); ?> </li>
			<li> <strong>vous indiquant un lien d&eacute;butant</strong> par : <?php echo(substr($mailLien,0,strpos($mailLien,".php")+4)); ?> </li>
		</ul>
		<p> Cliquer sur ce lien vous am&egrave;nera &agrave; l'&eacute;tape suivante... </p>
		<p class="green_button"> <a href="https://courriel.biotoul.fr">Cliquez ICI pour acc&eacute;der &agrave; votre messagerie !</a> </p>
	<?php
		break;
		case 3:  
	?>
		<h1> &Eacute;tape 3 - Saisie de votre nouveau mot de passe </h1>
		<?php	echoErreurs($messageErreur);	?>
		<h3> R&egrave;gles de mot de passe </h3>
		<p> Un mot de passe doit : </p>
		<ul>
			<li> avoir 10 &agrave; 16 caract&egrave;res, </li>
			<li> contenir au moins 3 classes de caract&egrave;res (minuscules, majuscules, chiffres, caract&egrave;res s&eacute;ciaux), </li>
			<li> ne pas inclure de portion de votre login, </li>
			<li> ne pas inclure de mot appartenant &agrave; un dictionnaire, </li>
			<li> &ecirc;tre diff&eacute;rent de l'ancien. </li>
		</ul>
		<form title="Changement du mot de passe" action="<? echo($_SERVER['PHP_SELF']); ?>" method="post">
			<input type="hidden" name="etape" value="<?php echo($etape); ?>" />
			<input type="hidden" name="champUtilisateur" value="<?php echo($champUtilisateur); ?>" />
			<input type="hidden" name="code" value="<?php echo($code); ?>" />
			<table><tr>
				<td class="tete"> Nom d'utilisateur </td>
				<td> <strong><?php echo($champUtilisateur); ?></strong> </td>
			</tr><tr>
				<td class="tete"> <strong>Ancien</strong> mot de passe </td>
				<td> <input type="password" name="champMotPasse" value="" /> </td>
			</tr><tr>
				<td class="tete"> <strong>Nouveau</strong> mot de passe </td>
				<td> <input type="password" name="champMotPasseNouveau" value="" /> </td>
			</tr><tr>
				<td class="tete"> <strong>Confirmation du nouveau</strong> mot de passe </td>
				<td> <input type="password" name="champMotPasseConfirme" value="" /> </td>
			</tr><tr>
				<td colspan="2" class="bouton"> <input type="submit" name="action" id="action" value="<?php echo(BOUTON_OK_CHANGE); ?>"> </td>
			</tr></table>
		</form>
	<?php   
		break;
		case 4:
	?>
		<h1> &Eacute;tape 4 - V&eacute;rification de la prise en compte de votre mot de passe actuel </h1>
		<p> <strong>ATTENTION</strong> : Le changement de mot de passe effectif peut prendre quelques minutes ;
		si vous constatez des dysfonctionnements dans l'imm&eacute;diat, veuillez patienter quelques instants et refaire un essai... </p>
		<p> Si vous &ecirc;tes identifi&eacute; sur un des services de l'IBCG (votre messagerie par exemple), veuillez vous d&eacute;connecter, redÃ©marrer votre navigateur, puis vous reconnecter ! </p>
		<p> Utilisez un des services suivants pour v&eacute;rifier que votre mot de passe a bien &eacute;t&eacute; modifi&eacute; : </p>
		<ul>
			<li> Ouvrir une session sur un ordinateur sous Windows &agrave; l'IBCG ; </li>
			<li> Messagerie SOGo : <a href="https://courriel.biotoul.fr" target="_self"> https://courriel.biotoul.fr </a> ; </li>
		</ul>
	<?php
		break;
	}	// Fin du SWITCH $etape
	?>
  
  </div>
<?php
require_once($Root.'pied.inc.php');
?>
