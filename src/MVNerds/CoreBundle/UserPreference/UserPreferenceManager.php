<?php

namespace MVNerds\CoreBundle\UserPreference;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

use MVNerds\CoreBundle\Model\UserPreferenceQuery;
use MVNerds\CoreBundle\Model\UserPreferencePeer;

class UserPreferenceManager
{
	public function findByUniqueNameAndUserId($uniqueName, $userId)
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
}
