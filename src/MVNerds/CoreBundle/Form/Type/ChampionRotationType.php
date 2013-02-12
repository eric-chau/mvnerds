<?php

namespace MVNerds\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use \Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChampionRotationType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		
		$builder->add('champion', 'model', array(
			'class' => '\MVNerds\CoreBundle\Model\Champion',
			'property' => 'name'
		));
	}
	
	public function getName()
	{
		return 'champion_rotation';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver) 
	{
		$resolver->setDefaults(array(
			'data_class' => 'MVNerds\CoreBundle\Model\ChampionRotation'
		));
	}
}