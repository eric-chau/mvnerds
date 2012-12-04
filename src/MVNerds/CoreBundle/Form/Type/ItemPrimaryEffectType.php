<?php

namespace MVNerds\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use \Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ItemPrimaryEffectType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		if (isset($options['attr']['lang']))
		{
			$locale = $options['attr']['lang'];
		} else {
			$locale = 'fr';
		}
		
		$builder->add('value', 'text', array(
			'label' => 'valeur',
			'required' => true
		));
		
		$builder->add('primary_effect', 'model', array(
			'class' => '\MVNerds\CoreBundle\Model\PrimaryEffect',
			'query' => \MVNerds\CoreBundle\Model\PrimaryEffectQuery::create()
				->joinWithI18n($locale)
				->orderBy('PrimaryEffectI18n.Label'),
			'property' => 'label'
		));
	}
	
	public function getName()
	{
		return 'item_primary_effect';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver) 
	{
		$resolver->setDefaults(array(
			'data_class' => 'MVnerds\CoreBundle\Model\ItemPrimaryEffect'
		));
	}
}