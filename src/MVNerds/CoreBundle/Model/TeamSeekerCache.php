<?php

namespace MVNerds\CoreBundle\Model;

use MVNerds\CoreBundle\Model\om\BaseTeamSeekerCache;

class TeamSeekerCache extends BaseTeamSeekerCache
{
	public function setRoster($v)
	{
		if (!is_array($v)) {
			throw new \Exception();
		}
		
		parent::setRoster(json_encode($v));
	}
	
	public function getRoster()
	{
		return json_decode(parent::getRoster());
	}	
}
