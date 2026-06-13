<?php
// delete_reference.php
// Allows Patricia to delete a value from a reference table.
// Mirrors the architecture of add_reference.php.
//
// FK strategy per table (derived from isfinder.sql):
//
//   family               → no ON DELETE on ET or groups → BLOCK if referenced
//   groups               → ON DELETE SET NULL on ET     → WARN, allow delete
//   tnp_chemestry        → ON DELETE SET NULL on orf    → WARN, allow delete
//   type_element_trans.  → ON DELETE SET NULL on ET + nom_type → WARN, allow delete
//   ag_description       → ON DELETE SET NULL on orf    → WARN, allow delete
//   pg_function          → ON DELETE SET NULL on orf    → WARN, allow delete
//   nom_type             → no ON DELETE on current_names → BLOCK if referenced

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('includes/function.inc.php');

// ---------------------------------------------------------------------------
// Allowed tables whitelist.
// Each entry:
//   column      — the human-readable value column (used for display)
//   label       — UI label
//   pk_col      — integer primary key column
//   fk_checks   — array of checks to run before allowing deletion:
//       table     — the referencing table
//       column    — the referencing column (FK column)
//       type      — 'block' (refuse delete) or 'warn' (allow but warn)
//       label     — human-readable description for the message
// ---------------------------------------------------------------------------
$allowed_tables = [

    'family' => [
        'column'    => 'Family_Name',
        'label'     => 'Family Name',
        'pk_col'    => 'ID_Family',
        // element_transposable.Family_ID_Family: FK ibfk_4/7, ON UPDATE CASCADE only (no ON DELETE)
        //   → DB rejects delete if any ET row references this family → BLOCK
        // groups.Family_ID_Family: FK groups_ibfk_1, ON UPDATE CASCADE only (no ON DELETE)
        //   → DB rejects delete if any groups row references this family → BLOCK
        'fk_checks' => [
            [
                'table'  => 'element_transposable',
                'column' => 'Family_ID_Family',
                'type'   => 'block',
                'label'  => 'élément(s) transposable(s)',
            ],
            [
                'table'  => 'groups',
                'column' => 'Family_ID_Family',
                'type'   => 'block',
                'label'  => 'groupe(s)',
            ],
        ],
    ],

    'groups' => [
        'column'       => 'Group_Name',
        'label'        => 'Group (Sub-group)',
        'pk_col'       => 'ID_Groups',
        'needs_parent' => true,
        'parent_table' => 'family',
        'parent_col'   => 'ID_Family',
        'parent_label' => 'Family_Name',
        'parent_fk'    => 'Family_ID_Family',
        // element_transposable.Groups_ID_Groups: FK ibfk_6/8, ON DELETE SET NULL
        //   → DB sets NULL automatically → WARN only
        'fk_checks'    => [
            [
                'table'  => 'element_transposable',
                'column' => 'Groups_ID_Groups',
                'type'   => 'warn',
                'label'  => 'élément(s) transposable(s) (Groups_ID_Groups mis à NULL)',
            ],
        ],
    ],

    'tnp_chemestry' => [
        'column'    => 'chemestry',
        'label'     => 'Tnp Chemistry',
        'pk_col'    => 'ID_Tnp_chemestry',
        // orf.Tnp_chemestry_ID_Tnp_chemestry: FK orf_ibfk_5, ON DELETE SET NULL → WARN
        'fk_checks' => [
            [
                'table'  => 'orf',
                'column' => 'Tnp_chemestry_ID_Tnp_chemestry',
                'type'   => 'warn',
                'label'  => 'ORF(s) (champ chimie mis à NULL)',
            ],
        ],
    ],

    'type_element_transposable' => [
        'column'    => 'Type_ET',
        'label'     => 'Type Element Transposable',
        'pk_col'    => 'ID_Type_ET',
        // element_transposable.type_element_transposable_ID_Type_ET: FK ibfk_9, ON DELETE SET NULL → WARN
        // nom_type.type_element_transposable_ID_Type_ET: FK nom_type_ibfk_1, ON DELETE SET NULL → WARN
        'fk_checks' => [
            [
                'table'  => 'element_transposable',
                'column' => 'type_element_transposable_ID_Type_ET',
                'type'   => 'warn',
                'label'  => 'élément(s) transposable(s) (type mis à NULL)',
            ],
            [
                'table'  => 'nom_type',
                'column' => 'type_element_transposable_ID_Type_ET',
                'type'   => 'warn',
                'label'  => 'nom(s) type (type mis à NULL)',
            ],
        ],
    ],

    'ag_description' => [
        'column'    => 'description',
        'label'     => 'AG Description',
        'pk_col'    => 'ID_AG_description',
        // orf.AG_description_ID_AG_description: FK orf_ibfk_6, ON DELETE SET NULL → WARN
        'fk_checks' => [
            [
                'table'  => 'orf',
                'column' => 'AG_description_ID_AG_description',
                'type'   => 'warn',
                'label'  => 'ORF(s) (champ AG description mis à NULL)',
            ],
        ],
    ],

    'pg_function' => [
        'column'    => 'function',
        'label'     => 'PG Function',
        'pk_col'    => 'ID_PG_function',
        // orf.PG_function_ID_PG_function: FK orf_ibfk_7, ON DELETE SET NULL → WARN
        'fk_checks' => [
            [
                'table'  => 'orf',
                'column' => 'PG_function_ID_PG_function',
                'type'   => 'warn',
                'label'  => 'ORF(s) (champ PG function mis à NULL)',
            ],
        ],
    ],

    'nom_type' => [
        'column'    => 'nomType',
        'label'     => 'Nom Type',
        'pk_col'    => 'ID_nom_type',
        // current_names.nom_type_ID_nom_type: FK current_names_ibfk_1, ON UPDATE CASCADE only (no ON DELETE)
        //   → DB rejects delete if any current_names row references this nom_type → BLOCK
        'fk_checks' => [
            [
                'table'  => 'current_names',
                'column' => 'nom_type_ID_nom_type',
                'type'   => 'block',
                'label'  => 'nom(s) courant(s)',
            ],
        ],
    ],
];

