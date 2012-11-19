<?php

namespace MVNerds\CoreBundle\Preference;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

use MVNerds\CoreBundle\Model\Preference;
use MVNerds\CoreBundle\Model\PreferenceQuery;
use MVNerds\CoreBundle\Model\PreferencePeer;
use MVNerds\CoreBundle\Model\PreferenceI18nPeer;

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
	
	public function findByUniqueNameAndUserId($uniqueName, $userId)
	{
		PreferenceQuery::create()
				->findOne();
	}
}
