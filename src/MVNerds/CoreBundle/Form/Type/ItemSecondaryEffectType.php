<?php

namespace MVNerds\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use \Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Component\OptionsResolver\OptionsResolverInterface;

use MVNerds\CoreBundle\Model\ItemSecondaryEffectPeer;

class ItemSecondaryEffectType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		$builder->add('item_secondary_effect_i18ns', 'collection', array(
			'type' => new ItemSecondaryEffectI18nType(),
			'allow_add' => true,
			'allow_delete' => true,
			'by_reference' => false
		));
		
		$categories = ItemSecondaryEffectPeer::getValueSet(ItemSecondaryEffectPeer::CATEGORY);
		$categoryChoice = array();
		foreach ($categories as $category)
		{
			$categoryChoice[$category] = $category;
		}
		
		$builder->add('category', 'choice', array(
			'label'		=> 'Catégorie',
			'required'	=> true,
			'choices'	=> $categoryChoice
		));
		
		$builder->add('is_unique', 'checkbox', array(
			'label' => 'Est unique ?',
			'required'	=> false
		));
	}
	
	public function getName()
	{
		return 'item_secondary_effect';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver) 
	{
		$resolver->setDefaults(array(
			'data_class' => 'MVNerds\CoreBundle\Model\ItemSecondaryEffect'
		));
	}
}