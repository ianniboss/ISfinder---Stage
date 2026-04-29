<?php
$Root = "../";
require_once($Root.'entete.inc.php');
require_once($Root.'menu.inc.php');
?>
  <div class="content">
  <h2>Les certificats électroniques</h2>
<h3>Qu&rsquo;est-ce qu&rsquo;un certificat – a quoi ça sert </h3>
          <p>Un certificat d'authentification est la version électronique de la carte d'identité.</p>
          <p>Il s&rsquo;installe sur votre poste de travail, dans un espace qui   vous est propre. Il permet de prouver votre identité auprès des   applications classiques de l&rsquo;Internet, comme la messagerie électronique   ou les sites webs.</p>
          <p>Avec la messagerie , les certificats permettent, entre autre,   de garantir que les messages que nous envoyons ou recevons proviennent   bien de l'émetteur indiqué. En effet, autrement il est très facile de   modifier l'adresse de l'émetteur et ainsi, fabriquer de faux messages   électroniques, pour tromper les destinataires de ces messages</p>
          <p>Pour le &quot;web&quot;, les certificats permettent de réserver   certains services à une population bien définie, comme les agents CNRS,   les membres d'un laboratoire ou bien individuellement, telle ou telle   personne. </p>
          <p>Pour une personne, le certificat contient : </p>
          <ul>
            <li>Le nom de l'autorité (de certification) qui a créé le certificat </li>
            <li>Le nom et le prénom de la personne </li>
            <li>Son entreprise (CNRS par exemple) </li>
            <li>Son service (au CNRS, le nom du laboratoire ou le code labintel) </li>
            <li>Son adresse électronique </li>
            <li>Sa clef publique </li>
            <li>Les dates de validité du certificat </li>
            <li>Des informations optionnelles </li>
            <li>Une signature électronique </li>
          </ul>
          <p>Chaque certificat est émis par une autorité de certification,   de la même façon qu'une carte d'identité classique est émise   directement ou indirectement par une préfecture. </p>
 <h3>Comment obtenir un certificat CNRS-Standard</h3>
          <h4>Installation des certificats racines de l&rsquo;Autorité de Certification CNRS </h4>
          <p>De même qu&rsquo;une carte d&rsquo;identité est signée par le préfet du   département, les certificats CNRS sont signés de façon électronique par   une sorte de préfecture électronique qu&rsquo;on appelle autorité de   certitication.</p>
          <p>Il est indispensable que vos outils de navigation et de   messagerie Internet connaissent les principales autorités de   certification du CNRS. Le chargement des autorités de certification du   CNRS est une opération très simple qui s&rsquo;effectue en quelques clicks de   souris.</p>
          <p>Pour ce faire, cliquez sur ce lien  <a href="http://igc.services.cnrs.fr/CNRS2-Standard/?lang=fr&amp;cmd=search_CA_certificate&amp;body=view_ca.html" target="_blank">charger les certificats de la chaine de certification de l'AC CNRS-Standard</a>.<br />
            Puis cliquez sur la <strong>X</strong> correspondant à <em>Chargement dans le navigateur</em> de <em>Toute la chaine de certification</em>. </p>
          <p>Selon le navigateur que vous utilisez, et la configuration de   celui-ci il vous faudra confirmer zéro, une ou plusieurs fois votre   volonté d'installer ces certificats. Donc cliquer sur <em>OUI</em> le nombre de fois nécessaire. </p>
          <h4>Demande de certification : formulaire à remplir en ligne </h4>
          <p><strong><em> IMPORTANT :  Il est absolument nécessaire d'effectuer toutes ces opérations depuis la même machine et le même navigateur.</em></strong></p>
          <ul>
            <li>Aller sur le site <a href="http://igc.services.cnrs.fr/CNRS2-Standard/certificats.html" target="_blank">http://igc.services.cnrs.fr/CNRS2-Standard/certificats.html </a></li>
            <li>choisir l'option &quot;certificat personnel&quot; (menu gauche) puis   remplir le formulaire (Prenom, Nom, adresse email, et tel) et l'envoyer.</li>
          </ul>
          <p><strong><em>ATTENTION : Pour utiliser votre certificat sur les sites CNRS il est important de donner votre adresse canonique du type Prenom.Nom@ibcg.biotoul.fr qui est celle connue par Labintel. Penser aussi à vérifier que cette adresse soit bien celle qui figure dans le champ from de votre   messagerie sinon votre demande de certificat n&rsquo;aboutira pas. </em></strong></p>
          <ul>
            <li>Compléter ensuite avec le code de votre unité par exemple &quot;UMR5100&quot; ou &quot;UMR5099&quot;  Faire suite, jusqu'à l'envoi de la demande. </li>
          </ul>
          <p><strong><em>ATTENTION : Sous IE lors de la création de la clé d&rsquo;échange RSA, il faut cliquer sur Définir le niveau de sécurité et sélectionnez Haut </em></strong></p>
          <ul>
            <li>Attendre un courriel de confirmation et le renvoyer comme indiqué à igc-request@services.cnrs.fr </li>
          </ul>
          <p><strong><em>ATTENTION :</em></strong></p>
          <p><strong><em> - Pour confirmer, cliquez sur le lien indiqué   dans le message et envoyez le message qui s&rsquo;affiche sans le modifier. Si   votre Gestionnaire de messagerie inclue directement votre signature   veillez à l'éliminer </em></strong></p>
          <p><strong><em>- Ne pas utiliser SOGo pour envoyer ce message.</em></strong></p>
          <ul>
            <li>Dés que votre Autorité d'Enregistrement (Christophe Carles    ou Jocelyne Pérochon) aura verifié et validé votre demande, vous   recevrez un message vous permettant de récupérer votre certificat. </li>
          </ul>
          <h4>Recupération, installation et sauvegarde </h4>
          <p>Le mail de ra-admin@services.cnrs.fr indique comment   récupérer dans votre navigateur votre certificat CNRS-Standard. Il   suffit de cliquer sur le lien fourni.</p>
          <p>Il est impératif que la récupération du certificat se fasse   avec le même navigateur que la demande. Vérifiez cela quand vous cliquez   sur le lien du courriel final de récupération.</p>
          <p>  </p>
          <p>IMPORTANT : comme indiqué dans le mail reçu, il vous   appartient de sauvegarder votre certificat. Allez pour cela dans   Outils/Options/Avancé/Gérer les certificats/Vos Certificats et   sélectionnez le certificat à sauvegarder avant de cliquer sur Exporter.</p>
          <p>Stockez alors le fichier obtenu en lieu sûr et mémorisez le   mot de passe que vous avez fourni et qui sera nécessaire à la   ré-installation éventuelle de votre certificat. Le plus simple est de   donner le même mot de passe pour votre certificat et pour la sauvegarde   du fichier.<br />
            Ce fichier vous servira aussi à importer votre certificat dans votre   logiciel de messagerie au cas où vous souhaiteriez signer vos messages.</p>
  </div>
<?php
require_once($Root.'pied.inc.php');
?>
