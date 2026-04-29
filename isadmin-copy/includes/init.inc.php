<?PHP
session_start();
// Constantes de prefixes de dossier et de session
define('DOSSIER_GLPI', 'http://envalira.ibcg.biotoul.fr/glpi');
define('PREFIXE_SESSION', "RESIBCG_");
// Constantes pour accès BDD
define('DB_server',"localhost");
define('DB_user',"client");
define('DB_password',"client");
define('DB_bdd',"reseau");
// Constantes pour accès SSH
define('SSH_ERREUR_EXECUTION',"Erreur SSH (ex&eacute;cution");
define('SSH_ERREUR_IDENTIFICATION',"Erreur SSH (identification)");
define('SSH_ERREUR_CONNECTION',"Erreur SSH (connection)");
define('SSH_ERREUR_COMMANDE',"Erreur SSH (pas de commande)");
define('SSH_ERREUR_SERVEUR',"Erreur SSH (serveur inconnu)");
define('SSH_ERREUR_INFO_SERVEUR',"Erreur SSH (informations serveur incomplètes)");
define('SSH_ERREUR_FONCTION',"Erreur SSH (fonctions SSH inutilisables)");

// VARIABLES

$pagePreTitre = 'Acc&egrave;s r&eacute;serv&eacute;';
$dossierGLPI = 'http://envalira.ibcg.biotoul.fr/glpi';

require_once("function.inc.php");
require_once("function_variables.inc.php");
require_once("function_ssh.inc.php");
require_once("function_sql.inc.php");
require_once("function_formulaires.inc.php");
require_once("function_controles.inc.php");
require_once("function2.inc.php");

connexion();