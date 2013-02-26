<?php

namespace MVNerds\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use MVNerds\CoreBundle\Model\VideoQuery;

class SkinType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('skin_i18ns', 'collection', array(
			'type' => new SkinI18nType(),
			'allow_add' => true,
			'allow_delete' => true,
			'by_reference' => false
		));
		
		$builder->add('cost');
		$builder->add('image', 'text', array(
			'required' => false
		));
		$builder->add('video', 'model', array(
			'class' => '\MVNerds\CoreBundle\Model\Video',
			'query' => VideoQuery::create()
				->joinVideoCategory('vc')
				->addJoinCondition('vc', 'vc.UniqueName LIKE ?', 'SKIN_PREVIEW')
				->orderBy('Title', 'asc'),
			'property' => 'title',
			'required' => false
		));
	}

	public function getName()
	{
		return 'skin';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'MVNerds\CoreBundle\Model\Skin'
		));
	}


}
