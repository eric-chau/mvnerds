<?php

namespace MVNerds\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use MVNerds\CoreBundle\Model\NewsPeer;

class NewsI18nType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{		
		$builder->add('lang', 'choice', array(
			'choices'   => array('fr' => 'Français', 'en' => 'English'),
			'required'  => true,
		));
		
		$builder->add('title', 'text', array(
			'label'		=> 'Titre',
			'required' 	=> true
		));
		
		$builder->add('preview', 'textarea', array(
			'label'		=> 'Présentation',
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
		return 'news_i18n';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'MVNerds\CoreBundle\Model\NewsI18n'
		));
	}


}
