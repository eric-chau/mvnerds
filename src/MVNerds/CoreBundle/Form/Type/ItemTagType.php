<?php

namespace MVNerds\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use \Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Component\OptionsResolver\OptionsResolverInterface;

use MVNerds\CoreBundle\Model\TagTypeQuery;
use MVNerds\CoreBundle\Model\TagTypePeer;

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
		
		$tagType = TagTypeQuery::create()->add(TagTypePeer::UNIQUE_NAME, 'BASE_ITEM_PARENT')->findOne();
		
		$builder->add('tag', 'model', array(
			'class' => '\MVNerds\CoreBundle\Model\Tag',
			'query' => \MVNerds\CoreBundle\Model\TagQuery::create()
				->joinWithI18n($locale)
				->joinWith('TagType tt')
				->addJoinCondition('tt', 'tt.ParentId = ?', $tagType->getId())
				->orderBy('TagI18n.Label', 'asc'),
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
			'data_class' => 'MVNerds\CoreBundle\Model\ItemTag'
		));
	}
}