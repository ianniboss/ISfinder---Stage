  <div class="sidebar1">
  	<a href="index.php" target="_self"><img src="images/geswebpetit.gif"></a>
    <ul class="nav">
		<li><h2> Comptes </h2></li>
		<?php
		if (in_array($AUTORISATION, array(2,3))) {
			echo('<li><a href="compte.php?mode='.urlencode(MODE_COMPTE).'">Nouveau</a></li>');
		}
		if (in_array($AUTORISATION, array(2,3))) {	
			echo('<li><a href="liste.php?mode='.urlencode(MODE_LISTE_DEMANDES).'">Demandes en cours</a></li>');
			echo('<li><a href="liste.php?mode='.urlencode(MODE_LISTE_FIN_ACTIVITE).'">Fin d&acute;activité</a></li>'); }

                if (in_array($AUTORISATION, array(2))) {
                            echo('<li><h2> Equipes </h2></li>');
                                echo('<li><a href="listEquipe.php">Liste des équipes</a></li>');
                        }
                ?>
		<li><h2> Annuaire </h2></li>
		<?php
        if (in_array($AUTORISATION, array(1,2,3))) {
			echo('<li><a href="liste.php?mode='.urlencode(MODE_LISTE_ANNUAIRE).'">Liste comptes affichés</a></li>');
		}
		if (in_array($AUTORISATION, array(2,3))) {
			echo('<li><a href="compte.php?mode='.urlencode(MODE_ANNUAIRE).'">Ajout d&acute;une entrée</a></li>');
		}
		if (in_array($AUTORISATION, array(2,3))) {
			echo('<li><a href="liste.php?mode='.urlencode(MODE_LISTE_SUPPRIME).'">Comptes suspendus &amp; supprimés</a></li>');
		}
		?>
    </ul>
    <p>
    	Connecté en tant que <strong><?php echo( getenv('SSL_CLIENT_S_DN_CN').", ".$AUTORISATION_DESCRIPTION ); ?></strong>.
		<?php 
			if ($AUTORISATION!=2) {
				echo('<br />Si vous voulez plus de droits, contactez le <a href="mailto:sinfo@ibcg.biotoul.fr">service informatique</a>.');
			}
		?>
    </p>
</div>