<?php

namespace MVNerds\CoreBundle\Model;

use MVNerds\CoreBundle\Model\om\BaseSkinI18n;


/**
 * Skeleton subclass for representing a row from the 'skin_i18n' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.src.MVNerds.CoreBundle.Model
 */
class SkinI18n extends BaseSkinI18n {

	protected $skinSlug;
	
	public function setName($name) 
	{
		parent::setName($name);
		
		if ('fr' == $this->getLang() || $this->getSkin() == null || $this->getSkin()->getSlug() == null || $this->getSkin()->getSlug() == '') {
			$slug = \MVNerds\CoreBundle\Utils\MVNerdsSluggify::mvnerdsSluggify($this->getName());
			if (( $skin = $this->getSkin())) {
				$skin->setSlug($slug);
			} else {
				$this->skinSlug = $slug;
			}
			
		}

		  return $this;
	}
	
	public function setSkin(Skin $v = null)
	{
		if ($this->skinSlug != null) {
			$v->setSlug($this->skinSlug);
		}
		
		parent::setSkin($v);
	}
} // SkinI18n
