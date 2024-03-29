<?php

namespace MVNerds\CoreBundle\Preference;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

use MVNerds\CoreBundle\Model\PreferencePeer;
use MVNerds\CoreBundle\Model\PreferenceQuery;
use MVNerds\CoreBundle\Model\User;
use MVNerds\CoreBundle\Model\UserPreference;
use MVNerds\CoreBundle\Model\UserPreferenceQuery;
use MVNerds\CoreBundle\Model\UserPreferencePeer;

class PreferenceManager
{
	public function findByUniqueName($uniqueName)
	{
		$preference = PreferenceQuery::create()
			->add(PreferencePeer::UNIQUE_NAME, $uniqueName)
		->findOne();
		
		if (null == $preference) {
			throw new InvalidArgumentException('Cannot found preference with unique name `'. $uniqueName .'`.');
		}
		
		return $preference;
	}
	
	public function findUserPreferenceByUniqueNameAndUserId($uniqueName, $userId)
	{
		$userPreference = UserPreferenceQuery::create()
				->joinWith('Preference')
				->add(UserPreferencePeer::USER_ID, $userId)
				->add(\MVNerds\CoreBundle\Model\PreferencePeer::UNIQUE_NAME, $uniqueName)
		->findOne();
		
		if (null === $userPreference)
		{
			throw new InvalidArgumentException('No user preference with user id:' . $userId . ' and preference with unique name '.$uniqueName.' !');
		}

		return $userPreference;
	}
	
	public function saveUserPreference(User $user, $preferenceUniqueName, $preferenceValue)
	{
		$userPreference = null;
		$preference = $this->findByUniqueName($preferenceUniqueName);
		try {
			$userPreference = $this->findUserPreferenceByUniqueNameAndUserId($preferenceUniqueName, $user->getId());
		}
		catch(InvalidArgumentException $e) {
			$userPreference = new UserPreference();
			$userPreference->setUser($user);
			$userPreference->setPreference($preference);
			
		}
		
		$userPreference->setValue($preferenceValue);
		
		// Finally
		$userPreference->save();
	}
}
