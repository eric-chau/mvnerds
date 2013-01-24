<?php

namespace MVNerds\DataGrabberBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use \Symfony\Component\Form\FormBuilderInterface;

class ChampionGrabberByNameType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		$builder->add('name', 'text', array(
			'label'		=> 'Le nom du champion',
			'required'	=> true
		));
	}
	
	public function getName()
	{
		return 'champion_grabber_by_name';
	}
}