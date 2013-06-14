<?php

namespace MVNerds\SkeletonBundle\Feed;

use \Criteria;
use \PropelObjectCollection;
use \PropelModelPager;

use MVNerds\CoreBundle\Exception\ObjectNotFoundException;
use MVNerds\CoreBundle\Model\Feed;
use MVNerds\CoreBundle\Model\FeedQuery;
use MVNerds\CoreBundle\Model\FeedPeer;
use MVNerds\CoreBundle\Model\FeedSuperTag;
use MVNerds\CoreBundle\Model\FeedSuperTagQuery;
use MVNerds\CoreBundle\Model\User;
use MVNerds\SkeletonBundle\SuperTag\SuperTagManager;

class FeedManager
{
	/**
	 * Le nombre maximum de Feeds à récupérer avec une requête.
	 */
	const MAX_FEEDS_PER_PAGE = 3;
	
	/**
	 * La valeur valide minimum du champ rating d'un feed afin qu'il soit affiché
	 */
	const MIN_VALID_RATING = -10;
	
	/**
	 * @var SuperTagManager
	 */
	private $superTagManager;
	
	
	
	/**
	 * @param integer $id l'id du feed à récupérer
	 * @throws ObjectNotFoundException si aucun feed n'est associé à l'id $id
	 * 
	 * @return Feed l'objet Feed qui correspond à l'id $id 
	 */
	public function findById($id)
	{
		$feed = FeedQuery::create()->findPk($id);

		if (!$feed instanceof Feed) {
			throw new ObjectNotFoundException('Le Feed ayant pour ID :' . $id . ' est introuvable');
		}

		return $feed;
	}
	
	/**
	 * @param string $slug le slug du feed à récupérer
	 * @throws ObjectNotFoundException si aucun feed n'est associé au slug $slug
	 * 
	 * @return Feed l'objet Feed qui correspond au slug $slug 
	 */
	public function findBySlug($slug)
	{
		$feed = FeedQuery::create()
			->add(FeedPeer::SLUG, $slug)
		->findOne();

		if (!$feed instanceof Feed) {
			throw new ObjectNotFoundException('Le Feed ayant pour Slug :' . $slug . ' est introuvable');
		}

		return $feed;
	}
	
	/**
	 * @param string $title le titre du feed à récupérer
	 * @throws ObjectNotFoundException si aucun feed n'est associé au titre $title
	 * 
	 * @return Feed l'objet Feed qui correspond au slug $slug 
	 */
	public function findByTitle($title)
	{
		$feed = FeedQuery::create()
			->add(FeedPeer::TITLE, $title, Criteria::LIKE)
		->findOne();

		if (!$feed instanceof Feed) {
			throw new ObjectNotFoundException('Le Feed ayant pour Titre :' . $title . ' est introuvable');
		}

		return $feed;
	}
	
	/**
	 * @param integer $id l'id du feed à supprimer
	 * @throws ObjectNotFoundException si aucun feed n'est associé à l'id $id
	 */
	public function deleteById($id)
	{
		$feed = FeedQuery::create()->findPk($id);

		if (!$feed instanceof Feed) {
			throw new ObjectNotFoundException('Le Feed ayant pour ID :' . $id . ' est introuvable');
		}

		$feed->delete();
	}
	
	/**
	 * @param string $slug le slug du feed à supprimer
	 * @throws ObjectNotFoundException si aucun feed n'est associé au slug $slug
	 */
	public function deleteBySlug($slug)
	{
		$feed = FeedQuery::create()
			->add(FeedPeer::SLUG, $slug)
		->findOne();

		if (!$feed instanceof Feed) {
			throw new ObjectNotFoundException('Le Feed ayant pour Slug :' . $slug . ' est introuvable');
		}

		$feed->delete();
	}

	/**
	 * @return PropelObjectCollection|Feed[] retourne un objet PropelObjectCollection qui contient
	 * tous les feed de la base de données
	 */
	public function findAll()
	{
		return FeedQuery::create()
			->joinWith('User')
			->OrderBy(FeedPeer::CREATE_TIME, Criteria::DESC)
		->find();
	}
	
	/**
	 * @return PropelObjectCollection|Feed[] retourne un objet PropelObjectCollection qui contient les
	 * MAX_FEEDS_PER_PAGE derniers Feeds (Les feeds ayant un rating trop faible sont ignorés)
	 */
	public function findLatest()
	{
		return FeedQuery::create()
			->joinWith('User')
			->add(FeedPeer::RATING, self::MIN_VALID_RATING, Criteria::GREATER_EQUAL)
			->limit(self::MAX_FEEDS_PER_PAGE)
			->OrderBy(FeedPeer::CREATE_TIME, Criteria::DESC)
		->find();
	}
	
	/**
	 * @param array $superTags un tableau contenant des superTags sous forme de chaines de caractères
	 * 
	 * @return PropelObjectCollection|Feed[] Une collection Propel de feeds 
	 * associés aux SuperTags passés en paramètre
	 */
	public function findBySuperTags(array $superTags, $page = 1, $maxPerPage = self::MAX_FEEDS_PER_PAGE) 
	{
		return FeedQuery::create()
			->joinWith('User')
			->joinFeedSuperTag()
			->useFeedSuperTagQuery()
				->joinSuperTag()
			->endUse()
			->add('super_tag.unique_name', $superTags, Criteria::IN)
			->add(FeedPeer::RATING, self::MIN_VALID_RATING, Criteria::GREATER_EQUAL)
			->OrderBy(FeedPeer::CREATE_TIME, Criteria::DESC)
		->paginate($page, $maxPerPage)->getResults();
	}
	
	public function createFeed(Feed $feed, User $user, $superTagsStr)
	{
		$feed->setUser($user);
		$feedType = $feed->getTypeUniqueName();
		$feed->setTypeUniqueName($feedType->getUniqueName());
		
		$superTags = explode(',', $superTagsStr);
		
		foreach($this->superTagManager->findAllByLabels($superTags) as $superTag) {
			$feedSuperTag = new FeedSuperTag();
			$feedSuperTag->setSuperTag($superTag);
			$feedSuperTag->setFeed($feed);
			
			$feed->addFeedSuperTag($feedSuperTag);
		}
		
		// Finally
		$feed->save();
	}
	
	public function setSuperTagManager(SuperTagManager $superTagManager)
	{
		$this->superTagManager = $superTagManager;
	}
}