<?php

namespace MVNerds\CoreBundle\Model;

use MVNerds\CoreBundle\Model\om\BaseUserPeer;


/**
 * Skeleton subclass for performing query and update operations on the 'user' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.src.MVNerds.CoreBundle.Model
 */
class UserPeer extends BaseUserPeer 
{
	/**
	 * Permet de vérifier si oui ou non l'email passé en paramètre est déjà utilisé par un autre
	 * utilisateur;
	 * /!\ Si le paramètre $user est fourni et est différent de null, la méthode vérifie si 
	 * l'utilisateur a renseigné la même adresse email ou non; si c'est le cas, la méthode renvoie false
	 * 
	 * @param string $email l'email dont on veut vérifier l'existence
	 * @return boolean true si l'email est déjà utilisé par un autre utilisateur, false sinon
	 */
	public static function isEmailAlreadyInUse($email, User $user = null)
	{
		$isEmailAlreadyInUse = false;
		// S'il existe, on récupère l'utilisateur qui a l'adresse mail $email en BDD
		$u = UserQuery::create()
			->add(UserPeer::EMAIL, $email)
		->findOne();
		
		// On vérifie si le paramètre $user est fourni
		if (null != $user) 
		{
			// Ici $user != null, on vérifie si l'adresse $email == $user->getEmail(), si oui on renvoi false
			// Sinon, cela dépend de la valeur de $u
			$isEmailAlreadyInUse = ($u == null? false : ($u->getId() == $user->getId()? false : true)); 
		}
		else 
		{
			// Ici, $user == null
			// si $u != null, cela signifie que l'adresse email est déjà utilisé, on renvoi alors true
			$isEmailAlreadyInUse = null != $u;
		}
		
		return $isEmailAlreadyInUse;
	}
} // UserPeer
