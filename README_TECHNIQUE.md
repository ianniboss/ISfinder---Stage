# Documentation du stage ISfinder / ISadmin

**Résumé de ce qui a été fait**
* **Dates :** Du 14 Avril au 13 Juin 2026.
* **Nombre de modifications analysées :** 95 modifications
* **Les grandes nouveautés :**
    * Mise à jour de tout l'ancien code pour qu'il fonctionne avec les nouvelles versions de PHP (8.4 et 8.5).
    * Ajout d'une sécurité (Captcha) sur les formulaires ouverts au public, sans faire perdre le texte si on se trompe.
    * Création d'un outil pour exporter les données en fichier CSV, avec un système qui construit les recherches tout seul.
    * Création de menus pour ajouter ou supprimer des valeurs de référence (comme les familles), avec une sécurité qui vérifie qu'on ne casse rien ailleurs en supprimant.
    * Ajout de boutons pour supprimer et renvoyer des fiches.
    * Outil pour trouver les séquences qui sont incomplètes.
* **Ce qu'il reste à vérifier :**
    * Tester l'exportation CSV sur la vraie base de données (le serveur .150) avec beaucoup de données.
    * Bien vérifier que les formulaires de soumission marchent toujours parfaitement avec le nouveau Captcha.
    * Tester les suppressions avec les responsables pour être sûr que ça réagit bien face aux erreurs.

---

## 1. Présentation générale du projet

