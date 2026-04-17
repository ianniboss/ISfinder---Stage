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


### Jour 4 (16 AVril 2026)
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
