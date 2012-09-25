
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