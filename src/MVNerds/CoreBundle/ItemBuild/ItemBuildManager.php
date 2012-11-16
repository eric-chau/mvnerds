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
			->joinWith('ChampionItemBuild', \Criteria::LEFT_JOIN)
			->joinWith('ChampionItemBuild.GameMode', \Criteria::LEFT_JOIN)
			->joinWith('ChampionItemBuild.Champion', \Criteria::LEFT_JOIN)
			->joinWith('Champion.ChampionI18n', \Criteria::LEFT_JOIN)
			->add(ItemBuildPeer::SLUG, $slug)
		->findOne();

		if (null === $itemBuild)
		{
			throw new InvalidArgumentException('No item build with slug:' . $slug . '!');
		}

		return $itemBuild;
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
	
	public function findByUserId($userId)
	{
		$itemBuilds = ItemBuildQuery::create()
			->joinWith('ChampionItemBuild', \Criteria::LEFT_JOIN)
			->joinWith('ChampionItemBuild.GameMode', \Criteria::LEFT_JOIN)
			->joinWith('ChampionItemBuild.Champion chp', \Criteria::LEFT_JOIN)
			->joinWith('chp.ChampionI18n', \Criteria::LEFT_JOIN)
			->add(ItemBuildPeer::USER_ID, $userId)
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
	
	/**
	 * Récupère les builds les plus récents
	 */
	public function findLatestBuilds($championItemBuildsCriteria, $itemsCriteria)
	{
		$itemBuilds = ItemBuildQuery::create()
			->add(ItemBuildPeer::STATUS, ItemBuildPeer::STATUS_PUBLIC)
			->orderById(\Criteria::DESC)
			->limit(5)
		->find();
		
		$itemBuilds->populateRelation('ChampionItemBuild', $championItemBuildsCriteria);
		$itemBuilds->populateRelation('ItemRelatedByItem1Id', $itemsCriteria);
		$itemBuilds->populateRelation('ItemRelatedByItem2Id', $itemsCriteria);
		$itemBuilds->populateRelation('ItemRelatedByItem3Id', $itemsCriteria);
		$itemBuilds->populateRelation('ItemRelatedByItem4Id', $itemsCriteria);
		$itemBuilds->populateRelation('ItemRelatedByItem5Id', $itemsCriteria);
		$itemBuilds->populateRelation('ItemRelatedByItem6Id', $itemsCriteria);
		
		if (null === $itemBuilds)
		{
			throw new InvalidArgumentException('No item build found !');
		}

		return $itemBuilds;
	}	
	
	/**
	 * Récupère les builds les plus téléchargés
	 */
	public function findMostDownloadedBuilds($championItemBuildsCriteria, $itemsCriteria)
	{
		$itemBuilds = ItemBuildQuery::create()
			->add(ItemBuildPeer::STATUS, ItemBuildPeer::STATUS_PUBLIC)
			->orderByDownload(\Criteria::DESC)
			->limit(5)
		->find();
		
		$itemBuilds->populateRelation('ChampionItemBuild', $championItemBuildsCriteria);
		$itemBuilds->populateRelation('ItemRelatedByItem1Id', $itemsCriteria);
		$itemBuilds->populateRelation('ItemRelatedByItem2Id', $itemsCriteria);
		$itemBuilds->populateRelation('ItemRelatedByItem3Id', $itemsCriteria);
		$itemBuilds->populateRelation('ItemRelatedByItem4Id', $itemsCriteria);
		$itemBuilds->populateRelation('ItemRelatedByItem5Id', $itemsCriteria);
		$itemBuilds->populateRelation('ItemRelatedByItem6Id', $itemsCriteria);
		
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
	 * Rends tous les item builds qui contiennent l item ayant pour id $id obsoletes
	 */
	public function findByItemId($id)
	{
		$itemBuilds = ItemBuildQuery::create()
				->where(
					ItemBuildPeer::ITEM1_ID . ' = '. $id . ' OR ' .
					ItemBuildPeer::ITEM2_ID . ' = '. $id . ' OR ' .
					ItemBuildPeer::ITEM3_ID . ' = '. $id . ' OR ' .
					ItemBuildPeer::ITEM4_ID . ' = '. $id . ' OR ' .
					ItemBuildPeer::ITEM5_ID . ' = '. $id . ' OR ' .
					ItemBuildPeer::ITEM6_ID . ' = '. $id
				)
		->find();

		if (null === $itemBuilds)
		{
			throw new InvalidArgumentException('No item build where item id = ' . $id . '!');
		}

		return $itemBuilds;
	}
}