// ---------------------------------------------------------------------------
// Helper: run all FK checks for a given table config and PK value.
// Returns ['blocking' => bool, 'blocks' => [...], 'warnings' => [...]]
// Each entry in blocks/warnings: ['count' => int, 'label' => string]
// ---------------------------------------------------------------------------
function runFkChecks(array $table_config, int $pk_value, $cnx): array {
    $result = ['blocking' => false, 'blocks' => [], 'warnings' => []];

    foreach ($table_config['fk_checks'] as $check) {
        $sql  = "SELECT COUNT(*) AS cnt FROM `{$check['table']}` WHERE `{$check['column']}` = ?";
        $stmt = mysqli_prepare($cnx, $sql);
        if (!$stmt) {
            continue;
        }
        mysqli_stmt_bind_param($stmt, 'i', $pk_value);
        mysqli_stmt_execute($stmt);
        $res   = mysqli_stmt_get_result($stmt);
        $row   = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        $count = (int)($row['cnt'] ?? 0);
        if ($count <= 0) {
            continue;
        }

        if ($check['type'] === 'block') {
            $result['blocking']  = true;
            $result['blocks'][]  = ['count' => $count, 'label' => $check['label']];
        } else {
            $result['warnings'][] = ['count' => $count, 'label' => $check['label']];
        }
    }

    return $result;
}

