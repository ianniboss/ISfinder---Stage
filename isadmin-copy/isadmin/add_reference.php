<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('includes/function.inc.php');

// ---------------------------------------------------------------------------
// Allowed tables whitelist
// Tables without needs_parent: simple single-column insert.
// Tables with needs_parent: require a foreign key selected by the user first.
// ---------------------------------------------------------------------------
$allowed_tables = [
    'family'                    => ['column' => 'Family_Name',  'label' => 'Family Name'],
    'tnp_chemestry'             => ['column' => 'chemestry',    'label' => 'Tnp Chemistry'],
    'type_element_transposable' => ['column' => 'Type_ET',      'label' => 'Type Element Transposable'],
    'ag_description'            => ['column' => 'description',  'label' => 'AG Description'],
    'pg_function'               => ['column' => 'function',     'label' => 'PG Function'],
    // groups has a FK to family — the user must pick a family first
    'groups' => [
        'column'       => 'Group_Name',
        'label'        => 'Group (Sub-group)',
        'needs_parent' => true,
        'parent_table' => 'family',
        'parent_col'   => 'ID_Family',
        'parent_label' => 'Family_Name',
        'parent_fk'    => 'Family_ID_Family',
    ],
];

// ---------------------------------------------------------------------------
// AJAX endpoint: GET ?action=get_values&table=<name>
// Returns an HTML fragment listing the current rows of the requested table.
// Same whitelist is applied — no arbitrary table access possible.
// ---------------------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'get_values') {
    $table_name = $_GET['table'] ?? '';

    if (!array_key_exists($table_name, $allowed_tables)) {
        echo '<p class="erreur">Table invalide.</p>';
        exit;
    }

    $table_config = $allowed_tables[$table_name];
    $cnx = connexion('isfinder');

    if (!empty($table_config['needs_parent'])) {
        // For groups: show ID, family name, and group name together
        $parent_label = $table_config['parent_label'];
        $parent_col   = $table_config['parent_col'];
        $parent_table = $table_config['parent_table'];
        $parent_fk    = $table_config['parent_fk'];
        $col          = $table_config['column'];

        $sql = "SELECT g.`{$col}`, f.`{$parent_label}`
                FROM `{$table_name}` g
                JOIN `{$parent_table}` f ON g.`{$parent_fk}` = f.`{$parent_col}`
                ORDER BY f.`{$parent_label}`, g.`{$col}`";
        $res = mysqli_query($cnx, $sql);

        if (!$res || mysqli_num_rows($res) === 0) {
            echo '<p style="color:#666; font-style:italic;">Aucune valeur trouvée.</p>';
            exit;
        }

        echo '<table style="width:100%; border-collapse:collapse; font-size:0.9em;">';
        echo '<thead><tr>';
        echo '<th style="text-align:left; padding:4px 8px; border-bottom:1px solid #ccc;">Famille</th>';
        echo '<th style="text-align:left; padding:4px 8px; border-bottom:1px solid #ccc;">Groupe</th>';
        echo '</tr></thead><tbody>';

        while ($row = mysqli_fetch_assoc($res)) {
            echo '<tr>';
            echo '<td style="padding:3px 8px; border-bottom:1px solid #eee;">'
                . htmlspecialchars($row[$parent_label]) . '</td>';
            echo '<td style="padding:3px 8px; border-bottom:1px solid #eee;">'
                . htmlspecialchars($row[$col]) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        // Simple single-column table
        $col = $table_config['column'];
        $stmt = mysqli_prepare($cnx, "SELECT `{$col}` FROM `{$table_name}` ORDER BY `{$col}`");

        if (!$stmt) {
            echo '<p class="erreur">Erreur de base de données.</p>';
            exit;
        }

        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($res) === 0) {
            echo '<p style="color:#666; font-style:italic;">Aucune valeur trouvée.</p>';
            exit;
        }

        echo '<ul style="margin:0; padding-left:18px; font-size:0.9em;">';
        while ($row = mysqli_fetch_row($res)) {
            echo '<li style="padding:2px 0;">' . htmlspecialchars($row[0]) . '</li>';
        }
        echo '</ul>';

        mysqli_stmt_close($stmt);
    }

    exit; // End of AJAX endpoint
}

