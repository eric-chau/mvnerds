<?php

namespace MVNerds\CoreBundle\Tag;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Session\Session;

use MVNerds\CoreBundle\Model\Tag;
use MVNerds\CoreBundle\Model\TagPeer;
use MVNerds\CoreBundle\Model\TagQuery;
use MVNerds\CoreBundle\Model\TagI18nPeer;
		
class TagManager
{
	private $userLocale;
	
	/**
	 * Permet de récupérer tous les tags
	 */
	public function findAll()
	{
		return TagQuery::create()
			->joinWithI18n()
			->OrderBy(TagPeer::ID)
		->find();
	}
	
	/**
	 * Récupère un objet Tag à partir de son label $label
	 * 
	 * @param string $label le label du tag à récupérer
	 * @return MVNerds\CoreBundle\Model\Tag l'objet Tag qui correspond au label $label
	 * @throws InvalidArgumentException exception levée si aucun tag n'est associé au label $label
	 */
	public function findOneByLabel($label, $locale ='en')
	{
		$tag = TagQuery::create()
			->joinWithI18n($locale)
			->joinWith('ChampionTag', \Criteria::LEFT_JOIN)
			->joinWith('ChampionTag.Champion', \Criteria::LEFT_JOIN)
			->add(TagI18nPeer::LABEL, $label)
		->findOne();
		
		if (null === $tag)
		{
			throw new InvalidArgumentException('No tag with label:' . $label . '!');
		}

		return $tag;
	}
	
	public function findByParentName($name)
	{
		$tags = TagQuery::create()
			->joinWithI18n($this->userLocale, \Criteria::LEFT_JOIN)
			->joinWith('TagType')
			->addJoinCondition('TagType', 'TagType.UniqueName LIKE ?', $name)
		->find();
		
		if (null === $tags)
		{
			throw new InvalidArgumentException('No tag with parent name:' . $name . '!');
		}

		return $tags;
	}
	
	public function setUserLocale(Session $session)
	{
		$locale = $session->get('locale', null);
		$this->userLocale = null === $locale? 'fr' : $locale;
	}
}
