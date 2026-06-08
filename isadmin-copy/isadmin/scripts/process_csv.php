<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('../includes/init.inc.php');
// Do NOT include function_sql.inc.php as we are using native mysqli securely here

function afficherErreur($msg) {
    $msg_clean = strip_tags($msg);
    $msg_clean = html_entity_decode($msg_clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    echo "<script>alert('" . addslashes($msg_clean) . "'); window.history.back();</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    afficherErreur("M&eacute;thode non autoris&eacute;e.");
}

$raw_sql = isset($_POST['custom_sql']) ? trim($_POST['custom_sql']) : '';

if (empty($raw_sql)) {
    afficherErreur("La requ&ecirc;te SQL est vide.");
}

// SECURITY VALIDATION 1: Must start with SELECT
if (stripos($raw_sql, 'SELECT') !== 0) {
    afficherErreur("S&eacute;curit&eacute; : Seules les requ&ecirc;tes SELECT sont autoris&eacute;es.");
}

// SECURITY VALIDATION 2: No semicolons to prevent multiple statements
if (strpos($raw_sql, ';') !== false) {
    afficherErreur("S&eacute;curit&eacute; : L'utilisation de points-virgules (;) est interdite pour emp&ecirc;cher les requ&ecirc;tes multiples.");
}

// SECURITY VALIDATION 3: Blacklist destructive commands
$blacklist = [
    'INSERT', 'UPDATE', 'DELETE', 'DROP', 'TRUNCATE', 'ALTER', 'GRANT', 'REVOKE', 'EXEC', 'UNION'
];

$upper_sql = strtoupper($raw_sql);
foreach ($blacklist as $keyword) {
    // Check for whole words to avoid matching columns casually
    if (preg_match('/\b' . $keyword . '\b/', $upper_sql)) {
         afficherErreur("S&eacute;curit&eacute; : L'utilisation du mot-cl&eacute; " . $keyword . " est strictement interdite.");
    }
}

// Connect to DB and execute
$lien = mysqli_connect(DB_server, DB_user, DB_password, DB_bdd);
if (!$lien) {
    afficherErreur("Erreur de connexion &agrave; la base de donn&eacute;es.");
}

$result = mysqli_query($lien, $raw_sql);

if ($result === false) {
    $error = mysqli_error($lien);
    mysqli_close($lien);
    afficherErreur("Erreur SQL : " . $error);
}

// It must be a result set from a SELECT
if (!($result instanceof mysqli_result)) {
    mysqli_close($lien);
    afficherErreur("S&eacute;curit&eacute; : La requ&ecirc;te n'a pas retourn&eacute; de jeu de r&eacute;sultats.");
}

// Prepare CSV output
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="export_multitable_' . date('Ymd_His') . '.csv"');
if (ob_get_length()) ob_clean();

$output = fopen('php://output', 'w');

// Extract headers
$fields = mysqli_fetch_fields($result);
$headers = [];
foreach ($fields as $field) {
    $headers[] = $field->name;
}
fputcsv($output, $headers);

// Extract data
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, $row);
}

fclose($output);
mysqli_free_result($result);
mysqli_close($lien);
exit;
