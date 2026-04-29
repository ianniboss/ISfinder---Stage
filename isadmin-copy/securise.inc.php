<?php
echo(" <h3> Acc&egrave;s s&eacute;curis&eacute; : </h3> ");
echo("<p> Cette section est sécurisée par certificat numérique&nbsp;:<br />");
$qui = getenv(SSL_CLIENT_S_DN_CN);
$cnrs = getenv(SSL_CLIENT_S_DN_OU);
$certif_ok = FALSE;
if ($qui!="") {
	echo("Vous êtes identifié comme ".$qui.", ");
	if (in_array($cnrs, array("UMR5100", "UMR5099", "IFR109"))) {
		echo("et autorisé à utiliser cette page.");
		$certif_ok = TRUE;
	} else {
		echo("<em>mais pas autorisé</em> à utiliser cette page.");
	}
} else {
	echo("Vous n'êtes pas identifié.");
}
if (!$certif_ok) {
	echo("<br />Contactez le <a href='mailto:sinfo@ibcg.biotoul.fr'>service informatique</a> de l'IBCG si vous estimez y avoir droit.");
}
echo("</p>");
?>