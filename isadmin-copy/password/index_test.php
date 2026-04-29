<?php
@session_start();
define("BOUTON_OK_IDENTIFIE","Valider");
define("BOUTON_OK_CHANGE","Changer le mot de passe");
$alphanum = array();
for ($i=48 ; $i<=57 ; $i++) {	$alphanum[] = chr($i);	}
for ($i=65 ; $i<=90 ; $i++) {	$alphanum[] = chr($i);	}
for ($i=97 ; $i<=122 ; $i++) {	$alphanum[] = chr($i);	}
function getPGS($variable, $defaut=NULL) {
	if ( isset($_POST[$variable]) ) { $retour = $_POST[$variable]; }
	elseif ( isset($_GET[$variable]) ) { $retour = $_GET[$variable]; }
	elseif ( isset($_SESSION[$variable]) ) { $retour = $_SESSION[$variable]; }
	else { $retour = $defaut; }
	return $retour;
}
function echoErreurs($messageErreur) {
	if (count($messageErreur)>0) {
		echo('<h3 class="erreur"> Erreurs </h3><ul>');
		foreach ($messageErreur as $ligne) {	echo('<li class="erreur">'.$ligne.'</li>');	}
		echo("</ul>");
	}
}
function remplaceGuillemets($texte) {
	return(str_replace(array('<','>'),array('&lt;','&gt;'),$texte));
}
define('LDAP_host','ldap.ibcg.biotoul.fr');
define('LDAP_port','389');
define('LDAP_bind_dn','cn=rootldap,dc=ibcg,dc=biotoul,dc=fr');
define('LDAP_password','pinduitr');
define('LDAP_base_dn','dc=ibcg,dc=biotoul,dc=fr');
/**
 * Trouve un utilisateur par son adresse mail (exacte !).
 * @param	string	$uid			identifiant de l'utilisateur dans l'annuaire LDAP
 * @param	array	$attributsDemandes	tableau d'attributs à retourner
 * Retourne :
 * 	- FALSE si l'accès à l'annuaire LDAP est impossible
 * 	- NULL si l'utilisateur est introuvable (ou si rien n'a été indiqué, valeur par défaut...)
 * 	- un tableau associatif des données LDAP pour l'utilisateur, s'il est trouvé.
 **/
function ldapTrouveUtilisateur($uid='', $ldapAttributs=NULL) {
	$retour = NULL;
	if ($uid<>'') {	// Connexion au serveur LDAP uniquement si une adresse mail est indiquée
		$ldapConnexion = ldap_connect(LDAP_host, LDAP_port);
		if ($ldapConnexion) {	// Vérifie si la connexion au serveur LDAP est possible
			$ldapBind = @ldap_bind($ldapConnexion, LDAP_bind_dn, LDAP_password);
			if ($ldapBind) {
				$ldapFiltre = "(uid=".$uid.")";
				if (is_null($ldapAttributs)) { 	// employeetype = équipe ; businesscategory = laboratoire ;
					$ldapAttributs = array('uid','mail','mailforwardingaddress','employeetype','businesscategory');
				}
				$ldapRecherche = ldap_search($ldapConnexion,LDAP_base_dn,$ldapFiltre,$ldapAttributs);
				$ldapInfo = ldap_get_entries($ldapConnexion,$ldapRecherche);
				foreach ($ldapAttributs as $uneCle) {	// Crée le tableau de résultat vide avec les clés demandées !
					$retour[$uneCle] = '';
				}
				foreach ($ldapInfo[0] as $cle=>$valeur) {	// Parcours les valeurs du tableau
					if (array_key_exists($cle,$retour)) {	// Si la clé correspond à une valeur demandée
						$retour[$cle] = $valeur[0];
					}
				}
			}
			@ldap_unbind($ldapBind);
		} else {
			$retour = FALSE;
		}
	}
	return $retour;
}

/**
 * Vérifie l'authentification d'un utilisateur par son UID OU mail ; et mot de passe.
 * Retourne :
 * 	- NULL si l'accès à l'annuaire LDAP est impossible
 * 	- FALSE si le mot de passe est incorrect (mais adresse mail valide)
 * 	- TRUE si l'authentification est valide.
 **/
