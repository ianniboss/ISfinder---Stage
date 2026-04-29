<?php
define('LDAP_host','lesgun.ibcg.biotoul.fr');
define('LDAP_port','389');
define('LDAP_bind_dn','CN=root,DC=ad,DC=biotoul,DC=FR');
define('LDAP_password','S4cmQS3');
define('LDAP_base_dn','dc=ad,dc=biotoul,dc=fr');
/**
 * Trouve un utilisateur par son adresse mail (exacte !).
 * @param	string	$uid			identifiant de l'utilisateur dans l'annuaire LDAP
 * @param	array	$attributsDemandes	tableau d'attributs Ã  retourner
 * Retourne :
 * 	- FALSE si l'accÃ¨s Ã  l'annuaire LDAP est impossible
 * 	- NULL si l'utilisateur est introuvable (ou si rien n'a Ã©tÃ© indiquÃ©, valeur par dÃ©faut...)
 * 	- un tableau associatif des donnÃ©es LDAP pour l'utilisateur, s'il est trouvÃ©.
 **/
function ldapTrouveUtilisateur($uid='', $ldapAttributs=NULL) {
	$retour = NULL;
	if ($uid<>'') {	// Connexion au serveur LDAP uniquement si une adresse mail est indiquÃ©e
		$ldapConnexion = ldap_connect(LDAP_host, LDAP_port); 
		if ($ldapConnexion) {	// VÃ©rifie si la connexion au serveur LDAP est possible
                        @ldap_set_option($ldapConnexion, LDAP_OPT_PROTOCOL_VERSION, 3);
			$ldapBind = @ldap_bind($ldapConnexion);
			if ($ldapBind) {
				$ldapFiltre = "(uid=".$uid.")";
				if (is_null($ldapAttributs)) { 	// employeetype = Ã©quipe ; businesscategory = laboratoire ;
					$ldapAttributs = array('uid','mail','mailforwardingaddress','employeetype','businesscategory');
				}
				$ldapRecherche = ldap_search($ldapConnexion,LDAP_base_dn,$ldapFiltre,$ldapAttributs);
				$ldapInfo = ldap_get_entries($ldapConnexion,$ldapRecherche);
				foreach ($ldapAttributs as $uneCle) {	// CrÃ©e le tableau de rÃ©sultat vide avec les clÃ©s demandÃ©es !
					$retour[$uneCle] = '';
				}
				foreach ($ldapInfo[0] as $cle=>$valeur) {	// Parcours les valeurs du tableau
					if (array_key_exists($cle,$retour)) {	// Si la clÃ© correspond Ã  une valeur demandÃ©e
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
 * VÃ©rifie l'authentification d'un utilisateur par son UID OU mail ; et mot de passe.
 * Retourne :
 * 	- NULL si l'accÃ¨s Ã  l'annuaire LDAP est impossible
 * 	- FALSE si le mot de passe est incorrect (mais adresse mail valide)
 * 	- TRUE si l'authentification est valide.
 **/
function ldapAuthentifieUtilisateur($user='',$password='') {
	$retour = FALSE;
	$ldapConnexion = ldap_connect(LDAP_host, LDAP_port);
	if ($ldapConnexion) {
                @ldap_set_option($ldapConnexion, LDAP_OPT_PROTOCOL_VERSION, 3);
		$ldapBind = @ldap_bind($ldapConnexion, "uid=".$user.",ou=Peoples,".LDAP_base_dn, $password);
		if ($ldapBind) {
			$retour = TRUE;
			@ldap_unbind($ldapConnexion);
		}
	} else {	$retour = NULL;	}
	return $retour;
}
?>