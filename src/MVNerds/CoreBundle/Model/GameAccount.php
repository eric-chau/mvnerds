<?php

namespace MVNerds\CoreBundle\Model;

use MVNerds\CoreBundle\Model\om\BaseGameAccount;


/**
 * Skeleton subclass for representing a row from the 'game_account' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.src.MVNerds.CoreBundle.Model
 */
class GameAccount extends BaseGameAccount {
	
	public function isActive()
	{
		return $this->getIsActive();
	}
	
	public function activate()
	{
		$this->setIsActive(true);
	}
	
	public function generateActivationCode()
	{
		$this->setActivationCode('MVNerds Code ' . substr(md5(uniqid(rand(), true)), 0, 12));
	}
} // GameAccount
