<?php

namespace MVNerds\CoreBundle\Model;

use MVNerds\CoreBundle\Model\om\BaseProfile;


/**
 * Skeleton subclass for representing a row from the 'profile' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.src.MVNerds.CoreBundle.Model
 */
class Profile extends BaseProfile {
	
	public function getAvatarName()
	{
		return $this->getAvatar()->getName();
	}
} // Profile
