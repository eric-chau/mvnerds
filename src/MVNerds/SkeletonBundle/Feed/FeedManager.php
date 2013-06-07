<?php

namespace MVNerds\SkeletonBundle\Feed;

use \Criteria;

use MVNerds\CoreBundle\Exception\ObjectNotFoundException;
use MVNerds\CoreBundle\Model\Feed;
use MVNerds\CoreBundle\Model\FeedQuery;
use MVNerds\CoreBundle\Model\FeedPeer;

class FeedManager
{
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
	 * @return \PropelCollection<MVNerds\CoreBundle\Model\Feed> retourne un objet PropelCollection qui contient
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
	 * @param array $superTags un tableau contenant des superTags sous forme de chaines de caractères
	 * 
	 * @return \PropelCollection<MVNerds\CoreBundle\Model\Feed>
	 */
	public function findBySuperTags(array $superTags) 
	{
		return FeedQuery::create()
			->joinWith('FeedSuperTag FST')
			->joinWith('FST.SuperTag ST')
			->add('ST.unique_name', $superTags, Criteria::IN)
			->OrderBy(FeedPeer::CREATE_TIME, Criteria::DESC)
		->find();
	}
}