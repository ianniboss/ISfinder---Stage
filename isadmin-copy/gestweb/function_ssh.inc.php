<?PHP
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
 * 
function sshCommande($commande = NULL) {
	$retour = array('out'=>NULL, 'erreur'=>NULL);
	if (!is_null($commande)) {
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
}**/
function sshFTP($serveur, $origine, $destination) {
$methods = array(
  'kex' => 'diffie-hellman-group1-sha1',
  'client_to_server' => array(
  'crypt'            => '3des-cbc',
  'comp'             => 'none'),
  'server_to_client' => array(
  'crypt'            => 'aes256-cbc,aes192-cbc,aes128-cbc',
  'comp'             => 'none'));

$callbacks = array('disconnect' => 'my_ssh_disconnect');
    $serveurOK = false;
    if ($serveur=="cbi-toulouse") {
            $serveurOK = TRUE;
            $ip = "194.57.136.10";
            $port = 50023;
            $user = "lab0546";
            $pass = "WD4+h1rr+";
    }
    if ($serveurOK) {
        $connexion = ssh2_sftp($ip, $port, $methods, $callbacks);
        ssh2_auth_password($connexion, $user, $pass);
        ssh2_scp_send($connexion, $origine, $destination);
    } else {  return "Serveur inconnu";  }
}


/**
 * Exécute une commande SSH sur un serveur distant.
 * Retourne soit une chaîne indiquant le type d'erreur, soit un flux de sortie,
 * Donc : (is_string($sshCommande('commande....'))) -> indique une erreur
 *     et (!is_string($sshCommande('commande....'))) -> la commande à dû fonctionner...
 * Spécifier la machine est obligatoire, elle doit être indiquée dans la table $SSH_MACHINES.
 * **/
function sshCommande($machine=NULL, $commande=NULL) {
	$SSH_MACHINES = array('cagire'=>array('SSH_server'=>'cagire.ibcg.biotoul.fr', 'SSH_IP'=>'192.168.12.10'),
                            'baqueira'=>array('SSH_server'=>'baqueira.ibcg.biotoul.fr', 'SSH_IP'=>'192.168.12.19'),
                            'peric'=>array('SSH_server'=>'peric.ibcg.biotoul.fr', 'SSH_IP'=>'192.168.12.15'),
                            'cbi-toulouse'=>array('SSH_server'=>'194.57.136.10', 'SSH_IP'=>'194.57.136.10',  'SSH_user'=>'lab0546', 'SSH_pass'=>'WD4+h1rr+', 'SSH_port'=>50023));
    $retour = NULL;
	if (!array_key_exists($machine, $SSH_MACHINES)) {	$retour = SSH_ERREUR_SERVEUR.'-1';	}	// La machine n'est pas définie (clé inexistante) !
	elseif (!is_array($SSH_MACHINES[$machine])) {		$retour = SSH_ERREUR_SERVEUR.'-2';	}	// La machine n'est pas définie (pas de tableau) !
	else {	// La machine est définie
		$infoMachine = $SSH_MACHINES[$machine];
		if ( (array_key_exists('SSH_server', $infoMachine)) and (array_key_exists('SSH_IP', $infoMachine)) ) {	// Les infos de la machine sont définies
			if ( ($infoMachine['SSH_server']<>"") and (estAdresseIP($infoMachine['SSH_IP'])) ) {	// Les infos ne sont pas vides
				if (!is_null($commande)) {	// La commande est indiquée
					if (function_exists('ssh2_connect')) {	// Fonctions SSH OK
						$sshConnection = ssh2_connect($infoMachine['SSH_server'], (array_key_exists('SSH_port', $infoMachine)?$infoMachine['SSH_port']:22));
						if ($sshConnection!==FALSE) {
							if (ssh2_auth_pubkey_file($sshConnection, (array_key_exists('SSH_user', $infoMachine)?$infoMachine['SSH_user']:'root'), '/var/www/.ssh/id_rsa.pub', '/var/www/.ssh/id_rsa')) {    // Identification réussie
								$sshFluxSortie = ssh2_exec($sshConnection, $commande);
								if ($sshFluxSortie!==FALSE) {    // Commande exécutée :
									stream_set_blocking($sshFluxSortie, 1);  
									$retour = stream_get_contents($sshFluxSortie);
								} else {    $retour = "Erreur d'exécution de la commande";    }
							}
                                                        elseif ( (array_key_exists('SSH_user', $SSH_MACHINES)) and (array_key_exists('SSH_pass', $SSH_MACHINES)) ) {
                                                            if (ssh2_auth_password($sshConnection, $infoMachine['SSH_user'], $infoMachine['SSH_pass'])) {   // Identification réussie par mot de passe
								$sshFluxSortie = ssh2_exec($sshConnection, $commande);
								if ($sshFluxSortie!==FALSE) {    // Commande exécutée :
									stream_set_blocking($sshFluxSortie, 1);  
									$retour = stream_get_contents($sshFluxSortie);
								} else {    $retour = "Erreur d'exécution de la commande";    }
                                                            } else {    $retour = "Identification par clé impossible, mot de passe non renseigné";    }
                                                        }
                                                        else {    $retour = "Erreur d'identification (ni par clé, ni par mot de passe)";    }
						} else {    $retour = "Le serveur n'est pas joignable";    }
					} else {    $retour = "Fonctions SSH indisponibles sur le serveur";    }	// Fonctions SSH indisponibles
				} else {    $retour = "Commande non renseignée";    }
			} else {	$retour = "Le serveur indiqué n'est pas entièrement renseigné"; }
		} else {	$retour = "Le serveur indiqué n'est pas renseigné";	}
	}
    return $retour;
}
/**
 * Exécute une commande SSH sur un serveur distant.
 * Retourne soit une chaîne indiquant le type d'erreur, soit un flux de sortie,
 * Donc : (is_string($sshCommande('commande....'))) == TRUE -> indique une erreur
 *     et (is_string($sshCommande('commande....'))) == FALSE -> la commande à dû fonctionner...
 * Spécifier la machine est obligatoire, elle doit être indiquée dans la table $SSH_MACHINES.
 * **/
function sshCommande2($commande=NULL, $machine=NULL, $sudo=FALSE) {
	$SSH_MACHINES = array('cagire'=>array('SSH_server'=>'cagire.ibcg.biotoul.fr', 'SSH_IP'=>'192.168.12.10', 'SSH_user'=>'root', 'SSH_pass'=>'ph:78mm'),
						'peric'=>array('SSH_server'=>'peric.ibcg.biotoul.fr', 'SSH_IP'=>'192.168.12.15',  'SSH_user'=>'root', 'SSH_pass'=>'ph:78mm'),
						'aneto'=>array('SSH_server'=>'aneto.ibcg.biotoul.fr', 'SSH_IP'=>'192.168.12.17',  'SSH_user'=>'root', 'SSH_pass'=>'ph:78mm'),
                                                'cbi-toulouse'=>array('SSH_server'=>'194.57.136.10', 'SSH_IP'=>'194.57.136.10',  'SSH_user'=>'lab0546', 'SSH_pass'=>'WD4+h1rr+', 'SSH_port'=>50023));
    $retour = NULL;
	if (!array_key_exists($machine, $SSH_MACHINES)) {	$retour = SSH_ERREUR_SERVEUR.'-1';	}	// La machine n'est pas définie (clé inexistante) !
	elseif (!is_array($SSH_MACHINES[$machine])) {		$retour = SSH_ERREUR_SERVEUR.'-2';	}	// La machine n'est pas définie (pas de tableau) !
	else {	// La machine est définie   
		$infoMachine = $SSH_MACHINES[$machine];
		if ( (array_key_exists('SSH_server', $infoMachine)) and (array_key_exists('SSH_IP', $infoMachine))
		and (array_key_exists('SSH_user', $infoMachine)) and (array_key_exists('SSH_pass', $infoMachine)) ) {	// Les infos de la machine sont définies
			if ( ($infoMachine['SSH_server']<>"") and (estAdresseIP($infoMachine['SSH_IP']))
			and ($infoMachine['SSH_user']<>"") and ($infoMachine['SSH_pass']<>"") ) {	// Les infos ne sont pas vides
				if (!is_null($commande)) {	// La commande est indiquée
					if (function_exists('ssh2_connect')) {	// Fonctions SSH OK
						$sshConnection = ssh2_connect($infoMachine['SSH_server'], (array_key_exists('SSH_port', $infoMachine)?$infoMachine['SSH_port']:22));
						if ($sshConnection!==FALSE) {
							if (ssh2_auth_password($sshConnection, $infoMachine['SSH_user'], $infoMachine['SSH_pass'])) {    // Identification réussie
								$sshFluxSortie = ssh2_exec($sshConnection, ( $sudo ? "sudo " : "" ).$commande);
								if ($sshFluxSortie!==FALSE) {    // Commande exécutée :
									stream_set_blocking($sshFluxSortie, 1);  
									$retour = stream_get_contents($sshFluxSortie);
								} else {    $retour = SSH_ERREUR_EXECUTION;    }
							} else {    $retour = SSH_ERREUR_IDENTIFICATION;    }
						} else {    $retour = SSH_ERREUR_CONNECTION;    }
					} else {    $retour = SSH_ERREUR_FONCTION;    }	// Fonctions SSH indisopnibles
				} else {    $retour = SSH_ERREUR_COMMANDE;    }
			} else {	$retour = SSH_ERREUR_INFO_SERVEUR.'-3'; }
		} else {	$retour = SSH_ERREUR_INFO_SERVEUR.'-4';	}
	}
    return $retour;
} 

