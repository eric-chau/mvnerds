<?php

namespace MVNerds\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use \Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ItemTagType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		if (isset($options['attr']['lang']))
		{
			$locale = $options['attr']['lang'];
		} else {
			$locale = 'fr';
		}
		
		$builder->add('tag', 'model', array(
			'class' => '\MVNerds\CoreBundle\Model\Tag',
			'query' => \MVNerds\CoreBundle\Model\TagQuery::create()
				->joinTagType('tt')
				->joinTagI18n('ti')
				->addJoinCondition('tt', 'tt.UniqueName = ?', 'BASE_ITEM_PARENT')
				->addJoinCondition('ti', 'ti.Lang = ?', $locale)
				->orderBy('ti.Label'),
			'property' => 'label'
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