<?php

namespace MVNerds\CoreBundle\ChampionTag;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;


use MVNerds\CoreBundle\Model\ChampionTag;
use MVNerds\CoreBundle\Model\ChampionTagQuery;
use MVNerds\CoreBundle\Model\ChampionTagPeer;

use MVNerds\CoreBundle\Model\Champion;
use MVNerds\CoreBundle\Model\Tag;

use MVNerds\CoreBundle\Utils\ObjectValidation;

class ChampionTagManager
{	
	/**
	 * Associe un tag à un champion
	 * 
	 * @param \MVNerds\CoreBundle\Model\Tag $tag
	 * @param \MVNerds\CoreBundle\Model\Champion $champion
	 * 
	 * @throws InvalidArgumentException exception levée lorsque le champion est déjà associé au tag
	 */
	public function addTagToChampion(Tag $tag, Champion $champion)
	{
		//On vérifie que ce champion n'est pas déjà associé avec le tag
		if (null != $this->findOneByChampionAndTag($champion, $tag))
		{
			throw new InvalidArgumentException('Champion with name:\'' . $champion->getName() . '\' already has the tag \'' . $tag->getLabel() . '\'');
		}
		
		//On affecte le tag et le champion à un nouveau championTag
		$championTag = new ChampionTag();
		$championTag->setChampion($champion);
		$championTag->setTag($tag);
		
		// Finally
		$this->save($championTag);
	}

	/**
	 * Associe à un champion plusieurs tags
	 * 
	 * @param \MVNerds\CoreBundle\Model\Tag[] $tags les tags à associer avec le champion $champion
	 * @param \MVNerds\CoreBundle\Model\Champion $champion l'objet champion à associer avec chacun des tags du tableau $tags
	 * 
	 * @throws InvalidArgumentException exception levée lorsque :
	 * - le champion est déjà associé à l'un des tags présents dans le tableau $tags
	 * - le tableau $tags fournis ne contient pas uniquement que des objets de type Tag
	 */
	public function addTagsToChampion(array $tags, Champion $champion)
	{
		if ( ! ObjectValidation::isObjectsInstanceof($tags, 'MVNerds\CoreBundle\Model\Tag') )
		{
			throw new InvalidArgumentException('The given tags array doesn\'t only contains MVNerds\CoreBundle\Model\Tag objects');
		}
		
		foreach($tags as $tag)
		{
			$this->addTagToChampion($tag, $champion);
		}
	}
	
	/**
	 * Permet de retirer un tag associé à un champion
	 * 
	 * @param \MVNerds\CoreBundle\Model\Tag $tag le tag à retirer au champion $champion
	 * @param \MVNerds\CoreBundle\Model\Champion $champion le champion auqul il faut retirer le tag $tag
	 * 
	 * @throws InvalidArgumentException exception levée lorsque le champion $champion et le tag $tag ne sont pas associés
	 */
	public function removeTagFromChampion(Tag $tag, Champion $champion)
	{
		$championTag = $this->findOneByChampionAndTag($champion, $tag);
		
		if (null === $championTag)
		{
			throw new InvalidArgumentException('ChampionTag with championId:' . $champion->getId() . ' and tagId: ' . $tag->getId() . 'does not exist!');
		}
		
		$championTag->delete();
	}
	
	/**
	 * Permet de retirer plusieurs tags associés à un champion
	 * 
	 * @param \MVNerds\CoreBundle\Model\Tag[] $tags les tags à retirer au champion $champion
	 * @param \MVNerds\CoreBundle\Model\Champion $champion le champion auqul il faut retirer les tags $tags
	 * 
	 * @throws InvalidArgumentException exception levée lorsque le tableau $tags fournis ne contient pas uniquement que des objets de type Tag
	 */
	public function removeTagsFromChampion(array $tags, Champion $champion)
	{
		if ( ! ObjectValidation::isObjectsInstanceof($tags, 'MVNerds\CoreBundle\Model\Tag') )
		{
			throw new InvalidArgumentException('The given tags array doesn\'t only contains MVNerds\CoreBundle\Model\Tag objects');
		}
		
		foreach($tags as $tag)
		{
			$this->removeTagToChampion($tag, $champion);
		}
	}
	
	/**
	 * Renvoie un tableau de champions ayants dans leurs tags le tag $tag
	 * 
	 * @param \MVNerds\CoreBundle\Model\Tag $tag le tag pour lequel on veut faire correspondre des champions
	 * 
	 * @return Champion[] retourne un tableau qui contient tous les champions ayant dans leur tags $tag
	 */
	public function findChampionsByTag(Tag $tag)
	{
		
		$championTags = ChampionTagQuery::create()
			->joinWith('Champion')
			->add(ChampionTagPeer::TAG_ID, $tag->getId())
		->find();
		
		$champions = array();
		
		/* @var $championTag ChampionTag */
		foreach ($championTags as $championTag)
		{			
			$champions[] = $championTag->getChampion();
		}
		
		return $champions;
	}
	
	/**
	 * Renvoie un tableau de champions ayants dans leurs tags le tag $tag
	 * 
	 * @param \MVNerds\CoreBundle\Model\Tag[] $tags le tag pour lequel on veut faire correspondre des champions
	 * 
	 * @throws InvalidArgumentException exception levée lorsque le tableau $tags fournis ne contient pas uniquement que des objets de type Tag
	 * 
	 * @return PropelCollection<MVNerds\CoreBundle\Model\Champion> retourne un objet PropelCollection qui contient
	 * tous les champions ayant parmi leurs tags les éléments présents dans $tags
	 */
	public function findChampionsByTags(array $tags)
	{		
		//On verifie que $tags soit bien un array de tags
		if ( ! ObjectValidation::isObjectsInstanceof($tags, 'MVNerds\CoreBundle\Model\Tag') )
		{
			throw new InvalidArgumentException('The given tags array doesn\'t only contains MVNerds\CoreBundle\Model\Tag objects');
		}
		
		$tagIds = array();
		
		/* @var $tag Tag */
		foreach ($tags as $tag)
		{
			$tagIds[] = $tag->getId();
		}
		
		$championTags = ChampionTagQuery::create()
			->joinWith('Champion')
			->add(ChampionTagPeer::TAG_ID, $tagIds, \Criteria::IN)
		->find();
		
		$champions = array();
		
		/* @var $championTag ChampionTag */
		foreach ($championTags as $championTag)
		{			
			$champions[] = $championTag->getChampion();
		}
		
		return $champions;
	}
	
	/**
	 * Permet de récupérer un objet championTag en fonction d'un champion et d'un tag
	 * 
	 * @param \MVNerds\CoreBundle\Model\Champion $champion
	 * @param \MVNerds\CoreBundle\Model\Tag $tag
	 * 
	 * @return ChampionTag Renvoie un champion tag aillant pour championId et TagId les id de $champion et $tag
	 */
	public function findOneByChampionAndTag(Champion $champion, Tag $tag)
	{
		return ChampionTagQuery::create()
			->add(ChampionTagPeer::CHAMPION_ID, $champion->getId())
			->add(ChampionTagPeer::TAG_ID, $tag->getId())
		->findOne();
	}
	
	/**
	 * Permet de faire persister en base de données le championTag $championTag
	 * 
	 * @param \MVNerds\CoreBundle\Model\Champion $champion l'objet Champion à associer avec le tag $tag
	 * @param \MVNerds\CoreBundle\Model\Tag $tag l'objet Tag à associer avec le champion $champion
	 */
	public function save(ChampionTag $championTag)
	{
		$championTag->save();
	}

}