// ---------------------------------------------------------------------------
// Normal page rendering starts here
// ---------------------------------------------------------------------------
require_once('includes/entete.inc.php');
require_once('includes/aside.inc.php');

echo '<link type="text/css" rel="stylesheet" href="styles/styles.css" media="screen" />';
echo "<article>";
echo "<h2>Ajouter une valeur</h2>";
echo "<p style='font-style: italic; color: #666; margin-bottom: 15px;'>Permettre d'ajouter l'enregistrement dans les tables pré-remplies.</p>";

$message = "";
// Track which table was last used, so JS can restore the panel after a POST
$last_table = $_POST['table_name'] ?? '';

// ---------------------------------------------------------------------------
// POST handler
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reference'])) {
    $table_name      = $_POST['table_name'] ?? '';
    $reference_value = trim($_POST['reference_value'] ?? '');

    if (!array_key_exists($table_name, $allowed_tables)) {
        $message = "<p class='erreur'>Table sélectionnée invalide.</p>";
    } elseif ($reference_value === '') {
        $message = "<p class='erreur'>La valeur ne peut pas être vide.</p>";
    } else {
        $table_config = $allowed_tables[$table_name];
        $column       = $table_config['column'];

        $cnx = connexion('isfinder');

        // --- groups: requires a parent family FK ---
        if (!empty($table_config['needs_parent'])) {
            $parent_id    = intval($_POST['parent_id'] ?? 0);
            $parent_fk    = $table_config['parent_fk'];
            $parent_table = $table_config['parent_table'];
            $parent_col   = $table_config['parent_col'];

            if ($parent_id <= 0) {
                $message = "<p class='erreur'>Veuillez sélectionner une famille.</p>";
            } else {
                // Validate the parent FK actually exists
                $chk = mysqli_prepare($cnx, "SELECT `{$parent_col}` FROM `{$parent_table}` WHERE `{$parent_col}` = ? LIMIT 1");
                mysqli_stmt_bind_param($chk, 'i', $parent_id);
                mysqli_stmt_execute($chk);
                mysqli_stmt_store_result($chk);
                $parent_exists = (mysqli_stmt_num_rows($chk) > 0);
                mysqli_stmt_close($chk);

                if (!$parent_exists) {
                    $message = "<p class='erreur'>Famille sélectionnée invalide.</p>";
                } else {
                    // Duplicate check: same Group_Name AND same Family
                    $dup = mysqli_prepare($cnx,
                        "SELECT `{$column}` FROM `{$table_name}`
                         WHERE `{$column}` = ? AND `{$parent_fk}` = ? LIMIT 1");
                    mysqli_stmt_bind_param($dup, 'si', $reference_value, $parent_id);
                    mysqli_stmt_execute($dup);
                    mysqli_stmt_store_result($dup);

                    if (mysqli_stmt_num_rows($dup) > 0) {
                        $message = "<p class='erreur'>Le groupe '<strong>"
                            . htmlspecialchars($reference_value)
                            . "</strong>' existe déjà pour cette famille.</p>";
                        mysqli_stmt_close($dup);
                    } else {
                        mysqli_stmt_close($dup);

                        // Insert
                        $ins = mysqli_prepare($cnx,
                            "INSERT INTO `{$table_name}` (`{$column}`, `{$parent_fk}`) VALUES (?, ?)");
                        mysqli_stmt_bind_param($ins, 'si', $reference_value, $parent_id);

                        if (mysqli_stmt_execute($ins)) {
                            // data-refresh-table tells JS to reload the panel after page load
                            $message = "<p style='color:green;' data-refresh-table='"
                                . htmlspecialchars($table_name) . "'>Le groupe '<strong>"
                                . htmlspecialchars($reference_value)
                                . "</strong>' a été ajouté avec succès dans la table '<strong>"
                                . htmlspecialchars($table_name) . "</strong>'.</p>";
                        } else {
                            $message = "<p class='erreur'>Erreur lors de l'insertion : "
                                . htmlspecialchars(mysqli_stmt_error($ins)) . "</p>";
                        }
                        mysqli_stmt_close($ins);
                    }
                }
            }

        // --- Simple single-column tables ---
        } else {
            // Duplicate check
            $check_stmt = mysqli_prepare($cnx, "SELECT `{$column}` FROM `{$table_name}` WHERE `{$column}` = ? LIMIT 1");
            if ($check_stmt) {
                mysqli_stmt_bind_param($check_stmt, 's', $reference_value);
                mysqli_stmt_execute($check_stmt);
                mysqli_stmt_store_result($check_stmt);

                if (mysqli_stmt_num_rows($check_stmt) > 0) {
                    $message = "<p class='erreur'>La valeur '<strong>"
                        . htmlspecialchars($reference_value)
                        . "</strong>' existe déjà dans la table '<strong>"
                        . htmlspecialchars($table_name) . "</strong>'.</p>";
                    mysqli_stmt_close($check_stmt);
                } else {
                    mysqli_stmt_close($check_stmt);

                    $stmt = mysqli_prepare($cnx, "INSERT INTO `{$table_name}` (`{$column}`) VALUES (?)");
                    if ($stmt) {
                        mysqli_stmt_bind_param($stmt, 's', $reference_value);
                        if (mysqli_stmt_execute($stmt)) {
                            // data-refresh-table tells JS to reload the panel after page load
                            $message = "<p style='color:green;' data-refresh-table='"
                                . htmlspecialchars($table_name) . "'>La valeur '<strong>"
                                . htmlspecialchars($reference_value)
                                . "</strong>' a été ajoutée avec succès dans la table '<strong>"
                                . htmlspecialchars($table_name) . "</strong>'.</p>";
                        } else {
                            $message = "<p class='erreur'>Erreur lors de l'insertion : "
                                . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>";
                        }
                        mysqli_stmt_close($stmt);
                    } else {
                        $message = "<p class='erreur'>Erreur de base de données : impossible de préparer la requête.</p>";
                    }
                }
            } else {
                $message = "<p class='erreur'>Erreur de base de données : impossible de préparer la requête de vérification.</p>";
            }
        }
    }
}

