<?php

namespace MVNerds\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use \Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Component\OptionsResolver\OptionsResolverInterface;


class ItemSecondaryEffectI18nType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		$builder->add('lang', 'choice', array(
			'choices' => array('fr' => 'Français', 'en' => 'English'),
			'required' => true
		));
		
		$builder->add('description', 'textarea', array(
			'required' => true
		));
	}
	
	public function getName()
	{
		return 'item_secondary_effect_i18n';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver) 
	{
		$resolver->setDefaults(array(
			'data_class' => 'MVNerds\CoreBundle\Model\ItemSecondaryEffectI18n'
		));
	}
}