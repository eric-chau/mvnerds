<?php

namespace MVNerds\CoreBundle\Model;

use MVNerds\CoreBundle\Model\om\BaseItemSecondaryEffect;


/**
 * Skeleton subclass for representing a row from the 'item_secondary_effect' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.src.MVNerds.CoreBundle.Model
 */
class ItemSecondaryEffect extends BaseItemSecondaryEffect {

	public function removeItemSecondaryEffectI18n(ItemSecondaryEffectI18n $l)
	{
		if ($this->collItemSecondaryEffectI18ns === null) {
			return $this;
		}
		
		if (!$this->collItemSecondaryEffectI18ns->contains($l)) {
			$this->doRemoveItemSecondaryEffectI18n($l);
		}

		return $this;
	}
	
	public function doRemoveItemSecondaryEffectI18n(ItemSecondaryEffectI18n $itemSecondaryEffectI18n)
	{
		
		foreach ($this->collItemSecondaryEffectI18ns as $key => $o) {
			if ($o == $itemSecondaryEffectI18n) {
				unset($this->collItemSecondaryEffectI18ns[$key]);
				break;
			}
		}
		$this->save();
		$itemSecondaryEffectI18n->delete();
	}
} // ItemSecondaryEffect
