<?php

namespace MVNerds\CoreBundle\Model;

use MVNerds\CoreBundle\Model\om\BaseChampion;


/**
 * Skeleton subclass for representing a row from the 'champion' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.src.MVNerds.CoreBundle.Model
 */
class Champion extends BaseChampion {

	public function getProperty($name)
	{
		$classMethods = get_class_methods($this);
		
		$method = 'get'.$name;
		
		if(in_array($method, $classMethods))
		{
			return $this->$method();
		}
		return null;
	}
	
	/**
	 * 
	 * @return array renvoie le tableau contenant tous les tags du champion
	 */
	public function getTags()
	{
		$tags = array();
		foreach ($this->getChampionTags() as $championtag) 
		{
			$tags[] = $championtag->getTag()->getLabel();
		}
		return $tags;
	}
	
	/**
	 * 
	 * @return string renvoie une chaine de caracteres contenant tous les tags 
	 * du champion pour les utiliser en tant que classe CSS
	 */
	public function getTagsToString()
	{
		$tags = '';
		foreach ($this->getChampionTags() as $championtag) 
		{
			$tags .= strtolower($championtag->getTag()->getLabel() . ' ');
		}
		return $tags;
	}
} // Champion
