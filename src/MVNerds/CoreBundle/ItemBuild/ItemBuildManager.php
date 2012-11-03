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
				
//			->joinWith('ItemRelatedByItem1Id i1', \Criteria::LEFT_JOIN)
//			->joinWith('i1.ItemI18n i18n1', \Criteria::LEFT_JOIN)
//			->joinWith('i1.ItemPrimaryEffect ipe1', \Criteria::LEFT_JOIN)
//			->joinWith('i1.ItemSecondaryEffect', \Criteria::LEFT_JOIN)
//			->joinWith('ipe1.PrimaryEffect pe1', \Criteria::LEFT_JOIN)
//				
//			->joinWith('ItemRelatedByItem2Id i2', \Criteria::LEFT_JOIN)
//			->joinWith('i2.ItemI18n i18n2', \Criteria::LEFT_JOIN)
//			->joinWith('i2.ItemPrimaryEffect ipe2', \Criteria::LEFT_JOIN)
//			->joinWith('i2.ItemSecondaryEffect ise2', \Criteria::LEFT_JOIN)
//			->joinWith('ipe2.PrimaryEffect pe2', \Criteria::LEFT_JOIN)
//				
//			->joinWith('ItemRelatedByItem3Id i3', \Criteria::LEFT_JOIN)
//			->joinWith('i3.ItemI18n i18n3', \Criteria::LEFT_JOIN)
//			->joinWith('i3.ItemPrimaryEffect ipe3', \Criteria::LEFT_JOIN)
//			->joinWith('i3.ItemSecondaryEffect ise3', \Criteria::LEFT_JOIN)
//			->joinWith('ipe3.PrimaryEffect pe3', \Criteria::LEFT_JOIN)
//				
//			->joinWith('ItemRelatedByItem4Id i4', \Criteria::LEFT_JOIN)
//			->joinWith('i4.ItemI18n i18n4', \Criteria::LEFT_JOIN)
//			->joinWith('i4.ItemPrimaryEffect ipe4', \Criteria::LEFT_JOIN)
//			->joinWith('i4.ItemSecondaryEffect ise4', \Criteria::LEFT_JOIN)
//			->joinWith('ipe4.PrimaryEffect pe4', \Criteria::LEFT_JOIN)
//				
//			->joinWith('ItemRelatedByItem5Id i5', \Criteria::LEFT_JOIN)
//			->joinWith('i5.ItemI18n i18n5', \Criteria::LEFT_JOIN)
//			->joinWith('i5.ItemPrimaryEffect ipe5', \Criteria::LEFT_JOIN)
//			->joinWith('i5.ItemSecondaryEffect ise5', \Criteria::LEFT_JOIN)
//			->joinWith('ipe5.PrimaryEffect pe5', \Criteria::LEFT_JOIN)
//				
//			->joinWith('ItemRelatedByItem6Id i6', \Criteria::LEFT_JOIN)
//			->joinWith('i6.ItemI18n i18n6', \Criteria::LEFT_JOIN)
//			->joinWith('i6.ItemPrimaryEffect ipe6', \Criteria::LEFT_JOIN)
//			->joinWith('i6.ItemSecondaryEffect ise6', \Criteria::LEFT_JOIN)
//			->joinWith('ipe6.PrimaryEffect pe6', \Criteria::LEFT_JOIN)
				
//			->joinWith('ItemRelatedByItem2Id i2', \Criteria::LEFT_JOIN)
//			->joinWith('ItemRelatedByItem3Id i3', \Criteria::LEFT_JOIN)
//			->joinWith('ItemRelatedByItem4Id i4', \Criteria::LEFT_JOIN)
//			->joinWith('ItemRelatedByItem5Id i5', \Criteria::LEFT_JOIN)
//			->joinWith('ItemRelatedByItem6Id i6', \Criteria::LEFT_JOIN)

			->joinWith('ChampionItemBuild', \Criteria::LEFT_JOIN)
			->joinWith('ChampionItemBuild.GameMode', \Criteria::LEFT_JOIN)
			->joinWith('ChampionItemBuild.Champion', \Criteria::LEFT_JOIN)
			->joinWith('Champion.ChampionI18n', \Criteria::LEFT_JOIN)
		->find();

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
}
