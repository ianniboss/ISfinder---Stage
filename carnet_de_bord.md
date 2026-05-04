# Carnet de Bord - Stage ISfinder

## Informations de stage
- **Tuteur de stage**: Patricia Siguier
- **Objectif**: Analyse, maintenance et évolution de la plateforme ISfinder.

---

## Compétences acquises ou renforcées
- Compréhension de l'architecture d'une application web scientifique (ISfinder).
- Analyse de bases de données relationnelles (MySQL/MariaDB).
- Lecture et compréhension de code PHP existant (versions 8.0 à 8.2).
- Compréhension des flux de données entre plusieurs bases (ISfinder / ISsubmit).
- **Importance de la documentation du code** pour assurer la maintenabilité et faciliter la rédaction du rapport de stage.
- Approche méthodique d’analyse avant développement.

---

## Journal de bord

### Jour 1 (13 Avril 2026)
- Accueil au laboratoire CBI et réalisation des formalités administratives avec le service RH.
- Installation du poste de travail avec le service informatique (création de compte, configuration des accès).
- Participation à la formation obligatoire "nouveaux entrants" (sécurité, règles du laboratoire, sensibilisation aux questions d’égalité et VSS).
- Découverte de l’environnement de travail et des outils utilisés au sein du CNRS.

---

### Jour 2 (14 Avril 2026)
- Prise en main du projet ISfinder et exploration des fichiers fournis (Web-ISFinder_PHP8.2, ISadmin, fichiers SQL).
- Analyse de l’organisation du code PHP et identification des différents modules (site public, interface d’administration, bases de données).
- Lecture des scripts principaux afin de comprendre les connexions aux bases de données et les flux de données.
- Comparaison des différentes versions du projet (PHP 8.0 / PHP 8.2) pour identifier les évolutions liées à la migration.

---

### Jour 3 (15 Avril 2026)
- Échange avec la tutrice de stage pour clarifier les objectifs du stage et l’organisation du travail.
- Validation de la compréhension de l’architecture globale (ISfinder, ISsubmit, ISadmin).
- Analyse approfondie du flux de soumission des IS via le script `subIS.php`.
- Identification du rôle de la base de données temporaire `ISsubmit` dans le processus de validation.
- Compréhension du mécanisme de connexion aux bases via la fonction `connexion($base)` dans `include/function.inc.php`.
- Analyse des schémas de bases de données ISfinder et ISsubmit et identification des tables principales (`element_transposable`, `submission`, `submitters`).
- Mise en place d’un système de documentation personnelle pour le suivi du stage et la préparation du rapport final.


### Jour 4 (16 Avril 2026)
- Approfondissement de l’analyse du code source du projet ISfinder, en particulier dans le répertoire "scripts", afin de mieux comprendre le flux de données côté client (JavaScript) et côté serveur (PHP).
- Identification des scripts liés aux soumissions (subIS.php, submission.php) et aux requêtes (search-db.php, recherche.php) pour suivre le cheminement des données dans l’application.
- Échanges avec la tutrice de stage (Mme Siguier) ainsi qu’avec M. Pierre et Théo (équipe informatique) concernant l’accès aux serveurs de développement et aux bases de données.
- Mise en place de l’environnement de travail :
    -  accès au serveur interne (SSH)
    - installation de WinSCP pour le transfert de fichiers
    - accès aux logs et aux ressources nécessaires au développement
- Première utilisation de Tchap pour les échanges au sein de l’équipe ISfinder.
- Discussion avec la tutrice sur la suite du stage, avec une présentation prévue de l’architecture et du flux du site afin de faciliter la compréhension globale.

### Jour 5 (17 Avril 2026)
- Échange avec la tutrice de stage pour faire un point sur l’avancement du travail et validation des premières analyses réalisées.
- Organisation d’un point prévu en début de semaine suivante.
- Proposition d’un échange avec la développeuse du projet afin d’approfondir la compréhension du code et du flux de données.
- petit continuation sur le flux de donnes apres la submission
- Définition des premiers objectifs techniques du stage : migration des scripts d’affichage du site vers PHP 8.4 (hors traitement des formulaires).

### Jour 6 (20 Avril 2026)
- Réalisation de la formation obligatoire NEO (hygiène et sécurité) pour les nouveaux entrants au CBI.
- Échange avec Théo pour la mise en place des accès au serveur de développement, notamment l’accès aux fichiers racine nécessaires pour le projet.
- Prise en main de l’environnement de travail côté serveur :
    - utilisation de WinSCP pour le transfert de fichiers
    - compréhension de l’organisation des répertoires sur le serveur
- Préparation à la phase de développement (mise en place des outils nécessaires pour modifier et déployer les fichiers du projet ISfinder).

