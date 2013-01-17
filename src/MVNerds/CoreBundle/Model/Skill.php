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
	public function removeSkillI18n(SkillI18n $l)
	{
		if ($this->collSkillI18ns === null) {
			return $this;
		}
		
		if (!$this->collSkillI18ns->contains($l)) {
			$this->doRemoveSkillI18n($l);
		}

		return $this;
	}
	
	public function doRemoveSkillI18n(SkillI18n $skillI18n)
	{
		
		foreach ($this->collSkillI18ns as $key => $o) {
			if ($o == $skillI18n) {
				unset($this->collSkillI18ns[$key]);
				break;
			}
		}
		$this->save();
		$skillI18n->delete();
	}
	
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
				file_put_contents(__DIR__ . '/../../../../web/images/skills/'. $slug .'.png', file_get_contents($imageUrl));
			} catch (\Exception $e) {}
		}
	}
} // Skill
