# Documentation Technique - Maintenance ISfinder & ISadmin (PHP 8.5)

Ce document liste les modifications techniques appliquées pour assurer la compatibilité avec PHP 8.5 et détaille les nouveaux outils intégrés. L'objectif principal est le maintien fonctionnel de l'existant.

## 1. Mise à jour vers PHP 8.5

Corrections apportées pour lever les erreurs fatales introduites par le passage à PHP 8.5.

### Vérification des variables inconnues
Ajout de conditions pour vérifier l'existence des variables avant leur utilisation afin de prévenir les erreurs fatales.
* **Fichiers concernés :** `Web-ISFinder_PHP8.2/scripts/subIS.php`, `Web-ISFinder_PHP8.2/scripts/request_name.php`, `Web-ISFinder_PHP8.2/include/function.inc.php`
* **Statut :** Validé

### Nettoyage des sessions
Nettoyage des données avant stockage en mémoire pour respecter les formats d'identifiant stricts de PHP 8.
* **Fichiers concernés :** `Web-ISFinder_PHP8.2/request_name_form.php`, `Web-ISFinder_PHP8.2/feedback.php`, `Web-ISFinder_PHP8.2/scripts/subIS.php`
* **Statut :** Validé

### Insertion en base de données
Remplacement des valeurs vides par "NULL" ou "DEFAULT" lors de l'insertion pour les champs obligatoires, conformément aux nouvelles restrictions de la base de données.
* **Fichiers concernés :** `Web-ISFinder_PHP8.2/scripts/subIS.php`, `Web-ISFinder_PHP8.2/scripts/request_name.php`
* **Tables concernées :** `submission`, `submiters`, `element_transposable`
* **Statut :** Validé

### Vérification des images manquantes
Ajout d'un test d'existence du fichier image avant la lecture de ses dimensions pour éviter le plantage du serveur.
* **Fichiers concernés :** `isadmin-copy/isadmin/ficheIS.php`, `isadmin-copy/webadm/fonctions_fichiers.inc.php`
* **Statut :** Validé

## 2. Modifications sur ISfinder (Site public)

### Intégration du Captcha
Ajout d'un Captcha sur les formulaires pour la protection contre les requêtes automatisées. En cas d'erreur de validation, les données du formulaire sont conservées et rechargées automatiquement.
* **Fichiers concernés :** `Web-ISFinder_PHP8.2/scripts/ptitcaptcha.php`, `Web-ISFinder_PHP8.2/request_name_form.php`, `Web-ISFinder_PHP8.2/feedback.php`
* **Statut :** Validé

### Refonte de la page des liens
Transformation de l'affichage sous forme de carrousel de cartes. Mise en avant des bases de données clés en haut de page.
* **Fichiers concernés :** `Web-ISFinder_PHP8.2/links.php`, `Web-ISFinder_PHP8.2/styles/links.css`
* **Statut :** Validé

### Ajustements visuels
Centrage des titres et harmonisation de l'affichage des boutons.
* **Fichiers concernés :** `Web-ISFinder_PHP8.2/styles/submission.css`, `Web-ISFinder_PHP8.2/styles/styles_feedback.css`
* **Statut :** Validé

## 3. Modifications sur ISadmin (Site interne)

### Boutons d'action rapide (Séquences)
Ajout de boutons de suppression et de retour en brouillon sur la liste des séquences. Intégration d'une boîte de dialogue de confirmation affichant le nom de la base.
* **Fichiers concernés :** `isadmin-copy/isadmin/liste.php`, `isadmin-copy/isadmin/scripts/function.js`
* **Statut :** Validé

### Logique de suppression
La suppression d'une séquence entraîne la suppression des `orf` liés, mais conserve les enregistrements `submiters` et `host` pour maintenir l'intégrité des autres fiches existantes.
* **Fichiers concernés :** `isadmin-copy/isadmin/liste.php`, `isadmin-copy/isadmin/ficheIS.php`
* **Tables concernées :** `submission`, `orf` (Supprimés) / `submiters`, `host` (Conservés)
* **Statut :** Validé

## 4. Nouveaux outils

### Export CSV
Génération de requêtes SQL de type `SELECT` basées sur la sélection d'une table et de filtres. Contournement d'un bug bloquant les requêtes contenant le mot "insert". L'outil bloque toute tentative de modification de données.
* **Fichiers concernés :** `isadmin-copy/isadmin/export_csv.php`
* **Tables concernées :** Toutes les tables exportables (ex: `et_insertion_site`)
* **Statut :** Validé

### Gestion des listes déroulantes (Éléments de référence)
Interface d'ajout et de suppression d'éléments de référence (familles, groupes).
- Vérification de l'existence du nom lors de l'ajout.
- Blocage de la suppression d'une famille si elle est déjà référencée.
- Affichage d'une alerte avec le nombre de fiches impactées lors de la suppression d'un groupe.
* **Fichiers concernés :** `isadmin-copy/isadmin/add_reference.php`, `isadmin-copy/isadmin/delete_reference.php`
* **Tables concernées :** `family`, `groups`, `tnp_chemestry`, `type_element_transposable`, `ag_description`, `pg_function`, `nom_type`
* **Statut :** Validé

### Suivi des fiches incomplètes
Ajout d'une interface listant les fiches sauvegardées mais incomplètes (ex: absence d'ORF). Redirection directe vers l'édition de la fiche au clic.
* **Fichiers concernés :** `isadmin-copy/isadmin/liste_incomplete.php`
* **Tables concernées :** `submission`, `orf`
* **Statut :** Validé

## 5. Tests obligatoires (Pour les prochains développeurs)

Lors de futures modifications, les vérifications suivantes sont nécessaires :

* **L'intégrité de la base de données :** S'assurer de ne pas supprimer en cascade un hôte ou un soumetteur partagé avec d'autres entités.
* **Les erreurs de formulaire :** Simuler une erreur de validation (ex: Captcha invalide) et s'assurer que les champs (notamment les champs dynamiques ajoutés) conservent leurs valeurs.
* **Le changement de statut :** Lors du renvoi d'une fiche en brouillon, vérifier dans la base de données que son statut repasse à 1 (`ISsubmit`).

## 6. Limites actuelles et points d'attention

* **Architecture existante :** La logique PHP et l'affichage HTML sont intriqués dans les mêmes fichiers.
* **Gestion des sessions :** Le système de session actuel peut s'avérer sensible selon la configuration du serveur (risques de déconnexion).
* **Intégrité référentielle :** L'intégrité des contraintes (ex: interdiction de supprimer une famille utilisée) est actuellement gérée de manière applicative (code PHP) plutôt que par des clés étrangères (`FOREIGN KEY`) dans la base de données.