### Le contexte
Le but de ce stage était de réparer et moderniser deux sites internet : **ISfinder** (le site public utilisé par les chercheurs) et **ISadmin** (le site privé utilisé par l'équipe pour valider les données).
Le problème, c'est que le code de ces sites est assez vieux. 

Il fallait mettre à jour les serveurs vers les nouvelles versions **PHP 8.4 et PHP 8.5** pour des questions de sécurité. Le souci, c'est que ces nouvelles versions sont très sévères. Là où l'ancien PHP laissait passer des petites erreurs, le nouveau PHP bloque tout et affiche des pages blanches.

L'objectif était donc de :
1. Trouver toutes ces erreurs et les corriger.
2. Améliorer l'interface d'administration pour vous faciliter la vie.
3. Créer de nouveaux outils, comme l'exportation des données.

### La règle d'or
La consigne principale était claire : on ne réécrit pas tout depuis zéro. Il fallait garder l'ancien code tel qu'il est et faire des petites modifications ciblées. Cela permet d'être sûr de ne rien casser sur les sites qui sont actuellement en ligne.

---

## 2. Mise à jour vers PHP 8

### Le but
Faire marcher l'ancien code sur les nouveaux serveurs PHP sans que tout plante.

### Ce qui a été corrigé
Il a fallu changer pas mal de choses dans les fichiers :

1. **Les variables inconnues :**
    * Avant, si on essayait d'afficher une information qui n'existait pas, PHP ne disait rien. Maintenant, il fait planter la page. 
    * *Ce que j'ai fait :* J'ai rajouté des vérifications partout. Avant de lire une information, on vérifie d'abord si elle existe.
2. **Le système de sauvegarde temporaire (Les Sessions) :**
    * Quand on passe d'une page à l'autre, on garde des données en mémoire (comme les formulaires). PHP 8 bloquait quand ces données avaient de mauvais numéros d'identification.
    * *Ce que j'ai fait :* J'ai nettoyé ces données avant de les mettre en mémoire, pour ne garder que ce qui est propre.
3. **Les erreurs de base de données (les cases vides) :**
    * Avant, si on envoyait une case vide pour une information obligatoire (comme une fonction), la base de données mettait la valeur par défaut. Maintenant, elle refuse et affiche une erreur.
    * *Ce que j'ai fait :* J'ai forcé le code à envoyer le mot "NULL" ou "DEFAULT" au lieu d'une case vide, pour que la base de données comprenne.
4. **Vérifier si les images existent :**
    * Le code essayait de lire la taille d'images qui n'existaient pas sur le serveur. Ça faisait tout bloquer.
    * *Ce que j'ai fait :* J'ai rajouté une vérification toute simple : "Est-ce que le fichier image existe ?" avant d'essayer de le lire.

---

## 3. Ce qui a changé sur ISfinder (le site public)

### Le but
Garder le même site visuellement, mais le rendre plus sûr et plus pratique pour les chercheurs qui soumettent des séquences.

### Le nouveau Captcha
* **Le problème :** Des robots spammaient les formulaires de contact et de soumission.
* **La solution :** J'ai ajouté une sécurité Captcha (les lettres un peu tordues à recopier).
* **Ce qui est cool :** Avant, si on se trompait sur ce genre de sécurité, on perdait tout ce qu'on avait tapé. Pour des chercheurs qui passent 20 minutes à remplir un formulaire, c'est très frustrant. Donc, j'ai fait en sorte que si on se trompe, les informations sont gardées en mémoire. La page se recharge et remplit le formulaire toute seule, comme par magie.

### La nouvelle page "Links"
* J'ai transformé la page des liens. Avant c'était une longue liste de textes un peu triste. Maintenant, c'est un carrousel avec des cartes qui défilent de gauche à droite. 
* J'ai aussi mis les bases de données importantes (les Transposons) tout en haut pour qu'on les trouve plus vite.

### Un coup de propre visuel
* J'ai centré certains titres et rendu les boutons plus cohérents sur les pages de recherche et de soumission. C'est plus propre, tout en gardant l'esprit du site.

---

## 4. Ce qui a changé sur ISadmin (le site d'administration)

### Le but
Vous faire gagner du temps quand vous validez ou triez les séquences soumises.

### Les boutons directs (Supprimer et Renvoyer)
* Quand vous regardez la liste des séquences sur ISfinder, j'ai ajouté deux petits boutons au bout de chaque ligne.
    * Un bouton "Poubelle" pour supprimer.
    * Un bouton "Retour" pour renvoyer la séquence en mode brouillon (vers ISsubmit).
* **Sécurité :** Comme un clic accidentel est vite arrivé, quand on clique, une petite boîte apparaît pour demander "Êtes-vous sûr de vouloir supprimer cet élément ?". Elle affiche même le nom de la base de données pour être sûr de ne pas se tromper.

### Comment marche la suppression
Supprimer une séquence n'est pas simple. Il y a des informations liées un peu partout (les ORF, les bouts de séquences, etc.). J'ai fait une fonction qui va proprement supprimer tous ces petits bouts liés, mais qui fait très attention à ne pas supprimer les "Soumetteurs" (les personnes) ou les "Hôtes" (les bactéries), car ils peuvent être liés à d'autres séquences.

---

## 5. Exporter les données en fichier CSV

### Le but
Vous permettre de télécharger des listes de données très précises (format Excel/CSV), sans avoir besoin de demander à un développeur de taper des commandes compliquées.

### Comment ça marche pour vous (le formulaire)
* Vous choisissez une table dans un menu déroulant.
* Les colonnes de cette table apparaissent automatiquement dans un deuxième menu.
* Vous choisissez si vous voulez que ça soit "égal", "plus grand", "ressemble à", et vous tapez votre mot.
* Le système construit la phrase compliquée (la requête) tout seul. Il comprend s'il doit mettre des guillemets autour des mots ou non.
* Vous pouvez même voir la phrase finale et la modifier si vous vous y connaissez un peu.

### Comment ça marche derrière (la sécurité)
Laisser quelqu'un écrire une commande directement vers la base de données, c'est dangereux.
* Le code va vérifier que vous ne demandez qu'à "Lire" (SELECT) et jamais à "Effacer" ou "Modifier" (DROP, DELETE).
* Il y avait aussi un vieux bug dans le code existant qui bloquait les requêtes sur certaines tables (comme `et_insertion_site`) juste parce qu'elles contenaient le mot "insert". J'ai contourné ce vieux bug en créant un accès tout neuf et sécurisé rien que pour ce formulaire.
* Si on fait une erreur en tapant, le site affiche un message d'erreur clair et nous ramène au formulaire avec notre texte intact, pour ne pas tout recommencer.

---

## 6. Gérer les listes déroulantes (les tables de références)

### Le but
Sur le site, il y a plein de listes déroulantes (les familles, les groupes, les chimies). Jusqu'ici, pour rajouter ou enlever un choix, il fallait aller directement dans la base de données. J'ai créé un écran pour que vous puissiez le faire directement depuis ISadmin.

### Ajouter un choix
* C'est très simple : on choisit la liste à modifier, on tape le nouveau nom, et on valide.
* En fond, le site vérifie tout seul que ce nom n'existe pas déjà pour éviter les doublons.

### Supprimer un choix (Gérer les clés étrangères)
* Le problème : Si on supprime la famille "IS3", que se passe-t-il pour les 4000 fiches qui disaient appartenir à la famille "IS3" ? Ça risque de casser le site. C'est ce qu'on appelle un problème de clés étrangères (les liens de dépendance entre les données).
* La solution : J'ai mis en place un système d'avertissement intelligent, qui change selon la liste qu'on modifie :
    * **Blocage total :** Pour les "familles", on n'a pas le droit de supprimer si des fiches l'utilisent encore. Le site bloque le bouton et vous le dit.
    * **Avertissement :** Pour les "groupes", on a le droit de supprimer. Mais le site va vous prévenir : "Attention, si vous faites ça, 42 fiches vont perdre leur groupe. Voulez-vous continuer ?".
* Dès que l'on clique sur un choix, le site calcule tout ça très vite en arrière-plan et affiche la couleur (rouge ou orange). C'est très sécurisé.

---

## 7. Trouver les fiches incomplètes

### Le but
Parfois, des fiches sont enregistrées mais il manque des choses importantes (comme les ORF). Il fallait un moyen de les repérer vite.

### Ce que j'ai fait
* J'ai ajouté une page "Liste Incomplète" dans le menu.
* Cette page croise les informations pour trouver toutes les fiches qui n'ont pas les liens nécessaires.
* J'ai rendu le tableau très simple à lire. Et surtout, on peut cliquer directement sur la ligne pour aller corriger la fiche en question.

---

## 8. Comment j'ai travaillé et le déploiement

### Ma méthode
* J'ai utilisé Git pour garder un historique de tout ce que j'ai fait. J'ai avancé petit à petit. Chaque modification était classée (soit c'est une nouveauté, soit c'est une correction, soit c'est visuel).
* J'ai gardé l'organisation existante des dossiers. Les écrans sont à la racine, et les calculs sont dans le dossier `scripts/` ou `includes/`.
* J'ai passé beaucoup de temps à regarder les journaux d'erreurs du serveur. C'est le meilleur moyen de voir ce qui cloche avec le nouveau PHP.

