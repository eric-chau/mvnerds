<?php

namespace MVNerds\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use \Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Component\OptionsResolver\OptionsResolverInterface;


class ItemI18nType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		$builder->add('lang', 'choice', array(
			'choices' => array('fr' => 'Français', 'en' => 'English'),
			'required' => true
		));
		
		$builder->add('name', 'text', array(
			'required' => true
		));
	}
	
	public function getName()
	{
		return 'item_i18n';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver) 
	{
		$resolver->setDefaults(array(
			'data_class' => 'MVnerds\CoreBundle\Model\ItemI18n'
		));
	}
}