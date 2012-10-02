<?php

namespace MVNerds\CoreBundle\Tag;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

use MVNerds\CoreBundle\Model\Tag;
use MVNerds\CoreBundle\Model\TagPeer;
use MVNerds\CoreBundle\Model\TagQuery;
use MVNerds\CoreBundle\Model\TagI18nPeer;
		
class TagManager
{
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
	public function findOneByLabel($label)
	{
		$tag = TagQuery::create()
			->joinWithI18n()
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
	
	/**
	 * Permet de persister en base de données le tag $tag
	 * 
	 * @param \MVNerds\CoreBundle\Model\Tag $tag l'objet Tagà faire persister en base de données
	 */
	public function save(Tag $tag)
	{
		$tag->save();
	}
}
