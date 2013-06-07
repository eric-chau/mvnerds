<?php

namespace MVNerds\SkeletonBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

use MVNerds\SkeletonBundle\Form\Type\FeedTypeType;

class FeedType extends AbstractType
{
	private $translator;
	
	public function __construct(Translator $translator) 
	{
		$this->translator = $translator;
	}
	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('lang', 'choice', array(
			'choices' => array(
				'fr' => $this->translator->trans('FRENCH'),
				'en' => $this->translator->trans('ENGLISH'),
				'de' => $this->translator->trans('DEUTSCH'),
				'es' => $this->translator->trans('SPANISH'),
				'ru' => $this->translator->trans('RUSSIAN'),
			)
		));
		$builder->add('type_unique_name', new FeedTypeType($this->translator), array(
			'label'		=> false,
		));
		$builder->add('title');
		$builder->add('content');
		$builder->add('feed_tags', 'hidden', array(
			'mapped' => false,
		));
	}
	
	public function getName()
	{
		return 'feed';
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'MVNerds\CoreBundle\Model\Feed',
		));
	}
}
