// JavaScript Document
// Fonction qui affiche une div particulière (parmi un ensemble de div)
// en fonction du choix dans un select
function Affiche_div(id_ensemble_div, id_select) {
    console.log(id_ensemble_div);
    console.log(id_select);
    // Sélection du bloc contenant les sélections liées (id = "functionORF_" avec l'indice de l'orf concerné dans notre exemple)
    var id_ensemble_div = document.getElementById(id_ensemble_div);
    console.log(id_ensemble_div);

    // Sélection de la sélection liée
    var id_select = document.getElementById(id_select);
    console.log(id_select);

    if (id_ensemble_div) {
        //Initialisation d'une variable pour contenir un tableau.
        var tab = new Array();

        // Cherche les balises div inlues dans le bloc contenant les sélections liées  et les retourne dans un tableau
        tab = id_ensemble_div.getElementsByTagName('div');

        var tablength = tab.length;

        //          console.log('tablength: '+tablength);

        // Liste les éléments du tableau
        for (i = 0; i < tablength; i++) { // Effacer ancienne selection si changement
            // Met les div en disable = true et les cache avec style.display = 'none'
            tab[i].disabled = true;
            tab[i].style.display = 'none';
            //			console.log(tab[i].id);
        }

        // Met la sélection liée sélectionné en disable = false et l'affiche avec style.display = 'inline'
        if (id_select) {
            id_select.disabled = false;
            id_select.style.display = 'inline';
        }
    }
}

// Affichage d'une liste de nombre de deb à fin avec un nombre de sélectionné
function liste_nombre(deb, fin, nb_orf) {
    for (var i = deb; i < fin; i++) {
        document.write('<option value="' + i + '"');
        if (i == nb_orf) {
            document.write(' selected="selected"');
        }
        document.write('> ' + i + ' </option>');
    }
}

// Rechargement de la page (meme url sans la partie ? .....) avec transmission de 2 parametres (raz = 1 on efface les variables de session)
function loadPage(page_form, val_session) {
    self.location.href = page_form + "?val_session=" + val_session;
}

//  Efface tous les elements du formulaire
function effacer(formulaire) {
    for (var i = 0; i < formulaire.length; i++) {
        if (formulaire.elements[i].type == "radio" || formulaire.elements[i].type == "checkbox") {
            formulaire.elements[i].checked = false;
        } else if (formulaire.elements[i].type == "select-one") {
            formulaire.elements[i].options[0].selected = true;
        } else if (!(formulaire.elements[i].type == 'reset' || formulaire.elements[i].type == 'submit' || formulaire.elements[i].type == 'button')) {
            formulaire.elements[i].value = "";
        }
    }
}

//  Confirme la soumission du formulaire pour modification d'une fiche
function Confirmer() {
    toto = confirm("Etes-vous bien sur de vouloir modifier ces renseignements ?");
    if (toto != "1") {
        return false;
    }
}

// fonction de validation de suppression
function Foncpop() {
    return confirm("Etes-vous bien sur de vouloir supprimer cet enregistrement\n Cette opération ne peut être annulée!");
}

// fonction de validation de changement de base et suppression
function validsuppr() {
    return confirm("Etes-vous bien sur de vouloir supprimer cette fiche dans ISsub?\n Cette opération ne peut être annulée!");
}

function validsub() {
    return confirm("Etes-vous bien sur de vouloir transférer cette fiche dans ISsub?\n Cette opération ne peut être annulée!");
}

function validwait() {
    return confirm("Etes-vous bien sur de vouloir mettre cette soumission dans la base private en attente de validation ultérieure?\n Cette opération ne peut être annulée!");
}

function validtrash() {
    return confirm("Etes-vous bien sur de vouloir mettre cette soumission dans la base poubelle en attente de modification ultérieure?\n Cette opération ne peut être annulée!");
}

function validIS() {
    return confirm("Etes-vous bien sur de vouloir valider cette soumission?\n Cette opération ne peut être annulée!\nLa fiche sera automatiquement publiée sur la base IS publique!");
}