---

## 9. Pour ceux qui reprendront le code (Tests à faire)

Si quelqu'un modifie le code plus tard, voici ce qu'il devra absolument tester pour ne pas créer de problèmes :

1. **Vérifier les liens de données :** 
    * Si on ajoute ou supprime des choses, il faut toujours aller regarder dans la base (phpMyAdmin) si on n'a pas accidentellement effacé un "soumetteur" ou une "bactérie hôte" qui servait ailleurs.
2. **Tester les formulaires si on se trompe :** 
    * Il faut faire exprès de remplir un formulaire de soumission avec un mauvais Captcha. Le but est de vérifier que tout le texte saisi, et surtout les blocs dynamiques ajoutés, réapparaissent bien à l'écran sans rien perdre.
3. **Vérifier les boutons Renvoyer/Valider :**
    * Il faut renvoyer une fiche en mode brouillon et vérifier dans la base de données que le numéro de la base est bien repassé à 1 (ISsubmit).

---

## 10. Les limites actuelles

* **C'est toujours l'ancien code :** Le code est toujours écrit "à l'ancienne". L'affichage et les calculs sont mélangés, ce qui rend le tout un peu difficile à relire pour un développeur habitué aux sites récents.
* **Les connexions (Sessions) :** Le système de connexion est un peu vieux. Parfois, il peut y avoir de petites déconnexions aléatoires qui affichent des alertes bizarres si le serveur est trop strict.
* **Le générateur CSV a ses limites :** Pour des raisons de sécurité, je n'autorise pas les commandes SQL trop farfelues dans l'exportation CSV. Donc, pour des statistiques ultra poussées, l'administrateur sera un peu limité par l'outil.

---

## 11. Ce qui pourrait être fait dans le futur

1. **Passer à PDO :** C'est un moyen beaucoup plus moderne de parler à la base de données. Ça éviterait beaucoup de petits bidouillages de sécurité dans le code.
2. **Laisser la base de données gérer les erreurs :** Actuellement, c'est le site qui vérifie si on a le droit de supprimer une famille (les avertissements). L'idéal serait de configurer la base de données pour qu'elle interdise ça toute seule. Ça s'appelle les clés étrangères.
3. **Garder un historique des actions :** On ne sait pas qui valide ou qui supprime une fiche. Ce serait bien d'ajouter une table "Historique" pour tracer qui a fait quoi et à quelle heure.

---

## 12. Conclusion

Pour résumer, l'objectif critique de ce stage est réussi : les sites ISfinder et ISadmin fonctionnent désormais très bien sur les nouveaux serveurs PHP 8.4 et 8.5. Au lieu de tout casser et de recommencer (ce qui aurait été trop risqué pour des sites en production), on a posé des pansements là où il fallait, et on a corrigé les erreurs.

En plus de ça, de nouveaux outils pratiques ont été créés pour vous. L'exportation de données et la gestion des listes déroulantes devraient vous rendre autonomes pour beaucoup de petites tâches quotidiennes. Le système est plus sûr, plus malin avec les erreurs, et prêt à tenir quelques années de plus sans soucis.