// ---------------------------------------------------------------------------
// AJAX endpoint: GET ?action=get_values&table=<name>
// Returns an HTML fragment with a radio-button table of current rows.
// Each radio carries value="<pk_id>" data-label="<display text>".
// ---------------------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'get_values') {
    $table_name = $_GET['table'] ?? '';

    if (!array_key_exists($table_name, $allowed_tables)) {
        echo '<p class="erreur">Table invalide.</p>';
        exit;
    }

    $config = $allowed_tables[$table_name];
    $pk_col = $config['pk_col'];
    $col    = $config['column'];
    $cnx    = connexion('isfinder');

    if (!empty($config['needs_parent'])) {
        // groups: JOIN with family to show family name alongside group name
        $parent_label = $config['parent_label'];
        $parent_col   = $config['parent_col'];
        $parent_table = $config['parent_table'];
        $parent_fk    = $config['parent_fk'];

        $sql = "SELECT g.`{$pk_col}`, g.`{$col}`, f.`{$parent_label}`
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
        echo '<th style="width:28px;"></th>';
        echo '<th style="text-align:left; padding:4px 8px; border-bottom:1px solid #ccc;">Famille</th>';
        echo '<th style="text-align:left; padding:4px 8px; border-bottom:1px solid #ccc;">Groupe</th>';
        echo '</tr></thead><tbody>';

        while ($row = mysqli_fetch_assoc($res)) {
            $pk_val    = (int)$row[$pk_col];
            $text_val  = htmlspecialchars($row[$col]);
            $fam_label = htmlspecialchars($row[$parent_label]);
            $display   = $fam_label . ' / ' . $text_val;
            echo '<tr class="value-row" style="cursor:pointer;" onclick="selectRow(this)">';
            echo '<td style="padding:3px 8px; text-align:center;">'
                . '<input type="radio" name="selected_id" value="' . $pk_val . '"'
                . ' data-label="' . htmlspecialchars($display, ENT_QUOTES) . '"'
                . ' onclick="event.stopPropagation(); handleSelection(this)">'
                . '</td>';
            echo '<td style="padding:3px 8px; border-bottom:1px solid #eee;">' . $fam_label . '</td>';
            echo '<td style="padding:3px 8px; border-bottom:1px solid #eee;">' . $text_val . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';

    } else {
        // Simple single-column table
        $stmt = mysqli_prepare($cnx,
            "SELECT `{$pk_col}`, `{$col}` FROM `{$table_name}` ORDER BY `{$col}`");

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

        echo '<table style="width:100%; border-collapse:collapse; font-size:0.9em;">';
        echo '<thead><tr>';
        echo '<th style="width:28px;"></th>';
        echo '<th style="text-align:left; padding:4px 8px; border-bottom:1px solid #ccc;">Valeur</th>';
        echo '</tr></thead><tbody>';

        while ($row = mysqli_fetch_assoc($res)) {
            $pk_val   = (int)$row[$pk_col];
            $text_val = htmlspecialchars($row[$col]);
            echo '<tr class="value-row" style="cursor:pointer;" onclick="selectRow(this)">';
            echo '<td style="padding:3px 8px; text-align:center;">'
                . '<input type="radio" name="selected_id" value="' . $pk_val . '"'
                . ' data-label="' . htmlspecialchars($row[$col], ENT_QUOTES) . '"'
                . ' onclick="event.stopPropagation(); handleSelection(this)">'
                . '</td>';
            echo '<td style="padding:3px 8px; border-bottom:1px solid #eee;">' . $text_val . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';

        mysqli_stmt_close($stmt);
    }

    exit;
}

// ---------------------------------------------------------------------------
// AJAX endpoint: GET ?action=check_usage&table=<name>&id=<pk_int>
// Returns JSON with usage info so JS can show block/warn UI before the user submits.
// ---------------------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'check_usage') {
    header('Content-Type: application/json; charset=utf-8');

    $table_name = $_GET['table'] ?? '';
    $pk_value   = intval($_GET['id'] ?? 0);

    if (!array_key_exists($table_name, $allowed_tables) || $pk_value <= 0) {
        echo json_encode(['error' => 'Paramètres invalides.']);
        exit;
    }

    $config   = $allowed_tables[$table_name];
    $pk_col   = $config['pk_col'];
    $col      = $config['column'];
    $cnx      = connexion('isfinder');

    // Fetch the display label of the row being checked
    $stmt = mysqli_prepare($cnx, "SELECT `{$col}` FROM `{$table_name}` WHERE `{$pk_col}` = ? LIMIT 1");
    if (!$stmt) {
        echo json_encode(['error' => 'Erreur base de données.']);
        exit;
    }
    mysqli_stmt_bind_param($stmt, 'i', $pk_value);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$row) {
        echo json_encode(['error' => 'Valeur introuvable.']);
        exit;
    }

    $check = runFkChecks($config, $pk_value, $cnx);

    echo json_encode([
        'blocking'   => $check['blocking'],
        'blocks'     => $check['blocks'],
        'warnings'   => $check['warnings'],
        'text_value' => $row[$col],
    ]);
    exit;
}

