<?php
/**
 *  SSSS  QQQ  L
 * S     Q   Q L
 *  SSS  Q   Q L
 *     S Q  QQ L
 * SSSS   QQQQ LLLLL
 **/


/**
 * En cas d'erreur SQL, renvoie la valeur null apr&egrave;s avoir affich&eacute; l'erreur (l&agrave; ou &ccedil;a tombe !)
 * S'il n'y a pas de r&eacute;sultat (0 lignes), retourne un tableau de valeur NULL.
 * @param string $requeteSQL	Requete &agrave; envoyer telle qu'elle (aucun contr&ocirc;le !)
 * @param boolean $logOK		TRUE : logue les modifications ; FALSE : ne logue pas !
 * La donn&eacute;e renvoy&eacute;e d&eacute;pend du type de requette :
 * INSERT : ID de la ligne ins&eacute;r&eacute;e (null = pas d'insertion possible)
 * UPDATE : nombre de lignes modifi&eacute;es (null = modif non faite ; 0 = aucune !)
 * SELECT : tableau associatif "nom_champ" => "valeur" (null = pas de r&eacute;sultat trouv&eacute; (ou erreur...))
 * DELETE : nombre de lignes supprim&eacute;es (0 ou null = rien effac&eacute; (erreur si null...)
 **/
