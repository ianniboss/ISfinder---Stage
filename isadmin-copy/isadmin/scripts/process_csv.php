<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('../includes/init.inc.php');
require_once('../includes/function_sql.inc.php');

$allowed_tables = [
    'element_transposable',
    'orf',
    'submiters',
    'submission',
    'request_names',
    'host',
    'et_insertion_site'
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("M&eacute;thode non autoris&eacute;e.");
}

$table = isset($_POST['table']) ? $_POST['table'] : '';
if (!in_array($table, $allowed_tables)) {
    die("Table non autoris&eacute;e.");
}

$columns_post = isset($_POST['columns']) && is_array($_POST['columns']) ? $_POST['columns'] : [];
if (empty($columns_post)) {
    die("Aucune colonne s&eacute;lectionn&eacute;e.");
}

// Check real columns to prevent SQL injection on column names
$query = "SELECT COLUMN_NAME AS Field FROM information_schema.columns WHERE table_schema = '" . DB_bdd . "' AND table_name = '" . $table . "'";
$result = sqlRequete($query);
$actual_columns = [];
if ($result) {
    foreach ($result as $row) {
        $actual_columns[] = $row['Field'];
    }
}

// Validate requested columns
$selected_columns = [];
foreach ($columns_post as $col) {
    if (in_array($col, $actual_columns)) {
        $selected_columns[] = "`" . $col . "`";
    }
}

if (empty($selected_columns)) {
    die("Colonnes s&eacute;lectionn&eacute;es invalides.");
}

$filter_column = isset($_POST['filter_column']) ? $_POST['filter_column'] : '';
$filter_operator = isset($_POST['filter_operator']) ? $_POST['filter_operator'] : '';
$filter_value = isset($_POST['filter_value']) ? $_POST['filter_value'] : '';

$where_clause = "";

$allowed_operators = ['=', '!=', '>', '<', '>=', '<=', 'LIKE', 'NOT LIKE'];

// Applying structured filter safely
if ($filter_column !== '' && in_array($filter_column, $actual_columns) && in_array($filter_operator, $allowed_operators)) {
    $lien = mysqli_connect(DB_server, DB_user, DB_password, DB_bdd);
    if ($lien) {
        $escaped_value = mysqli_real_escape_string($lien, $filter_value);
        $where_clause = "`" . $filter_column . "` " . $filter_operator . " '" . $escaped_value . "'";
        mysqli_close($lien);
    } else {
        die("Erreur de connexion &agrave; la base de donn&eacute;es pour le filtrage.");
    }
}

$query_select = "SELECT " . implode(", ", $selected_columns) . " FROM `" . $table . "`";
if ($where_clause !== '') {
    $query_select .= " WHERE " . $where_clause;
}

$data = sqlRequete($query_select);

if ($data === false) {
    // Determine if query succeeded but returned 0 rows, or if it failed
    // sqlRequete returns false on error or 0 rows for SELECT. 
    // We can just output an empty CSV with headers in both cases for safety.
    $data = [];
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $table . '_export_' . date('Ymd_His') . '.csv"');
// Clean output buffer if there's any to prevent corrupting the CSV
if (ob_get_length()) ob_clean();

$output = fopen('php://output', 'w');

// Write headers
$headers = array_map(function($col) { return trim($col, "`"); }, $selected_columns);
fputcsv($output, $headers);

// Write rows
if (!empty($data)) {
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
}

fclose($output);
exit;
