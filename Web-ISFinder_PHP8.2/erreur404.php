<!DOCTYPE html>
<html>
<head>
    <title>Error 404 - Page Not Found</title>
    <meta charset="utf-8" /> 
    <link type="text/css" rel="stylesheet" href="styles/styles.css" media="screen" />
</head>
<body style="background-color: #e6f1eb;">
    <div align="center" style="margin-top: 100px;">
        <h3>The page 
            <?php
            /* 
             * Mise à jour pour PHP 8.5 : 
             * - Utilisation de $_SERVER au lieu de getenv pour une meilleure compatibilité.
             * - Utilisation de htmlspecialchars() pour prévenir les failles XSS (sécurité).
             */
            $page = $_SERVER['REQUEST_URI'] ?? '/';
            echo "https://www-is.biotoul.fr" . htmlspecialchars($page);
            ?>
            does not exist on this server
        </h3>
        <p>
            <a href="https://www-is.biotoul.fr/" target="_top">Return to website home page</a>
        </p>
    </div>
</body>
</html>