// ---------------------------------------------------------------------------
// Full-page includes — must come before any HTML output
// ---------------------------------------------------------------------------
require_once('includes/entete.inc.php');
require_once('includes/aside.inc.php');

echo '<link type="text/css" rel="stylesheet" href="styles/styles.css" media="screen" />';
echo "<article>";
echo "<h2>Supprimer une valeur</h2>";
echo "<p style='font-style: italic; color: #666; margin-bottom: 15px;'>Supprimer un enregistrement dans les tables de référence. Les dépendances sont vérifiées avant chaque suppression.</p>";

$message    = '';
$last_table = $_POST['table_name'] ?? '';

// ---------------------------------------------------------------------------
// POST handler
// Server-side FK re-check (never trust the client alone), then DELETE.
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_reference'])) {
    $table_name = $_POST['table_name'] ?? '';
    $pk_value   = intval($_POST['delete_id'] ?? 0);

    if (!array_key_exists($table_name, $allowed_tables)) {
        $message = "<p class='erreur'>Table sélectionnée invalide.</p>";

    } elseif ($pk_value <= 0) {
        $message = "<p class='erreur'>Aucune valeur sélectionnée.</p>";

    } else {
        $config = $allowed_tables[$table_name];
        $pk_col = $config['pk_col'];
        $col    = $config['column'];
        $cnx    = connexion('isfinder');

        // Fetch the display label for the success/error message
        $stmt = mysqli_prepare($cnx,
            "SELECT `{$col}` FROM `{$table_name}` WHERE `{$pk_col}` = ? LIMIT 1");

        if (!$stmt) {
            $message = "<p class='erreur'>Erreur de base de données : impossible de préparer la requête.</p>";
        } else {
            mysqli_stmt_bind_param($stmt, 'i', $pk_value);
            mysqli_stmt_execute($stmt);
            $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);

            if (!$row) {
                $message = "<p class='erreur'>La valeur sélectionnée n'existe plus dans la table.</p>";
            } else {
                $text_value = $row[$col];
                $check      = runFkChecks($config, $pk_value, $cnx);

                if ($check['blocking']) {
                    // Build a readable list of what's blocking the delete
                    $details = array_map(function ($b) {
                        return $b['count'] . ' ' . $b['label'];
                    }, $check['blocks']);

                    $message = "<p class='erreur'>Impossible de supprimer '<strong>"
                        . htmlspecialchars($text_value)
                        . "</strong>' — référencée par : "
                        . implode(', ', $details) . ".</p>";

                } else {
                    // Safe to delete — execute the DELETE
                    $del = mysqli_prepare($cnx,
                        "DELETE FROM `{$table_name}` WHERE `{$pk_col}` = ?");

                    if (!$del) {
                        $message = "<p class='erreur'>Erreur de base de données : impossible de préparer la suppression.</p>";
                    } else {
                        mysqli_stmt_bind_param($del, 'i', $pk_value);

                        if (mysqli_stmt_execute($del)) {
                            // data-refresh-table tells JS to reload the values panel after page load
                            $message = "<p style='color:green;' data-refresh-table='"
                                . htmlspecialchars($table_name) . "'>La valeur '<strong>"
                                . htmlspecialchars($text_value)
                                . "</strong>' a été supprimée avec succès de la table '<strong>"
                                . htmlspecialchars($table_name) . "</strong>'.</p>";
                        } else {
                            $message = "<p class='erreur'>Erreur lors de la suppression : "
                                . htmlspecialchars(mysqli_stmt_error($del)) . "</p>";
                        }
                        mysqli_stmt_close($del);
                    }
                }
            }
        }
    }
}

echo $message;
?>

<form action="delete_reference.php" method="POST" id="delete-form" style="max-width: 600px; margin: 20px 0;">
    <fieldset style="border: 1px solid #ccc; padding: 20px; border-radius: 5px; background-color: #f9f9f9;">
        <legend style="font-weight: bold; padding: 0 10px; color: #333;">Sélection de la valeur à supprimer</legend>

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

        <!-- Populated by JS when the user selects a radio button -->
        <input type="hidden" name="delete_id"    id="delete_id"    value="">
        <input type="hidden" name="delete_label" id="delete_label" value="">
    </fieldset>
