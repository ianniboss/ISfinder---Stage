// Rechargement de la page (meme url sans la partie ? .....) avec transmission de 3 parametres
function reLoadPage3Param(page_form,lettre,type,host) {
	var type ;
	var lettre ;
// console.log(type);
self.location.href=page_form+"?lettre="+lettre+"&type="+type+"&host="+host;

}