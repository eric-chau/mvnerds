<?php

namespace MVNerds\CoreBundle\Profile;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Criteria;

use MVNerds\CoreBundle\Model\User;
use MVNerds\CoreBundle\Model\UserQuery;
use MVNerds\CoreBundle\Model\UserPeer;
use MVNerds\CoreBundle\Model\AvatarPeer;
use MVNerds\CoreBundle\Model\AvatarQuery;

class ProfileManager
{
	public function findAvatarByName($name)
	{
		$avatar = AvatarQuery::create()
			->add(AvatarPeer::NAME, $name)
		->findOne();
		
		if (null == $avatar) {
			throw new InvalidArgumentException('No avatar for name:`'. $name .'`.');
		}
		
		return $avatar;
	}
	
	public function findAvatarByUserRoles(User $user)
	{
		$roleIds = array();
		foreach ($user->getUserRoles() as $userRole) {
			$roleIds[] = $userRole->getRoleId();
		}
		
		return $avatars = AvatarQuery::create()
			->add(AvatarPeer::ROLE_ID, $roleIds, \Criteria::IN)
		->find();
	}
	
	public function saveAvatarIfValid(User $user, $name)
	{
		// Check if $name exist as avatar
		$avatar = null;
		try {
			$avatar = $this->findAvatarByName($name);
		}
		catch (InvalidArgumentException $e) {
			return false;
		}
		
		$roleIds = array();
		foreach ($user->getUserRoles() as $userRole) {
			$roleIds[] = $userRole->getRoleId();
		}
		
		$allowedToUseThisAvatar = false;
		foreach ($roleIds as $roleId) {
			if ($avatar->getRoleId() == $roleId) {
				$allowedToUseThisAvatar = true;
				break;
			}
		}
		
		if (!$allowedToUseThisAvatar) {
			return false;
		}
		
		$profile = $user->getProfile();
		$profile->setAvatarId($avatar->getId());
		
		// Finally
		$profile->save();
		
		return true;
	}
	
	public function getGameAccountByUser(User $user)
	{
		$user = UserQuery::create()
			->joinWith('Profile')
			->joinWith('Profile.GameAccount', Criteria::LEFT_JOIN)
			->add(UserPeer::ID, $user->getId())
		->findOne();
		
		return $user->getProfile()->getGameAccount();
	}
}
