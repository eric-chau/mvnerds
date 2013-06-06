<?php

namespace MVNerds\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FeedTypeType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('uniqueName', 'text', array(
			'label'		=> 'Unique Name',
			'required' 	=> true
		));
		
		$builder->add('is_private', 'checkbox', array(
			'label' => 'Est privé ?',
			'required'	=> false
		));
	}

	public function getName()
	{
		return 'feed_type';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'MVNerds\CoreBundle\Model\FeedType'
		));
	}


}
