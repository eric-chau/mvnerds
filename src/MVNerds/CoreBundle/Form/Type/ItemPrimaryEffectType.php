<?php

namespace MVNerds\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use \Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ItemPrimaryEffectType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		$builder->add('value', 'text', array(
			'label' => 'valeur',
			'required' => true
		));
		
		$builder->add('primary_effect', 'model', array(
			'class' => '\MVNerds\CoreBundle\Model\PrimaryEffect',
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