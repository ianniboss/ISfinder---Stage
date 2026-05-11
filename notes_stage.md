# Stage ISfinder - Notes de suivi

## Jour 3 : Exploration du flux de soumission

---

## 🔍 Observations

### Flux de soumission
Les données soumises via le formulaire ISfinder (géré par `subIS.php`) sont insérées dans la base de données `ISsubmit`, utilisée comme espace intermédiaire avant validation.

---

### Organisation de ISsubmit
Contrairement à une simple base temporaire, `ISsubmit` implémente une gestion d’état des soumissions :

- `ISSub` → Soumissions prêtes à être examinées  
- `ISWait` → En attente d’informations complémentaires  
- `ISTrash` → Soumissions rejetées  

---

### Cycle de vie des données

```text
Soumission → Révision → Correction → Validation / Rejet → Intégration dans ISfinder
```

L’administrateur (via ISadmin) fait évoluer les données entre ces états.

### Détails techniques

- `include/function.inc.php`  
  → contient `connexion($base)` pour choisir entre `ISfinder` et `ISsubmit`

- `ISadmin/liste.php`  
  → affiche les soumissions selon leur état (`ISSub`, `ISWait`, `ISTrash`)

## Interprétation

ISsubmit n’est pas une simple base temporaire.

C’est déjà un système de gestion de workflow avec plusieurs états.

Implication :
- la future base temporaire devra s’intégrer dans ce système  
- ou être placée en amont de ISsubmit  

## Questions à explorer

- Où se fait exactement la validation finale vers ISfinder ?
- Quels scripts effectuent le transfert ISsubmit → ISfinder ?
- La gestion des états est-elle centralisée ?
- Comment sont gérés les accès entre les bases ?

## Tâches

- [x] Comprendre le flux de soumission (ISfinder → ISsubmit)
- [x] Identifier les états des données (ISSub, ISWait, ISTrash)
- [x] Analyser le rôle de ISadmin
- [ ] Trouver le script de validation finale
- [ ] Identifier où intégrer la base temporaire

serveurs
acces a la bd

display pages remain :
  - [x] reserved_blocks.php 
  - [x] nomenclature.php
  - [x] search.php
  - [x] list_names_attributed.php
  - [x] under_construct.php
  - [x] erreur404.php
  - [x] blast.php

# rabu
display pages done 
conservé la structure HTML existante (IDs, labels, etc.) afin de garantir la compatibilité avec le CSS et les scripts existants. 
Certaines incohérences (IDs dupliqués, labels non liés) semblent déjà présentes dans le code initial.

questions :
1. l'architecture
Au niveau du code, comment sont organisées les interactions entre ISfinder, ISsubmit et ISadmin ? 
Est-ce que ce sont des bases séparées avec des scripts de transfert, ou bien un système centralisé ?
2. le flux de données 
Quels scripts gèrent concrètement le passage des données de ISsubmit vers ISfinder ? 
Est-ce que c’est déclenché automatiquement ou via ISadmin ?

3. les parties critiques du code 
Y a-t-il des fichiers ou des fonctions dans le code où il faut être particulièrement prudent, car ils sont critiques ou fortement couplés au reste du système ?

4. migration vers php 8.4
Dans ce projet, est-ce qu’il y a des parties du code qui risquent particulièrement de poser problème avec PHP 8.4 (fonctions dépréciées, comportements différents, etc.) ?

5. scripts externes
Les scripts comme blast.php font-ils appel à des commandes système (exec, shell, etc.) ? 
y a-t-il des dépendances serveur spécifiques à prendre en compte pour éviter de casser ces fonctionnalités ?

6. mails
Pour les envois d’emails, est-ce que le projet utilise mail() directement ou une bibliothèque spécifique ?

migration made on the following pages :
  - [x] index.php (works)
  - [x] about.php (works)
  - [x] credits.php (works)
  - [x] general_information.php (works)
  - [x] howto.php (works)
  - [x] links.php (works)
  - [x] list_names_attributed.php (works)
  - [x] nomenclature.php (works)
  - [x] reserved_blocks.php (works)
  - [x] search.php (works)
  - [x] blast.php (works)
  - [x] erreur404.php (works)
  - [x] under_construct.php (works)

# Selasa 28/04
## Suggestions d'améliorations pour links.php
### 🎨 Design & Expérience Utilisateur
- **Structure en Grilles** : Remplacer le tableau HTML par une grille flexible (CSS Grid/Flexbox).
- **Cartes Interactives** : Chaque lien est présenté dans une carte avec logo, titre et description.
- **Micro-animations** : Effets de survol (hover) sur les cartes pour une navigation plus dynamique.
- **Accessibilité** : Utilisation de balises sémantiques et amélioration du contraste.

### 📁 Fichiers associés
- `links_wireframe.png` : Maquette visuelle du nouveau design.

