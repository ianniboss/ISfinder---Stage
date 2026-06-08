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

### Command to check the error log : 

tail -f /var/log/apache2/error.log
tail -f /var/log/httpd/error_log

  
for line 118 in ficheIS.php:
print "<ul><li><span class='entete_propriete'>Family </span>".$family."</li>";
		if (strcasecmp($groupe, 'No group') !== 0) {
			print "<li><span class='entete_propriete'>Group </span>".$groupe."</li>";
		}
		print "</ul>";

question :
specifie le relation entre tables manuellement ou automatiquement
juste en mode lecture pour que vous puisse verifier le requete generees avant d'installer le csv
limite dans le colonnes/tables qu'on peut ajouter
