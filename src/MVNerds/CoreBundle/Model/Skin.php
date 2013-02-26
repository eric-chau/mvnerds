<?php

namespace MVNerds\CoreBundle\Model;

use MVNerds\CoreBundle\Model\om\BaseSkin;


/**
 * Skeleton subclass for representing a row from the 'skin' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.src.MVNerds.CoreBundle.Model
 */
class Skin extends BaseSkin {
	
	public function removeSkinI18n(SkinI18n $l)
	{
		if ($this->collSkinI18ns === null) {
			return $this;
		}
		
		if ($this->collSkinI18ns->contains($l)) {
			$this->doRemoveSkinI18n($l);
		}

		return $this;
	}
	
	public function doRemoveSkillI18n(SkinI18n $skinI18n)
	{
		foreach ($this->collSkinI18ns as $key => $o) {
			if ($o == $skinI18n) {
				unset($this->collSkinI18ns[$key]);
				break;
			}
		}
		$this->save();
		$skinI18n->delete();
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
				file_put_contents(__DIR__ . '/../../../../web/medias/images/champions/skins/'. $slug .'.png', file_get_contents($imageUrl));
			} catch (\Exception $e) {}
		}
	}
} // Skin
