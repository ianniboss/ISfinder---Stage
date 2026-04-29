<?php
/**
 * LL     DDDDD    AA   PPPPP
 * LL     D    D  A  A  PP   P
 * LL     D    D AAAAAA PPPPP
 * LL     D    D A    A PP
 * LLLLLL DDDDD  A    A PP
 **/
define('LDAP_host','ossau.ibcg.biotoul.fr');
define('LDAP_port','389');
define('LDAP_bind_dn','cn=rootldap,dc=ibcg,dc=biotoul,dc=fr');
define('LDAP_password','pinduitr');
define('LDAP_base_dn','dc=ibcg,dc=biotoul,dc=fr');

/**
 * Trouve un utilisateur par son adresse mail (exacte !).
 * Retourne :
 * 	- FALSE si l'acc&egrave;s &agrave; l'annuaire LDAP est impossible
 * 	- NULL si l'adresse mail est introuvable (ou si une adresse vide a &eacute;t&eacute; indiqu&eacute;e, valeur par d&eacute;faut...)
 * 	- un tableau associatif des donn&eacute;es LDAP pour l'utilisateur, s'il est trouv&eacute;.
 * On notera en particulier la n&eacute;cessit&eacute; de r&eacute;cup&eacute;rer son UID par :
 * 	$infoLdapUser = ldapTrouveUtilisateur($mail);
 * 	$UID = ( is_array($infoLdapUser) ? $infoLdapUser['uid'] : '' );
 **/
function ldapTrouveUtilisateur($mail='') {
	$retour = NULL;
	if ($mail<>'') {
		$ldapConnexion = ldap_connect(LDAP_host, LDAP_port);
		if ($ldapConnexion) {
			$ldapBind = ldap_bind($ldapConnexion, LDAP_bind_dn, LDAP_password);
			
		} else {
			$retour = FALSE;
		}
	}
	return $retour;
}

/**
 * V&eacute;rifie l'authentification d'un utilisateur par son UID OU mail ; et mot de passe.
 * Retourne :
 * 	- NULL si l'acc&egrave;s &agrave; l'annuaire LDAP est impossible
 * 	- un message d'erreur en texte si :
 * 		- l'adresse mail OU uid indiqu&eacute; sont introuvables
 * 	- FALSE si le mot de passe est incorrect (mais adresse mail valide)
 * 	- TRUE si l'authentification est valide.
 * On notera en particulier la n&eacute;cessit&eacute; de r&eacute;cup&eacute;rer son UID par :
 * 	$infoLdapUser = ldapTrouveUtilisateur($mail);
 * 	$UID = ( is_array($infoLdapUser) ? $infoLdapUser['uid'] : '' );
 **/
function ldapAuthentifieUtilisateur($uid='',$password='') {
	$retour = FALSE;
	$ldapConnexion = ldap_connect(LDAP_host, LDAP_port);
	if ($ldapConnexion) {
		$ldapBind = @ldap_bind($ldapConnexion, "uid=".$uid.",ou=Peoples,".LDAP_base_dn, $password);
		if ($ldapBind) { $retour = TRUE; }
	} else {
		$retour = NULL;
	}
	return $retour;
}