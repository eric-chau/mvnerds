<?php

namespace MVNerds\CoreBundle\Role;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

use MVNerds\CoreBundle\Model\Role;
use MVNerds\CoreBundle\Model\RolePeer;
use MVNerds\CoreBundle\Model\RoleQuery;
use MVNerds\CoreBundle\Model\User;
use MVNerds\CoreBundle\Model\UserRole;

class RoleManager 
{
	public function findByUniqueName($uniqueName)
	{
		$role = RoleQuery::create()
			->add(RolePeer::UNIQUE_NAME, $uniqueName)
		->findOne();
		
		if (null == $role) {
			throw new InvalidArgumentException('No role with unique name `'. $uniqueName .'`.');
		}
		
		return $role;
	}
	
	public function assignRoleToUser(User $user, Role $role)
	{
		$userRole = new UserRole();
		$userRole->setUser($user);
		$userRole->setRole($role);
		
		// Finally
		$userRole->save();
	}
}
