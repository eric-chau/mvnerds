<?php

namespace MVNerds\CoreBundle\Model;

use MVNerds\CoreBundle\Model\om\BaseSkill;


/**
 * Skeleton subclass for representing a row from the 'skill' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.src.MVNerds.CoreBundle.Model
 */
class Skill extends BaseSkill {	
	public function getImage() {
		return '';
	}
	public function setImage($imageUrl) {
		if ($imageUrl && $imageUrl != '') {
			$imageUrl = preg_replace('/ /', '%20', $imageUrl);
			
			if ( null === $this->getSlug() || $this->getSlug() == '') {
				$slug = $this->createSlug();
			} else {
				$slug = $this->getSlug();
			}
			
			try {
				file_put_contents(__DIR__ . '/../../../../web/medias/images/champions/skills/'. $slug .'.png', file_get_contents($imageUrl));
			} catch (\Exception $e) {}
		}
	}
} // Skill