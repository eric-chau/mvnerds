<?php

namespace MVNerds\CoreBundle\Model;

use MVNerds\CoreBundle\Model\om\BaseItemI18n;


/**
 * Skeleton subclass for representing a row from the 'item_i18n' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.src.MVNerds.CoreBundle.Model
 */
class ItemI18n extends BaseItemI18n {

	protected $itemSlug;
	
	public function setName($name) 
	{
		parent::setName($name);
		
		if ('fr' == $this->getLang()) {
			$slug = \MVNerds\CoreBundle\Utils\MVNerdsSluggify::mvnerdsSluggify($this->getName());
			if (( $item = $this->getItem())) {
				$item->setSlug($slug);
			} else {
				$this->itemSlug = $slug;
			}
			
		}

		  return $this;
	}
	
	public function setItem(Item $v = null)
	{
		if ($this->itemSlug != null) {
			$v->setSlug($this->itemSlug);
		}
		
		parent::setItem($v);
	}
} // ItemI18n