function ldapAuthentifieUtilisateur($user='',$password='') {
	$retour = FALSE;
	$ldapConnexion = ldap_connect(LDAP_host, LDAP_port);
	if ($ldapConnexion) {
		$ldapBind = @ldap_bind($ldapConnexion, "uid=".$user.",ou=Peoples,".LDAP_base_dn, $password);
		if ($ldapBind) {
			$retour = TRUE;
			@ldap_unbind($ldapConnexion);
		}
	} else {	$retour = NULL;	}
	return $retour;
}
define('SSH_server','aneto');
define('SSH_user','root');
define('SSH_pass','ph:78mm');
//define('SSH_user','mdp');
//define('SSH_pass','mdp!123');
define('SSH_Err_Exec',"Erreur d'ex&eacute;cution SSH");
define('SSH_Err_Ident',"Erreur d'identification SSH");
define('SSH_Err_Connect',"Erreur de connection SSH");
/**
 * Exécute une commande SSH sur un serveur distant.
 * Retourne un tableau de valeurs indiquant le contenu du flux de sortie (NULL en cas d'erreur) et le type d'erreur (NULL en cas de réussite)
 * Exemple d'utilisation :
 * 	$commande = 'ls -l';	// Mettre la commande que l'on veut...
 *	$retourSSH = sshCommande($commande);
 *	if (is_null($retourSSH['out'])) { echo($retourSSH['erreur']); }
 *	else { echo('Commande exécutée : '.$retourSSH['out']); }
 * **/
function sshCommande($commande = NULL) {
	$retour = array('out'=>NULL, 'erreur'=>NULL);
	if (!is_null($commande)) {
		$sshConnection = ssh2_connect(SSH_server);
		if ($sshConnection!==FALSE) {
			if (ssh2_auth_password($sshConnection,SSH_user,SSH_pass)) {	// Identification réussie
				$sshFluxSortie = ssh2_exec($sshConnection, $commande);
				if ($sshFluxSortie!==FALSE) {	// Commande exécutée :
					stream_set_blocking($sshFluxSortie, 1); 
					$retour['out'] = stream_get_contents($sshFluxSortie);
				} else {	$retour['erreur'] = SSH_Err_Exec;	}
			} else {	$retour['erreur'] = SSH_Err_Ident;	}
		} else {	$retour['erreur'] = SSH_Err_Connect;	}
	} // var_dump($retour);
	return $retour;
}
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN"
                      "http://www.w3.org/TR/html4/strict.dtd">
<html><!-- InstanceBegin template="/Templates/seconde-navgauche.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Language" content="fr">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<!-- InstanceBeginEditable name="doctitle" -->
<title>Changement de mot de passe</title>
<!-- InstanceEndEditable --><link rel="stylesheet" type="text/css" href="../styles/xcharte.css">

<style type="text/css">
	.erreur { color: red; }
	h1, h2 { text-align: center; }
	form table { border: none; }
	form table tr td { padding: 1px 4px; }
	form table tr td.tete { text-align: right;	font-variant: small-caps; }
	form table tr td.bouton { text-align: center; margin: 30px; }
	div#ZonePrint { padding: 10px; }
	p.green_button { margin: 20px; text-align: center; }
	p.green_button a { padding: 15px; background-color: green; color: white; }
</style>

<link rel="stylesheet" type="text/css" href="../styles/styles.css">
<script language="JavaScript" src="../z-outils/init.js"></script>
<script language="JavaScript" src="../z-outils/outils.js"></script>
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
//-->
</script>
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
</head>
<body marginwidth="0" marginheight="0" >
  <div class="bandeau-liens" id="divbandeau-lienCNRS"> <a href="http://www2.cnrs.fr/band/2.htm" target="_blank">Le CNRS</a> </div>
  <div id="divbandeau-traitvert1"><img src="../z-outils/images/charte/trait-vertical.gif"></div>
  <div id="divbandeau-lienAccueil" class="bandeau-liens"> <a href="http://www.cnrs.fr/" target="_blank">Accueil CNRS </a></div>
  <div id="divbandeau-traitvert2"><img src="../z-outils/images/charte/trait-vertical.gif"></div>
  <div id="divbandeau-lienAutres" class="bandeau-liens"><a href="http://www2.cnrs.fr/band/5.htm" target="_blank">Autres sites CNRS</a></div> 
