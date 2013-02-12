<?php

namespace MVNerds\CoreBundle\Model;

use MVNerds\CoreBundle\Model\om\BaseRotation;


/**
 * Skeleton subclass for representing a row from the 'rotation' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.src.MVNerds.CoreBundle.Model
 */
class Rotation extends BaseRotation {

	public function removeChampionRotation(ChampionRotation $l)
	{
		if ($this->collChampionRotations === null) {
			return $this;
		}
		if ($this->collChampionRotations->contains($l)) {
			$this->doRemoveChampionRotation($l);
		}

		return $this;
	}
	
	public function removeRotationI18n(RotationI18n $l)
	{
		die('ko');
	}
	
	protected function doRemoveChampionRotation($championRotation)
	{		
		foreach ($this->collChampionRotations as $key => $ct) {
			if ($ct == $championRotation) {
				unset($this->collChampionRotations[$key]);
				break;
			}
		}
		$this->save();
		$championRotation->delete();
		return $this;
	}
} // Rotation
