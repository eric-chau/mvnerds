<?php

namespace MVNerds\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use \Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChampionTagType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		$builder->add('tag', 'model', array(
			'class' => '\MVNerds\CoreBundle\Model\Tag',
			'property' => 'slug'
		));
	}
	
	public function getName()
	{
		return 'champion_tag';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver) 
	{
		$resolver->setDefaults(array(
			'data_class' => 'MVnerds\CoreBundle\Model\ChampionTag'
		));
	}
}