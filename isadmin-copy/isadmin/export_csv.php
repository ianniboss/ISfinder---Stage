<?php
require_once('includes/entete.inc.php');
require_once('includes/aside.inc.php');
require_once('includes/init.inc.php');

$allowed_tables = [
    'ag_description', 'current_names', 'element_transposable', 'element_transposable_has_host',
    'et_insertion_site', 'family', 'groups', 'host', 'is_ends', 'name_attribution', 'nom_type',
    'orf', 'orf_has_orf_modification', 'orf_modification', 'parent_link', 'pg_function',
    'References', 'submission', 'submiters', 'synonyme', 'tnp_chemestry', 'tnp_description',
    'type_element_transposable'
];

// Récupérer le schéma complet pour les tables autorisées
$schema = [];
$lien = mysqli_connect(DB_server, DB_user, DB_password, DB_bdd);
if ($lien) {
    // Échappement du nom de la base de données
    $db_esc = mysqli_real_escape_string($lien, DB_bdd);
    
    // Au lieu de faire 23 requêtes, faire une seule requête en utilisant IN
    $tables_esc = array_map(function($t) use ($lien) {
        return "'" . mysqli_real_escape_string($lien, $t) . "'";
    }, $allowed_tables);
    $in_clause = implode(',', $tables_esc);

    $query = "SELECT table_name, COLUMN_NAME AS Field FROM information_schema.columns WHERE table_schema = '{$db_esc}' AND table_name IN ({$in_clause}) ORDER BY table_name, ordinal_position";
    $result = mysqli_query($lien, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $t = $row['table_name'];
            if (!isset($schema[$t])) {
                $schema[$t] = [];
            }
            $schema[$t][] = $row['Field'];
        }
    }
    mysqli_close($lien);
}
?>

<article>
    <section>
        <h2> Export (CSV)</h2>
        <p>Utilisez le constructeur ci-dessous pour créer votre requête SQL. Vous pouvez également modifier la requête générée manuellement avant l'exportation.</p>
        
        <div id="query-builder" style="background: #f9f9f9; padding: 15px; border: 1px solid #ccc; margin-bottom: 20px;">
            <h3>Générateur de Requête</h3>
            <div id="blocks-container" style="display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 15px;">
                <!-- Les blocs seront ajoutés ici par JS -->
            </div>
            
            <button type="button" id="btn-add-block" style="padding: 5px 10px; cursor: pointer;">+ Ajouter une colonne</button>
            <button type="button" id="btn-generate-sql" class="btn-droit" style="margin-left: 10px;">Générer SQL</button>
        </div>

        <form method="post" action="scripts/process_csv.php">
            <h3>Requête SQL (Éditable) :</h3>
            <p style="font-size: 0.9em; color: #666;">Seules les requêtes <code>SELECT</code> sont autorisées. L'utilisation de points-virgules (<code>;</code>) est interdite pour sécurité.</p>
            <textarea id="custom_sql" name="custom_sql" style="width: 100%; height: 150px; font-family: monospace; padding: 10px; border: 1px solid #bdac99; border-radius: 4px;" placeholder="La requête apparaîtra ici..."></textarea>
            
            <br /><br />
            <p>
                <input type="submit" class="btn-droit" value="Exécuter et Télécharger CSV" />
            </p>
        </form>
    </section>
</article>

