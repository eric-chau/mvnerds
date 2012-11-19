<?php

namespace  MVNerds\ItemHandlerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChangeLoLDirectoryType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('lolDirectory', 'text', array(
			'label'		=> 'ItemBuilder.ChangeLoLDirectory.label',
			'required' 	=> true
		));
	}

	public function getName()
	{
		return 'change_lol_directory';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'MVNerds\ItemHandlerBundle\Form\Model\ChangeLoLDirectoryModel'
		));
	}


}
