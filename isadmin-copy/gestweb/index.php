<?php
require_once('entete_nohtml.inc.php');
require_once('menu.inc.php');
require_once('entete_html.inc.php');
?>


  <div class="content">
  <H4><CENTER>
    <p>
		<?php echo getenv('SSL_CLIENT_S_DN_CN'); ?><br />
        sur l'outil de gestion,<br />
        de l'annuaire et des stages <br />
        de l'<?php echo getenv('SSL_CLIENT_S_DN_OU'); ?>.
    </p>
    </CENTER></H4>

<br><br>

<H4><CENTER>
    <IMG SRC="images/gesweb.gif" 
ALIGN="BOTTOM" BORDER="0" >
</CENTER></H4>

<H4><CENTER>
    Choisissez sur la gauche une rubrique.
</CENTER></H4>
    <!-- end .content --></div>
<?php
require_once('pied.inc.php');
?>