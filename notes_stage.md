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