<?php

namespace  MVNerds\SiteBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SummonerType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('username', 'text', array(
			'label'		=> 'Registration.User.username',
			'required' 	=> true
		));
		
		$builder->add('password', 'password', array(
			'label'		=> 'Registration.User.pwd',
			'required' 	=> true
		));
		
		$builder->add('passwordConfirm', 'password', array(
			'label'		=> 'Registration.User.pwd_confirm',
			'required' 	=> true
		));
		
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
			'data_class' => 'MVNerds\SiteBundle\Form\Model\SummonerModel'
		));
	}


}
