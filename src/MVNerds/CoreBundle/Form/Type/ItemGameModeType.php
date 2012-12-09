<?php

namespace MVNerds\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use \Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ItemGameModeType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		$builder->add('game_mode', 'model', array(
			'class' => '\MVNerds\CoreBundle\Model\GameMode',
			'property' => 'label'
		));
	}
	
	public function getName()
	{
		return 'item_game_mode';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver) 
	{
		$resolver->setDefaults(array(
			'data_class' => 'MVNerds\CoreBundle\Model\ItemGameMode'
		));
	}
}