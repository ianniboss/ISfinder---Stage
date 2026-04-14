// JavaScript Document
function Affiche_liste(id_ensemble_div,id_select)
{
        // Sélection du bloc contenant les sélections liées (id = "categorie" dans notre exemple)
        var id_ensemble_div = document.getElementById(id_ensemble_div);
//	   console.log(id_ensemble_div);
        // Sélection de la sélection liée
//        var id_select = id_ensemble_div.getElementById(id_select);
        var id_select = document.getElementById(id_select);   
   
        if(id_ensemble_div)
                        {

                                //Initialisation d'une variable pour contenir un tableau.
                                var tab = new Array();
                   
                                // Cherche les balises div inlues dans le bloc contenant les sélections liées  et les retourne dans un tableau
                                tab = id_ensemble_div.getElementsByTagName('div');

                                var tablength = tab.length;
 //         console.log('tablength: '+tablength);
 
                                // Liste les éléments du tableau
                                for (i=0; i < tablength; i++)        
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

