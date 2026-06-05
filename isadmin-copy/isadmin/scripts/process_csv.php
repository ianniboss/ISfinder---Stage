<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('../includes/init.inc.php');
require_once('../includes/function_sql.inc.php');

$allowed_tables = [
    'ag_description',
    'current_names',
    'element_transposable',
    'element_transposable_has_host',
    'et_insertion_site',
    'family',
    'groups',
    'host',
    'is_ends',
    'name_attribution',
    'nom_type',
    'orf',
    'orf_has_orf_modification',
    'orf_modification',
    'parent_link',
    'pg_function',
    'References',
    'submission',
    'submiters',
    'synonyme',
    'tnp_chemestry',
    'tnp_description',
    'type_element_transposable'
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
$actual_columns = [];
$lien = mysqli_connect(DB_server, DB_user, DB_password, DB_bdd);
if (!$lien) {
    die("Erreur de connexion &agrave; la base de donn&eacute;es.");
}

$query = "SELECT COLUMN_NAME AS Field FROM information_schema.columns WHERE table_schema = '" . mysqli_real_escape_string($lien, DB_bdd) . "' AND table_name = '" . mysqli_real_escape_string($lien, $table) . "'";
$result = mysqli_query($lien, $query);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
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
    $escaped_value = mysqli_real_escape_string($lien, $filter_value);
    $where_clause = "`" . $filter_column . "` " . $filter_operator . " '" . $escaped_value . "'";
}

$query_select = "SELECT " . implode(", ", $selected_columns) . " FROM `" . mysqli_real_escape_string($lien, $table) . "`";
if ($where_clause !== '') {
    $query_select .= " WHERE " . $where_clause;
}

$data = [];
$result_data = mysqli_query($lien, $query_select);
if ($result_data && mysqli_num_rows($result_data) > 0) {
    while ($row = mysqli_fetch_assoc($result_data)) {
        $data[] = $row;
    }
}

// Close connection now that we're done with DB
mysqli_close($lien);

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