error to fix tomorrow :
[Mon May 11 17:16:18.501687 2026] [proxy_fcgi:error] [pid 1692306:tid 1692306] [client 192.168.120.6:63594] AH01071: Got error 'PHP message: PHP Warning:  Undefined array key "recoding_image_error" in /var/www/html/isadmin/isadmin/ficheIS.php on line 403', referer: http://192.168.12.150/isadmin/liste.php?list=1
[Mon May 11 17:16:18.502033 2026] [proxy_fcgi:error] [pid 1692306:tid 1692306] [client 192.168.120.6:63594] AH01071: Got error '; PHP message: PHP Warning:  Undefined array key "recoding_image_error" in /var/www/html/isadmin/isadmin/ficheIS.php on line 558', referer: http://192.168.12.150/isadmin/liste.php?list=1
[Mon May 11 17:16:18.502109 2026] [proxy_fcgi:error] [pid 1692306:tid 1692306] [client 192.168.120.6:63594] AH01071: Got error '; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 0 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 1 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 2 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 3 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 4 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 5 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 6 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 7 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 8 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 9 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 10 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 11 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 12 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 13 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 14 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 15 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 16 in Unknown on line 0; P', referer: http://192.168.12.150/isadmin/liste.php?list=1
[Mon May 11 17:16:18.502132 2026] [proxy_fcgi:error] [pid 1692306:tid 1692306] [client 192.168.120.6:63594] AH01071: Got error 'HP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 17 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 18 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 19 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 20 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 21 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 22 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 23 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 24 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 25 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 26 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 27 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 28 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 29 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 30 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 31 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 32 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 33 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 34 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 35 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 36 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 37 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 38 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 39 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 40 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 41 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 42 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 43 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 44 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 45 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 46 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 47 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 48 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 49 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 50 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 51 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 52 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 53 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 54 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 55 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 56 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 57 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 58 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 59 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 60 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 61 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 62 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 63 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 64 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 65 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 66 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 67 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 68 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 69 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 70 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 71 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 72 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 73 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 74 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 75 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 76 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 77 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 78 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 79 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 80 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 81 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 82 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 83 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 84 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 85 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 86 in Unknown on line 0', referer: http://192.168.12.150/isadmin/liste.php?list=1
[Mon May 11 17:16:51.467481 2026] [proxy_fcgi:error] [pid 1716881:tid 1716881] [client 192.168.120.6:50362] AH01071: Got error 'PHP message: PHP Warning:  Undefined array key 1 in /var/www/html/isadmin/isadmin/ficheIS.php on line 118; PHP message: PHP Warning:  foreach() argument must be of type array|object, null given in /var/www/html/isadmin/isadmin/ficheIS.php on line 118', referer: http://192.168.12.150/isadmin/liste.php?list=1
[Mon May 11 17:16:51.468276 2026] [proxy_fcgi:error] [pid 1716881:tid 1716881] [client 192.168.120.6:50362] AH01071: Got error '; PHP message: PHP Warning:  Undefined array key "recoding_image_error" in /var/www/html/isadmin/isadmin/ficheIS.php on line 403', referer: http://192.168.12.150/isadmin/liste.php?list=1
[Mon May 11 17:16:51.468620 2026] [proxy_fcgi:error] [pid 1716881:tid 1716881] [client 192.168.120.6:50362] AH01071: Got error '; PHP message: PHP Warning:  Undefined array key "recoding_image_error" in /var/www/html/isadmin/isadmin/ficheIS.php on line 558', referer: http://192.168.12.150/isadmin/liste.php?list=1
[Mon May 11 17:16:51.468736 2026] [proxy_fcgi:error] [pid 1716881:tid 1716881] [client 192.168.120.6:50362] AH01071: Got error '; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 0 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 1 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 2 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 3 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 4 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 5 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 6 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 7 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 8 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 9 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 10 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 11 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 12 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 13 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 14 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 15 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 16 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 17 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 18 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 19 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 20 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 21 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 22 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 23 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 24 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 25 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 26 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 27 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 28 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 29 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 30 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 31 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 32 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 33 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 34 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 35 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 36 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 37 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 38 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 39 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 40 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 41 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 42 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 43 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 44 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 45 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 46 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 47 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 48 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 49 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 50 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 51 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 52 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 53 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 54 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 55 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 56 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 57 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 58 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 59 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 60 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 61 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 62 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 63 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 64 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 65 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 66 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 67 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 68 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 69 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 70 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 71 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 72 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 73 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 74 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 75 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 76 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 77 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 78 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 79 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 80 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 81 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 82 in Unknown on line 0; PHP message: PHP Wa', referer: http://192.168.12.150/isadmin/liste.php?list=1
[Mon May 11 17:16:51.468812 2026] [proxy_fcgi:error] [pid 1716881:tid 1716881] [client 192.168.120.6:50362] AH01071: Got error 'rning:  PHP Request Shutdown: Skipping numeric key 83 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 84 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 85 in Unknown on line 0; PHP message: PHP Warning:  PHP Request Shutdown: Skipping numeric key 86 in Unknown on line 0', referer: http://192.168.12.150/isadmin/liste.php?list=1
[Mon May 11 17:17:10.609567 2026] [proxy_fcgi:error] [pid 1691664:tid 1691664] [client 192.168.120.6:54820] AH01071: Got error 'PHP message: PHP Fatal error:  Uncaught mysqli_sql_exception: Column 'ORF_function' cannot be null in /var/www/html/isadmin/isadmin/includes/function.inc.php:76\nStack trace:\n#0 /var/www/html/isadmin/isadmin/includes/function.inc.php(76): mysqli_query()\n#1 /var/www/html/isadmin/isadmin/includes/actions.inc.php(340): execute_sql()\n#2 /var/www/html/isadmin/isadmin/scripts/actions.php(49): ecrit_data()\n#3 {main}\n  thrown in /var/www/html/isadmin/isadmin/includes/function.inc.php on line 76', referer: http://192.168.12.150/isadmin/liste.php?list=1
 