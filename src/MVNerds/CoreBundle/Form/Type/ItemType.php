<?php

namespace MVNerds\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use \Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Component\OptionsResolver\OptionsResolverInterface;


class ItemType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		if (isset($options['attr']['lang']))
		{
			$locale = $options['attr']['lang'];
		} else {
			$locale = 'fr';
		}
		
		$builder->add('item_i18ns', 'collection', array(
			'type' => new ItemI18nType(),
			'allow_add' => true,
			'prototype' => true
		));
		
		$builder->add('cost', 'number', array(
			'label'		=> 'Coût',
			'required'	=> true
		));
		
		$builder->add('sell_value', 'number', array(
			'label'		=> 'Prix de vente',
			'required'	=> false
		));
		
		$builder->add('riot_code', 'text', array(
			'label'		=> 'Code Riot',
			'required'	=> true
		));
		
		$builder->add('stacks', 'number', array(
			'label'		=> 'Stacks'
		));
		
		$builder->add('is_obsolete', 'checkbox', array(
			'label' => 'Est obsolete ?',
			'required'	=> false
		));
		
		$builder->add('item_game_modes', 'collection', array(
			'type' => new ItemGameModeType(),
			'allow_add' => true,
			'allow_delete' => true,
			'by_reference' => false
		));
		
		$builder->add('item_primary_effects', 'collection', array(
			'type' => new ItemPrimaryEffectType(),
			'allow_add' => true,
			'allow_delete' => true,
			'by_reference' => false,
			'options' => array('attr' => array('lang' => $locale))
		));
		
		$builder->add('item_secondary_effects', 'collection', array(
			'type'			=> new ItemSecondaryEffectType(),
			'allow_add'		=> true,
			'allow_delete'	=> true,
			'by_reference'	=> false,
			'attr' => array('data-i18n-prototype' => '<div id="item_item_secondary_effects___parent-name___item_secondary_effect_i18ns___name__"><div><label for="item_item_secondary_effects___parent-name___item_secondary_effect_i18ns___name___lang" class="required">Lang</label><select id="item_item_secondary_effects___parent-name___item_secondary_effect_i18ns___name___lang" name="item[item_secondary_effects][__parent-name__][item_secondary_effect_i18ns][__name__][lang]" required="required"><option value="fr">Français</option><option value="en">English</option></select></div><div><label for="item_item_secondary_effects___parent-name___item_secondary_effect_i18ns___name___description" class="required">Description</label><textarea id="item_item_secondary_effects___parent-name___item_secondary_effect_i18ns___name___description" name="item[item_secondary_effects][__parent-name__][item_secondary_effect_i18ns][__name__][description]" required="required"></textarea></div></div>')
		));
		
		$builder->add('item_geneologies_related_by_parent_id_custom', 'collection', array(
			'type' => new ItemGeneologyType(),
			'allow_add' => true,
			'allow_delete' => true,
			'by_reference' => false,
			'options' => array('attr' => array('lang' => $locale))
		));
		
		$builder->add('item_tags', 'collection', array(
			'type' => new ItemTagType(),
			'allow_add' => true,
			'allow_delete' => true,
			'by_reference' => false,
			'options' => array('attr' => array('lang' => $locale))
		));
	}
	
	public function getName()
	{
		return 'item';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver) 
	{
		$resolver->setDefaults(array(
			'data_class' => 'MVnerds\CoreBundle\Model\Item'
		));
	}
}