</form>

<!-- Values panel: radio-button list loaded via AJAX -->
<div id="current-values"
     style="max-width: 600px; margin-top: 10px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background-color: #fafafa; display: none;">
    <strong style="display:block; margin-bottom: 8px; color: #333;">Valeurs actuelles — cliquez sur une ligne pour la sélectionner :</strong>
    <div id="current-values-content"></div>
</div>

<!-- FK check result panel: shown after a radio is selected -->
<div id="fk-check-panel" style="max-width: 600px; margin-top: 10px; padding: 15px; border-radius: 5px; display: none;">
    <div id="fk-check-content"></div>
</div>

<script>
(function () {
    'use strict';

    var tableSelect      = document.getElementById('table_name');
    var valuesPanel      = document.getElementById('current-values');
    var valuesContent    = document.getElementById('current-values-content');
    var fkPanel          = document.getElementById('fk-check-panel');
    var fkContent        = document.getElementById('fk-check-content');
    var deleteIdInput    = document.getElementById('delete_id');
    var deleteLabelInput = document.getElementById('delete_label');
    var deleteForm       = document.getElementById('delete-form');

    // -----------------------------------------------------------------------
    // Load the radio-button list for the selected table
    // -----------------------------------------------------------------------
    function loadValues(table) {
        if (!table) {
            valuesPanel.style.display = 'none';
            valuesContent.innerHTML   = '';
            hideFkPanel();
            return;
        }
        valuesContent.innerHTML   = '<em style="color:#888;">Chargement...</em>';
        valuesPanel.style.display = 'block';
        hideFkPanel();
        deleteIdInput.value    = '';
        deleteLabelInput.value = '';

        fetch('delete_reference.php?action=get_values&table=' + encodeURIComponent(table))
            .then(function (r) { return r.text(); })
            .then(function (html) { valuesContent.innerHTML = html; })
            .catch(function () {
                valuesContent.innerHTML = '<p class="erreur">Erreur lors du chargement des valeurs.</p>';
            });
    }

    // -----------------------------------------------------------------------
    // Called when a radio button is selected (direct click or row click)
    // -----------------------------------------------------------------------
    window.handleSelection = function (radioEl) {
        var pk    = radioEl.value;
        var label = radioEl.getAttribute('data-label');
        var table = tableSelect.value;

        deleteIdInput.value    = pk;
        deleteLabelInput.value = label;

        // Highlight the selected row
        var rows = valuesContent.querySelectorAll('tr.value-row');
        rows.forEach(function (r) { r.style.backgroundColor = ''; });
        var parentRow = radioEl.closest('tr.value-row');
        if (parentRow) {
            parentRow.style.backgroundColor = '#e8f0fe';
        }

        checkUsage(table, pk, label);
    };

    // Allow clicking anywhere on the row to select the radio
    window.selectRow = function (rowEl) {
        var radio = rowEl.querySelector('input[type="radio"]');
        if (radio) {
            radio.checked = true;
            handleSelection(radio);
        }
    };

    // -----------------------------------------------------------------------
    // Check FK usage via AJAX and render the result panel
    // -----------------------------------------------------------------------
    function checkUsage(table, pk, label) {
        fkPanel.style.display         = 'block';
        fkPanel.style.border          = '1px solid #ddd';
        fkPanel.style.backgroundColor = '#f5f5f5';
        fkContent.innerHTML           = '<em style="color:#888;">Vérification des dépendances...</em>';

        fetch('delete_reference.php?action=check_usage&table=' + encodeURIComponent(table) + '&id=' + encodeURIComponent(pk))
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.error) {
                    renderError(data.error);
                    return;
                }

                var html = '';

                if (data.blocking) {
                    // Cannot delete — show block message, no delete button
                    fkPanel.style.backgroundColor = '#fff0f0';
                    fkPanel.style.border          = '1px solid #e57373';
                    html += '<p style="color:#c62828; font-weight:bold; margin:0 0 8px;">&#128683; Suppression impossible</p>';
                    html += '<p style="margin:0 0 6px;">La valeur <strong>' + escHtml(label) + '</strong> est référencée par :</p>';
                    html += '<ul style="margin:0 0 8px; padding-left:20px;">';
                    data.blocks.forEach(function (b) {
                        html += '<li>' + b.count + ' ' + escHtml(b.label) + '</li>';
                    });
                    html += '</ul>';
                    html += '<p style="color:#666; font-style:italic; margin:0;">Modifiez ou supprimez ces enregistrements avant de retenter.</p>';

                } else {
                    if (data.warnings && data.warnings.length > 0) {
                        // Warn: DB will SET NULL on some rows
                        fkPanel.style.backgroundColor = '#fff8e1';
                        fkPanel.style.border          = '1px solid #f9a825';
                        html += '<p style="color:#e65100; font-weight:bold; margin:0 0 8px;">&#9888; Suppression avec impact</p>';
                        html += '<p style="margin:0 0 6px;">Supprimer <strong>' + escHtml(label) + '</strong> mettra à NULL les champs suivants :</p>';
                        html += '<ul style="margin:0 0 12px; padding-left:20px;">';
                        data.warnings.forEach(function (w) {
                            html += '<li>' + w.count + ' ' + escHtml(w.label) + '</li>';
                        });
                        html += '</ul>';
                    } else {
                        // No references at all — clean delete
                        fkPanel.style.backgroundColor = '#f1f8e9';
                        fkPanel.style.border          = '1px solid #81c784';
                        html += '<p style="color:#2e7d32; font-weight:bold; margin:0 0 8px;">&#10003; Aucune dépendance détectée</p>';
                    }

                    // Delete button — JS confirmation required before submitting
                    html += '<button type="button"'
                        + ' style="padding:9px 18px; background-color:#c62828; color:white; border:none;'
                        + ' border-radius:4px; cursor:pointer; font-weight:bold;"'
                        + ' onmouseover="this.style.backgroundColor=\'#b71c1c\'"'
                        + ' onmouseout="this.style.backgroundColor=\'#c62828\'"'
                        + ' onclick="submitDelete(\'' + escAttr(label) + '\')">'
                        + 'Supprimer cette valeur</button>';
                }

                fkContent.innerHTML = html;
            })
            .catch(function () {
                renderError('Erreur lors de la vérification des dépendances.');
            });
    }

    // -----------------------------------------------------------------------
    // Submit the delete form after a JS confirmation dialog
    // -----------------------------------------------------------------------
    window.submitDelete = function (label) {
        if (!confirm('Confirmer la suppression de "' + label + '" ?\n\nCette action est irréversible.')) {
            return;
        }
        var marker   = document.createElement('input');
        marker.type  = 'hidden';
        marker.name  = 'delete_reference';
        marker.value = '1';
        deleteForm.appendChild(marker);
        deleteForm.submit();
    };

    // -----------------------------------------------------------------------
    // Utility helpers
    // -----------------------------------------------------------------------
    function hideFkPanel() {
        fkPanel.style.display = 'none';
        fkContent.innerHTML   = '';
    }

    function renderError(msg) {
        fkPanel.style.backgroundColor = '#fff0f0';
        fkPanel.style.border          = '1px solid #e57373';
        fkContent.innerHTML = '<p class="erreur">' + escHtml(msg) + '</p>';
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // Safe to embed inside a single-quoted JS string attribute
    function escAttr(str) {
        return String(str).replace(/\\/g, '\\\\').replace(/'/g, "\\'");
    }

    // -----------------------------------------------------------------------
    // Event wiring
    // -----------------------------------------------------------------------
    tableSelect.addEventListener('change', function () {
        loadValues(this.value);
    });

    // On page load after a successful POST, auto-reload the panel for the same table
    document.addEventListener('DOMContentLoaded', function () {
        var successEl = document.querySelector('[data-refresh-table]');
        if (successEl) {
            var table = successEl.getAttribute('data-refresh-table');
            tableSelect.value = table;
            loadValues(table);
        } else if (tableSelect.value) {
            loadValues(tableSelect.value);
        }
    });

    // Also initialise if a table was already selected on first load
    if (tableSelect.value) {
        loadValues(tableSelect.value);
    }

}());
</script>

</article>
<?php // require_once('includes/pied.inc.php'); ?>
