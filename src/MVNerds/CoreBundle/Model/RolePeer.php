<?php

namespace MVNerds\CoreBundle\Model;

use MVNerds\CoreBundle\Model\om\BaseRolePeer;


/**
 * Skeleton subclass for performing query and update operations on the 'role' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.src.MVNerds.CoreBundle.Model
 */
class RolePeer extends BaseRolePeer {
	public static function isUniqueNameAlreadyInUse($uniqueName, Role $role = null)
	{
		$isUniqueNameAlreadyInUse = false;
		// S'il existe, on récupère le rôle qui a le nom unique $uniqueName en BDD
		$r = RoleQuery::create()
			->add(RolePeer::UNIQUE_NAME, $uniqueName)
		->findOne();
		
		// On vérifie si le paramètre $role est fourni
		if (null != $role) 
		{
			// Ici $role != null, on vérifie si le uniqueName == $role->getUniqueName(), si oui on renvoi false
			// Sinon, cela dépend de la valeur de $r
			$isUniqueNameAlreadyInUse = ($r == null? false : ($r->getId() == $role->getId()? false : true)); 
		}
		else 
		{
			// Ici, $role == null
			// si $r != null, cela signifie que l'uniqueName est déjà utilisé, on renvoi alors true
			$isUniqueNameAlreadyInUse = null != $r;
		}
		
		return $isUniqueNameAlreadyInUse;
	}
} // RolePeer
