<?php

namespace MVNerds\CoreBundle\Model;

use MVNerds\CoreBundle\Model\om\BaseUser;


/**
 * Skeleton subclass for representing a row from the 'user' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.src.MVNerds.CoreBundle.Model
 */
class User extends BaseUser 
{
	/**
	 * Permet de vérifier si l'utilisateur courant possède une adresse mail unique ou non
	 * 
	 * @return boolean renvoie true si l'email est déjà utilisé, false sinon; renvoie également false
	 * si l'utilisateur a entré la même adresse mail que celui qu'il a actuellement
	 */
	public function isEmailAlreadyInUse()
	{
		return UserPeer::isEmailAlreadyInUse($this->getEmail(), ($this->getId() == null? null : $this));
	}
	
	/**
	 * Permet de vérifier si l'utilisateur courant possède une adresse mail avec un domaine valide ou non
	 * 
	 * @return boolean renvoie true si le domaine de l'email est valide, false sinon
	 */
	public function isValidDomain()
	{
		$domain = explode('@', $this->getEmail()); 
		if (checkdnsrr($domain[1])) {
			return false;
		}
		
		return true;
	}
} // User
