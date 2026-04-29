<?PHP

/**
 * En cas d'erreur SQL, renvoie la valeur null après avoir affiché l'erreur (là ou ça tombe !)
 * S'il n'y a pas de résultat (0 lignes), retourne un tableau de valeur NULL.
 * @param string $requeteSQL	Requete à envoyer telle qu'elle (aucun contrôle !)
 * @param string $champID	Identifiant utilisé comme clé du tableau de valeurs, pour les requètes SELECT (inutile dans les autres cas)
 * La donnée renvoyée dépend du type de requette :
 * INSERT : ID de la ligne insérée (null = pas d'insertion possible)
 * UPDATE : nombre de lignes modifiées (null = modif non faite ; 0 = aucune !)
 * SELECT : tableau de valeurs "id" => tableau associatif "nom_champ" => "valeur" (null = pas de résultat trouvé (ou erreur...))
 * DELETE : nombre de lignes supprimées (0 ou null = rien effacé (erreur si null...)
 **/
function sqlRequete($requeteSQL, $champID=NULL) {
//	echo('[ '.$requeteSQL.' ]<br />');
	$resultatArray=null;
	// récupération des résultats SI possible...
	$lien = mysql_connect(DB_server,DB_user,DB_password);
	if (!$lien) {
		echo('ERREUR '.mysql_error().' : Accès Impossible au serveur SQL.');
		$retour = FALSE;
	} else {
		if (!mysql_select_db(DB_bdd)) {
			echo('ERREUR '.mysql_error().' : Accès impossible à la base de données.');
			$retour = FALSE;
		} else {
			$resultat = mysql_query($requeteSQL,$lien);
			if (!$resultat) {
				echo('ERREUR '.mysql_error().' : Impossible d&acute;exécuter la requète ('.$requeteSQL.').');
				$retour = FALSE;
			} else {
				if (stristr($requeteSQL,"SELECT")!==FALSE) {	// SELECT
					if (mysql_num_rows($resultat)>0) {	// Renvoi les lignes dans un tableau de tableau associatif...
						while ($ligne = mysql_fetch_assoc($resultat)) {
                                                    if (is_string($champID)) {  $resultatArray[$ligne[$champID]] = $ligne;  }
                                                        else {                  $resultatArray[] = $ligne;  }
                                                }
						$retour = $resultatArray;
					} else {	$retour = FALSE;	}
					if ($retour=='') { $retour = FALSE; }
				}
				if (stristr($requeteSQL,"INSERT")!==FALSE) {	$retour = mysql_insert_id($lien);	}
				if ( (stristr($requeteSQL,"UPDATE")!==FALSE) || (stristr($requeteSQL,"DELETE")!==FALSE) ) {	$retour = mysql_affected_rows($lien);	}
			}
		}
		mysql_close($lien);
	}
	return($retour);
}
/**
 * Retourne un tableau ordonné de tableau associatif nom_champ => valeur.
 * En cas d'erreur SQL, renvoie une chaine de caractère décrivant l'erreur,
 * S'il n'y a pas de résultat (0 lignes), retourne un tableau de valeur NULL.
 * Se contente de construire la requete, et utilise la fonction sqlRequete.
 * Remplacement éventuel du champ limite par le champ groupe (selon le type !) :
 * 	nb_xxx qui renvoie le nombre de lignes en groupant xxx :
 * 	exemple : sqlSelect('logs_visite','page NOT LIKE "/intranet/%"','nb_page desc','page')
 * @param string	$table			Table sur laquelle on effectue la requète
 * @param string	$condition		Condition pour sélectionner les lignes
 * @param string	$tri			Tri pour retour des résultats
 * @param numeric	$limite_ou_groupe	Si nombre : indique le nombre de ligne à retourner, attention, si le nombre est indiqué en type string, cela ne fonctionnera pas !
 * @param string	$limite_ou_groupe	Si chaine : indique le champ à grouper : ajoute un champ nb_xxx qui indique le nombre
 * @param string        $champID                Identifiant utilisé comme clé du tableau de valeurs
 **/
