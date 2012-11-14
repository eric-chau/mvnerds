<?php

namespace MVNerds\CoreBundle\Role;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

use MVNerds\CoreBundle\Model\Role;
use MVNerds\CoreBundle\Model\RolePeer;
use MVNerds\CoreBundle\Model\RoleQuery;
use MVNerds\CoreBundle\Model\User;
use MVNerds\CoreBundle\Model\UserRole;
use MVNerds\CoreBundle\Model\UserRolePeer;
use MVNerds\CoreBundle\Model\UserRoleQuery;

class RoleManager 
{
	public function findById($id)
	{
		$role = RoleQuery::create()
			->add(RolePeer::ID, $id)
		->findOne();
		
		if (null == $role) {
			throw new InvalidArgumentException('No role with id `'. $id .'`.');
		}
		
		return $role;
	}	
	
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
	
	public function findAll()
	{
		return RoleQuery::create()->find();
	}
	
	public function deleteById($id)
	{
		$role = $this->findById($id);
		
		// Finally
		$role->delete();
	}
	
	public function save(Role $role)
	{
		$role->save();
	}
	
	public function getUsersByRole(Role $role)
	{
		$users = array();
		$userRoles = UserRoleQuery::create()
			->joinWith('User')
			->add(UserRolePeer::ROLE_ID, $role->getId())
		->find();
		
		if (0 >= count($userRoles)) {
			return $users;
		}
		
		foreach ($userRoles as $userRole) {
			$users[] = $userRole->getUser();
		}
		
		return $users;
	}
	
	public function assignRoleToUser(User $user, $roleId)
	{
		$role = $this->findById($roleId);
		
		$userRole = UserRoleQuery::create()
			->add(UserRolePeer::ROLE_ID, $role->getId())
			->add(UserRolePeer::USER_ID, $user->getId())
		->findOne();
		
		if (null != $userRole) {
			return false;
		}
		
		$userRole = new UserRole();
		$userRole->setUser($user);
		$userRole->setRole($role);
		
		// Finally
		$userRole->save();
		
		return true;
	}
	
	public function removeRoleToUser(User $user, $roleId)
	{
		$role = $this->findById($roleId);
		
		$userRole = UserRoleQuery::create()
			->add(UserRolePeer::ROLE_ID, $role->getId())
			->add(UserRolePeer::USER_ID, $user->getId())
		->findOne();
		
		if (null == $userRole) {
			return false;
		}
		
		//Finally
		$userRole->delete();
		
		return true;
	}
}
