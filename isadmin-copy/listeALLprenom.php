<?php
	include("https://secure.ibcg.biotoul.fr/listeALL_1.php");
	// Gestion des tris !
	//$liste1 = array('nom'=>"comptes.nom", 'prenom'=>"comptes.prenom", 'email'=>"comptes.email",
	//				'equipe'=>"reseau.labos.nom, reseau.equipes.equipe");
	$_SESSION['IBCG_INTRANET_TRI'] = "comptes.prenom";
	include("https://secure.ibcg.biotoul.fr/listeALL_2.php");
?>