function sqlSelect($table, $condition='', $tri='', $limite_ou_groupe=NULL, $champID=NULL) {
	$resultatArray=null;
	// construction de la requète :
	$sql = 'SELECT ';
	if ($table=='lan') {	// Spécifique si la table est "lan" !
		$sql.= 'lan_machines.idx as idx, lan_machines.adr_ip as adr_ip, lan.old_ip as old_ip, lan.vlan as vlan, lan.ip as ip, machines.nom as nom, machines.id_labo as id_labo, machines.id_equipe as id_equipe, machines.lieu as lieu, ';
		$sql.= 'machines.id_lieu as id_lieu, lan.date_debut as date_debut, lan.date_fin as date_fin, machines.date_installation as date_installation, machines.date_destruction as date_destruction, lan.ether as ether, ';
		$sql.= 'lan.dhcp as dhcp, lan.dhcp_nom as dhcp_nom, lan.dhcp_ether as dhcp_ether, lan.dhcp_user as dhcp_user, lan.id_prise as id_prise, machines.os as os, machines.type as type, lan.id_switch as id_switch, lan.com as com, ';
		$sql.= 'lan_machines';
	}
	$sql.= '*';
	if ( (!is_null($limite_ou_groupe)) and (is_string($limite_ou_groupe)) ) {
		$sql.= ', COUNT('.$limite_ou_groupe.') as nb_'.$limite_ou_groupe;
		if ($tri!='') { $tri = 'ORDER BY '.$tri; }
		$tri = 'GROUP BY '.$limite_ou_groupe.' '.$tri;
	}
	$sql.= ' FROM '.$table;
	if ($condition!='') { $sql.= ' WHERE '.$condition; }
	if ($tri!='') { $sql.= ((stristr($tri,"GROUP BY")===FALSE)?" ORDER BY ":" ").$tri; }
	if ( (!is_null($limite_ou_groupe)) and (is_numeric($limite_ou_groupe)) ) { $sql.= ' LIMIT '.$limite_ou_groupe; }
	$sql.= ' ;';    // echo($sql.'<br />');
	// récupération des résultats SI possible...
	return(sqlRequete($sql, $champID));
}

/**
 * Retourne un tableau associatif nom_champ => valeur de la première ligne correspondant à la condition indiquée selon le tri.
 * En cas d'erreur SQL, renvoie une chaine de caractère décrivant l'erreur,
 * S'il n'y a pas de résultat (0 lignes), retourne un tableau de valeur NULL.
 * @param string $table		Nom de la table dans laquelle faire la recherche
 * @param string $condition	Condition à prendre en compte
 * @param string $tri		Ordre de tri (TRES IMPORTANT car cette fonction retourne UNIQUEMENT la première ligne trouvée !)
 * @param string $groupe	Variable par laquelle on veut regrouper plusieurs lignes, retourne un champ supplémentaire nommé nb_xxx indiquant le nombre de lignes
 **/
function sqlTrouve($table,$condition='',$tri='',$groupe=NULL) {
	$resultatArray[]=null;
	// construction de la requète :
	$sql = 'SELECT *';
	if (is_string($groupe)) {
		$sql.= ', COUNT('.$groupe.') as nb_'.$groupe;
		if ($tri!='') { $tri = 'ORDER BY '.$tri; }
		$tri = 'GROUP BY '.$groupe.' '.$tri;
	} elseif ($tri!='') {
		$tri = 'ORDER BY '.$tri;
	}
	$sql.= ' FROM '.$table;
	if ($condition!='') { $sql.= ' WHERE '.$condition; }
	if ($tri!='') { $sql.= ' '.$tri; }
	$sql.= ' ;';
	// récupération des résultats SI possible...
	$resultat = sqlRequete($sql);
	if (is_array($resultat)) {
		$resultatArray = $resultat[0];
		if (!is_array($resultatArray)) {	$resultatArray = NULL;	}
	} else {	$resultatArray = NULL;	}
	return($resultatArray);
/*	if (!$resultat) { return('ERREUR '.mysql_error().' : Impossible d&acute;exécuter la requète ('.$sql.').'); }
	if (mysql_num_rows($resultat)==0) {	return null;	}
	else {	$resultatArray = mysql_fetch_assoc($resultat);	}
	return($resultatArray);*/
}