echo $message;

// ---------------------------------------------------------------------------
// Load family list for the groups parent selector
// ---------------------------------------------------------------------------
$families = [];
$cnx_families = connexion('isfinder');
$res_families = mysqli_query($cnx_families, "SELECT `ID_Family`, `Family_Name` FROM `family` ORDER BY `Family_Name`");
if ($res_families) {
    while ($row = mysqli_fetch_assoc($res_families)) {
        $families[] = $row;
    }
}

// Restore the previously selected parent_id after a POST (e.g. after an error or success)
$selected_parent_id = intval($_POST['parent_id'] ?? 0);
?>

<form action="add_reference.php" method="POST" style="max-width: 600px; margin: 20px 0;">
    <fieldset style="border: 1px solid #ccc; padding: 20px; border-radius: 5px; background-color: #f9f9f9;">
        <legend style="font-weight: bold; padding: 0 10px; color: #333;">Détails de la nouvelle valeur</legend>

        <p style="margin-bottom: 15px;">
            <label for="table_name" style="display: block; margin-bottom: 5px; font-weight: bold;">Sélectionner la table :</label>
            <select name="table_name" id="table_name" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                <option value="">-- Choisissez une table --</option>
                <?php foreach ($allowed_tables as $table => $config): ?>
                    <option value="<?php echo htmlspecialchars($table); ?>" <?php echo ($last_table === $table) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($config['label']); ?> (<?php echo htmlspecialchars($table); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <?php
        // Determine whether to show the parent selector on load (after a POST for groups)
        $show_parent = (!empty($_POST['table_name']) && $_POST['table_name'] === 'groups');
        ?>
        <div id="parent-selector" style="margin-bottom: 15px; display: <?php echo $show_parent ? 'block' : 'none'; ?>;">
            <label for="parent_id" style="display: block; margin-bottom: 5px; font-weight: bold;">Sélectionner la famille :</label>
            <select name="parent_id" id="parent_id" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                <option value="">-- Choisissez une famille --</option>
                <?php foreach ($families as $fam): ?>
                    <option value="<?php echo (int)$fam['ID_Family']; ?>"
                        <?php echo ($selected_parent_id === (int)$fam['ID_Family']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($fam['Family_Name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <p style="margin-bottom: 20px;">
            <label for="reference_value" style="display: block; margin-bottom: 5px; font-weight: bold;">Valeur à ajouter :</label>
            <input type="text" name="reference_value" id="reference_value" required
                value="<?php echo isset($_POST['submit_reference']) ? htmlspecialchars($_POST['reference_value'] ?? '') : ''; ?>"
                style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;"
                placeholder="Entrer la nouvelle valeur ici..." />
        </p>

        <p style="margin: 0; text-align: right;">
            <input type="submit" name="submit_reference" value="Ajouter la valeur"
                style="padding: 10px 20px; background-color: #0056b3; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;"
                onmouseover="this.style.backgroundColor='#004494'"
                onmouseout="this.style.backgroundColor='#0056b3'" />
        </p>
    </fieldset>
</form>

<!-- Panel that displays current values of the selected table -->
<div id="current-values"
     style="max-width: 600px; margin-top: 10px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background-color: #fafafa; display: none;">
    <strong style="display:block; margin-bottom: 8px; color: #333;">Valeurs actuelles :</strong>
    <div id="current-values-content"></div>
</div>

<script>
(function () {
    'use strict';

    var tableSelect   = document.getElementById('table_name');
    var parentDiv     = document.getElementById('parent-selector');
    var valuesPanel   = document.getElementById('current-values');
    var valuesContent = document.getElementById('current-values-content');

    // Load the current values for the given table into the panel
    function loadValues(table) {
        if (!table) {
            valuesPanel.style.display = 'none';
            valuesContent.innerHTML = '';
            return;
        }
        valuesContent.innerHTML = '<em style="color:#888;">Chargement...</em>';
        valuesPanel.style.display = 'block';

        fetch('add_reference.php?action=get_values&table=' + encodeURIComponent(table))
            .then(function (r) { return r.text(); })
            .then(function (html) { valuesContent.innerHTML = html; })
            .catch(function () {
                valuesContent.innerHTML = '<p class="erreur">Erreur lors du chargement des valeurs.</p>';
            });
    }

    // Show or hide the parent (family) selector depending on the selected table
    function toggleParentSelector(table) {
        parentDiv.style.display = (table === 'groups') ? 'block' : 'none';
        // parent_id is only required when inserting into groups
        var parentSelect = document.getElementById('parent_id');
        if (parentSelect) {
            parentSelect.required = (table === 'groups');
        }
    }

    // When the user changes the table dropdown
    tableSelect.addEventListener('change', function () {
        var table = this.value;
        toggleParentSelector(table);
        loadValues(table);
    });

    // On page load: if there was a successful insertion (PHP added data-refresh-table),
    // restore the select and auto-refresh the panel so Patricia sees the new value immediately.
    document.addEventListener('DOMContentLoaded', function () {
        var successEl = document.querySelector('[data-refresh-table]');
        if (successEl) {
            var table = successEl.getAttribute('data-refresh-table');
            tableSelect.value = table;
            toggleParentSelector(table);
            loadValues(table);
        } else if (tableSelect.value) {
            // After a failed POST, restore the panel for the already-selected table
            toggleParentSelector(tableSelect.value);
            loadValues(tableSelect.value);
        }
    });

    // Initialise on first load if a table was already selected (e.g. back from POST with errors)
    if (tableSelect.value) {
        toggleParentSelector(tableSelect.value);
        loadValues(tableSelect.value);
    }
}());
</script>

</article>
<?php // require_once('includes/pied.inc.php'); ?>
