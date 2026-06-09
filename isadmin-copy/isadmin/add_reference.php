<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('includes/entete.inc.php');
require_once('includes/aside.inc.php');
require_once("includes/function.inc.php");

echo '<link type="text/css" rel="stylesheet" href="styles/styles.css" media="screen" />';
echo "<article>";
echo "<h2>Add Reference Value</h2>";

$allowed_tables = [
    'family' => ['column' => 'Family_Name', 'label' => 'Family Name'],
    'tnp_chemestry' => ['column' => 'chemestry', 'label' => 'Tnp Chemistry'],
    'type_element_transposable' => ['column' => 'Type_ET', 'label' => 'Type Element Transposable'],
    'ag_description' => ['column' => 'description', 'label' => 'AG Description'],
    'pg_function' => ['column' => 'function', 'label' => 'PG Function']
];

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reference'])) {
    $table_name = $_POST['table_name'] ?? '';
    $reference_value = trim($_POST['reference_value'] ?? '');

    if (!array_key_exists($table_name, $allowed_tables)) {
        $message = "<p class='erreur'>Invalid table selected.</p>";
    } elseif ($reference_value === '') {
        $message = "<p class='erreur'>Reference value cannot be empty.</p>";
    } else {
        $column = $allowed_tables[$table_name]['column'];
        
        $cnx = connexion("isfinder");
        
        // Check if value already exists
        $check_stmt = mysqli_prepare($cnx, "SELECT `$column` FROM `$table_name` WHERE `$column` = ? LIMIT 1");
        if ($check_stmt) {
            mysqli_stmt_bind_param($check_stmt, "s", $reference_value);
            mysqli_stmt_execute($check_stmt);
            mysqli_stmt_store_result($check_stmt);
            
            if (mysqli_stmt_num_rows($check_stmt) > 0) {
                $message = "<p class='erreur'>The value '<strong>" . htmlspecialchars($reference_value) . "</strong>' already exists in table '<strong>" . htmlspecialchars($table_name) . "</strong>'.</p>";
                mysqli_stmt_close($check_stmt);
            } else {
                mysqli_stmt_close($check_stmt);
                
                // Prepare statement to prevent SQL injection
                $stmt = mysqli_prepare($cnx, "INSERT INTO `$table_name` (`$column`) VALUES (?)");
                
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "s", $reference_value);
                    if (mysqli_stmt_execute($stmt)) {
                        $message = "<p style='color: green;'>Successfully added '<strong>" . htmlspecialchars($reference_value) . "</strong>' to table '<strong>" . htmlspecialchars($table_name) . "</strong>'.</p>";
                    } else {
                        $message = "<p class='erreur'>Error inserting value: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>";
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $message = "<p class='erreur'>Database error: Could not prepare statement.</p>";
                }
            }
        } else {
            $message = "<p class='erreur'>Database error: Could not prepare check statement.</p>";
        }
    }
}

echo $message;
?>

<form action="add_reference.php" method="POST" style="max-width: 600px; margin: 20px 0;">
    <fieldset style="border: 1px solid #ccc; padding: 20px; border-radius: 5px; background-color: #f9f9f9;">
        <legend style="font-weight: bold; padding: 0 10px; color: #333;">New Reference Details</legend>
        
        <p style="margin-bottom: 15px;">
            <label for="table_name" style="display: block; margin-bottom: 5px; font-weight: bold;">Select Target Table:</label>
            <select name="table_name" id="table_name" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                <option value="">-- Choose a table --</option>
                <?php foreach ($allowed_tables as $table => $config): ?>
                    <option value="<?php echo htmlspecialchars($table); ?>" <?php echo (isset($_POST['table_name']) && $_POST['table_name'] === $table) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($config['label']); ?> (<?php echo htmlspecialchars($table); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p style="margin-bottom: 20px;">
            <label for="reference_value" style="display: block; margin-bottom: 5px; font-weight: bold;">Reference Value:</label>
            <input type="text" name="reference_value" id="reference_value" required value="<?php echo isset($_POST['submit_reference']) && empty($message) ? '' : htmlspecialchars($_POST['reference_value'] ?? ''); ?>" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;" placeholder="Enter the new value here..." />
        </p>

        <p style="margin: 0; text-align: right;">
            <input type="submit" name="submit_reference" value="Add Value" style="padding: 10px 20px; background-color: #0056b3; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;" onmouseover="this.style.backgroundColor='#004494'" onmouseout="this.style.backgroundColor='#0056b3'" />
        </p>
    </fieldset>
</form>

</article>
<?php // require_once('includes/pied.inc.php'); ?>
