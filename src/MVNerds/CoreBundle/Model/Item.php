<?php

namespace MVNerds\CoreBundle\Model;

use MVNerds\CoreBundle\Model\om\BaseItem;


/**
 * Skeleton subclass for representing a row from the 'item' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.src.MVNerds.CoreBundle.Model
 */
class Item extends BaseItem {

	public function getTagsToString()
	{
		$tags = '';
		foreach ($this->getItemTags() as $itemTag) 
		{
			$tags .= strtolower($itemTag->getTag()->getSlug() . ' ');
		}
		
		return $tags;
	}
} // Item
