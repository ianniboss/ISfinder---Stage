<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('../includes/init.inc.php');
// N'incluez PAS function_sql.inc.php car nous utilisons ici mysqli natif de manière sécurisée

function afficherErreur($msg) {
    $msg_clean = strip_tags($msg);
    $msg_clean = html_entity_decode($msg_clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    echo "<script>alert('" . addslashes($msg_clean) . "'); window.history.back();</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    afficherErreur("Méthode non autorisée.");
}

$raw_sql = isset($_POST['custom_sql']) ? trim($_POST['custom_sql']) : '';

if (empty($raw_sql)) {
    afficherErreur("La requête SQL est vide.");
}

// VALIDATION DE SÉCURITÉ 1 : Doit commencer par SELECT
if (stripos($raw_sql, 'SELECT') !== 0) {
    afficherErreur("Sécurité : Seules les requêtes SELECT sont autorisées.");
}

// VALIDATION DE SÉCURITÉ 2 : Pas de point-virgule pour empêcher les requêtes multiples
if (strpos($raw_sql, ';') !== false) {
    afficherErreur("Sécurité : L'utilisation de points-virgules (;) est interdite pour empêcher les requêtes multiples.");
}

// VALIDATION DE SÉCURITÉ 3 : Liste noire des commandes destructrices
$blacklist = [
    'INSERT', 'UPDATE', 'DELETE', 'DROP', 'TRUNCATE', 'ALTER', 'GRANT', 'REVOKE', 'EXEC', 'UNION'
];

$upper_sql = strtoupper($raw_sql);
foreach ($blacklist as $keyword) {
    // Vérifier les mots entiers pour éviter de faire correspondre des colonnes par accident
    if (preg_match('/\b' . $keyword . '\b/', $upper_sql)) {
         afficherErreur("Sécurité : L'utilisation du mot-clé " . $keyword . " est strictement interdite.");
    }
}

// Connexion à la base de données et exécution
$lien = mysqli_connect(DB_server, DB_user, DB_password, DB_bdd);
if (!$lien) {
    afficherErreur("Erreur de connexion à la base de données.");
}

$result = mysqli_query($lien, $raw_sql);

if ($result === false) {
    $error = mysqli_error($lien);
    mysqli_close($lien);
    afficherErreur("Erreur SQL : " . $error);
}

// Il doit s'agir d'un ensemble de résultats provenant d'un SELECT
if (!($result instanceof mysqli_result)) {
    mysqli_close($lien);
    afficherErreur("Sécurité : La requête n'a pas retourné de jeu de résultats.");
}

// Préparation de la sortie CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="export_multitable_' . date('Ymd_His') . '.csv"');
if (ob_get_length()) ob_clean();

$output = fopen('php://output', 'w');

// Extraction des en-têtes
$fields = mysqli_fetch_fields($result);
$headers = [];
foreach ($fields as $field) {
    $headers[] = $field->name;
}
fputcsv($output, $headers);

// Extraction des données
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, $row);
}

fclose($output);
mysqli_free_result($result);
mysqli_close($lien);
exit;
