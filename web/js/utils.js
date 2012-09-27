
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
