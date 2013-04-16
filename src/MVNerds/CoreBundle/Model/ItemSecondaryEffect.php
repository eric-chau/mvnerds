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

	//Permet de récupérer la partie qui précède les ":" de la description
	public function getDescriptionTitle() 
	{
		$exploded = explode(':', $this->getDescription(), 2);
		return (($exploded != null && count($exploded) > 1) ? $exploded[0] . ' : ' : $this->getDescription());
	}
	
	//Permet de ne récupérer que la partie qui suit les ":" de la description
	public function getSimpleDescription() 
	{
		$exploded = explode(':', $this->getDescription(), 2);
		return (($exploded != null && count($exploded) > 1) ? $exploded[1] : '');
	}
} // ItemSecondaryEffect
