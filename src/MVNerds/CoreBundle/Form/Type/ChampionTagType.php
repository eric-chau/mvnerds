<?php

namespace MVNerds\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use \Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Component\OptionsResolver\OptionsResolverInterface;

use MVNerds\CoreBundle\Model\TagQuery;
use MVNerds\CoreBundle\Model\TagTypeQuery;
use MVNerds\CoreBundle\Model\TagTypePeer;

class ChampionTagType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		if (isset($options['attr']['lang']))
		{
			$locale = $options['attr']['lang'];
		} else {
			$locale = 'en';
		}
		$tagType = TagTypeQuery::create()->add(TagTypePeer::UNIQUE_NAME, 'BASE_CHAMPION_PARENT')->findOne();
		
		$builder->add('tag', 'model', array(
			'class' => '\MVNerds\CoreBundle\Model\Tag',
			'query' => TagQuery::create()
				->joinTagType('tt')
				->joinTagI18n('ti', \Criteria::LEFT_JOIN)
				->addJoinCondition('tt', 'tt.ParentId = ?', $tagType->getId())
				->addJoinCondition('ti', 'ti.Lang = ?', $locale)
				->orderBy('ti.Label', 'asc'),
			'property' => 'label'
		));
	}
	
	public function getName()
	{
		return 'champion_tag';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver) 
	{
		$resolver->setDefaults(array(
			'data_class' => 'MVNerds\CoreBundle\Model\ChampionTag'
		));
	}
}