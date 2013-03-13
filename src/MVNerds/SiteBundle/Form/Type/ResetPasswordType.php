<?php

namespace  MVNerds\SiteBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ResetPasswordType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{		
		$builder->add('password', 'password', array(
			'label'		=> 'Registration.User.pwd',
			'required' 	=> true
		));
		
		$builder->add('passwordConfirm', 'password', array(
			'label'		=> 'Registration.User.pwd_confirm',
			'required' 	=> true
		));
	}

	public function getName()
	{
		return 'reset_summoner_password';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'MVNerds\SiteBundle\Form\Model\ResetPasswordModel'
		));
	}


}
