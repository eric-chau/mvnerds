<?php

namespace MVNerds\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RoleType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('label', 'text', array(
			'label'		=> 'Label',
			'required' 	=> true
		));
		
		$builder->add('uniqueName', 'text', array(
			'label'		=> 'Unique name',
			'required' 	=> true
		));
	}

	public function getName()
	{
		return 'user';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'MVNerds\CoreBundle\Model\Role'
		));
	}


}
