<?php

namespace  MVNerds\LaunchSiteBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ForgotPasswordType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{		
		$builder->add('email', 'email', array(
			'label'		=> 'Registration.User.email',
			'required' 	=> true
		));
	}

	public function getName()
	{
		return 'summoner';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'MVNerds\LaunchSiteBundle\Form\Model\ForgotPasswordModel'
		));
	}


}