function sqlRequete($requeteSQL) {
//	echo('[ '.$requeteSQL.' ]<br />');
	$resultatArray=null;
	// r&eacute;cup&eacute;ration des r&eacute;sultats SI possible...
	$lien = mysql_connect(DB_server,DB_user,DB_password);
	if (!$lien) {
		echo('ERREUR '.mysql_error().' : Acc&egrave;s Impossible au serveur SQL.');
		$retour = FALSE;
	} else {
		if (!mysql_select_db(DB_bdd)) {
			echo('ERREUR '.mysql_error().' : Acc&egrave;s impossible &agrave; la base de donn&eacute;es.');
			$retour = FALSE;
		} else {
			$resultat = mysql_query($requeteSQL,$lien);
			if (!$resultat) {
				echo('ERREUR '.mysql_error().' : Impossible d\'ex&eacute;cuter la requ&egrave;te ('.$requeteSQL.').');
				$retour = FALSE;
			} else {
				if (stristr($requeteSQL,"SELECT")!==FALSE) {	// SELECT
					if (mysql_num_rows($resultat)>0) {	// Renvoi les lignes dans un tableau de tableau associatif...
						while ($ligne = mysql_fetch_assoc($resultat)) {	$resultatArray[] = $ligne;	}
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
 * Retourne un tableau ordonn&eacute; de tableau associatif nom_champ => valeur.
 * En cas d'erreur SQL, renvoie une chaine de caract&egrave;re d&eacute;crivant l'erreur,
 * S'il n'y a pas de r&eacute;sultat (0 lignes), retourne un tableau de valeur NULL.
 * Se contente de construire la requete, et utilise la fonction sqlRequete.
 * Remplacement &eacute;ventuel du champ limite par le champ groupe (selon le type !) :
 * 	nb_xxx qui renvoie le nombre de lignes en groupant xxx :
 * 	exemple : sqlSelect('logs_visite','page NOT LIKE "/intranet/%"','nb_page desc','page')
 * @param string	$table			Table sur laquelle on effectue la requ&egrave;te
 * @param string	$condition		Condition pour s&eacute;lectionner les lignes
 * @param string	$tri			Tri pour retour des r&eacute;sultats
 * @param numeric	$limite_ou_groupe	Si nombre : indique le nombre de ligne &agrave; retourner
 * @param string	$limite_ou_groupe	Si chainte : indique le champ &agrave; grouper : ajoute un champ nb_xxx qui indique le nombre
 **/
function sqlSelect($table, $condition='', $tri='', $limite_ou_groupe=null) {
	$resultatArray=null;
	// construction de la requ&egrave;te :
	$sql = 'SELECT *';
	if ( (!is_null($limite_ou_groupe)) and (is_string($limite_ou_groupe)) ) {
		$sql.= ', COUNT('.$limite_ou_groupe.') as nb_'.$limite_ou_groupe;
		if ($tri!='') { $tri = 'ORDER BY '.$tri; }
		$tri = 'GROUP BY '.$limite_ou_groupe.' '.$tri;
	}
	$sql.= ' FROM '.$table;
	if ($condition!='') { $sql.= ' WHERE '.$condition; }
	if ($tri!='') { $sql.= ((stristr($tri,"GROUP BY")===FALSE)?" ORDER BY ":" ").$tri; }
	if ( (!is_null($limite_ou_groupe)) and (is_numeric($limite_ou_groupe)) ) { $sql.= ' LIMIT '.$limite_ou_groupe; }
	$sql.= ' ;';
	//var_dump($sql);
	// r&eacute;cup&eacute;ration des r&eacute;sultats SI possible...
	return(sqlRequete($sql));
}
/**
 *	Renvoie le bout de code qui permet de faire appel &agrave; 2 tables SQL, avec la liaison entre les deux identifiants tels qu'indiqu&eacute;s.
 * Cette fonction sert uniquement &agrave; remplacer le nom de la table, lors de l'appel des fonctions sqlSelect ou sqlTrouve !
 **/
function sqlTablesLiees($table1, $table2, $id1, $id2=NULL) {
	if (is_null($id2)) {	$id2 = $id1;	}
	$retour = $table1.' LEFT OUTER JOIN `'.$table2.'` ON `'.$table1.'`.`'.$id1.'` = `'.$table2.'`.`'.$id2.'`';
	//var_dump($retour);
	return($retour);
}

/**
 * Retourne un tableau associatif nom_champ => valeur de la premi&egrave;re ligne correspondant &agrave; la condition indiqu&eacute;e selon le tri.
 * En cas d'erreur SQL, renvoie une chaine de caract&egrave;re d&eacute;crivant l'erreur,
 * S'il n'y a pas de r&eacute;sultat (0 lignes), retourne un tableau de valeur NULL.
 * @param string $table		Nom de la table dans laquelle faire la recherche
 * @param string $condition	Condition &agrave; prendre en compte, si la condition est un integer, cherche l'identifiant unique correspondant
 * @param string $tri		Ordre de tri (TRES IMPORTANT car cette fonction retourne UNIQUEMENT la premi&egrave;re ligne trouv&eacute;e !)
 * @param string $groupe	Variable par laquelle on veut regrouper plusieurs lignes, retourne un champ suppl&eacute;mentaire nomm&eacute; nb_xxx indiquant le nombre de lignes
 **/
function sqlTrouve($table, $condition='', $tri='', $groupe=null) {
	$resultatArray[]=null;
	// construction de la requ&egrave;te :
	$sql = 'SELECT *';
	if (is_string($groupe)) {
		$sql.= ', COUNT('.$groupe.') as nb_'.$groupe;
		if ($tri!='') { $tri = 'ORDER BY '.$tri; }
		$tri = 'GROUP BY '.$groupe.' '.$tri;
	} else {
		if ($tri!='') { $tri = 'ORDER BY '.$tri; }
	}
	$sql.= ' FROM '.$table;
	if ($condition!='') { $sql.= ' WHERE '.$condition; }
	if ($tri!='') { $sql.= ' '.$tri; }
	$sql.= ' ;';
	// r&eacute;cup&eacute;ration des r&eacute;sultats SI possible...
	$lien = mysql_connect(DB_server,DB_user,DB_password);
	if (!$lien) { return('ERREUR '.mysql_error().' : Acc&egrave;s Impossible au serveur SQL.'); }
	if (!mysql_select_db(DB_bdd)) { return('ERREUR '.mysql_error().' : Acc&egrave;s impossible &agrave; la base de donn&eacute;es.'); }
	$resultat = mysql_query($sql,$lien);
	if (!$resultat) { return('ERREUR '.mysql_error().' : Impossible d\'ex&eacute;cuter la requ&egrave;te ('.$sql.').'); }
	if (mysql_num_rows($resultat)==0) {	return null;	}
	else {	$resultatArray = mysql_fetch_assoc($resultat);	}
	mysql_close($lien);
	return($resultatArray);
}

/**
 * Ex&eacute;cute une requete d'ajout dans la $table,
 * Ajoute les couples de cle=>valeur indiqu&eacute;es dans le tableau associatif $donnees,
 * Retourne l'identifiant de la ligne ins&eacute;r&eacute;e.
**/
function sqlInsert($table, $donnees) {
	$retour = FALSE;
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
			if (is_null($valeur)) {
				$valeur="NULL";
			} else {
				if (!is_numeric($valeur)) {
					$valeur='"'.$valeur.'"';
				}
			}
			$liste_champs.= '`'.$cle.'`';
			$liste_valeurs.= $valeur;
		}
		$sql = "INSERT INTO ".$table." (".$liste_champs.") VALUES (".$liste_valeurs.") ;";
		$retour = sqlRequete($sql);
	}
	return $retour;
}

/**
 * Ex&eacute;cute une requete de mise &agrave; jour sur la $table, sur la condition indiqu&eacute;e (ou identifiant de la table si num&eacute;rique)
 * Met &agrave; jour les couples de cle=>valeur indiqu&eacute;es dans le tableau associatif $donnees
**/
function sqlUpdate($table, $condition, $donnees) {
	$retour = FALSE;
	$sql = "UPDATE ".$table." SET ";
	if (is_array($donnees)) {
		$premiere_ligne = TRUE;
		foreach($donnees as $cle=>$valeur) {
			if ($premiere_ligne) {
				$premiere_ligne = FALSE;
			} else {
				$sql.= ", ";
			}
			if (is_null($valeur)) {
				$valeur="NULL";
			} else {
				if ($cle=="password") {
					$valeur = "MD5('".$valeur."')";
				} else {
					if (!is_numeric($valeur)) {
						$valeur = '"'.$valeur.'"';
					}
				}
			}
			$sql.= "`".$cle."` = ".$valeur;
		}
		$sql.= " WHERE ".$condition." ;";
		$retour = sqlRequete($sql);
	}
	return $retour;
}

/**
 * Ex&eacute;cute une requete de suppression sur la $table, sur la $condition indiqu&eacute;e
**/
function sqlDelete($table, $condition) {
	$retour = FALSE;
	if ($condition!="") {
		$sql = "DELETE FROM ".$table." WHERE ".$condition." ;";
		$retour = sqlRequete($sql);
	}
	return $retour;
}
?>