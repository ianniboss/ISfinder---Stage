// JavaScript Document
// Fonction qui affiche une div particulière (parmi un ensemble de div)
// en fonction du choix dans un select
function Affiche_div(id_ensemble_div,id_select)
{
console.log(id_ensemble_div);
console.log(id_select);
        // Sélection du bloc contenant les sélections liées (id = "functionORF_" avec l'indice de l'orf concerné dans notre exemple)
        var id_ensemble_div = document.getElementById(id_ensemble_div);
console.log(id_ensemble_div);

        // Sélection de la sélection liée
        var id_select = document.getElementById(id_select);
console.log(id_select);
   
        if(id_ensemble_div)
                        {
                                //Initialisation d'une variable pour contenir un tableau.
                                var tab = new Array();
                   
                                // Cherche les balises div inlues dans le bloc contenant les sélections liées  et les retourne dans un tableau
                                tab = id_ensemble_div.getElementsByTagName('div');

                                var tablength = tab.length;
								
//          console.log('tablength: '+tablength);
 
                                // Liste les éléments du tableau
                                for (i=0; i < tablength; i++)	  // Effacer ancienne selection si changement
                                        {
								// Met les div en disable = true et les cache avec style.display = 'none'
                                            tab[i].disabled = true;
                                            tab[i].style.display = 'none';
//			console.log(tab[i].id);
                                        }
                                                            
                                // Met la sélection liée sélectionné en disable = false et l'affiche avec style.display = 'inline'
                                if(id_select)
                                        {
                                            id_select.disabled = false;
                                            id_select.style.display = 'inline';
                                        }
                        }
}
// ___________________________________________________________________________________
// Affichage d'une liste de nombre de deb à fin avec un nombre de sélectionné
function liste_nombre(deb,fin,nb_orf){
		for (var i=deb; i<fin; i++) {
			document.write('<option value="'+i+'"');
			if (i== nb_orf) {document.write(' selected="selected"');}
			document.write('> '+i+' </option>');
			}
}

// ___________________________________________________________________________________
// Rechargement de la page (meme url sans la partie ? .....) avec transmission de 2 parametres (raz = 1 on efface les variables de session)
function loadPage(page_form,raz) {

self.location.href=page_form+"?raz="+raz;

}
// ___________________________________________________________________________________
//  Efface tous les elements du formulaire
function effacer(formulaire){
for (var i=0; i<formulaire.length; i++){
if (formulaire.elements[i].type=="radio" || formulaire.elements[i].type=="checkbox") {formulaire.elements[i].checked=false;}
else if (formulaire.elements[i].type=="select-one") {formulaire.elements[i].options[0].selected=true;}
else if (!(formulaire.elements[i].type=='reset' || formulaire.elements[i].type=='submit' || formulaire.elements[i].type=='button')) {formulaire.elements[i].value="";}
}
}