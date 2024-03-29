<?php

namespace MVNerds\CoreBundle\Model;

use \PropelCollection;

use MVNerds\CoreBundle\Model\om\BaseItem;


/**
 * Skeleton subclass for representing a row from the 'item' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.src.MVNerds.CoreBundle.Model
 */
class Item extends BaseItem {

	public function getTagsToString()
	{
		$tags = '';
		foreach ($this->getItemTags() as $itemTag) 
		{
			$tags .= strtolower($itemTag->getTag()->getSlug() . ' ');
		}
		
		return $tags;
	}
	
	/**
	 * 
	 * @return string renvoie une chaine de caracteres contenant tous les game modes 
	 * de l'item pour les utiliser en tant que classe CSS
	 */
	public function getGameModesToString()
	{
		$gameModes = '';
		foreach ($this->getItemGameModes() as $itemGameMode) 
		{
			$gameModes .= strtolower($itemGameMode->getGameMode()->getLabel() . ' ');
		}
		
		return $gameModes;
	}	
	
	public function removeItemGameMode($itemGameMode)
	{
		if ($this->collItemGameModes === null) {
			return $this;
		}
		if ($this->collItemGameModes->contains($itemGameMode)) {
			$this->doRemoveItemGameMode($itemGameMode);
		}

		return $this;
	}
	protected function doRemoveItemGameMode($itemGameMode)
	{		
		foreach ($this->collItemGameModes as $key => $igm) {
			if ($igm == $itemGameMode) {
				unset($this->collItemGameModes[$key]);
				break;
			}
		}
		$this->save();
		$itemGameMode->delete();
		return $this;
	}
	
	public function removeItemPrimaryEffect($itemPrimaryEffect)
	{
		if ($this->collItemPrimaryEffects === null) {
			return $this;
		}
		if ($this->collItemPrimaryEffects->contains($itemPrimaryEffect)) {
			$this->doRemoveItemPrimaryEffects($itemPrimaryEffect);
		}

		return $this;
	}
	protected function doRemoveItemPrimaryEffects($itemPrimaryEffect)
	{		
		foreach ($this->collItemPrimaryEffects as $key => $ipe) {
			if ($ipe == $itemPrimaryEffect) {
				unset($this->collItemPrimaryEffects[$key]);
				break;
			}
		}
		$this->save();
		$itemPrimaryEffect->delete();
		return $this;
	}
	
	public function removeItemSecondaryEffect($itemSecondaryEffect)
	{
		if ($this->collItemSecondaryEffects === null) {
			return $this;
		}
		if ($this->collItemSecondaryEffects->contains($itemSecondaryEffect)) {
			$this->doRemoveItemSecondaryEffects($itemSecondaryEffect);
		}

		return $this;
	}
	protected function doRemoveItemSecondaryEffects($itemSecondaryEffect)
	{		
		foreach ($this->collItemSecondaryEffects as $key => $ise) {
			if ($ise == $itemSecondaryEffect) {
				unset($this->collItemSecondaryEffects[$key]);
				break;
			}
		}
		$this->save();
		$itemSecondaryEffect->delete();
		return $this;
	}
	
	public function removeItemTag($itemTag)
	{
		if ($this->collItemTags === null) {
			return $this;
		}
		if ($this->collItemTags->contains($itemTag)) {
			$this->doRemoveItemTag($itemTag);
		}

		return $this;
	}
	
	protected function doRemoveItemTag($itemTag)
	{		
		foreach ($this->collItemTags as $key => $ct) {
			if ($ct == $itemTag) {
				unset($this->collItemTags[$key]);
				break;
			}
		}
		$this->save();
		$itemTag->delete();
		return $this;
	}
	
	public function removeItemI18n($itemI18n)
	{
		if ($this->collItemI18ns === null) {
			return $this;
		}
		if (!$this->collItemI18ns->contains($itemI18n)) { // only add it if the **same** object is not already associated
			$this->doRemoveItemI18n($itemI18n);
		}

		return $this;
	}
	
	public function doRemoveItemI18n(ItemI18n $itemI18n)
	{
		foreach ($this->collItemI18ns as $key => $i) {
			if ($i == $itemI18n) {
				unset($this->collItemI18ns[$key]);
				break;
			}
		}
		$this->save();
		$itemI18n->delete();
		return $this;
	}
	
	public function getItemGeneologiesRelatedByParentIdCustom($criteria = null, PropelPDO $con = null)
	{
		return $this->getItemGeneologiesRelatedByParentId($criteria, $con);
	}
	public function setItemGeneologiesRelatedByParentIdCustom($itemGeneologiesRelatedByParentId, PropelPDO $con = null)
	{
		if (is_array($itemGeneologiesRelatedByParentId)) {
			$collection = new \PropelCollection();
			foreach($itemGeneologiesRelatedByParentId as $itemGeneology ) {
				$collection->append($itemGeneology);
			}
			$itemGeneologiesRelatedByParentId = $collection;
		}
		
		$this->setItemGeneologiesRelatedByParentId($itemGeneologiesRelatedByParentId, $con);
	}
	
	public function getTotalCost() 
	{
		$cost = $this->getCost();
		$itemGeneologies = $this->getItemGeneologiesRelatedByParentId();
		
		if ($itemGeneologies && !$itemGeneologies->isEmpty())
		{
			foreach ($itemGeneologies as $itemGeneology) 
			{
				if ( ($child = $itemGeneology->getItemRelatedByChildId()) )
				{	
					$cost += $child->getTotalCost();
				} else {
					return $cost;
				}
			}
		}
		return $cost;
	}
	
	public function getUniqueItemGeneologiesRelatedByChildId()
	{
		$uniqueItemGeneologies = new PropelCollection();
		$itemGeneologies = $this->getItemGeneologiesRelatedByChildId();
		
		foreach($itemGeneologies as $itemGeneology) {
			$exists = false;
			foreach($uniqueItemGeneologies as $uniqueItemGeneology) {
				if ($uniqueItemGeneology->getParentId() == $itemGeneology->getParentId() && $uniqueItemGeneology->getChildId() == $itemGeneology->getChildId()) {
					$exists = true;
				}
			}
			if(!$exists && !$itemGeneology->getItemRelatedByParentId()->getIsObsolete()) {
				$uniqueItemGeneologies->append($itemGeneology);
			}
		}
		
		return $uniqueItemGeneologies;
	}
} // Item
