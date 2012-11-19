<?php

namespace MVNerds\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use \Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ItemGeneologyType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		$builder->add('item_related_by_child_id', 'model', array(
			'class' => '\MVNerds\CoreBundle\Model\Item',
			'query' => \MVNerds\CoreBundle\Model\ItemQuery::create()
				->joinWithI18n()
				->orderBy('ItemI18n.Name'),
			'property' => 'name'
		));
	}
	
	public function getName()
	{
		return 'item_geneology';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver) 
	{
		$resolver->setDefaults(array(
			'data_class' => 'MVnerds\CoreBundle\Model\ItemGeneology'
		));
	}
}