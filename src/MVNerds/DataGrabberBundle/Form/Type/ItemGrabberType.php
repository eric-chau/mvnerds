<?php

namespace MVNerds\DataGrabberBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use \Symfony\Component\Form\FormBuilderInterface;

class ItemGrabberType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		$builder->add('start_index', 'integer', array(
			'label'		=> 'Index de départ (Commence à partir de 0)',
			'required'	=> true,
			'data'		=> 0
		));
		
		$builder->add('nb_items', 'integer', array(
			'label'		=> 'Nombre d\'items à récupérer (0 pour récupérer tout ce qui suit l\'index de départ)',
			'required'	=> true,
			'data'		=> 0
		));
	}
	
	public function getName()
	{
		return 'item_grabber';
	}
}