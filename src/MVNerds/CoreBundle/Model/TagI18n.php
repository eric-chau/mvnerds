<?php

namespace MVNerds\CoreBundle\Model;

use MVNerds\CoreBundle\Model\om\BaseTagI18n;


/**
 * Skeleton subclass for representing a row from the 'tag_i18n' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.src.MVNerds.CoreBundle.Model
 */
class TagI18n extends BaseTagI18n {

	protected $tagSlug;
	
	public function setLabel($label) 
	{
		parent::setLabel($label);
		
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
			
			$slug = preg_replace($in, $out, mb_strtolower($this->getLabel(), 'UTF-8'));
			if (( $tag = $this->getTag())) {
				$tag->setSlug($slug);
			} else {
				$this->tagSlug = $slug;
			}
			
		}

		  return $this;
	}
	
	public function setTag(Tag $v = null)
	{
		if ($this->tagSlug != null) {
			$v->setSlug($this->tagSlug);
		}
		
		parent::setTag($v);
	}
} // TagI18n
