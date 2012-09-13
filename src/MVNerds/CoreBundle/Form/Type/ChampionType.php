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
		$builder->add('name', 'text', array(
			'label'		=> 'Nom du champion',
			'required'	=> true
		));
		
		$builder->add('main_role', 'choice', array(
			'label'		=> 'Rôle principal',
			'required'	=> true,
			'choices' => ChampionPeer::getValueSet(ChampionPeer::MAIN_ROLE)
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
		
		$builder->add('mana_type', 'choice', array(
			'label'		=> 'Type de "mana"',
			'required'	=> true,
			'choices'	=> ChampionPeer::getValueSet(ChampionPeer::MANA_TYPE)
		));
	}
	
	public function getName()
	{
		return 'champion';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver) 
	{
		$resolver->setDefaults(array(
			'data_class' => 'MVnerds\CoreBundle\Model\Champion'
		));
	}
}