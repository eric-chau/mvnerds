<?php

namespace MVNerds\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use \Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Component\OptionsResolver\OptionsResolverInterface;
use  \MVNerds\CoreBundle\Model\ChampionPeer;

class RotationType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		$builder->add('rotation_i18ns', 'collection', array(
			'type' => new RotationI18nType(),
			'allow_add' => true,
			'prototype' => true,
			'by_reference' => false
		));
		
		$builder->add('champion_rotations', 'collection', array(
			'type' => new ChampionRotationType(),
			'allow_add' => true,
			'allow_delete' => true,
			'by_reference' => false
		));
	}
	
	public function getName()
	{
		return 'rotation';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver) 
	{
		$resolver->setDefaults(array(
			'data_class' => 'MVNerds\CoreBundle\Model\Rotation'
		));
	}
}