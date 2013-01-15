<?php

namespace MVNerds\DataGrabberBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use \Symfony\Component\Form\FormBuilderInterface;

class ChampionGrabberType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		$builder->add('start_index', 'integer', array(
			'label'		=> 'Index de départ (Commence à partir de 0)',
			'required'	=> true,
			'data'		=> 0
		));
		
		$builder->add('nb_champions', 'integer', array(
			'label'		=> 'Nombre de champions à parcourir (0 pour récupérer tout ce qui suit l\'index de départ)',
			'required'	=> true,
			'data'		=> 0
		));
	}
	
	public function getName()
	{
		return 'champion_grabber';
	}
}