<div id="divbandeau-traitvert3"><img src="../z-outils/images/charte/trait-vertical.gif"></div>

<table width="751"  border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="150"><img src="../z-outils/images/boite-outils/espaceur.gif" width="150" height="65" alt=""></td>
    <td colspan="2"><img src="../z-outils/images/site/bandeau-haut-droit.jpg" alt="" width="600" height="65" border="0"></td>
    <td width="1" class="separateur"><img src="../z-outils/images/boite-outils/espaceur.gif" width="1" height="1"></td>
  </tr>
  <tr>
    <td width="150" class="separateur"><img src="../z-outils/images/boite-outils/espaceur.gif" width="1" height="1"></td>
    <td colspan="2" class="separateur"><img src="../z-outils/images/boite-outils/espaceur.gif" width="1" height="1"></td>
    <td class="separateur"><img src="../z-outils/images/boite-outils/espaceur.gif" width="1" height="1"></td>
  </tr>
  <tr>
    <td width="150">&nbsp;</td>
    <td width="1" class="separateur"><img src="../z-outils/images/boite-outils/espaceur.gif" width="1" height="62"></td>
    <td width="599" height="1"><!-- InstanceBeginEditable name="Visuel" --><img src="../z-outils/images/charte/seconde/bandeaux-sec/doigts-01.jpg" alt="" width="599" height="62" border="0"><!-- InstanceEndEditable --></td>
    <td width="1" class="separateur"><img src="../z-outils/images/boite-outils/espaceur.gif" width="1" height="62"></td>
  </tr>
  <tr>
    <td width="150"><img src="../z-outils/images/boite-outils/espaceur.gif" width="150" height="1"></td>
    <td width="1" class="separateur"><img src="../z-outils/images/boite-outils/espaceur.gif" width="1" height="1"></td>
    <td width="599" class="separateur"><img src="../z-outils/images/boite-outils/espaceur.gif" width="1" height="1"><img src="../z-outils/images/boite-outils/espaceur.gif" width="1" height="1"></td>
    <td width="1" class="separateur"><img src="../z-outils/images/boite-outils/espaceur.gif" width="1" height="1"></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td class="separateur"><img src="../z-outils/images/boite-outils/espaceur.gif" width="1" height="1"></td>
    <td class="Xchemin"> &nbsp;<!-- InstanceBeginEditable name="Chemin" --><a href="../index.php">&nbsp;Accueil</a> &gt; Outils informatiques &gt; Changement de Mot de Passe <!-- InstanceEndEditable --></td>
    <td class="separateur"><img src="../z-outils/images/boite-outils/espaceur.gif" width="1" height="30"></td>
  </tr>
  <tr>
    <td height="1"><img src="../z-outils/images/boite-outils/espaceur.gif" width="150" height="1"></td>
    <td height="1" class="separateur"><img src="../z-outils/images/boite-outils/espaceur.gif" width="1" height="1"></td>
    <td height="1" class="separateur"><img src="../z-outils/images/boite-outils/espaceur.gif" width="599" height="1"></td>
    <td class="separateur"><img src="../z-outils/images/boite-outils/espaceur.gif" width="1" height="1"></td>
  </tr>
  <tr>
    <td width="150">&nbsp;</td>
    <td width="1" class="separateur"><img src="../z-outils/images/boite-outils/espaceur.gif" width="1" height="1"></td>
    <td width="599" rowspan="2"><div id="ZonePrint">
      <!-- InstanceBeginEditable name="Contenu" -->

	<h1> Changement de Mot de Passe </h1>
	<h2> Annuaire LDAP de l'IBCG </h2>
	<?php
	
	$etape = getPGS('etape',0);
	// AVANT de faire quoi que ce soit, on v�rifie s'il y a bien des dicos dans lesquels tester le mot de passe...
	// R�cup�re la liste des dicos disponibles dans le dossier "dicos"
	$listeDicos = array();
	$dossierDicos = "dicos";
	$fichiers = scandir($dossierDicos);
	foreach ($fichiers as $fic) {	// Rempli le tableau $listeDicos en prenant les fichiers .TXT du dossier...
		$ficComplet = $dossierDicos."/".$fic;
		if ( (!is_dir($ficComplet)) and (substr($ficComplet,0,1)!=".") and (strtolower(pathinfo($ficComplet, PATHINFO_EXTENSION))=="txt") ) {
			$listeDicos[] = $ficComplet;
		}
	}
	if (count($listeDicos)==0) {	// Pas de dicos trouv�s ! erreur, pas d'action effectu�e
		$etape = 100;
		$action = "";
	}
	// V�rifie si Crack_lib est disponible !
	if ( (!function_exists('crack_opendict')) or (!function_exists('crack_check'))
	or (!function_exists('crack_getlastmessage')) or (!function_exists('crack_closedict')) ) {
		$etape = 101;
		$action = "";
	}
	if ( ($etape==1) or ($etape==3) ) {  
		$champUtilisateur = getPGS('champUtilisateur',getPGS('uid',''));
		$champMotPasse = getPGS('champMotPasse',"");
		$action = getPGS('action','');
		$messageErreur = array();
		if ($etape==3) {	// Etape 3 - Changement du mot de passe                      
			$champMotPasseNouveau = getPGS('champMotPasseNouveau',"");
			$champMotPasseConfirme = getPGS('champMotPasseConfirme',"");
			$code = getPGS('code','');
			$codeValidation = md5($champUtilisateur);
			if ($code=='') {	// Code de validation non indiqué pour l'étape 3
				$messageErreur[] = "Vous devez d'abord demander &agrave; recevoir un mail de validation de la demande.";
			}
			if ($code!=$codeValidation) {	// Code de validation incorrect
				$messageErreur[] = "Vous devez utiliser le lien compet du mail de validation de la demande.";
			}
			if (count($messageErreur)>0) {	// Déjà une erreur, obligatoirement dus à un code de validation erronné à l'étape 3, donc retour à l'étape 1
				$etape = 1;
			}
		}
		switch ($action) {
			case BOUTON_OK_IDENTIFIE:	// Vérifie l'authentification !
				if ($champUtilisateur=="") {
					$messageErreur[] = "Vous devez indiquer un nom d'utilisateur.";
				} elseif ($champMotPasse=="") {
					$messageErreur[] = "Vous devez indiquer votre mot de passe.";
				}
				if (count($messageErreur)==0) {		// Utilisateur & Mot de passe indiqués !
					$infoLDAP = ldapAuthentifieUtilisateur($champUtilisateur,$champMotPasse);
					if (is_null($infoLDAP)) {
						$messageErreur[] = "Acc&egrave;s &agrave; l'annuaire impossible, r&eacute;essayez plus tard.";
					} elseif ($infoLDAP!=TRUE) {
						$messageErreur[] = "Utilisateur et/ou mot de passe erronn&eacute;s.";
					} else {	// Authentification correcte : Envoyer le mail puis passer à l'étape 2 !
						$infoUtilisateur = ldapTrouveUtilisateur($champUtilisateur,array('uid','mail','mailforwardingaddress','displayname'));
						$codeValidation = md5($infoUtilisateur['uid']);
						$mailLien = "http://intranet.ibcg.biotoul.fr/password/index.php?etape=3&uid=".$infoUtilisateur['uid']."&code=".$codeValidation;
						$mailExpediteur = "Administrateur IBCG <hpadmin@ibcg.biotoul.fr>";
						$mailEntetes = "From:".$mailExpediteur."\r\n";
						$mailEntetes.= "Cc:Administrateur IBCG <hpadmin@ibcg.biotoul.fr>\r\n";
						$mailDestinataire = $infoUtilisateur['displayname']." <".$infoUtilisateur['mail'].">";
						$mailSujet = "Demande de changement de mot de passe pour ".$infoUtilisateur['displayname'];
						$mailMessage = "Vous avez demandé à modifier votre mot de passe LDAP de l'IBCG.\r\n";
						$mailMessage.= "Si vous n'êtes pas à l'origine de cette demande, contactez IMMEDIATEMENT le service informatique de l'IBCG !\r\n\r\n";
						$mailMessage.= "Vous allez pouvoir accéder à l'étape suivante en cliquant sur le lien ci-dessous.\r\n\r\n";
						$mailMessage.= "Si votre logiciel de messagerie ne vous permet pas de cliquer simplement dessus,\r\n";
						$mailMessage.= " copiez le dans son intégralité pour le collez dans la barre d'adresse de votre navigateur.\r\n\r\n";
						$mailMessage.= "$mailLien\r\n";
						if (mail($mailDestinataire,$mailSujet,$mailMessage,$mailEntetes)) {	// Mail envoyé : passe à l'étape suivante !
							$etape = 2;
						} else {	// Problème d'envoi du mail, affiche message et reste sur la page du début...
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
				} else {	// Mot de passe indiqué, continue les vérifications :
					$infoLDAP = ldapAuthentifieUtilisateur($champUtilisateur,$champMotPasse);
					if (is_null($infoLDAP)) {
							$messageErreur[] = "Acc&egrave;s à l'annuaire impossible, r&eacute;essayez plus tard.";
					} elseif ($infoLDAP!=TRUE) {
							$messageErreur[] = "Nom d'utilisateur et/ou mot de passe erronn&eacute;s.";
					} else {	// Authentification correcte : Continuer les vérifications
						// Pour contr�le de la pr�sence du mot de passe dans un dico...
						$tropCourt = FALSE;	// Evite de multiplier les messages d'erreur identiques
						$tropSimple = FALSE;	// idem
						foreach ($listeDicos as $dico) {
							$resDico = crack_opendict($dico);
							if ($resDico!==FALSE) {
								$resultCheck = crack_check($resDico, $champMotPasseNouveau);
								if ($resultCheck==FALSE) {
								/*	$erreur = crack_getlastmessage();
									switch ($erreur) {
										case "it's WAY too short":	// (Le mot de passe est beaucoup trop court)
										case "it is too short":	//  (Le mot de passe est trop court)
											if (!$tropCourt) {
												$messageErreur[] = "Le mot de passe est beaucoup trop court";
												$tropCourt = TRUE;
											}
										break;
										case "it does not contain enough DIFFERENT characters":	//  (Le mot de passe ne contient pas assez de caract�res)
										case "it is all whitespace":	//  (Le mot de passe ne contient que des espaces)
										case "it is too simplistic/systematic":	//  (Le mot de passe est trop simple)
										case "it looks like a National Insurance number.":	//  (Le mot de passe ressemble � un num�ro d'assurance nationale.)
											if (!$tropSimple) {
												$messageErreur[] = "Le mot de passe est trop simple";
												$tropSimple = TRUE;
											}
										break;
										case "it is based on a dictionary word":	//  (Le mot de passe est bas� sur un mot du dictionnaire)
										case "it is based on a (reversed) dictionary word":	//  (Le mot de passe est bas� sur un mot invers� du dictionnaire)
											$messageErreur[] = "Le mot de passe est bas� sur un mot du dictionnaire ".pathinfo($dico, PATHINFO_FILENAME);
										break;
										case "strong password":	//  (Le mot de passe est solide, pas de message d'erreur suppl�mentaire !)
										break;
									}
									*/
									$messageErreur[] = "Crack_lib refuse !";
								}
								crack_closedict($resDico);
							}
						}
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
				if (count($messageErreur)==0) {		// Toutes les vérifications sont passées avec succès : essai de changer le mot de passe
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
					} else {	// Pas d'erreur SSH : Vérifie si le mot de passe a pu être changé
						if (ldapAuthentifieUtilisateur($champUtilisateur,$champMotPasseNouveau)) {    // Vérifie si le mot de passe a pu être changé :
							$etape = 4;	// C'est bon : étape suivante et mail de confirmation
							$infoUtilisateur = ldapTrouveUtilisateur($champUtilisateur,array('uid','mail','mailforwardingaddress','displayname'));
							$mailExpediteur = "Administrateur IBCG <hpadmin@ibcg.biotoul.fr>";
							$mailEntetes = "From:".$mailExpediteur."\r\n";
							$mailEntetes.= "Cc:Administrateur IBCG <hpadmin@ibcg.biotoul.fr>\r\n";
							$mailDestinataire = $infoUtilisateur['displayname']." <".$infoUtilisateur['mail'].">";
							$mailSujet = "Changement de mot de passe pour ".$infoUtilisateur['displayname'];
							$mailMessage = "Vous avez modifié votre mot de passe LDAP de l'IBCG.\r\n";
							$mailMessage.= "N'oubliez pas de le modifier partout où vous pourriez l'avoir enregistré (navigateurs internet, logiciels de messagerie...)\r\n\r\n";
							$mailMessage.= "Par sécurité :\r\n- ne l'écrivez pas,\r\n";
							$mailMessage.= "- ne le communiquez à personne,\r\n";
							mail($mailDestinataire,$mailSujet,$mailMessage,$mailEntetes);
						} else {	// Le nouveau mot de passe n'a pas pu être changé, explique pourquoi et quoi faire.
							if (ldapAuthentifieUtilisateur($champUtilisateur,$champMotPasse)) {    // Le mot de passe n'a pas été changé
							$messageErreur[] = "Le nouveau mot de passe a &eacute;t&eacute; refus&eacute; par le serveur<br /><strong>Essayez-en un autre</strong>";
							/* $messageErreur[] = "Commande : (".$commande.")";
							$messageErreur[] = "Codes de retour : (".$retourSSH['out'].")";
							$messageErreur[] = "Codes d'erreur : (".$retourSSH['erreur'].")"; */
							} else {	// Le mot de passe a été changé mais incorrectement !!
								$messageErreur[] = "Probl&egrave;me de changement de mot de passe : contactez le Service Informatique !";
							}
						}
					}
				}
				break;
		}
	}
	switch ($etape) {
		case 100:
		case 101:
	?>
    	<h1> Erreur SINFO-<?php echo($etape); ?> </h1>
        <p> En raison d'une erreur sur le serveur, le changement de mot de passe est impossible actuellement. </p>
        <p class="green_button"> <a href="?etape=1">contactez le service informatique !</a> </p>
    <?php
		break;
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
		si vous constatez des dysfonctionnements dans l'imm&eacute;diat, veuillez patienter quelques instants avant de refaire un essai... </p>
		<p> Si vous &ecirc;tes identifi&eacute; sur un des services de l'IBCG (votre messagerie par exemple), veuillez vous d&eacute;connecter, redémarrer votre navigateur, puis vous reconnecter ! </p>
		<p> Utilisez un des services suivants pour v&eacute;rifier que votre mot de passe a bien &eacute;t&eacute; modifi&eacute; : </p>
		<ul>
			<li> Ouvrir une session sur un ordinateur sous Windows &agrave; l'IBCG ; </li>
			<li> Messagerie SOGo : <a href="https://courriel.biotoul.fr" target="_self"> https://courriel.biotoul.fr </a> ; </li>
		</ul>
	<?php
		break;
	}	// Fin du SWITCH $etape
	?>
</div></div>
    </td>
    <td width="1" class="separateur"><img src="../z-outils/images/boite-outils/espaceur.gif" width="1" height="500"></td>
  </tr>
  <tr>
    <td width="150" class="XnavgaucheIcones"><img src="../z-outils/images/charte/icones-01.gif" alt="" width="150" height="55" border="0" usemap="#Map2"></td>
    <td width="1" class="separateur"><img src="../z-outils/images/boite-outils/espaceur.gif" width="1" height="1"></td>
    <td width="1" class="separateur"><img src="../z-outils/images/boite-outils/espaceur.gif" width="1" height="1"></td>
  </tr>
  <tr>
    <td width="150" class="separateur"><img src="../z-outils/images/boite-outils/espaceur.gif" width="150" height="1"></td>
    <td width="1" class="separateur"><img src="../z-outils/images/boite-outils/espaceur.gif" width="1" height="1"></td>
    <td width="599" class="separateur"><img src="../z-outils/images/boite-outils/espaceur.gif" width="1" height="1"><img src="../z-outils/images/boite-outils/espaceur.gif" width="150" height="1"></td>
    <td width="1" class="separateur"><img src="../z-outils/images/boite-outils/espaceur.gif" width="1" height="1"></td>
  </tr>
</table>
<p>&nbsp;</p>
 <p>&nbsp;</p>
 <div id="divpartenaires" >
   <table width="150"  border="0" cellspacing="0" cellpadding="0">
     <tr><td class="separateur"><img src="../z-outils/images/boite-outils/espaceur.gif" width="150" height="1"></td></tr>
     <tr><td><a href="http://www.cnrs.fr" target="_blank"><img src="../z-outils/images/charte/logo-cnrs.jpg" alt="" width="150" height="65" border="0"></a></td>
     </tr>
     <tr><td class="separateur"><img src="../z-outils/images/boite-outils/espaceur.gif" width="150" height="1"></td></tr>
     <tr><td>&nbsp;</td>
     </tr>
   </table>
</div>
 <p>
   <script type='text/javascript'>function Go(){return}</script>
   <script type='text/javascript' src='../z-outils/deroulants/top_pos_var.js'></script>
   <script type='text/javascript' src='../z-outils/deroulants/menu_var.js'></script>
   <script type='text/javascript' src='../z-outils/deroulants/menu9_com.js'></script>
</p>
 <noscript>
 <p>Your browser does not support script</p>
</noscript>

<div id="divnavgauche-spec"> 
  <table border="0" cellpadding="0" cellspacing="0"  width="150">
    <tr> 
      <td height="2"> <img border="0" src="../z-outils/images/boite-outils/pointilles.gif" alt="" width="150" height="3"></td>
    </tr>
    <tr> 
      <td width="100%"  class="Xnavgauche" > 
        <table border="0" cellpadding="10" cellspacing="0"  width="100%">
          <tr> 
            <td width="100%" class="Xnavgauche"> 
              <p class="intertitre"><a href="../annuaire.php">Annuaires</a></p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr> 
      <td height="2"> <img border="0" src="../z-outils/images/boite-outils/pointilles.gif" alt="" width="150" height="3"></td>
    </tr>
    <tr> 
      <td width="100%"  class="Xnavgauche" > 
        <table border="0" cellpadding="10" cellspacing="0"  width="100%">
          <tr> 
            <td width="100%" class="Xnavgauche"> 
              <p class="intertitre" >Rechercher</p>
              <p>Sur le WEB du CNRS <br>
                <br>
                <br>
            </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr> 
      <td height="2"> <img border="0" src="../z-outils/images/boite-outils/pointilles.gif" alt="" width="150" height="3"></td>
    </tr>
  </table>
</div>
<div id="divnavgauche-search"> 
  <form name="rechercher-spm" action="http://www.cnrs.fr/rechercher/" method="POST"
       target="_blank">
    <table cellspacing="0" cellpadding="0" border="0">
      <tr> 
        <td> 
          <input name ="request" maxLength="50" size="10" class="BoiteRechercher">
        </td>
        <td> <img border="0" src="../z-outils/images/boite-outils/espaceur.gif" width="10" height="8"
                alt=""></td>
        <td valign="middle" > 
          <input name="submit" type="image" src="../z-outils/images/charte/ok.gif" 
                                border="0" width="20" height="20">
        </td>
      </tr>
    </table>
  </form>
</div>
<div id="divnavhaut-nom-labo"> 
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr> 
      <td class="Xnavhaut"> 
        <p>Bienvenue sur l'intranet de l'IBCG</p>
      </td>
    </tr>
  </table>
</div>
<map name="Map2">
  <area shape="rect" coords="28,23,46,43" href="javascript:impression()" alt="Imprimer">
  <area shape="rect" coords="49,23,66,43" href="javascript:writemail('labo.cnrs.fr','contact','',1);" alt="Contact">
  <area shape="rect" coords="68,23,85,43" href="../modeles/Plan du site" alt="Plan du site">
  <area shape="rect" coords="87,23,105,43" href="../modeles/credits.htm" alt="Crédits">
  <area shape="rect" coords="107,23,128,43" href="#" alt="Plug-ins">
  <area shape="rect" coords="9,24,25,43" href="../modeles/une.htm" alt="Accueil">
</map>
</body>
<!-- InstanceEnd --></html>