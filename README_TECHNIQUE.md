# Documentation Technique - Maintenance ISfinder & ISadmin (PHP 8.5)

Ce document liste les modifications techniques faites pour le passage à PHP 8.5. Le but n'était pas de tout réécrire, mais de garder l'existant fonctionnel tout en ajoutant quelques outils.

Voici ce qui a été fait.

## 1. Mise à jour vers PHP 8.5

PHP 8.5 bloque complètement la page à la moindre petite erreur. Il a fallu corriger l'ancien code.

* **Variables inconnues :** J'ai ajouté des vérifications avant d'utiliser les variables. Si on essaie de lire un truc qui n'existe pas, ça plante.
* **Sessions :** J'ai nettoyé les données avant de les stocker en mémoire. PHP 8 refuse de gérer les données avec des mauvais formats d'identifiant.
* **Base de données :** Si une case est vide pour une information obligatoire, le code envoie maintenant "NULL" ou "DEFAULT". La base de données n'accepte plus les cases vides par défaut.
* **Images manquantes :** Le serveur plantait s'il essayait de lire la taille d'une image absente. J'ai ajouté un test tout simple pour vérifier que le fichier est bien là avant de le lire.

## 2. Modifications sur ISfinder (Site public)

* **Le Captcha :** J'ai mis un Captcha sur les formulaires pour bloquer les robots. Mais voici le point important : si on se trompe, on garde toutes les données tapées. La page se recharge avec le texte déjà rempli.
* **Page des liens :** C'est devenu un carrousel avec des cartes. J'ai aussi mis les bases de données clés tout en haut.
* **Ajustements visuels :** J'ai juste centré des titres et harmonisé les boutons. Rien de lourd.

## 3. Modifications sur ISadmin (Site interne)

* **Boutons rapides :** Sur la liste des séquences, j'ai ajouté deux boutons : une poubelle (supprimer) et un retour (renvoyer en brouillon). Une boîte de confirmation avec le nom de la base s'affiche pour éviter les erreurs.
* **Suppression propre :** Quand on supprime, ça enlève bien les séquences et les ORF liés. Mais ça garde les soumetteurs et les hôtes, parce que d'autres fiches en ont peut-être besoin.

## 4. Les nouveaux outils

### Export CSV
* Vous choisissez une table et des filtres, et l'outil crée la requête SQL.
* Pour la sécurité, ça bloque toute tentative de modification (il ne fait que des SELECT).
* J'ai aussi contourné un vieux bug qui bloquait les requêtes sur les tables avec le mot "insert" (comme `et_insertion_site`).

### Gestion des listes déroulantes
* Vous pouvez ajouter ou supprimer des éléments de référence (familles, groupes) depuis l'interface.
* Le code vérifie si le nom existe déjà quand on l'ajoute.
* Pour la suppression, le système gère les dépendances : il bloque la suppression d'une famille si elle est encore utilisée, et il affiche une alerte pour les groupes en indiquant le nombre de fiches touchées.

### Fiches incomplètes
* Une nouvelle page repère les fiches sauvegardées mais qui n'ont pas les données nécessaires (comme les ORF).
* On clique sur la ligne, et on arrive directement sur la page pour corriger.

## 5. Tests obligatoires (Pour les prochains développeurs)

Si quelqu'un touche au code, voici ce qu'il faut absolument vérifier :

* **L'intégrité de la base :** Après une modification, regardez dans la base si vous n'avez pas effacé un hôte ou un soumetteur utilisé ailleurs.
* **Les erreurs de formulaire :** Remplissez un formulaire, plantez le Captcha exprès, et vérifiez que les champs (surtout ceux ajoutés dynamiquement) ne sont pas effacés.
* **Le renvoi en brouillon :** Renvoyez une fiche et vérifiez dans la base que son statut repasse bien à 1 (ISsubmit).

## 6. Limites actuelles et prochaines étapes

* **Ancien style :** L'affichage (HTML) et les calculs sont mélangés dans les mêmes fichiers. C'est normal pour du vieux code, mais ça reste lourd à lire.
* **Déconnexions :** Le vieux système de sessions peut parfois créer de petites erreurs si le serveur est trop pointilleux.
* **Passer à PDO :** C'est ce qu'il faudrait faire ensuite pour la base de données. Ça réglerait beaucoup de soucis de sécurité.
* **Gérer les clés étrangères côté base :** Ce serait mieux que la base de données interdise elle-même de supprimer une famille utilisée, plutôt que de laisser le PHP s'en occuper.
