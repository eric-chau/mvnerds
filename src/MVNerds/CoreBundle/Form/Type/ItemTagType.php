<?php

namespace MVNerds\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use \Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ItemTagType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		$builder->add('tag', 'model', array(
			'class' => '\MVNerds\CoreBundle\Model\Tag',
			'query' => \MVNerds\CoreBundle\Model\TagQuery::create()
				->joinTagType('tt')
				->addJoinCondition('tt', 'tt.UniqueName = ?', 'BASE_ITEM_PARENT')
				->orderBy('Slug'),
			'property' => 'slug'
		));
	}
	
	public function getName()
	{
		return 'item_tag';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver) 
	{
		$resolver->setDefaults(array(
			'data_class' => 'MVnerds\CoreBundle\Model\ItemTag'
		));
	}
}