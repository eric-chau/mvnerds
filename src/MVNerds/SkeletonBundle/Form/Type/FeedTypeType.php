<?php

namespace MVNerds\SkeletonBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use MVNerds\CoreBundle\Model\FeedTypeQuery;

class FeedTypeType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$feedTypes = FeedTypeQuery::create()->find();
		
		$feedTypesArray = array();
		foreach ($feedTypes as $feedType) {
			$feedTypesArray[$feedType->getUniqueName()] = $feedType->getUniqueName();
		}
		
		$builder->add('unique_name', 'choice', array(
			'choices'	=> $feedTypesArray,
			'label'		=> false,
			'required'	=> true,
		));
	}
	
	public function getName()
	{
		return 'feed_type';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'MVNerds\CoreBundle\Model\FeedType',
		));
	}
}