### Jour 7 (21 Avril 2026)
- Validation de la méthode de travail avec la tutrice (Mme Siguier), confirmant l'approche consistant à modifier les fichiers PHP existants directement sur le serveur de développement.
- Obtention des autorisations nécessaires de la part de la développeuse pour modifier les fichiers du projet.
- Début de la phase de migration des scripts d'affichage du site vers PHP 8.4, en se concentrant sur la correction des erreurs de compatibilité identifiées.

### Jour 8 (22 Avril 2026)
- Continuation de la migration des scripts d'affichage du site vers PHP 8.4, en se concentrant sur la correction des erreurs de compatibilité identifiées.

### Jour 9 (23 Avril 2026)
- Finalisation de la migration des pages d'affichage principales vers PHP 8.4 (recherche, blast, nomenclature).
- Revue de code pour s'assurer du maintien de l'intégrité du frontend (IDs, classes CSS, structure HTML).
- Synchronisation du projet avec le dépôt Git et mise à jour de la documentation technique.

### Jour 10 (24 Avril 2026)
- **Réunion majeure avec Patricia et Jocelyne** concernant l’architecture du système ISfinder.
- **Clarification du flux** : Distinction nette entre ISfinder (consultation), ISsubmit (soumission) et ISadmin (validation).
- **Analyse de l'infrastructure** : Compréhension de la duplication MariaDB entre serveurs internes et externes.
- **Identification d'un risque de sécurité/architecture** : Les formulaires externes écrivent actuellement directement dans la base interne (Astune).
- **Pivot de l'objectif du stage** : 
    - Création d'une base `ISsubmit` sur le serveur externe pour isoler les écritures.
    - Modification future des scripts de soumission pour pointer vers cette base.
    - Nécessité de mettre à jour le système de Captcha (incompatible PHP 8).

### Jour 11 (27 Avril 2026)
- **Objectif du jour** : Configuration des accès phpMyAdmin sur les deux serveurs (interne et externe).
- Préparation de l'environnement de test pour valider les modifications de scripts et les transferts de données entre bases.
- Étude des prérequis pour la création de la nouvelle base de données externe.

### Jour 12 (28 Avril 2026)
- **Objectif du jour** : Proposition d'améliorations esthétiques et fonctionnelles pour la page `links.php`.
- Création d'un wireframe (maquette) pour la nouvelle structure de la page des liens favoris.
- Modernisation de la présentation sous forme de cartes catégorisées (Sequencing Centers, Databases, Institutions, Tools).
- Synchronisation de la maquette avec le dépôt Git (`links_wireframe.png`).

### Jour 13 (29 Avril 2026)
- **Migration vers PHP 8.5** : Suite à une discussion avec Patricia et Pierre, passage de l'objectif de migration de PHP 8.4 à PHP 8.5 pour assurer une meilleure longévité du projet.
- **Mise à jour des scripts d'affichage** : 
    - Migration des 13 scripts d'affichage principaux vers les standards PHP 8.5.
    - Standardisation des inclusions : utilisation de `include_once` pour les fichiers `.php` et `include` pour les fragments `.html`.
    - Correction de la mise en forme et de la cohérence du code sur l'ensemble des pages migrées.
- **Vérification** : Réalisation de tests de syntaxe et de cohérence sur les fichiers modifiés.

### Jour 14 (30 Avril 2026)
- **Migration des formulaires de soumission** : Migration complète des workflows `request_name_form.php` et `feedback.php` vers PHP 8.5.
- **Sécurisation et Antispam** :
    - Refonte du système de Captcha (`ptitcaptcha.php`) pour une compatibilité totale avec PHP 8 (passage en méthodes statiques).
    - Intégration du Captcha dans les deux formulaires avec validation côté serveur et persistance des données saisies.
- **Optimisation de l'UX/UI** :
    - Uniformisation du design des formulaires avec des sections encadrées et des titres orange (`fieldset` et `legend`).
    - Mise en place de messages d'erreur "natifs" via JavaScript (`setCustomValidity`) pour une intégration visuelle moderne et discrète.
    - Correction de bugs de session (gestion du `raz` pour éviter l'écrasement des erreurs et correction du bug d'inversion des noms).
- **Architecture Backend** : Mise à jour du script `request_name.php` pour pointer vers la base de données `ISsubmit` en utilisant des requêtes préparées (`mysqli`).

### Jour 15 (04 Mai 2026)
- **Objectif du jour (Lundi – Semaine 4)** :
    - Prendre en main le script principal de soumission (`subIS.php`).
    - Analyser le fonctionnement global du processus de soumission (formulaire → script → base de données).
    - Identifier les parties du script à adapter pour la compatibilité PHP 8.5 (requêtes SQL, gestion des types, etc.).
    - Vérifier la structure de la base de données liée à la soumission et les dépendances entre tables.

