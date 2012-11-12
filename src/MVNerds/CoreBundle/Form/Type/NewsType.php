<?php

namespace MVNerds\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class NewsType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('news_category', 'model', array(
			'class' => '\MVNerds\CoreBundle\Model\NewsCategory',
			'property' => 'label',
			'label' => 'Catégorie'
		));
		
		$builder->add('title', 'text', array(
			'label'		=> 'Titre',
			'required' 	=> true
		));
		
		$builder->add('content', 'textarea', array(
			'label'		=> 'Contenu',
			'required' 	=> true,
			'attr'		=> array(
				'cols'	=> '80',
				'rows'	=> '20'
			)
		));
	}

	public function getName()
	{
		return 'news';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'MVNerds\CoreBundle\Model\News'
		));
	}


}
