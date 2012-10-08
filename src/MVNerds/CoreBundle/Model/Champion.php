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
class Champion extends BaseChampion 
{
	private $level = 1;
	
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
	
	public function getBaseHealth()
	{
		return $this->base_health + ($this->bonus_health_per_level * ($this->level - 1));
	}
	
	public function getBaseHealthRegen()
	{
		return $this->base_health_regen + ($this->bonus_health_regen_per_level * ($this->level - 1));
	}
	
	public function getBaseMana()
	{
		return $this->base_mana + ($this->bonus_mana_per_level * ($this->level - 1));
	}
	
	public function getBaseManaRegen()
	{
		return $this->base_mana_regen + ($this->bonus_mana_regen_per_level * ($this->level - 1));
	}
	
	public function getBaseArmor()
	{
		return $this->base_armor + ($this->bonus_armor_per_level * ($this->level - 1));
	}
	
	public function getBaseMagicResist()
	{
		return $this->base_magic_resist + ($this->bonus_magic_resist_per_level * ($this->level - 1));
	}
	
	public function getBaseDamage()
	{
		return $this->base_damage + ($this->bonus_damage_per_level * ($this->level - 1));
	}
	
	public function getBaseAttackSpeed()
	{
		return $this->base_attack_speed + ($this->bonus_attack_speed_per_level * ($this->level - 1));
	}
	
	public function getLevel()
	{
		return $this->level;
	}
	
	public function setLevel($level)
	{
		$this->level = $level;
	}
} // Champion