<script>
    const dbSchema = <?php echo json_encode($schema); ?>;
    const allowedTables = Object.keys(dbSchema);

    const blocksContainer = document.getElementById('blocks-container');
    const btnAddBlock = document.getElementById('btn-add-block');
    const btnGenerateSql = document.getElementById('btn-generate-sql');
    const customSqlTextarea = document.getElementById('custom_sql');
    
    let blockCounter = 0;

    function createBlock() {
        blockCounter++;
        const blockId = 'block-' + blockCounter;
        
        const blockDiv = document.createElement('div');
        blockDiv.id = blockId;
        blockDiv.style.border = '1px solid #bdac99';
        blockDiv.style.padding = '10px';
        blockDiv.style.background = '#fff';
        blockDiv.style.borderRadius = '4px';
        blockDiv.style.minWidth = '250px';
        
        // Sélection de la table
        let tableOptions = '<option value="">-- Table --</option>';
        allowedTables.forEach(t => {
            tableOptions += `<option value="${t}">${t}</option>`;
        });
        
        blockDiv.innerHTML = `
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <strong>Bloc ${blockCounter}</strong>
                <button type="button" onclick="document.getElementById('${blockId}').remove()" style="color: red; border: none; background: none; cursor: pointer; font-weight: bold;">X</button>
            </div>
            <div style="margin-bottom: 5px;">
                <select class="qb-table" style="width: 100%; padding: 4px; margin-bottom: 4px;">${tableOptions}</select>
            </div>
            <div style="margin-bottom: 5px;">
                <select class="qb-field" style="width: 100%; padding: 4px; margin-bottom: 4px;">
                    <option value="">-- Colonne --</option>
                </select>
            </div>
            <div style="margin-bottom: 5px;">
                <label><input type="checkbox" class="qb-show" checked> Afficher dans le SELECT</label>
            </div>
            <hr style="margin: 5px 0;" />
            <div style="margin-bottom: 5px;">
                <select class="qb-operator" style="width: 100%; padding: 4px; margin-bottom: 4px;">
                    <option value="">-- Condition --</option>
                    <option value="=">=</option>
                    <option value="!=">!=</option>
                    <option value=">">&gt;</option>
                    <option value="<">&lt;</option>
                    <option value=">=">&gt;=</option>
                    <option value="<=">&lt;=</option>
                    <option value="LIKE">LIKE</option>
                    <option value="NOT LIKE">NOT LIKE</option>
                </select>
            </div>
            <div>
                <input type="text" class="qb-criteria" placeholder="Valeur ('string' ou orf.id)" style="width: 100%; padding: 4px; box-sizing: border-box;" />
            </div>
        `;
        
        blocksContainer.appendChild(blockDiv);
        
        // Ajouter un écouteur d'événement pour le changement de table
        const tableSelect = blockDiv.querySelector('.qb-table');
        const fieldSelect = blockDiv.querySelector('.qb-field');
        
        tableSelect.addEventListener('change', function() {
            const selectedTable = this.value;
            fieldSelect.innerHTML = '<option value="">-- Colonne --</option><option value="*">* (Toutes)</option>';
            if (selectedTable && dbSchema[selectedTable]) {
                dbSchema[selectedTable].forEach(col => {
                    fieldSelect.innerHTML += `<option value="${col}">${col}</option>`;
                });
            }
        });
    }

    // Initialiser avec un bloc
    createBlock();

    btnAddBlock.addEventListener('click', createBlock);

    btnGenerateSql.addEventListener('click', function() {
        const blocks = document.querySelectorAll('#blocks-container > div');
        
        let selectFields = [];
        let fromTables = new Set();
        let whereClauses = [];
        let hasError = false;
        
        blocks.forEach(block => {
            const table = block.querySelector('.qb-table').value;
            const field = block.querySelector('.qb-field').value;
            const isShow = block.querySelector('.qb-show').checked;
            const operator = block.querySelector('.qb-operator').value;
            let criteria = block.querySelector('.qb-criteria').value.trim();
            
            if (table) {
                fromTables.add(table);
                
                let fieldRef = field === '*' ? `\`${table}\`.*` : (field ? `\`${table}\`.\`${field}\`` : null);
                
                if (fieldRef && isShow) {
                    selectFields.push(fieldRef);
                }
                
                if (fieldRef && operator && criteria !== '') {
                    if (field === '*') {
                        alert(`Attention : Impossible de filtrer sur '*' (Toutes les colonnes). Veuillez ajouter un nouveau bloc, sélectionner la colonne spécifique (ex: et_name), décocher "Afficher dans le SELECT", et appliquer votre condition là-bas.`);
                        hasError = true;
                        return;
                    }

                    let formattedCriteria = criteria;
                    // Ajout automatique de guillemets s'il s'agit d'une chaîne (pas un nombre, pas de point pour les relations table.colonne, et pas de guillemets existants)
                    if (isNaN(criteria) && !criteria.includes('.') && !criteria.startsWith("'") && !criteria.startsWith('"')) {
                        // Doubler les guillemets simples pour l'échappement SQL dans le texte généré
                        formattedCriteria = "'" + criteria.replace(/'/g, "''") + "'";
                    }

                    whereClauses.push(`${fieldRef} ${operator} ${formattedCriteria}`);
                }
            }
        });
        
        if (hasError) return;

        if (fromTables.size === 0) {
            alert("Veuillez sélectionner au moins une table.");
            return;
        }
        
        let sql = "SELECT ";
        if (selectFields.length > 0) {
            sql += selectFields.join(", ");
        } else {
            sql += "*";
        }
        
        sql += "\nFROM " + Array.from(fromTables).map(t => `\`${t}\``).join(", ");
        
        if (whereClauses.length > 0) {
            sql += "\nWHERE " + whereClauses.join(" AND ");
        }
        
        customSqlTextarea.value = sql;
    });

</script>

<?php
require_once('includes/pied.inc.php');
?>
