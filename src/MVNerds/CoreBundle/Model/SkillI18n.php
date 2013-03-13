<?php

namespace MVNerds\CoreBundle\Model;

use MVNerds\CoreBundle\Model\om\BaseSkillI18n;


/**
 * Skeleton subclass for representing a row from the 'skill_i18n' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.src.MVNerds.CoreBundle.Model
 */
class SkillI18n extends BaseSkillI18n {

	protected $skillSlug;
	
	public function setName($name) 
	{
		//parent::setName($name);
		
		if ($name !== null && is_numeric($name)) {
			$name = (string) $name;
		}
		
		//Commenté pour simplifier la correction des bugs de skills à remettre plus tard
		//if ($this->name !== $v) {
			$this->name = $name;
			$this->modifiedColumns[] = SkillI18nPeer::NAME;
		//}
			
		if ('fr' == $this->getLang()) {
			$in = array(
				'/[éèê]/u',
				'/[àâ]/u',
				'/[ïî]/u',
				'/[ç]/u',
				'/[^\w]+/u'
			);
			
			$out = array(
				'e',
				'a',
				'i',
				'c',
				'-'
			);
			
			$slug = preg_replace($in, $out, mb_strtolower($this->getName(), 'UTF-8'));
			if (( $skill = $this->getSkill())) {
				$skill->setSlug($slug);
			} else {
				$this->skillSlug = $slug;
			}
			
		}

		  return $this;
	}
	
	public function setSkill(Skill $v = null)
	{
		if ($this->skillSlug != null) {
			$v->setSlug($this->skillSlug);
		}
		
		parent::setSkill($v);
	}
} // SkillI18n
