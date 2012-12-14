<?php

namespace MVNerds\CoreBundle\Model;

use MVNerds\CoreBundle\Model\om\BaseItemBuild;
use MVNerds\CoreBundle\Comment\IComment;

/**
 * Skeleton subclass for representing a row from the 'item_build' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.src.MVNerds.CoreBundle.Model
 */
class ItemBuild extends BaseItemBuild implements IComment {

	public function getChampionsNamesToString()
	{
		$toString = '';
		foreach($this->getChampionItemBuilds() as $championItemBuild)
		{
			$toString .= $championItemBuild->getChampion()->getName();
		}
		return $toString;
	}
} // ItemBuild
