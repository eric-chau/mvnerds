<?php

namespace MVNerds\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use MVNerds\CoreBundle\Model\NewsPeer;

class NewsType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('news_category', 'model', array(
			'class' => '\MVNerds\CoreBundle\Model\NewsCategory',
			'property' => 'unique_name',
			'label' => 'Catégorie'
		));
		
		$statusSet = NewsPeer::getValueSet(NewsPeer::STATUS);
		$statusChoice = array();
		foreach($statusSet as $status) {
			$statusChoice[$status] = $status;
		}
		
		$builder->add('status', 'choice', array(
			'choices'   => $statusChoice,
			'required'  => true,
		));
		
//		$builder->add('image_name', 'text', array(
//			'label'		=> 'Image de présentation',
//			'attr'		=> array('placeholder' => 'Ex : mon-image.jpg')
//		));
		
		$builder->add('image', 'file', array(
			'required' => false
		));
		
		$builder->add('is_highlight', 'checkbox', array(
			'label' => 'Est un highlight ?',
			'required'	=> false
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
		return 'news';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'MVNerds\CoreBundle\Model\News'
		));
	}


}
