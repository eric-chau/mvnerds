<?php

namespace MVNerds\CoreBundle\Preference;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

use MVNerds\CoreBundle\Model\Preference;
use MVNerds\CoreBundle\Model\PreferenceQuery;
use MVNerds\CoreBundle\Model\PreferencePeer;
use MVNerds\CoreBundle\Model\PreferenceI18nPeer;

class PreferenceManager
{
	public function findByUniqueNameAndUserId($uniqueName, $userId)
	{
		PreferenceQuery::create()
				->findOne();
	}
}
