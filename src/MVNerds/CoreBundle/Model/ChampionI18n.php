<?php

namespace MVNerds\CoreBundle\Model;

use MVNerds\CoreBundle\Model\om\BaseChampionI18n;


/**
 * Skeleton subclass for representing a row from the 'champion_i18n' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.src.MVNerds.CoreBundle.Model
 */
class ChampionI18n extends BaseChampionI18n {

	protected $championSlug;
	
	public function setName($name) 
	{
		parent::setName($name);
		
		if ('fr' == $this->getLang()) {
			$slug = \MVNerds\CoreBundle\Utils\MVNerdsSluggify::mvnerdsSluggify($this->getName());
			if (( $champion = $this->getChampion())) {
				$champion->setSlug($slug);
			} else {
				$this->championSlug = $slug;
			}
			
		}

		  return $this;
	}
	
	public function setChampion(Champion $v = null)
	{
		if ($this->championSlug != null) {
			$v->setSlug($this->championSlug);
		}
		
		parent::setChampion($v);
	}
} // ChampionI18n
