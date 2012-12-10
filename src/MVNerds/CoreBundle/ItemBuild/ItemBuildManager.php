<?php

namespace MVNerds\CoreBundle\ItemBuild;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Session\Session;

use MVNerds\CoreBundle\Model\Item;
use MVNerds\CoreBundle\Model\ItemBuild;
use MVNerds\CoreBundle\Model\ItemBuildQuery;
use MVNerds\CoreBundle\Model\ItemBuildPeer;

class ItemBuildManager
{
	private $userLocale;
	
	/**
	 * Récupère un objet ItemBuild à partir de son identifiant $id 
	 * 
	 * @param integer $id l'id de l'item build dont on souhaite récupérer l'objet ItemBuild associé 
	 * @return MVNerds\CoreBundle\Model\ItemBuild l'objet ItemBuild qui correspond à l'id $id 
	 * @throws InvalidArgumentException exception levé si aucun item build n'est associé à l'id $id
	 */
	public function findOneById($id)
	{
		$itemBuild = ItemBuildQuery::create()
			->joinItemRelatedByItem1Id()
			->joinItemRelatedByItem2Id()
			->joinItemRelatedByItem3Id()
			->joinItemRelatedByItem4Id()
			->joinItemRelatedByItem5Id()
			->joinItemRelatedByItem6Id()
			->joinWith('ChampionItemBuild', \Criteria::LEFT_JOIN)
			->joinWith('ChampionItemBuild.GameMode', \Criteria::LEFT_JOIN)
			->joinWith('ChampionItemBuild.Champion', \Criteria::LEFT_JOIN)
			->joinWith('Champion.ChampionI18n', \Criteria::LEFT_JOIN)
			->add(ItemBuildPeer::ID, $id)
		->findOne();

		if (null === $itemBuild)
		{
			throw new InvalidArgumentException('No item build with id:' . $id . '!');
		}
		return $itemBuild;
	}
	
	/**
	 * Récupère un objet ItemBuild à partir de son slug $slug
	 * 
	 * @param string $slug le slug de l'item build dont on souhaite récupérer l'objet ItemBuild associé 
	 * @return ItemBuild l'objet ItemBuild qui correspond au slug $slug
	 * @throws InvalidArgumentException exception levé si aucun item build n'est associé au slug $slug
	 */
	public function findOneBySlug($slug)
	{
		$itemBuild = ItemBuildQuery::create()
			->joinWith('User', \Criteria::LEFT_JOIN)
			->joinWith('ChampionItemBuild', \Criteria::LEFT_JOIN)
			->joinWith('ChampionItemBuild.GameMode', \Criteria::LEFT_JOIN)
			->joinWith('ChampionItemBuild.Champion', \Criteria::LEFT_JOIN)
			->joinWith('Champion.ChampionI18n', \Criteria::LEFT_JOIN)
			->joinWith('ItemBuildItems', \Criteria::LEFT_JOIN)
			->joinWith('ItemBuildItems.Item', \Criteria::LEFT_JOIN)
			->joinWith('Item.ItemI18n', \Criteria::LEFT_JOIN)
			->addJoinCondition('ItemI18n', 'ItemI18n.Lang = ?', $this->userLocale)
			->add(ItemBuildPeer::SLUG, $slug)
		->find();

		if (null === $itemBuild || null === $itemBuild[0])
		{
			throw new InvalidArgumentException('No item build with slug:' . $slug . '!');
		}

		return $itemBuild[0];
	}
	
	/**
	 * Récupère tous les item builds
	 * 
	 * @return ItemBuild l'objet ItemBuild qui correspond à l'id $id 
	 * @throws InvalidArgumentException exception levé si aucun item build n'est associé à l'id $id
	 */
	public function findAll()
	{
		$itemBuilds = ItemBuildQuery::create()
			->joinWith('ChampionItemBuild', \Criteria::LEFT_JOIN)
			->joinWith('ChampionItemBuild.GameMode', \Criteria::LEFT_JOIN)
			->joinWith('ChampionItemBuild.Champion chp', \Criteria::LEFT_JOIN)
			->joinWith('chp.ChampionI18n', \Criteria::LEFT_JOIN)
		->find();

		$items = \MVNerds\CoreBundle\Model\ItemQuery::create()
				->joinWith('ItemI18n', \Criteria::LEFT_JOIN)
				->joinWith('ItemPrimaryEffect', \Criteria::LEFT_JOIN)
				->joinWith('ItemPrimaryEffect.PrimaryEffect', \Criteria::LEFT_JOIN)
				->joinWith('PrimaryEffect.PrimaryEffectI18n', \Criteria::LEFT_JOIN)
				->joinWith('ItemSecondaryEffect', \Criteria::LEFT_JOIN)
				->joinWith('ItemSecondaryEffect.ItemSecondaryEffectI18n', \Criteria::LEFT_JOIN);
		
		$itemBuilds->populateRelation('ItemRelatedByItem1Id', $items);
		$itemBuilds->populateRelation('ItemRelatedByItem2Id', $items);
		$itemBuilds->populateRelation('ItemRelatedByItem3Id', $items);
		$itemBuilds->populateRelation('ItemRelatedByItem4Id', $items);
		$itemBuilds->populateRelation('ItemRelatedByItem5Id', $items);
		$itemBuilds->populateRelation('ItemRelatedByItem6Id', $items);
		
		if (null === $itemBuilds)
		{
			throw new InvalidArgumentException('No item build found !');
		}

		return $itemBuilds;
	}
	
