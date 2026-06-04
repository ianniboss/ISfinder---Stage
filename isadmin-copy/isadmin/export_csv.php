<?php
require_once('includes/entete.inc.php');
require_once('includes/aside.inc.php');
require_once('includes/init.inc.php');
require_once('includes/function_sql.inc.php');

$allowed_tables = [
    'element_transposable',
    'orf',
    'submiters',
    'submission',
    'request_names',
    'host',
    'et_insertion_site'
];

$selected_table = isset($_POST['table']) ? $_POST['table'] : (isset($_GET['table']) ? $_GET['table'] : '');

if (!in_array($selected_table, $allowed_tables)) {
    $selected_table = '';
}

$columns = [];
if ($selected_table !== '') {
    $query = "SELECT COLUMN_NAME AS Field FROM information_schema.columns WHERE table_schema = '" . DB_bdd . "' AND table_name = '" . $selected_table . "'";
    $result = sqlRequete($query);
    if ($result) {
        foreach ($result as $row) {
            $columns[] = $row['Field'];
        }
    }
}
?>

<article>
    <section>
        <h2>Export CSV</h2>
        <form method="get" action="export_csv.php">
            <p>
                <label for="table_select"><strong>Table :</strong></label>
                <select name="table" id="table_select" onchange="this.form.submit()">
                    <option value="">-- Sélectionnez une table --</option>
                    <?php foreach ($allowed_tables as $table): ?>
                        <option value="<?php echo htmlspecialchars($table); ?>" <?php if ($table === $selected_table) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($table); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
        </form>

        <?php if ($selected_table !== '' && !empty($columns)): ?>
            <hr />
            <form method="post" action="scripts/process_csv.php">
                <input type="hidden" name="table" value="<?php echo htmlspecialchars($selected_table); ?>" />
                
                <h3>Colonnes &agrave; exporter :</h3>
                <div style="column-count: 3; margin-bottom: 20px;">
                    <?php foreach ($columns as $col): ?>
                        <label>
                            <input type="checkbox" name="columns[]" value="<?php echo htmlspecialchars($col); ?>" checked="checked" />
                            <?php echo htmlspecialchars($col); ?>
                        </label><br />
                    <?php endforeach; ?>
                </div>

                <hr />
                <h3>Filtre (optionnel) :</h3>
                <p>
                    <label for="filter_column">Champ :</label>
                    <select name="filter_column" id="filter_column">
                        <option value="">-- Aucun filtre --</option>
                        <?php foreach ($columns as $col): ?>
                            <option value="<?php echo htmlspecialchars($col); ?>"><?php echo htmlspecialchars($col); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="filter_operator">Op&eacute;rateur :</label>
                    <select name="filter_operator" id="filter_operator">
                        <option value="=">=</option>
                        <option value="!=">!=</option>
                        <option value=">">&gt;</option>
                        <option value="<">&lt;</option>
                        <option value=">=">&gt;=</option>
                        <option value="<=">&lt;=</option>
                        <option value="LIKE">LIKE</option>
                        <option value="NOT LIKE">NOT LIKE</option>
                    </select>

                    <label for="filter_value">Valeur :</label>
                    <input type="text" name="filter_value" id="filter_value" placeholder="Valeur de recherche..." />
                </p>
                <br />
                <p>
                    <input type="submit" value="T&eacute;l&eacute;charger le fichier CSV" style="padding: 5px 15px; cursor: pointer; font-weight: bold;" />
                </p>
            </form>
        <?php endif; ?>
    </section>
</article>

<?php
require_once('includes/pied.inc.php');
?>
