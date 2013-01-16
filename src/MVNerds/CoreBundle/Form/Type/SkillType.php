<?php

namespace MVNerds\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SkillType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('name');
		$builder->add('description');
		$builder->add('cost');
		$builder->add('range');
		$builder->add('cooldown');
		$builder->add('position');
	}

	public function getName()
	{
		return 'skill';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'MVNerds\CoreBundle\Model\Skill'
		));
	}


}
