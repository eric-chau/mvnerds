<?php

namespace MVNerds\CoreBundle\Model;

use MVNerds\CoreBundle\Model\om\BaseFeed;

class Feed extends BaseFeed
{
	/**
	 * 
	 * @return type
	 */
	public function getSuperTags()
	{
		$superTags = array();
		foreach ($this->getFeedSuperTags() as $feedSuperTag) {
			$superTags[] = $feedSuperTag->getSuperTag();
		}
		
		return $superTags;
	}
}
