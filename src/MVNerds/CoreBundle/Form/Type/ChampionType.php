<?php

namespace MVNerds\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use \Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Component\OptionsResolver\OptionsResolverInterface;
use  \MVNerds\CoreBundle\Model\ChampionPeer;

class ChampionType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		$builder->add('champion_i18ns', 'collection', array(
			'type' => new ChampionI18nType(),
			'allow_add' => true,
			'prototype' => true,
			'by_reference' => false
		));
		
		$builder->add('base_damage', 'number', array(
			'label'		=> 'Dégâts de base',
			'required'	=> true
		));
		
		$builder->add('bonus_damage_per_level', 'number', array(
			'label'		=> 'Dégâts bonus par niveau',
			'required'	=> true
		));
		
		$builder->add('base_health', 'number', array(
			'label'		=> 'Santé de base',
			'required'	=> true
		));
		
		$builder->add('bonus_health_per_level', 'number', array(
			'label'		=> 'Santé bonus par niveau',
			'required'	=> true
		));
		
		$builder->add('base_health_regen', 'number', array(
			'label'		=> 'Régénération de santé de base',
			'required'	=> true
		));
		
		$builder->add('bonus_health_regen_per_level', 'number', array(
			'label'		=> 'Régénration de santé bonus par niveau',
			'required'	=> true
		));
		
		$builder->add('base_mana', 'number', array(
			'label'		=> 'Mana de base',
			'required'	=> true
		));
		
		$builder->add('bonus_mana_per_level', 'number', array(
			'label'		=> 'Mana bonus par niveau',
			'required'	=> true
		));
		
		$builder->add('base_mana_regen', 'number', array(
			'label'		=> 'Régénération de mana de base',
			'required'	=> true
		));
		
		$builder->add('bonus_mana_regen_per_level', 'number', array(
			'label'		=> 'Régénération de mana bonus par niveau',
			'required'	=> true
		));
		
		$builder->add('base_armor', 'number', array(
			'label'		=> 'Armure de base',
			'required'	=> true
		));
		
		$builder->add('bonus_armor_per_level', 'number', array(
			'label'		=> 'Armure bonus par niveau',
			'required'	=> true
		));
		
		$builder->add('base_magic_resist', 'number', array(
			'label'		=> 'Résistance magique de base',
			'required'	=> true
		));
		
		$builder->add('bonus_magic_resist_per_level', 'number', array(
			'label'		=> 'Résistance magique bonus par niveau',
			'required'	=> true
		));
		
		$builder->add('move_speed', 'number', array(
			'label'		=> 'Vitesse de déplacement',
			'required'	=> true
		));
		
		$manaTypes = ChampionPeer::getValueSet(ChampionPeer::MANA_TYPE);
		$manaTypeChoice = array();
		foreach ($manaTypes as $manaType)
		{
			$manaTypeChoice[$manaType] = $manaType;
		}
		
		$builder->add('mana_type', 'choice', array(
			'label'		=> 'Type de "mana"',
			'required'	=> true,
			'choices'	=> $manaTypeChoice
		));
		
		$builder->add('base_attack_speed', 'number', array(
			'label'		=> 'Vitesse d\'attaque de base',
			'required'	=> true
		));
		
		$builder->add('bonus_attack_speed_per_level', 'number', array(
			'label'		=> 'Vitesse d\'attaque bonus par niveau',
			'required'	=> true
		));
		
		$builder->add('attack_range', 'number', array(
			'label'		=> 'Portée de base',
			'required'	=> true
		));
		
		$builder->add('bonus_attack_range_per_level', 'number', array(
			'label'		=> 'Portée bonus par niveau',
			'required'	=> true
		));
		
		$builder->add('lifesteal', 'number', array(
			'label'		=> 'Vol de vie',
			'required'	=> false
		));
		
		$builder->add('critical_chance', 'number', array(
			'label'		=> 'Chance de coup critique',
			'required'	=> false
		));
		
		$builder->add('critical_damage', 'number', array(
			'label'		=> 'Dégât des coups critiques',
			'required'	=> false
		));
		
		$builder->add('ip_cost', 'number', array(
			'label'		=> 'Coût en IP',
			'required'	=> true
		));
		
		$builder->add('rp_cost', 'number', array(
			'label'		=> 'Coût en RP',
			'required'	=> true
		));
		
		$builder->add('champion_tags', 'collection', array(
			'type' => new ChampionTagType(),
			'allow_add' => true,
			'allow_delete' => true,
			'by_reference' => false
		));
		
		$builder->add('skills', 'collection', array(
			'type' => new SkillType(),
			'allow_add' => true,
			'allow_delete' => true,
			'by_reference' => false,
			'attr' => array(
				'data-i18n-prototype' => '<div id="champion_skills___parent-name___skill_i18ns___name__">'.
									'<div>'.
										'<label for="champion_skills___parent-name___skill_i18ns___name___lang" class="required">Lang</label>'.
										'<select id="champion_skills___parent-name___skill_i18ns___name___lang" name="champion[skills][__parent-name__][skill_i18ns][__name__][lang]" required="required">'.
											'<option value="fr">Français</option>'.
											'<option value="en">English</option>'.
										'</select>'.
									'</div>'.
									'<div>'.
										'<label for="champion_skills___parent-name___skill_i18ns___name___name" class="required">Nom</label>'.
										'<input type="text" id="champion_skills___parent-name___skill_i18ns___name___name" name="champion[skills][__parent-name__][skill_i18ns][__name__][name]" required="required"/>'.
									'</div>'.
									'<div>'.
										'<label for="champion_skills___parent-name___skill_i18ns___name___description" class="required">Description</label>'.
										'<textarea id="champion_skills___parent-name___skill_i18ns___name___description" name="champion[skills][__parent-name__][skill_i18ns][__name__][description]" required="required"></textarea>'.
									'</div>'.
									'<div>'.
										'<label for="champion_skills___parent-name___skill_i18ns___name___cost" class="required">Coût</label>'.
										'<input type="text" id="champion_skills___parent-name___skill_i18ns___name___cost" name="champion[skills][__parent-name__][skill_i18ns][__name__][cost]" required="required"/>'.
									'</div>'.
									'<div>'.
										'<label for="champion_skills___parent-name___skill_i18ns___name___cooldown" class="required">Cooldown</label>'.
										'<input type="text" id="champion_skills___parent-name___skill_i18ns___name___cooldown" name="champion[skills][__parent-name__][skill_i18ns][__name__][cooldown]" required="required"/>'.
									'</div>'.
								'</div>'
			)
		));
		
		$builder->add('skins', 'collection', array(
			'type' => new SkinType(),
			'allow_add' => true,
			'allow_delete' => true,
			'by_reference' => false,
			'attr' => array(
				'data-i18n-prototype' => '<div id="champion_skins___parent-name___skin_i18ns___name__">'.
									'<div>'.
										'<label for="champion_skins___parent-name___skin_i18ns___name___lang" class="required">Lang</label>'.
										'<select id="champion_skins___parent-name___skin_i18ns___name___lang" name="champion[skins][__parent-name__][skin_i18ns][__name__][lang]" required="required">'.
											'<option value="fr">Français</option>'.
											'<option value="en">English</option>'.
										'</select>'.
									'</div>'.
									'<div>'.
										'<label for="champion_skins___parent-name___skin_i18ns___name___name" class="required">Name</label>'.
										'<input type="text" id="champion_skins___parent-name___skin_i18ns___name___name" name="champion[skins][__parent-name__][skin_i18ns][__name__][name]" required="required"/>'.
									'</div>'.
								'</div>'
			)
		));
	}
	
	public function getName()
	{
		return 'champion';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver) 
	{
		$resolver->setDefaults(array(
			'data_class' => 'MVNerds\CoreBundle\Model\Champion'
		));
	}
}