	public function findAllPublic()
	{
		$itemBuilds = ItemBuildQuery::create()
			->add(ItemBuildPeer::STATUS, ItemBuildPeer::STATUS_PUBLIC)
			->joinWith('ChampionItemBuild', \Criteria::LEFT_JOIN)
			->joinWith('ChampionItemBuild.GameMode', \Criteria::LEFT_JOIN)
			->joinWith('ChampionItemBuild.Champion chp', \Criteria::LEFT_JOIN)
			->joinWith('chp.ChampionI18n', \Criteria::LEFT_JOIN)
			->orderByCreateTime('Desc')
		->find();
		
		if (null === $itemBuilds)
		{
			throw new InvalidArgumentException('No item build found !');
		}

		return $itemBuilds;
	}
	
	public function findByUserId($userId)
	{
		$itemBuilds = ItemBuildQuery::create()
			->joinWith('ChampionItemBuild', \Criteria::LEFT_JOIN)
			->joinWith('ChampionItemBuild.GameMode', \Criteria::LEFT_JOIN)
			->joinWith('ChampionItemBuild.Champion chp', \Criteria::LEFT_JOIN)
			->joinWith('chp.ChampionI18n', \Criteria::LEFT_JOIN)
			->add(ItemBuildPeer::USER_ID, $userId)
		->find();
		
		if (null === $itemBuilds)
		{
			throw new InvalidArgumentException('No item build found !');
		}

		return $itemBuilds;
	}	
	
	/**
	 * Récupère les builds les plus récents
	 */
	public function findLatestBuilds()
	{
		$itemBuilds = ItemBuildQuery::create()
			->add(ItemBuildPeer::STATUS, ItemBuildPeer::STATUS_PUBLIC)
			->orderById(\Criteria::DESC)
			->limit(5)
		->find();
		
		$championItemBuildsCriteria = \MVNerds\CoreBundle\Model\ChampionItemBuildQuery::create()
				->joinWith('GameMode')
				->joinWith('Champion')
				->joinWith('Champion.ChampionI18n');
		
		$itemBuilds->populateRelation('ChampionItemBuild', $championItemBuildsCriteria);
		
		if (null === $itemBuilds)
		{
			throw new InvalidArgumentException('No item build found !');
		}
		return $itemBuilds;
	}	
	
	/**
	 * Récupère les builds les plus téléchargés
	 */
	public function findMostDownloadedBuilds()
	{
		$itemBuilds = ItemBuildQuery::create()
			->add(ItemBuildPeer::STATUS, ItemBuildPeer::STATUS_PUBLIC)
			->orderByDownload(\Criteria::DESC)
			->limit(5)
		->find();
		
		$championItemBuildsCriteria = \MVNerds\CoreBundle\Model\ChampionItemBuildQuery::create()
				->joinWith('GameMode')
				->joinWith('Champion')
				->joinWith('Champion.ChampionI18n');
		
		$itemBuilds->populateRelation('ChampionItemBuild', $championItemBuildsCriteria);
		
		if (null === $itemBuilds)
		{
			throw new InvalidArgumentException('No item build found !');
		}

		return $itemBuilds;
	}	
	
	/**
	 * Permet de faire persister en base de données l'item $item
	 * 
	 * @param \MVNerds\CoreBundle\Model\Item $item l'objet Item à faire persister en base de données
	 */
	public function save(Item $item)
	{
		$item->save();
	}
	
	/**
	 * Rends tous les item builds qui contiennent l item ayant pour id $id 
	 */
	public function findByItemId($id)
	{
		$itemBuilds = ItemBuildQuery::create()
				->joinWithItemBuildItems()
				->add(\MVNerds\CoreBundle\Model\ItemBuildItemsPeer::ITEM_ID, $id)
		->find();

		if (null === $itemBuilds)
		{
			throw new InvalidArgumentException('No item build where item id = ' . $id . '!');
		}

		return $itemBuilds;
	}
	
	public function findItemBuildItemsByItemId($id)
	{
		$itemBuildItems = \MVNerds\CoreBundle\Model\ItemBuildItemsQuery::create()
				->add(\MVNerds\CoreBundle\Model\ItemBuildItemsPeer::ITEM_ID, $id)
		->find();

		if (null === $itemBuildItems)
		{
			throw new InvalidArgumentException('No item build items where item id = ' . $id . '!');
		}

		return $itemBuildItems;
	}
	
	public function countNbBuildsByUserId($id)
	{
		$nbItemBuilds = ItemBuildQuery::create()
				->add(ItemBuildPeer::USER_ID, $id)
		->count();

		return $nbItemBuilds;
	}
	
	public function setUserLocale(Session $session)
	{
		$locale = $session->get('locale', null);
		$this->userLocale = null === $locale? 'fr' : $locale;
	}
}
