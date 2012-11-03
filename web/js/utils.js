
/**
 * Fonction qui permet permet de dire si la chaîne de caractère `email` suit un pattern valide d'email ou non
 * 
 * @param string email chaîne de caractère à vérifier
 * @return boolean retourne true si l'email est valide, false sinon
 */
function validateEmail(email) {
    var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
    if( !emailReg.test( email ) ) {
        return false;
    } else {
        return true;
    }
}

/**
 * Fonction qui permet de sauvegarder une clé (key) et sa valeur (value) dans le localStorage d'HTML5
 *
 * @param string key chaîne de caractère correspondant à la clé
 * @param string key value de caractère correspondant à la valeur
 * @return boolean retourne false lorsque le navigateur de l'utilisateur ne permet pas l'utilisateur du localStorage
 */
function saveItemInLS(key, value)
{
	if (!localStorage) {
		return false;
	}

	localStorage[key] = value;
}

/**
 * Fonction qui permet de récupérer une valeur à partir de sa clé (key) dans le localStorage d'HTML5
 *
 * @param string key chaîne de caractère correspondant à la clé
 * @return boolean retourne false lorsque le navigateur de l'utilisateur ne permet pas l'utilisateur du localStorage
 * @return string correspondant à la valeur associée à la clé dans le localStorage
 */
function getItemFromLS(key)
{
	if (!localStorage) {
		return false;
	}

	return localStorage[key];
}

var gridColumnWidth = 67,
	gridGutterWidth = 20;

/************** ISOTOPE ****************/
var isotopeOptions = {
	itemSelector: '.champion',
	animationEngine: 'jquery',
	masonry: {
		columnWidth: 124
	},
	animationOptions: {
		duration: 400,
		queue: false,
		opacity: 1
	},
	filter: ''
};
function getIsotopeOptions(){
	return isotopeOptions;
}
/************** /ISOTOPE ****************/

//Permet d afficher data sur la page
function displayAjaxData(data){
	$('div.presentation-container').append(data);
}

//Permet d'afficher un chargement sur le curseur de la souris lors du survol de target (target = * par defaut)
//On peut également spécifier le type de curseur pour la remise a zero de la cible
function toggleProgressCursor(toggle, target, cursor){
	target = typeof target !== 'undefined' ? target : '*';
	cursor = typeof cursor !== 'undefined' ? cursor : 'auto';
	
	toggle ? $(target).css('cursor', 'progress') : $(target).css('cursor', cursor);
}


//Permet d activer ou de désactiver des boutons sous forme de li
//Utilisées pour les boutons de clean et d ajout de filtre
function activateButton($buttonLi) {
	$buttonLi.removeClass('disabled hide');
	$buttonLi.find('a').removeClass('disabled');
}

function deactivateButton($buttonLi) {
	$buttonLi.addClass('disabled hide');
	$buttonLi.find('a').addClass('disabled');
}