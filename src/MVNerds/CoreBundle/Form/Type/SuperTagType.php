<?php

namespace MVNerds\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SuperTagType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('uniqueName', 'text', array(
			'label'		=> 'Unique Name',
			'required' 	=> true
		));
		
		$builder->add('label', 'text', array(
			'label'		=> 'Label',
			'required' 	=> true
		));
		
		$builder->add('aliasUniqueName', 'text', array(
			'label'		=> 'Alias Unique Name',
			'required' 	=> false
		));
		
		$builder->add('linkedObjectId', 'text', array(
			'label'		=> 'Linked Object ID',
			'required' 	=> false
		));
		
		$builder->add('linkedObjectNamespace', 'text', array(
			'label'		=> 'Linked Object Namespace',
			'required' 	=> false
		));
	}

	public function getName()
	{
		return 'super_tag';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'MVNerds\CoreBundle\Model\SuperTag'
		));
	}


}