/**
 * Exécute une requete de mise à jour sur la $table, sur la condition indiquée
 * Met à jour les couples de cle=>valeur indiquées dans le tableau associatif $donnees
 * S'assure AVANT que les données ne sont pas déjà correctes !
 * Retourne la valeur FALSE si la mise à jour n'a pas pu se faire
 *          une valeur numérique (nombre de lignes affectées) si la mise à jour est faite
 *          la valeur NULL s'il n'y a rien à modifier (valeurs déjà correctes !
**/
function sqlUpdate($table, $condition, $donnees) {
	$retour = FALSE;
	if ( (is_array($donnees)) and (count($donnees)>=1) ) {	// Il y a des données à modifier !
		// Vérifie si les données doivent vraiment être modifiées !
		$donneesSQL = sqlRequete("SELECT ".implode(", ", array_keys($donnees))." FROM ".$table." WHERE ".$condition." ;");
		if ($donnees<>$donneesSQL) {	// Ne fais des modifications QUE si les données ne sont pas identiques !
			$sql = "UPDATE ".$table." SET ";
			$premiere_ligne = TRUE;
			foreach($donnees as $cle=>$valeur) {
				if ($premiere_ligne) {	$premiere_ligne = FALSE; }
				else {					$sql.= ", "; }
				if (is_null($valeur)) {			$valeur = "NULL"; }
				elseif ($cle=="password") {		$valeur = "MD5('".$valeur."')"; }
				elseif (!is_numeric($valeur)) { $valeur = '"'.texteVersBDD($valeur).'"'; }
				$sql.= "`".$cle."` = ".$valeur;
			}
			$sql.= " WHERE ".$condition." ;";
			$retour = sqlRequete($sql);
		} else {
			$retour = NULL;
		}
	}
	return $retour;
}

/**
 * Exécute une requete de mise à jour sur la $table, sur la condition indiquée
 * Met à jour les couples de cle=>valeur indiquées dans le tableau associatif $donnees
**/
function sqlInsert($table, $donnees) {
	$retour = FALSE;
	$sql = "INSERT INTO ".$table;
	$liste_champs = "";
	$liste_valeurs = "";
	if (is_array($donnees)) {
		$premiere_ligne = TRUE;
		foreach($donnees as $cle=>$valeur) {  
			if ($premiere_ligne) {
				$premiere_ligne = FALSE;
			} else {
				$liste_champs.= ", ";
				$liste_valeurs.= ", ";
			}
			$liste_champs.= $cle;
			if (is_null($valeur)) {				$liste_valeurs.= "NULL"; }
			elseif ($cle=="password") {			$liste_valeurs.= "MD5('".$valeur."')"; }
			else if (!is_numeric($valeur)) {	$liste_valeurs.= '"'.texteVersBDD($valeur).'"'; }
			else {								$liste_valeurs.= $valeur; }
		}
		$sql.= " ( ".$liste_champs." ) VALUES ( ".$liste_valeurs." );";
		$retour = sqlRequete($sql);
	}
	return $retour;
}
/**
 * Exécute une requete de supression dans une table, renvoie le nombre de lignes supprimées en cas de succès, FALSE en cas d'erreur
 **/
function sqlDelete($table, $condition) {
	$retour = FALSE;
	$sql = "DELETE FROM ".$table." WHERE ".$condition." ;";
	$retour = sqlRequete($sql);
	return $retour;
}
