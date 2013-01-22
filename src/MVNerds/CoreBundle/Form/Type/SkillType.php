<?php

namespace MVNerds\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use MVNerds\CoreBundle\Form\Type\SkillI18nType;

class SkillType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('skill_i18ns', 'collection', array(
			'type' => new SkillI18nType(),
			'allow_add' => true,
			'allow_delete' => true,
			'by_reference' => false
		));
		
		$builder->add('range');
		$builder->add('image', 'text', array(
			'required' => false
		));
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
