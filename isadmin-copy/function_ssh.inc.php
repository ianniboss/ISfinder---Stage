<?PHP
define('SSH_server','lesgun');
define('SSH_user','root');
define('SSH_pass','S4cmQS3');
//define('SSH_user','mdp');
//define('SSH_pass','mdp!123');
define('SSH_Err_Exec',"Erreur d'ex&eacute;cution SSH");
define('SSH_Err_Ident',"Erreur d'identification SSH");
define('SSH_Err_Connect',"Erreur de connection SSH");
/**
 * ExÃ©cute une commande SSH sur un serveur distant.
 * Retourne un tableau de valeurs indiquant le contenu du flux de sortie (NULL en cas d'erreur) et le type d'erreur (NULL en cas de rÃ©ussite)
 * Exemple d'utilisation :
 * 	$commande = 'ls -l';	// Mettre la commande que l'on veut...
 *	$retourSSH = sshCommande($commande);
 *	if (is_null($retourSSH['out'])) { echo($retourSSH['erreur']); }
 *	else { echo('Commande exÃ©cutÃ©e : '.$retourSSH['out']); }
 * **/
function sshCommande($commande=NULL, $serveur=NULL, $port=NULL, $user=NULL, $password=NULL) {
	$retour = array('out'=>NULL, 'erreur'=>NULL);
	if (!is_null($commande)) { var_dump($commande);
		$sshConnection = ssh2_connect(SSH_server);
		if ($sshConnection!==FALSE) {
			if (ssh2_auth_password($sshConnection,SSH_user,SSH_pass)) {	// Identification rÃ©ussie
				$sshFluxSortie = ssh2_exec($sshConnection, $commande);
				if ($sshFluxSortie!==FALSE) {	// Commande exÃ©cutÃ©e :
					stream_set_blocking($sshFluxSortie, 1); 
					$retour['out'] = stream_get_contents($sshFluxSortie);
				} else {	$retour['erreur'] = SSH_Err_Exec;	}
			} else {	$retour['erreur'] = SSH_Err_Ident;	}
		} else {	$retour['erreur'] = SSH_Err_Connect;	}
	} // var_dump($retour);
	return $retour;
}

?>