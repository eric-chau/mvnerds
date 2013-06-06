<?php

namespace MVNerds\SkeletonBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use MVNerds\SkeletonBundle\Form\Type\FeedTypeType;

class FeedType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('lang');
		$builder->add('type_unique_name', new FeedTypeType());
		$builder->add('title');
		$builder->add('content');
		$builder->add('save', 'submit');
	}
	
	public function getName()
	{
		return 'feed';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'MVNerds\CoreBundle\Model\Feed',
		));
	}
}
